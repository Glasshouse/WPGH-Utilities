<?php
/**
 * Plugin Name: Glasshouse Utilities
 * Plugin URI: http://www.glasshouse.fr
 * Description: Some useful functions that we use in pretty much every Wordpress website we do
 * Version: 0.1
 * Author: Paul BERNARD
 * Author URI: http://www.glasshouse.fr
 *
 *
 * @package Glasshouse
 * @version 0.1
 * @author Paul BERNARD <paul@glasshouse.fr>
 * @copyright Copyright (c) 2015 Glasshouse
 * @link http://www.glasshouse.fr
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * 
 * @todo Charger wpml uniquement si le plugin WPML est activé, est-ce possible à ce stade ? 
 * @todo Charger le script pour tinymce uniquement si le plugin ACF est activé, est-ce possible à ce stade ?
 */

define('TEMPLATE_URL', get_template_directory_uri());

add_action('init', 'wpgh_utilities_init');
function wpgh_utilities_init()
{
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/tinymce.php';
require_once __DIR__ . '/security.php';

    if (is_plugin_active('sitepress-multilingual-cms')) {
        require_once __DIR__ . '/wpml.php';
    }
}
