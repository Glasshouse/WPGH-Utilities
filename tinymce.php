<?php

// Advanced Custom Fields: Custom WYSIWYG Styles
// http://www.webdesign-muenchen.de/acf/
// License: GPLv2 or later
// License URI: http://www.gnu.org/licenses/gpl-2.0.html

function glasshouse_acf_wysiwyg_js()
{
    wp_enqueue_script('glasshouseacfjs',plugins_url('/assets/acf-wysiwyg.js', __FILE__ ), array('acf-input') );
}

function glasshouse_acf_wysiwyg_theme_setup()
{ 
    add_editor_style('prod/css/tinymce.css');
}

add_action('admin_enqueue_scripts','glasshouse_acf_wysiwyg_js');
add_action('after_setup_theme','glasshouse_acf_wysiwyg_theme_setup');
