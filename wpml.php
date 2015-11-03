<?php

/**
 *
 * @global array $_lon_urls
 * @global wpdb $wpdb
 * @param type $template_filename
 * @return string Retourne l'url d'une page en fonction de son template.
 */
function lon_get_page_url_by_template($template_filename, $lang = null) {
    global $_lon_urls, $wpdb, $sitepress;

    // on initialise le cache s'il n'existe pas
    if (!is_array($_lon_urls))
        $_lon_urls = array();

    if (!$lang) {
        $lang = $sitepress->get_current_language();
    }


    if (empty($_lon_urls[$template_filename])) {

        $sql = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_page_template' and meta_value = %s";
        $sql = $wpdb->prepare($sql, $template_filename);
        $mid = $wpdb->get_var($sql);

        if (!$mid) {
            $url = home_url('/');
        } else {
            $element_lang_details = $sitepress->get_element_language_details($mid, 'post_page');

            if ($element_lang_details->language_code == $lang)
                $url = get_permalink($mid);
            else {
                $mtid = $wpdb->get_var("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_type='post_page' AND trid='" . $element_lang_details->trid . "' AND language_code='" . $lang . "'");
                //$mtid = $sitepress->get_translation_id($element_lang_details->trid, ICL_LANGUAGE_CODE);
                if ($mtid)
                    $url = get_permalink($mtid);
                else
                    $url = get_permalink($mid);
            }

            $url = rtrim($url, '/') . '/'; // just in case, we add a trailing slash

            $_lon_urls[$template_filename] = $url;
        }

    }

    return $_lon_urls[$template_filename];
}


function lon_get_post_url_by_template($template_filename, $post_type, $lang = null) {
    global $_lon_urls, $wpdb, $sitepress;

    // on initialise le cache s'il n'existe pas
    if (!is_array($_lon_urls))
        $_lon_urls = array();

    if (!$lang) {
        $lang = $sitepress->get_current_language();
    }


    if (empty($_lon_urls[$template_filename])) {

        $sql = "SELECT pm.post_id
                FROM {$wpdb->postmeta} pm
                LEFT JOIN {$wpdb->prefix}icl_translations icl
                  ON pm.post_id = icl.element_id AND icl.element_type = 'post_$post_type'
                WHERE (meta_key = 'template')
                  AND meta_value = %s
                  AND icl.language_code = %s";
        $sql = $wpdb->prepare($sql, $template_filename, $lang);
        $pid = $wpdb->get_var($sql);

        if (!$pid) {
            $url = home_url('/');
        }
        else {
            $url = rtrim(get_permalink($pid), '/') . '/'; // just in case, we add a trailing slash
        }
    }

    return $url;
}

add_action('wp_head', 'lon_wpml_after_startup', 1);
function lon_wpml_after_startup()
{
    global $sitepress;
    remove_action( 'wp_head', array( $sitepress, 'meta_generator_tag' ) );
    remove_action('wp_head', 'wp_generator');
}

function getLanguageSwitcherItems()
{
    global $languageSwitcherItems, $sitepress;

    if (!isset($languageSwitcherItems)) {
        $languageSwitcherItems = $sitepress->get_ls_languages('');
//        var_dump($languageSwitcherItems);
    }

    return $languageSwitcherItems;
}

if (!function_exists('wpml_get_translation')) {
    function wpml_get_translation($element_id, $element_type, $language_code) {
        global $wpdb;
        $sql = "SELECT icl1.element_id
                FROM `{$wpdb->prefix}icl_translations` icl1
                LEFT JOIN `{$wpdb->prefix}icl_translations` icl2 ON icl1.trid = icl2.trid
                AND icl1.element_type = '{$element_type}'
                WHERE icl1.language_code = '{$language_code}'
                AND icl2.element_id = {$element_id}";
                
        $translated_id = $wpdb->get_var($sql);
            
        return $translated_id ? $translated_id : $element_id;
    }
}