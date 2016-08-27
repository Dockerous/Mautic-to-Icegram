<?php
use Mautic\Auth\ApiAuth;
use Mautic\MauticApi;


if (!defined('ABSPATH'))
    exit;
if (!class_exists('MTC_to_IG_Form_Menu')) {


    class MTC_to_IG_Form_Menu extends CJF_Base_Menu{

        var $options;
        var $menu_title;
        var $page_title;
        var $page_headline;
        private $accessTokenData;
        private $test = 1;
        private $error = '';

        function __construct($options) {
            /* Add Custom Admin Menu */
            $this->options = $options;
            $this->page_id = $this->options->prefix.'_forms';
            $this->menu_title = "Manage Forms";
            $this->page_title = "Manage Mautic Forms";
            $this->page_headline = "Manage Mautic Forms";
            add_action('admin_init', array($this, 'register_settings'));
            add_action('admin_init', array($this, 'authorize'));
            add_action('admin_menu', array($this, 'admin_menu'));
            parent::__construct($options,TRUE);
        }
        
        function admin_menu() {
            if(!empty($this->options->get_url)){
                flush_rewrite_rules();
            }
            add_submenu_page(
                $this->options->prefix.'_main',                  // Register this submenu with the menu defined above
                $this->page_title,          // The text to the display in the browser when this menu item is active
                $this->menu_title,                  // The text for this menu item
                'manage_options',            // Which type of users can see this menu
                $this->page_id,          // The unique ID - the slug - for this menu item
                array($this, 'settings_page')   // The function used to render the menu for this page to the screen
            );
        }

        function register_settings() {
            // Add the settings sections so we can add our
            // fields to it
            $section_1 = $this->options->prefix .'_settings_section';
            add_settings_section(
                $section_1,
                'Mautic Forms',
                array($this,'settings_section_output'),
                $this->page_id
            );
            
            // Add the fields with the names and function to use for the
            // settings, put it in their section
            add_settings_field(
                $this->options->prefix .'_update_forms',
                'Update Mautic Forms',
                array($this,'update_forms_output'),
                $this->page_id,
                $section_1
            );
            
            
            // Register our setting so that $_POST handling is done for us and
            // our callback function just has to echo the <input>
            register_setting($this->page_id, $this->page_id, array($this, 'validate'));
        }

        function validate($values){
            $option = get_option($this->options->prefix.'_forms');
            if(isset($option[$this->options->prefix .'_last_update'])){
                $values[$this->options->prefix .'_last_update'] = $option[$this->options->prefix .'_last_update'];
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
            $token = $this->options-accessToken;
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
                $option = get_option($this->options->prefix.'_forms');
                if(isset($option[$this->options->prefix .'_update_forms']) &&
                        $option[$this->options->prefix .'_update_forms'] == 1){
                    $option[$this->options->prefix .'_update_forms'] = 0;
                    if(isset($option[$this->options->prefix .'_last_update'])){
                        $last_update = new DateTime($option[$this->options->prefix .'_last_update']);
                    }else{
                        $last_update = FALSE;
                    }
                    $api = new MauticApi();
                    $formApi = $api->newApi('forms', $new_auth, $url."/api");
                    $forms = $formApi->getList('',0,0,'','DESC',TRUE);
                    //$this->test = $forms;
                    if (isset($forms['error'])) {
                        $this->options->add_notice($this->screen_id,'error', $forms['error']['code'] . ": " . $forms['error']['message']);
                    } else {
                        $dt_now = New DateTime('NOW');
                        $option[$this->options->prefix .'_last_update'] = $dt_now->format('Y-m-d H:i:s');
                        foreach($forms['forms'] as $f){
                            $dt = new DateTime($f['dateModified']);
                            if(!$last_update  || $dt > $last_update){
                            //Force update for testing
                            //if(TRUE){
                                $value = URLify::transliterate($f['name']);
                                $regex = "/[^0-9a-z]+/i";
                                $new_name = trim(preg_replace($regex, "", strtolower($value)));
                                $mautic_form = new MauticForm($this->options);
                                $mautic_form->load_by_value('form_id',$f['id']);
                                $mautic_form->content = $f['cachedHtml'];
                                $mautic_form->form_id = $f['id'];
                                $mautic_form->alias = $f['alias'];
                                $mautic_form->form_name = $new_name;
                                $mautic_form->update_db();
                                //$this->test[] = $mautic_form;
                            }
                        }
                        $this->options->add_notice($this->screen_id,'success', 'Your mautic forms were successfully updated!');
                    }
                    update_option($this->options->prefix.'_forms',$option);
                    $this->options->init();
                }
            }
        }
                
        function update_forms_output($args){
            $key = $this->options->prefix .'_update_forms';
            $subtext = '<p>Checking this box will update all forms from Mautic</p>';
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