<?php
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;


if (!defined('ABSPATH'))
    exit;
if (!class_exists('MTC_to_IG_Main_Menu')) {


    class MTC_to_IG_Main_Menu extends MTI_Base_Menu{

        var $options;
        var $menu_title;
        var $page_title;
        var $page_headline;
        private $accessTokenData;
        private $test = 1;

        function __construct($options) {
            /* Add Custom Admin Menu */
            $this->options = $options;
            $this->page_id = $options->prefix.'_main';
            $this->menu_title = "Mautic To Icegram";
            $this->page_title = "Mautic to Icegram";
            $this->page_headline = "Mautic to Icegram Settings";
            add_action('admin_init', array($this, 'register_settings'));
            add_action('admin_init', array($this, 'authorize'));
            add_action('admin_menu', array($this, 'admin_menu'));
            parent::__construct($options,TRUE);
        }
        
        function admin_menu() {
            if(!empty($this->options->get_url)){
                flush_rewrite_rules();
            }
            add_menu_page(
                $this->menu_title,          // The title to be displayed on the corresponding page for this menu
                $this->page_title,                  // The text to be displayed for this actual menu item
                'manage_options',            // Which type of users can see this menu
                $this->page_id,                  // The unique ID - that is, the slug - for this menu item
                array($this, 'settings_page'),// The name of the function to call when rendering the menu for this page
                '',
                '80.000000013424562245387101'
            );
            add_submenu_page(
                $this->page_id,                  // Register this submenu with the menu defined above
                $this->page_title,          // The text to the display in the browser when this menu item is active
                'Settings',                  // The text for this menu item
                'manage_options',            // Which type of users can see this menu
                $this->page_id,          // The unique ID - the slug - for this menu item
                array($this, 'settings_page')   // The function used to render the menu for this page to the screen
            );
        }

        function register_settings() {
            // Add the settings sections so we can add our
            // fields to it
            add_settings_section(
                $this->options->prefix .'_settings_section',
                'Settings',
                array($this,'settings_section_output'),
                $this->page_id
            );
            
            // Add the fields with the names and function to use for the
            // settings, put it in their section
            add_settings_field(
                $this->options->prefix .'_get_url',
                'Base URL',
                array($this,'get_url_output'),
                $this->page_id,
                $this->options->prefix .'_settings_section'
            );
            
            add_settings_field(
                $this->options->prefix .'_get_client_key',
                'Client Key',
                array($this,'client_key_output'),
                $this->page_id,
                $this->options->prefix .'_settings_section'
            );
            
            add_settings_field(
                $this->options->prefix .'_get_client_secret',
                'Client Secret',
                array($this,'client_secret_output'),
                $this->page_id,
                $this->options->prefix .'_settings_section'
            );
            
            add_settings_field(
                $this->options->prefix .'_accessToken',
                'Access Token',
                array($this,'accessToken_output'),
                $this->page_id,
                $this->options->prefix .'_settings_section'
            );
            
            add_settings_field(
                $this->options->prefix .'_accessTokenSecret',
                'Access Token Secret',
                array($this,'accessTokenSecret_output'),
                $this->page_id,
                $this->options->prefix .'_settings_section'
            );
            
            add_settings_field(
                $this->options->prefix .'_reset_token',
                'Reset Your Access Token',
                array($this,'reset_token_output'),
                $this->page_id,
                $this->options->prefix .'_settings_section'
            );
            
            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting($this->page_id, $this->page_id, array($this, 'validate'));
        }

        function validate($values){
            if(isset($values[$this->options->prefix .'_reset_token'])&&
                    $values[$this->options->prefix .'_reset_token'] == 1){
                unset($values[$this->options->prefix .'_reset_token']);
                $values[$this->options->prefix .'_accessToken'] = '';
                $values[$this->options->prefix .'_accessTokenSecret'] = '';
            }
            return $values;
        }
    
        function authorize(){
            /*
             * Check if current screen is My Admin Page
             * Don't add help tab if it's not
             */
            if (!session_id()){
                session_start();
            }
            $page_id = filter_input(INPUT_GET, 'page');
            if ( $page_id != $this->page_id ){
                return;
            }
            
            $url = $this->options->get_url;
            $client_key = $this->options->get_client_key;
            $client_secret = $this->options->get_client_secret;
            $token = $this->options->accessToken;
            $token_secret = $this->options->accessTokenSecret;
            if(!empty($url) && !empty($client_key) && !empty($client_secret) ){
                $settings = array(
                    'baseUrl' => $url,
                    'clientKey' => $client_key,
                    'clientSecret' => $client_secret,
                    'callback' => admin_url( 'admin.php?page='. $this->options->prefix.'_main'), 
                    'version' => 'OAuth1a'
                );
                if (!empty($token) && !empty($token_secret)) {
                    $settings['accessToken']        = $token ;
                    $settings['accessTokenSecret']  = $token_secret;
                }
                $auth = new ApiAuth();
                $new_auth = $auth->newAuth($settings);
              
                try {
                     
                    if ($new_auth->validateAccessToken()) {

                        // Obtain the access token returned; call accessTokenUpdated() to catch if the token was updated via a
                        // refresh token
                        // $accessTokenData will have the following keys:
                        // For OAuth1.0a: access_token, access_token_secret, expires
                        // For OAuth2: access_token, expires, token_type, refresh_token

                        if ($new_auth->accessTokenUpdated()) {
                            $this->accessTokenData = $new_auth->getAccessTokenData();
                            //store access token data however you want
                            if(is_array($this->accessTokenData) && 
                                    isset($this->accessTokenData['access_token']) &&
                                    isset($this->accessTokenData['access_token_secret']) ){
                                $option = get_option($this->options->prefix.'_main');
                                $option[$this->options->prefix .'_accessToken'] = $this->accessTokenData['access_token'];
                                $option[$this->options->prefix .'_accessTokenSecret'] = $this->accessTokenData['access_token_secret'];
                                update_option($this->options->prefix.'_main',$option);
                                $this->options->init();
                                session_unset();
                            }
                        }
                    }else{
                        session_unset();
                    }
                } catch (Exception $e) {
                    // Do Error handling
                }
            }
        }
                
        function get_url_output($args){
            $key = $this->options->prefix .'_get_url';
            $subtext = '<p>Enter the URL to your Mautic instance without the trailing slash "/" e.g. http://www.example.com/mautic.</p>';
            $this->output_input($key,$subtext,60);
        }

        function client_key_output($args){
            $key = $this->options->prefix .'_get_client_key';
            $subtext = '<p>Enter your Mautic client key.</p>';
            $this->output_input($key,$subtext,60);
        }

        function client_secret_output($args){
            $key = $this->options->prefix .'_get_client_secret';
            $subtext = '<p>Enter your Mautic client secret.</p>';
            $this->output_input($key,$subtext,60);
        }

        function accessToken_output($args){
            $key = $this->options->prefix .'_accessToken';
            $subtext = '<p>This is your access token.</p>';
            $this->output_input($key,$subtext,60,TRUE);
            $this->output_hidden($key);
        }

        function accessTokenSecret_output($args){
            $key = $this->options->prefix .'_accessTokenSecret';
            $subtext = '<p>This is your access token secret.</p>';
            $this->output_input($key,$subtext,60,TRUE);
            $this->output_hidden($key);
        }

        function reset_token_output($args){
            $key = $this->options->prefix .'_reset_token';
            $subtext = '<p>Checking this box will empty your access token and access token secret'.
                    ' and redirect you to Mautic to reauthorize the app.</p>';
            $this->output_checkbox($key,$subtext);
        }

        function settings_section_output() {
               
        }

        function settings_page() {
            if (!current_user_can('manage_options')) {
                wp_die('You do not have sufficient permissions to access this page.');
            }
            $this->open($this->page_headline);
            ?>
                        <form method="post" action="options.php">
                            <?php settings_fields( $this->page_id ); ?>
                            <?php do_settings_sections( $this->page_id ); ?>          
                            <?php submit_button(); ?>
                        </form>
    <?php
                                $this->close();
        }

    }

}