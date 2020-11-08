<?php
/**
 * Plugin Name: Glasshouse Utilities
 * Plugin URI: http://www.glasshouse.fr
 * Description: Some useful functions that we use in pretty much every Wordpress website we do
 * Version: 1.0.1
 * Author: Paul BERNARD
 * Author URI: http://www.glasshouse.fr
 *
 *
 * @package Glasshouse
 * @version 1.0
 * @author Paul BERNARD <paul@glasshouse.fr>
 * @copyright Copyright (c) 2015 Glasshouse
 * @link http://www.glasshouse.fr
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * @todo Charger le script pour tinymce uniquement si le plugin ACF est activé, est-ce possible à ce stade ?
 */

define('TEMPLATE_URL', get_template_directory_uri());

include_once(__DIR__ . '/updater/updater.php');

add_action('init', 'wpgh_utilities_init');
function wpgh_utilities_init()
{
    require_once __DIR__ . '/functions.php';
    require_once __DIR__ . '/tinymce.php';
    require_once __DIR__ . '/disable-emojis.php';
    require_once __DIR__ . '/security.php';

    disable_emojis();

    if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
        $config = array(
            'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
            'proper_folder_name' => 'wpgh-utilities', // this is the name of the folder your plugin lives in
            'api_url' => 'https://api.github.com/repos/Glasshouse/WPGH-Utilities', // the GitHub API url of your GitHub repo
            'raw_url' => 'https://raw.github.com/Glasshouse/WPGH-Utilities/main', // the GitHub raw url of your GitHub repo
            'github_url' => 'https://github.com/Glasshouse/WPGH-Utilities', // the GitHub url of your GitHub repo
            'zip_url' => 'https://github.com/Glasshouse/WPGH-Utilities/zipball/main', // the zip url of the GitHub repo
            'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
            'requires' => '5.0', // which version of WordPress does your plugin require?
            'tested' => '5..0', // which version of WordPress is your plugin tested up to?
            'readme' => 'README.md', // which file to use as the readme for the version number
            'access_token' => '', // Access private repositories by authorizing under Plugins > GitHub Updates when this example plugin is installed
        );
        new WP_GitHub_Updater($config);
    }
}