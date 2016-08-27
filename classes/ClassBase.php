<?php

abstract class MTI_Base{
    protected $table = "";
    protected $key_id = "";
    protected $v = array();
    protected $loadable_keys = array();
    protected $options;
    
    function __construct($options) {
        $this->options = $options;
        $this->table = "";
        $this->key_id = "";
        $this->v = array($this->key_id=>NULL);
        $this->loadable_keys = array();
    }
    
    abstract function load_by_value($key,$value);

    abstract function load_by_key($id);
    
    abstract function exists();

    abstract function update_db();

    abstract function delete();
    
    protected function is_valid($key,$value){
        if(array_key_exists($key,$this->v)){
                return true;
        }else{
                return false;
        }
    }
    
    function toArray(){ return $this->v;}
    
    final function get_key(){return $this->key_id;}
    
    final function get_table(){return $this->table;}
    
    final function key_value(){return $this->v[$this->key_id];}

    function __get($property){
        if (property_exists($this, $property)) {
          return $this->$property;
        }elseif(key_exists($property, $this->v)){
            return $this->v[$property];
        }elseif($property == 'key_value'){
            return $this->key_value();
        }else{
            return NULL;
        }
    }
    
    function __set($property, $value){
        if($property == "key_value"){
            $property = $this->key_id;
        }
        if($this->is_valid($property, $value)){
            return $this->v[$property] = $value;
        }else{
            return FALSE;
        }
    }
    
}