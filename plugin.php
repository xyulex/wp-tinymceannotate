<?php
/**
 * Plugin Name: TinyMCE Annotate
 * Description: Create annotations on your posts or pages
 * Version:     0.1
 * Author:      Raúl Martínez
 * Author URI:  https://profiles.wordpress.org/xyulex/
 * License:     GPLv2 or later
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.html
 */

// Don't display annotations in frontend
add_filter( 'the_content', 'tma_annotate_backend' );

function tma_annotate_backend($content) {
    return preg_replace('/(<[^>]+) class="annotation" style=".*?"/i', '$1', $content);
}


add_action( 'admin_head', 'tma_annotate' );
function tma_annotate() {
    global $typenow;

    // Only apply to posts and pages
    if( ! in_array( $typenow, array( 'post', 'page' ) ) )
        return ;

	// Add as an external TinyMCE plugin
    add_filter( 'mce_external_plugins', 'tma_annotate_plugin' );

    // Add to first row of the TinyMCE buttons
    add_filter( 'mce_buttons', 'tma_annotate_button' );
}

// Include the JS
function tma_annotate_plugin( $plugin_array ) {
    $plugin_array['tma_annotate'] = plugins_url( '/plugin.js', __FILE__ );
    return $plugin_array;
}

// Add the button key for address via JS
function tma_annotate_button( $buttons ) {
    array_push( $buttons, 'tma_annotate' );
    array_push( $buttons, 'tma_annotatedelete' );
    array_push( $buttons, 'tma_annotatehide' );
    return $buttons;
}