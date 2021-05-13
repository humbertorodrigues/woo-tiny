<?php

if(!function_exists('bw_get_meta_field')){
    function bw_get_meta_field($field, $post_id = null) {
        if(is_null($post_id)){
            global $post;
            $post_id = $post->ID;
        }
        $custom = get_post_custom($post_id);
        if (isset($custom[$field])) {
            return $custom[$field][0];
        }
        return '';
    }
}