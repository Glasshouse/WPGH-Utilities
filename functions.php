<?php

if (!function_exists('get_post_meta_all')) {
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
}


if (!function_exists('get_post_meta_single')) {
	/**
	 *
	 * @global type $wpdb
	 * @param type $post_id
	 * @return type
	 */
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

if (!function_exists('esc_attr_url')) {
	/**
	 * 
	 * @param string $string
	 * @return string
	 */
	function esc_attr_url($string)
	{
		return urlencode(html_entity_decode(trim(strip_tags($string))));
	}
}

add_filter('wp_title', 'wpgh_wp_title_filter', 9);
function wpgh_wp_title_filter($title) {
    return strip_tags($title);
}

/**
* Filter the single_template with our custom function
*/
add_filter('single_template', 'wpgh_single_template');
function wpgh_single_template($single) {
	global $post;

    $template = get_post_meta($post->ID, 'template', true);

    if (!empty($template) && file_exists(TEMPLATEPATH . '/' . $template)) {
        return TEMPLATEPATH . '/' . $template;
    }

    return $single;
}

if (!function_exists('wysiwyg')) {
	/**
	 * Perform some useful stuff on some useful piece of text
	 * 
	 * @param string $text
	 * @return string
	 */
	function wysiwyg($text)
	{
		if (!$text)
			return $text;
		global $wp_embed;

		$text = $wp_embed->autoembed( $text );
		$text = $wp_embed->run_shortcode( $text );
		$text = wpautop(do_shortcode($text));
		$text = wpgh_prevent_new_line($text);
		// @third-party could hack into this useful stuff
		$text = apply_filters('wpgh/wysiwyg', $text);
		return $text;
	}
}

function wpgh_prevent_new_line($text)
{
	return preg_replace('#\s(\?|\!|\:)#', '&nbsp;$1', $text);
}

function wpgh_get_attachment_image_src($attachment_id, $size = 'thumbnail', $icon = false)
{
    $image = wp_get_attachment_image_src($attachment_id, $size, $icon);
    if ($image) {
        $image['url']    = str_replace(site_url(), '', $image[0]);
        $image['width']  = $image[1];
        $image['height'] = $image[2];
    }

    return $image;
}

/**
* Callback for the "image_downsize" filter.
*
* @param bool $ignore A value meant to discard unfiltered info returned from this filter.
* @param int $attachment_id The ID of the attachment for which we want a certain size.
* @param string $size_name The name of the size desired.
*/
add_filter('image_downsize', 'wpgh_filter_image_downsize', 99, 3);
function wpgh_filter_image_downsize($ignore = false, $attachment_id = 0, $size_name = 'thumbnail')
{
   global $_wp_additional_image_sizes;

   $attachment_id = (int) $attachment_id;
   if (is_array($size_name)) {
       $size_name = $size_name[0] . 'x' . $size_name[1];
   }
   $size_name = trim($size_name);

   $meta = wp_get_attachment_metadata($attachment_id);
   
   if (empty($meta))
       return FALSE;

   /* the requested size does not yet exist for this attachment */
   if (
           empty($meta['sizes']) ||
           empty($meta['sizes'][$size_name])
   ) {

       $width = $height = '?' ;
       $crop = false;
       
       // let's first see if this is a registered size
       if (isset($_wp_additional_image_sizes[$size_name])) {
           $height = (int) $_wp_additional_image_sizes[$size_name]['height'];
           $width = (int) $_wp_additional_image_sizes[$size_name]['width'];
           $crop = (bool) $_wp_additional_image_sizes[$size_name]['crop'];
       }
       // do we ask for a thumbnail ?
       else if ($size_name == 'thumb' || $size_name == 'thumbnail') {
           $width = intval(get_option('thumbnail_size_w'));
           $height = intval(get_option('thumbnail_size_h'));
           if (!$width && !$height) {
               $width = '150';
               $height = '?';
           }
       }
       // if not, see if name is of form [width]x[height] and use that to crop
       else if (preg_match('#^((\d+|\?)x(\?|\d+))(\|crop)?$#', $size_name, $matches)) {
           //echo '<b>On doit recalculer la taille.</b>';
           $height = $matches[3];
           $width = $matches[2];
           $crop = isset($matches[4]);
           if (!$crop) {
               $ratio = $meta['width'] / $meta['height'];
           }
       }
       else if (preg_match('#^(\d+)x(\d+)$#', $size_name, $matches)) {
           $height = (int) $matches[2];
           $width = (int) $matches[1];
           $crop = true;
       }

       if ($width == '?' && $height == '?') {
           return false;
       }

       if ($height == '?' || (!$crop && $width !== '?')) {
           //echo '<b>On doit recalculer la hauteur.</b>';
           $ratio = $meta['width'] / $meta['height'];
           $height = intval($width / $ratio);
       }

       if ($width == '?') {
           //echo '<b>On doit recalculer la largeur.</b>';
           $ratio = $meta['height'] / $meta['width'];
           $width = intval($height / $ratio);
       }

       if (!empty($height) && !empty($width)) {
           //echo '<b>_generate_attachment</b>';
           $resized_path = wpgh_generate_attachment($attachment_id, $width, $height, $crop);
           //var_dump($resized_path);
           $fullsize_url = wp_get_attachment_url($attachment_id);

           $file_name = basename($resized_path);

           $new_url = str_replace(basename($fullsize_url), $file_name, $fullsize_url);

           if (!empty($resized_path)) {
               $meta['sizes'][$size_name] = array(
                   'file' => $file_name,
                   'width' => $width,
                   'height' => $height,
               );

               wp_update_attachment_metadata($attachment_id, $meta);

               return array(
                   $new_url,
                   $width,
                   $height,
                   true
               );
           }
       }
   }

   return false;
}

/**
* Creates a cropped version of an image for a given attachment ID.
*
* @param int $attachment_id The attachment for which to generate a cropped image.
* @param int $width The width of the cropped image in pixels.
* @param int $height The height of the cropped image in pixels.
* @param bool $crop Whether to crop the generated image.
* @return string The full path to the cropped image.  Empty if failed.
*/
function wpgh_generate_attachment($attachment_id = 0, $width = 0, $height = 0, $crop = true)
{
   $attachment_id = (int) $attachment_id;
   $width = (int) $width;
   $height = (int) $height;
   $crop = (bool) $crop;

   $original_path = get_attached_file($attachment_id);

   // fix a WP bug up to 2.9.2
   if (!function_exists('wp_load_image')) {
       require_once ABSPATH . 'wp-admin/includes/image.php';
   }

   if (function_exists('gd_info')) {
       $resized_path = @image_resize($original_path, $width, $height, $crop);
   }
   else {
       if (function_exists('imagemagick_image_resize'))
           $resized_path = @imagemagick_image_resize($original_path, $width, $height, $crop);
       else
           return false;
   }

   if (
           !is_wp_error($resized_path) &&
           !is_array($resized_path)
   ) {
       return $resized_path;

       // perhaps this image already exists.  If so, return it.
   }
   else {
       $orig_info = pathinfo($original_path);
       $suffix = "{$width}x{$height}";
       $dir = $orig_info['dirname'];
       $ext = $orig_info['extension'];
       $name = basename($original_path, ".{$ext}");
       $destfilename = "{$dir}/{$name}-{$suffix}.{$ext}";
       if (file_exists($destfilename)) {
           return $destfilename;
       }
   }

   return '';
}


// BC compatibility
function glasshouse_get_attachment_image_src($attachment_id, $size = 'thumbnail', $icon = false)
{
	return wpgh_get_attachment_image_src($attachment_id, $size, $icon);
}

// See: http://life.co-hey.com/2013/03/advanced-custom-fields%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%E3%81%A7%E3%83%97%E3%83%AC%E3%83%93%E3%83%A5%E3%83%BC%E3%82%82%E3%82%84%E3%82%8A%E3%81%9F%E3%81%84/
// preview用に保存された下書き情報(post)のIDを取得する
function get_preview_id($postId)
{
    global $post;
    $previewId = 0;
 
    if ( $post->ID == $postId && $_GET['preview'] == true ) {
        // プレビュー表示の際に、自動保存されたpostの下書き情報を取得する
        $preview = wp_get_post_autosave($postId);
        if ($preview != false) { $previewId = $preview->ID; }
    }
 
    return $previewId;
}
 
function get_preview_postmeta($metaValue, $postId, $metaKey, $single)
{
    // プレビュー表示のときにmeta情報(custom fileds情報)を、
    // プレビュー用のmeta情報に置き換える
    if ($previewId = get_preview_id($postId)) {
        if ($postId != $previewId) {
            $metaValue = get_post_meta($previewId, $metaKey, $single);
        }
    }
 
    return $metaValue;
}

if (!empty($_GET['preview'])) {
    add_filter('get_post_metadata', 'get_preview_postmeta', 10, 4);
}