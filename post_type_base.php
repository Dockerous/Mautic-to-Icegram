<?php

abstract class MTI_Post_Type_Base{
    
    /** Capability type to use when registering the custom post type. */
    private $post_capability_type;

    /** Name to use when registering the custom post type. */
    private $post_type_name;

    /** Label to identify post type */
    private $post_type_label;
    
    private $label = '';
    
    function __construct($options) {
        $this->options = $options;
        if(!empty($this->label)){
            add_action('init', array($this, 'init'));
            add_action('load-post.php', array($this, 'meta_boxes_setup'));
            add_action('load-post-new.php', array($this, 'meta_boxes_setup'));
            add_action('admin_enqueue_scripts', array($this, 'my_admin_scripts'));
            add_action('wp_enqueue_scripts', array($this, 'scripts_styles'));
        }
    }

    final function get_post_capability_type() {
        return $this->post_capability_type;
    }

    final function get_post_type_name() {
        return $this->post_type_name;
    }

    final function get_post_type_label() {
        return $this->post_type_label;
    }
    
    final protected function set_label($label){
        $this->label = $label;
    }

    final protected function set_post_capability_type($new_val = 'post') {
        $this->post_capability_type = apply_filters($this->label.'_post_capability_type', $new_val);
    }

    final protected function set_post_type_name($new_val = '') {
        if($new_val == ''){
            $new_val = $this->label;
        }
        $this->post_type_name = apply_filters($this->label.'_post_type_name', $new_val);
    }

    final protected function set_post_type_label($new_val = '') {
        if($new_val == ''){
            $new_val = ucfirst($this->label);
        }
        $this->post_type_label = apply_filters($this->label.'_post_type_label', $new_val);
    }
    
    final protected function start_init(){
        $this->set_post_type_name();
        $this->set_post_capability_type();
        $this->set_post_type_label();
    }

    abstract protected function init();
    
    function meta_boxes_add() {

    }
    
    function meta_boxes_setup() {
        add_action('add_meta_boxes', array($this, 'meta_boxes_add'));
        add_action('save_post_' . $this->get_post_type_name(), array($this, 'save_post'), 10, 2);
        add_action('pre_post_update', array($this, 'save_post'), 10, 2);
    }
    
    private function valid_save($post_id, $post){
        $post = get_post($post_id);
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return FALSE;
        }
        if ($post->post_type != $this->get_post_type_name()){
            return FALSE;
        }

        $nonce = filter_input(INPUT_POST, $this->get_post_type_name().'_nonce');
        if (empty($nonce) || !wp_verify_nonce($nonce, basename(__FILE__))) {
            return FALSE;
        }

        $post_type = get_post_type_object($post->post_type);
        if (!current_user_can($post_type->cap->edit_post, $post_id)) {
            return FALSE;
        }
    }

    function save_post($post_id, $post) {
        return $this->valid_save($post_id, $post);
    }
    
    function my_admin_scripts() {
    }    
    
    function scripts_styles(){
    }

    protected function update_meta($post_id, $meta_key, $new_value) {
        $post = get_post($post_id, OBJECT);
        if(isset($post)){
            $old_value = get_post_meta($post_id,$meta_key,true);
            if ($new_value && empty($old_value)){
                return add_post_meta($post_id, $meta_key, $new_value, true);
            }elseif (current_user_can('manage_options')) {
                if (empty($new_value)){
                    delete_post_meta($post_id, $meta_key, $old_value);
                    return NULL;
                }elseif ($new_value && $new_value != $old_value){
                    delete_post_meta($post_id, $meta_key, $old_value);
                    return update_post_meta($post_id, $meta_key, $new_value);
                }
            }
        }else{
            return FALSE;
        }
    }
    
}
