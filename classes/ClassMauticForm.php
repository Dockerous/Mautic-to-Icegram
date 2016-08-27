<?php

class MauticForm extends MTI_Base{
    
    private $post_type;
    private $meta;
    
    function __construct($options) {
        parent::__construct($options);
        $this->post_type = 'mautic_form';
        $this->table = "";
        $this->key_id = "post_id";
        $this->v = array($this->key_id=>NULL, 'post_status'=>'publish', 'content'=>NULL,'alias'=>NULL,'post_name'=>NULL,'form_id' => NULL,'form_name' => NULL);
        $this->loadable_keys = array('alias','post_name','form_id');
        $this->meta = array('form_id','form_name');
    }
    
    function load_by_value($key,$value){
        $args = array(
          'post_type'   => $this->post_type,
          'post_status' => 'publish',
          'numberposts' => 1
        );
        switch($key){
            case 'alias':
                $args['post_title'] = $value;
                break;
            case 'post_name':
                $args['post_name'] = $value;
                break;
            case 'form_id':
                $args['post_name'] = $this->post_type."_".$value;
                break;
            default:
                return FALSE;
        }
        $my_posts = get_posts($args);
        if( $my_posts ){
            return $this->load_post($my_posts[0]);
        }else{
            return FALSE;
        }
    }

    function load_by_key($id){
        $post = get_post( $id);
        return $this->load_post($post);
    }
    
    private function load_post($post){
        if ($post != NULL && $post->post_type  == $this->get_post_type()){
            $post_id = $post->ID;
            
            $this->$this->key_id = $post_id;
            $this->post_status = $post->post_status;
            $this->alias = $post->post_title;
            $this->content = $post->post_content;
            $this->post_name = $post->post_name;
            foreach($this->meta as $meta_key_end){
                $meta_key = $this->options->prefix . "_" . $meta_key_end;
                $meta_value = get_post_meta($post_id, $meta_key, true);
                $this->$meta_key_end = $meta_value;
            }
            return True;
        }
        return False;
    }
    
    function exists(){
        $post = get_post( $this->key_value);
        if ($post != NULL && $post->post_type  == $this->get_post_type()){
            return TRUE;
        }
        return FALSE;
    }

    function update_db(){
        $post = array(
            'post_content'   => $this->content, // The full text of the post.
            'post_title'     => $this->alias, // The title of your post.
            'post_status'    => $this->post_status, // Default 'draft'.
            'post_name'      => $this->post_type."_".$this->form_id,
            'post_type'      => $this->post_type, // Default 'post'.
            'ping_status'    => 'closed',
            'comment_status' => 'closed'
        );
        $id = $this->key_value;
        if ($id != NULL && trim($id) != "") {
            $post["ID"] = $id;
        }
        $post_id = wp_insert_post($post);
        if($post_id != 0){
            $this->$this->key_id = $post_id;
            $this->save_meta();
        }
    }

    function delete(){
        
    }
    
    function get_post_type(){
        return $this->post_type;
    }
    
    function is_valid($key,$value){
        $valid = parent::is_valid($key,$value);
        if($valid && $key == 'form_id'){
            return is_numeric($value);
        }else if($valid && $key == 'post_name'){
            if($value == $this->post_type."_".$this->form_id){
                return TRUE;
            }else{
                return FALSE;
            }
        }
        return $valid;
    }
    
    function __set($k, $v) {
        $ret = parent::__set($k, $v);
        if($ret && $k == 'form_id'){
            return parent::__set('post_name', $this->post_type."_".$v);
        }
        return $ret;
    }
    
    protected function save_meta(){
        $post_id = $this->key_value;
        foreach($this->meta as $meta_key_end){
            $meta_key = $this->options->prefix . "_" . $meta_key_end;
            $new_meta_value = $this->$meta_key_end;
            $meta_value = get_post_meta($post_id, $meta_key, true);

            if ($new_meta_value && '' == $meta_value){
                add_post_meta($post_id, $meta_key, $new_meta_value, true);
            }elseif ($new_meta_value && $new_meta_value != $meta_value){
                delete_post_meta($post_id, $meta_key);
                update_post_meta($post_id, $meta_key, $new_meta_value);
            }elseif ('' == $new_meta_value && $meta_value){
                delete_post_meta($post_id, $meta_key, $meta_value);
            }
        }
    }
}