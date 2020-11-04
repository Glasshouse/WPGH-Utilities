<?php

remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
remove_action( 'wp_head', 'feed_links', 2 );
remove_action( 'wp_head', 'feed_links_extra', 3 );

add_filter('oembed_response_data', function($data){
    unset($data['author_name']);
    unset($data['author_url']);

    return $data;
});

add_action('wp_head', 'wpgh_utilities_remove_generator_tag', 1);
function wpgh_utilities_remove_generator_tag()
{
    global $sitepress;
    remove_action( 'wp_head', array( $sitepress, 'meta_generator_tag' ) );
    remove_action('wp_head', 'wp_generator');
}