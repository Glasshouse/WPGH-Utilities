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

remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/wpml.php';
require_once __DIR__ . '/tinymce.php';
