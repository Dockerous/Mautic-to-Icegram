<?php


class Post_Type_Mautic_Form extends MTI_Post_Type_Base{
    
    private $load_js = false;
    private $mautic_forms = array();
        
    function __construct($options) {
        $this->set_label('mautic_form');
        parent::__construct($options);
        $this->url = $this->options->get_url;
        $this->client_key = $this->options->get_client_key;
        $this->client_secret = $this->options->get_client_secret;
        $this->token = $this->options-accessToken;
        $this->token_secret = $this->options->accessTokenSecret;
        add_shortcode('mautic-icegram', array($this, 'execute_shortcode'));
        add_filter('add_icegram_script',array($this,'add_mautic_script'),50);
        add_action('wp_footer',array($this,'mautic_form_js'), 500);
        add_action('wp_print_styles',array($this,'print_css'),500);
    }
    
    function print_css(){
        ?>
<style type="text/css">
    .mauticform_wrapper::before{display:none;}
    .mauticform-checkboxgrp-checkbox {
        width: 15% !important;
        float: left;
        margin-right: .5em !important;
    }
    .mauticform-checkboxgrp-row{
        padding: .2em 0;
    }
    .mauticform-errormsg{
        color:red;
    }
</style>
        <?php
    }

    function init() {
        $this->set_post_type_name();
        $this->set_post_capability_type();
        $this->set_post_type_label('Mautic Form');
	$labels = array(
		'name' => _x($this->get_post_type_label().'s', 'post type general name'),
		'singular_name' => _x($this->get_post_type_label(), 'post type singular name'),
		'add_new' => _x('Add New', $this->get_post_type_label()),
		'add_new_item' => __('Add New '.$this->get_post_type_label()),
		'edit_item' => __('Edit '.$this->get_post_type_label()),
		'new_item' => __('New '.$this->get_post_type_label()),
		'view_item' => __('View '.$this->get_post_type_label()),
		'search_items' => __('Search '.$this->get_post_type_label()),
		'not_found' =>  __('Nothing found'),
		'not_found_in_trash' => __('Nothing found in Trash'),
		'parent_item_colon' => ''
	);
 
        $args = array(
            'label' => $this->get_post_type_label(),
            'labels' => $labels,
            'supports' => False,
            'hierarchical' => false,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 5,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => false,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'rewrite' => false,
            'capability_type' => 'page',
        );
 

	register_post_type( $this->get_post_type_name() , $args );
                
    }
    
    function mautic_form_js(){
        if($this->load_js){
            ?>
<script>
                var add_mautic_forms = <?php echo json_encode($this->mautic_forms)?>;
                jQuery("body").on('mautic_loaded', function(){
                    if(icegram_data['messages']){
                        console.log('Messages');
                        jQuery.each(icegram_data['messages'],function(index, message){
                            if(message.id){
                                console.log(message.id);
                                if(jQuery('#icegram_message_'+message.id).has('.mauticform_wrapper').length){
                                    console.log('Found Mautic Form');
                                    mautic_form = jQuery('#icegram_message_'+message.id).find('.mauticform_wrapper').data("formid");
                                    ig_data = jQuery('#icegram_message_'+message.id).find('.ig_data');
                                    console.log(mautic_form);
                                    console.log(JSON.stringify(add_mautic_forms));
                                    jQuery('#icegram_message_'+message.id).find('.mauticform-button').addClass('ig_button').css('display','block');
                                    jQuery('#icegram_message_'+message.id).addClass('ig_form_'+add_mautic_forms[mautic_form]);
                                    jQuery('#'+mautic_form).addClass('ig_form_container').addClass('layout_'+add_mautic_forms[mautic_form]);
                                    jQuery('#icegram_message_'+message.id).addClass('ig_form_style_4');
                                    if(add_mautic_forms[mautic_form] == "left"){
                                        jQuery('#'+mautic_form).detach().insertBefore(ig_data);
                                    }else if(add_mautic_forms[mautic_form] == "right" ){
                                        //jQuery('#icegram_message_'+message.id).addClass('ig_form_style_4');
                                        jQuery('#'+mautic_form).detach().insertAfter(ig_data).addClass('layout_bottom');
                                    }else if(add_mautic_forms[mautic_form] == "bottom"){
                                        jQuery('#'+mautic_form).detach().insertAfter(ig_data).addClass('layout_right');
                                    }
                                }else{
                                    console.log('No Mautic Form');
                                }
                            }else{
                                console.log('Fail');
                            }
                        });
                    }else{
                        console.log('No Messages');
                    }
                });
            </script>
            <?php
        }
    }
            
    function add_mautic_script($scripts){
        $scripts[] = plugins_url( '/', __FILE__ ) . 'assets/js/mautic.php';
        return $scripts;
    }


    function execute_shortcode($atts = array()) {
        $this->load_js = TRUE;
        $loadable = array('alias','form_id');
        $a = shortcode_atts( array(
                'alias' => FALSE,
                'form_id' => FALSE,
                'location' => 'inline'
            ), $atts );
        $form = new MauticForm($this->options);
        foreach($loadable as $k){
            $v = $a[$k];
            if($v){
                $form->load_by_value($k, $v);
                continue;
            }
        }
        $html = '';
        if(!empty($form->key_value)){
            
            $html = $form->content; 
            switch ($a['location']){
                case 'bottom':
                    $this->mautic_forms['mauticform_wrapper_'.$form->form_name] = "bottom";
                    break;
                case 'left':
                    $this->mautic_forms['mauticform_wrapper_'.$form->form_name] = "left";
                    break;
                case 'right':
                    $this->mautic_forms['mauticform_wrapper_'.$form->form_name] = "right";
                    break;
                default :
                    $this->mautic_forms['mauticform_wrapper_'.$form->form_name] = "inline";
                    break;
            }
        }
        return $html;
        
    }
    
    function meta_boxes_add() {
        add_meta_box(
                $this->options->prefix.'_form_info', // Unique ID
                esc_html__('Form Info'), // Title
                array($this, 'meta_box_form_info'), // Callback function
                $this->get_post_type_name(), // Admin page (or post type)
                'normal', // Context
                'core'      // Priority
        );
        add_meta_box(
                $this->options->prefix.'_html', // Unique ID
                esc_html__('HTML'), // Title
                array($this, 'meta_box_html'), // Callback function
                $this->get_post_type_name(), // Admin page (or post type)
                'normal', // Context
                'core'      // Priority
        );
    }
    
    function meta_box_form_info($object, $box) {
        $form_id = get_post_meta($object->ID, $this->options->prefix . "_form_id", true);
        $form_name = get_post_meta($object->ID, $this->options->prefix . "_form_name", true);
        echo "Alias: ".$object->post_title."<br>";
        echo "Form ID: ".$form_id."<br>";
        echo "Form Slug: ".$object->post_name."<br>";
        echo "Form Name: ".$form_name."<br>";
    }

    function meta_box_html($object, $box) {
        echo htmlentities($object->post_content);
    }

    
}
