<?php

if (!class_exists('MTI_Base_Options')) {
    
    class MTI_Base_Options{
        var $options;
        private $option_key = '_options';
        private $notices = '_notices';
        private $keys = array();
        private $prefix = '';

        function __construct($prefix) {
            $this->prefix = $prefix;
            $this->option_key = $this->prefix.'_options';
            $this->notices = $this->prefix.'_notices';
            add_action('init', array($this, 'init'));
            add_action( 'admin_notices', array($this, 'display_notices') ); 
        }

        function init(){
            $this->keys = apply_filters($this->option_key, array());
            $this->options = array();
            foreach ($this->keys as $v) {
                $option = get_option($v);
                if(is_array($option)){
                    foreach($option as $e => $a){
                        $this->options[$e] = $a;
                    }
                }else{
                    $this->options[$v] = $option;
                }
            }
        }

        function register_settings() {
        }

        function validate(){

        }

        function __get($key){
            if(!isset($this->options)){
                $this->init();
            }
            if (property_exists($this, $key)) {
                return $this->$key;
            }elseif(is_array($this->options) && key_exists($key, $this->options)){
                $value = $this->options[$key];
            }elseif(is_array($this->options) && key_exists($this->prefix.'_'.$key, $this->options)){
                $value = $this->options[$this->prefix.'_'.$key];
            }elseif($key == 'key'){
               return $this->option_key;
            }else{
                $value = NULL;
            }
            return $value;
        }

        function g($key){
            if(!isset($this->options)){
                $this->init();
            }
            if(is_array($this->options) && key_exists($key, $this->options)){
                $value = $this->options[$key];
            }elseif(is_array($this->options) && key_exists($this->prefix.'_'.$key, $this->options)){
                $value = $this->options[$this->prefix.'_'.$key];
            }elseif($key == 'key'){
               return $this->option_key;
            }else{
                $value = NULL;
            }
            return $value;
        }

        function add_notice($page, $type, $message){
            $notices = get_option($this->notices);
            if($notices && is_array($notices)){
                if(isset($notices[$page]) && is_array($notices[$page])){
                    $notices[$page][]=array($type => $message);
                }else{
                    $notices[$page] = array();
                    $notices[$page][]=array($type => $message);
                }
            }  else {
                $notices = array();
                $notices[$page] = array();
                $notices[$page][]=array($type => $message);
            }
            update_option($this->notices,$notices);
        }

        function display_notices(){
            $screen = get_current_screen();
            $notices = get_option($this->notices);
            if($notices && is_array($notices)){
                if(isset($notices[$screen->id]) && is_array($notices[$screen->id])){
                    $notice_types = array('info','warning','error','success');
                    foreach($notices[$screen->id] as $n){
                        if(is_array($n)){
                            foreach ($n as $k => $v){
                                if(in_array($k, $notice_types)){
                                    $n = $k;
                                }else{
                                    $n = 'info';
                                }
                                echo"<div class=\"is-dismissible notice notice-$n\"><p> $v</p></div>";                            
                            }
                        }
                    }
                    unset($notices[$screen->id]);
                    update_option($this->notices,$notices);
                }
            }
        }
    }
}

