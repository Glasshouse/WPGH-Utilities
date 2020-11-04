<?php

/**
 *
 * @global array $_gh_urls
 * @global wpdb $wpdb
 * @global SitePress $sitepress
 * @param string $template_filename
 * @param array $opt
 * @return string|int|object L'url ou l'identifiant ou l'objet page recherch�
 */
function gh_get_page_by_template($template_filename, $opt = array())
{
    global $_gh_urls, $wpdb, $sitepress;

    // on initialise le cache s'il n'existe pas
    if (!is_array($_gh_urls))
        $_gh_urls = array();

    if (empty($opt['lang'])) {
        $lang = isset($sitepress) ? $sitepress->get_current_language() : get_locale();
    }
	else {
		$lang = $opt['lang'];
	}

    if (empty($opt['ret'])) {
        $opt['ret'] = 'url';
    }

    if (empty($_gh_urls[$template_filename])) {

        $sql = "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_page_template' and meta_value = %s";
        $sql = $wpdb->prepare($sql, $template_filename);
        $mid = $wpdb->get_var($sql);


        if (!$mid) {
            $url = home_url('/');
        } 
        elseif ( isset( $sitepress ) ) {
            $element_lang_details = $sitepress->get_element_language_details($mid, 'post_page');

            if ($element_lang_details->language_code == $lang)
                $url = get_permalink($mid);
            else {
                $mtid = $wpdb->get_var("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE element_type='post_page' AND trid='" . $element_lang_details->trid . "' AND language_code='" . $lang . "'");
                //$mtid = $sitepress->get_translation_id($element_lang_details->trid, ICL_LANGUAGE_CODE);
                if ($mtid) {
                    $url = get_permalink($mtid);
                    $mid = $mtid;
                }
                else
                    $url = get_permalink($mid);
            }

            $url = rtrim($url, '/') . '/'; // just in case, we add a trailing slash

            $_gh_urls[$template_filename] = $url;

        }
        else{
            $url = get_permalink($mid);
        }
        
        $_gh_urls[$template_filename] = array('url' => $url, 'id' => $mid);

    }

    if ($opt['ret'] == OBJECT && empty($_gh_urls[$template_filename][OBJECT])){
        $_gh_urls[$template_filename][OBJECT] = get_page($_gh_urls[$template_filename]['id']);
    }

    return $_gh_urls[$template_filename][$opt['ret']];
}


function gh_get_post_url_by_template($template_filename, $post_type, $lang = null) {
    global $_gh_urls, $wpdb, $sitepress;

    // on initialise le cache s'il n'existe pas
    if (!is_array($_gh_urls))
        $_gh_urls = array();

    if (!$lang) {
        $lang = $sitepress->get_current_language();
    }


    if (empty($_gh_urls[$template_filename])) {

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
    function wpml_get_translation($element_id, $element_type, $language_code=null) {
        global $wpdb;
        $sql = "SELECT icl1.element_id
                FROM `{$wpdb->prefix}icl_translations` icl1
                LEFT JOIN `{$wpdb->prefix}icl_translations` icl2 ON icl1.trid = icl2.trid
                AND icl1.element_type = '{$element_type}'
                WHERE icl2.element_id = {$element_id} 
                ";
                
        if(!empty($language_code)){ //fonction d'origine
            $sql.=" AND icl1.language_code = '{$language_code}'";
            $translated_id = $wpdb->get_var($sql);
            return $translated_id ? $translated_id : $element_id;
        }                
        //fonction etendue a get_all translation of
        $results = $wpdb->get_results($sql);
        $ret = array();
        foreach ($results as $object) {
            array_push($ret, $object->element_id);
        }
        return $ret;
    }
}

if (!function_exists('wpml_get_translations')) {
    function wpml_get_translations($element_id, $element_type, $language_code=null) {
        global $wpdb;
        $sql = "SELECT icl1.element_id as ID, icl1.language_code
                FROM `{$wpdb->prefix}icl_translations` icl1
                LEFT JOIN `{$wpdb->prefix}icl_translations` icl2 ON icl1.trid = icl2.trid
                AND icl1.element_type = '{$element_type}'
                WHERE icl2.element_id = {$element_id}
				  AND icl1.element_id != {$element_id}
                ";
                
        if(!empty($language_code)){ //fonction d'origine
            $sql .= " AND icl1.language_code = '{$language_code}'";
            $translated_id = $wpdb->get_row($sql, ARRAY_A);
            return $translated_id ? $translated_id : $element_id;
        }                
        //fonction etendue a get_all translation of
        $results = $wpdb->get_results($sql, ARRAY_A);
        
		return $results;
    }
}


// TO AVOID BC BREAK AND KEEP LEGACY
function gh_get_page_url_by_template($template_filename, $opt = array())
{
	return gh_get_page_by_template($template_filename, $opt);
}