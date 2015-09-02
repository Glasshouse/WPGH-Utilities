<?php

/**
 *
 * @todo mettre en cache les valeurs retrouvées *
 * @global type $wpdb
 * @param type $post_id
 * @return type
 */
function get_post_meta_all($post_id){
    global $wpdb;

    $data = array();

    $wpdb->query("
        SELECT `meta_key`, `meta_value`
        FROM $wpdb->postmeta
        WHERE `post_id` = $post_id
    ");

    foreach($wpdb->last_result as $k => $v){
        $data[$v->meta_key] =   $v->meta_value;
    };

    return $data;
}

/**
 *
 * @global type $wpdb
 * @param type $post_id
 * @return type
 */
if (!function_exists('get_post_meta_single')) {
    function get_post_meta_single($post_id = 0){

        $postMetas = get_post_custom($post_id);
        foreach ($postMetas as $key => $value) {
            if (is_array($value) && count($value) === 1) {
                $postMetas[$key] = maybe_unserialize($value[0]);
            }
        }

        return $postMetas;
    }
}

function esc_attr_url($string)
{
    return urlencode(html_entity_decode(trim(strip_tags($string))));
}

add_filter('wp_title', 'glasshouse_wp_title_filter', 9);
function glasshouse_wp_title_filter($title) {
    return strip_tags($title);
}

/**
* Filter the single_template with our custom function
*/
add_filter('single_template', 'glasshouse_single_template');
function glasshouse_single_template($single) {
	global $post;

    $template = get_post_meta($post->ID, 'template', true);

    if (!empty($template) && file_exists(TEMPLATEPATH . '/' . $template)) {
        return TEMPLATEPATH . '/' . $template;
    }

    return $single;
}

function glasshouse_get_attachment_image_src($attachment_id, $size = 'thumbnail', $icon = false)
{
    $image = wp_get_attachment_image_src($attachment_id, $size, $icon);
    if ($image) {
        $image['url']    = str_replace(site_url(), '', $image[0]);
        $image['width']  = $image[1];
        $image['height'] = $image[2];
    }

    return $image;
}


function wysiwyg($text){

    $text = wpautop(do_shortcode($text));

    $text = apply_filters('glasshouse/wysiwyg', $text);

    return $text;
}