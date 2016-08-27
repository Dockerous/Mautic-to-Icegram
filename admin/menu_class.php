<?php

if (!class_exists('MTI_Base_Menu')) {
    
    class MTI_Base_Menu {
        var $screen_id;
        var $page_id;
        var $prefix;
        var $options;

        function __construct($options, $toplevel = FALSE) {
            $this->options = $options;
            $this->prefix = $this->options->prefix;
            $this->set_screen_id($toplevel);
            add_filter($this->options->option_key,array($this,'add_option'));
        }

        function add_option($a){
            if(is_array($a)){
                $a[] =  $this->page_id;
            }
            return $a;
        }

        function set_screen_id($toplevel = FALSE){
            if($toplevel){
                $this->screen_id = 'toplevel_page_'.$this->page_id;
            }else{
                $this->screen_id = $this->prefix.'_page_'.$this->page_id;
            }
        }

        /*
         * Actions to be taken prior to page loading. This is after headers have been set.
         * @uses load-$hook
         */
        function add_screen_meta_boxes() {
            /* Trigger the add_meta_boxes hooks to allow meta boxes to be added */
            do_action('add_meta_boxes_'.$this->screen_id, null);
            do_action('add_meta_boxes', $this->screen_id, null);

            /* Enqueue WordPress' script for handling the meta boxes */
            wp_enqueue_script('postbox');

            /* Add screen option: user can choose between 1 or 2 columns (default 2) */
            add_screen_option('layout_columns', array('max' => 2, 'default' => 2) );
        }

        /* Prints script in footer. This 'initialises' the meta boxes */
        function print_script_in_footer() {
            ?>
            <script>jQuery(document).ready(function(){ postboxes.add_postbox_toggles(pagenow); });</script>
            <?php
        }

        function output_input($key,$subtext='',$size= NULL,$disabled = FALSE){
            $k = $this->page_id;
            $value = $this->options->$key;
            $size_str = '';
            if(isset($size) && is_int($size)){
                $size_str = ' size="'.$size.'"';
            }
            $d = '';
            if($disabled){
                $d = ' disabled="disabled" ';
            }
            echo '<input type="text" name="'.$k.'['.$key.']" id="'.$k.'['.$key.']" value="'.$value.'" '.$size_str.$d.'/>';
            echo $subtext;
        }

        function output_checkbox($key,$subtext='',$disabled = FALSE){
            $k = $this->page_id;
            $value = $this->options->$key;
            $d = '';
            if($disabled){
                $d = ' disabled="disabled" ';
            }
            $checked = checked( $value, 1, FALSE );
            echo '<input type="checkbox" name="'.$k.'['.$key.']" id="'.$k.'['.$key.']" value="1" '.$d.$checked.'/>';
            echo $subtext;
        }

        function output_hidden($key,$subtext=''){
            $k = $this->page_id;
            $value = $this->options->$key;
            echo '<input type="hidden" name="'.$k.'['.$key.']" id="'.$k.'['.$key.']" value="'.$value.'" />';
            echo $subtext;
        }

        function open($header){
            ?>
                <!-- Create a header in the default WordPress 'wrap' container -->
        <div class="wrap">
            <?php screen_icon()?>
            <?php 
            if(isset($header)){echo'<h1>'.$header.'</h1>';}
            settings_errors();
            ?>
            <div id="poststuff">

                <div id="post-body" class="metabox-holder columns-<?php echo 1 == get_current_screen()->get_columns() ? '1' : '2'; ?>">

                    <div id="post-body-content">
                        <div id="cjf_base_menu_wrap" >
            <?php
        }

        function close(){
            ?>
                        </div>
                    </div><!-- #post-body-content -->
                    <div id="postbox-container-1" class="postbox-container">
                        <?php do_meta_boxes($this->screen_id,'side',NULL); ?>
                    </div>

                    <div id="postbox-container-2" class="postbox-container">
                        <?php do_meta_boxes($this->screen_id,'normal',NULL); ?>
                        <?php do_meta_boxes($this->screen_id,'advanced',NULL); ?>
                    </div>
                </div> <!-- #post-body -->

            </div> <!-- #poststuff -->
        </div><!-- /.wrap -->
            <?php
        }
    }
}