<?php
/**
 * Plugin Name: TinyMCE Annotate
 * Description: Create annotations on your posts or pages
 * Text Domain: tinymce-annotate
 * Domain Path: /languages
 * Version:     1.1
 * Author:      xyulex
 * Author URI:  https://profiles.wordpress.org/xyulex/
 * License:     GPLv2 or later
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.html
 */

add_filter('the_content', 'tma_annotate_backend');
add_filter('mce_css', 'tma_annotate_css');
add_action('admin_head', 'tma_annotate');

function tma_annotate_css($mce_css) {
  if (!empty($mce_css))
    $mce_css .= ',';
    $mce_css .= plugins_url('css/style.css', __FILE__);
    return $mce_css;
}

// Don't display annotations in frontend
function tma_annotate_backend($content) {
    return preg_replace('/(<[^>]+) class="annotation" style=".*?"/i', '$1', $content);
}

function tma_annotate() {
    global $typenow;

    // Only apply to posts and pages
    if ( !in_array($typenow, array('post', 'page')) )
        return ;

	// Add as an external TinyMCE plugin
    add_filter('mce_external_plugins', 'tma_annotate_plugin');

    // Add to first row of the TinyMCE buttons
    add_filter('mce_buttons', 'tma_annotate_button');

    // I18n
    load_plugin_textdomain('tinymce-annotate', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
    $current_user = wp_get_current_user();
    wp_register_script( 'tmajs', plugins_url('/plugin.js', __FILE__));
    wp_localize_script( 'tmajs', 'TMA', array(
            'id'                    => $current_user->ID,
            'author'                => $current_user->display_name,
            'missing_fields'        => __('Select the color and the annotation text', 'tinymce-annotate'),
            'missing_annotation'    => __('Please select some text for creating an annotation', 'tinymce-annotate'),
            'missing_selected'      => __('Please select the annotation you want to delete', 'tinymce-annotate')
            )
    );
    wp_enqueue_script( 'tmajs' );
}

// Include the JS
function tma_annotate_plugin($plugin_array) {
    $plugin_array['tma_annotate'] = plugins_url('/plugin.js', __FILE__);
    return $plugin_array;
}

// Add the button key for address via JS
function tma_annotate_button($buttons) {
    array_push($buttons, 'tma_annotate');
    array_push($buttons, 'tma_annotatedelete');
    array_push($buttons, 'tma_annotatehide');
    return $buttons;
}