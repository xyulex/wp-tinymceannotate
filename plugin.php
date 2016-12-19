<?php
/**
 * Plugin Name: TinyMCE Annotate
 * Description: Create annotations on your posts or pages
 * Text Domain: tinymce-annotate
 * Domain Path: /languages
 * Version:     1.2
 * Author:      Raúl Martínez
 * Author URI:  https://profiles.wordpress.org/xyulex/
 * License:     GPLv2 or later
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.html
 */

add_filter('the_content', 'tma_annotate_backend');
add_filter('mce_css', 'tma_annotate_css');
add_filter('default_content', 'tma_dummy_post_content', 10 , 2);
add_filter('default_title', 'tma_dummy_post_title', 10 , 2);
add_action('admin_head', 'tma_annotate');
add_action('admin_menu', 'tma_settings_page');

// Freemius start
function ta_fs() {
    global $ta_fs;

    if ( ! isset( $ta_fs ) ) {
        // Include Freemius SDK.
        require_once dirname(__FILE__) . '/freemius/start.php';

        $ta_fs = fs_dynamic_init( array(
            'id'                => '244',
            'slug'              => 'tinymce-annotate',
            'public_key'        => 'pk_967fec31011f34f271d662cb6c939',
            'is_premium'        => false,
            'has_addons'        => false,
            'has_paid_plans'    => false,
            'menu'              => array(
                'slug'       => 'tinymce-annotate',
                'first-path' => 'post-new.php?tinymce-annotate=true',
                'account'    => false,
                'contact'    => false,
                'support'    => false,
            ),
        ) );
    }

    return $ta_fs;
}



function tma_dummy_post_content( $content , $post ) {

    if(isset($_GET['tinymce-annotate'])) {
        $content .= 'This is an example of <span class="annotation" data-author="Random user" data-annotation="This is a random annotation" style="background-color:#F0E465">annotation</span>, hover the highlighted "annotation" word to see it in action!';
        $content .= '<br/><br/>If you find the plugin useful, I would love to get a review or rating: <br /><a href="https://wordpress.org/support/view/plugin-reviews/tinymce-annotate">https://wordpress.org/support/view/plugin-reviews/tinymce-annotate</a>';
        $content .= '<br/><br/>Also, any donation would also be greatly appreciated:<br /><a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=C2DCQ4BXXVR3A">https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=C2DCQ4BXXVR3A</a>';
    }

    return $content;
}

function tma_dummy_post_title( $title , $post ) {

    if(isset($_GET['tinymce-annotate'])) {
        $title = 'Annotation example';
    }

    return $title;
}


function tma_dummy_redirect($url) {
    return "/post_new.php?tinymce-annotate=true";
}

add_filter( 'after_skip_url'    , 'tma_dummy_redirect' );
add_filter( 'after_connect_url' , 'tma_dummy_redirect' );

ta_fs();

// END Freemius 


// Settings
function tma_settings_page() {
    $page_title = 'TinyMCE Annotate settings';
    $menu_title = 'TinyMCE Annotate';
    $capability = 'edit_posts';
    $menu_slug = 'tinymceannotate-settings';
    $function = 'tma_settings_form';
    $icon_url = 'dashicons-format-aside';
    $position = 24;

    add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
}


function tma_settings_form() {
    if (isset($_POST['tma_showonfrontend'])) {
        update_option('tma_showonfrontend', $_POST['tma_showonfrontend']);
    } else {
        update_option('tma_showonfrontend', 0);
    }

    $value = get_option('tma_showonfrontend');    

    ?>
    <h1><?php echo __('TinyMCE Annotate settings', 'tinymce-annotate'); ?></h1>
    <form method="POST">        
        <br /><h2><?php echo __('General options', 'tinymce-annotate'); ?></h2>
        <input type="checkbox" name="tma_showonfrontend" id="tma_showonfrontend" value="1" <?php if ( 1 == $value ) echo 'checked="checked"'; ?>>
        <label for="tma_showonfrontend"><?php echo __('Show annotations on frontend', 'tinymce-annotate') ?></label>
        
        <?php $args = array(
       'public'   => true,
       '_builtin' => false,
        );

        $output = 'names'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'

        $post_types = get_post_types( $args, $output, $operator ); 
        

        echo '<h2>Post types</h2>';    
        if ($post_types) {
            foreach ( $post_types  as $post_type ) {
               echo '<p>' . $post_type . '</p>';
            }
        } else {
            echo __('There are no registered post types at the moment', 'tinymce-annotate');
        }
        ?>
        
    <br /><br /><input type="submit" value="Save" class="button button-primary button-large">
    </form>
<?php  }

// END Settings

function tma_annotate_css($mce_css) {
  if (!empty($mce_css))
    $mce_css .= ',';
    $mce_css .= plugins_url('css/style.css', __FILE__);
    return $mce_css;
}

// Determine wether to show or not annotations in frontend
function tma_annotate_backend($content) {
    $show = get_option('tma_showonfrontend');

    if ($show == 0) {
        return preg_replace('/(<[^>]+) class="annotation" style=".*?"/i', '$1', $content);
    } else {
        wp_enqueue_style( 'tma_styles', plugins_url( '/css/style.css', __FILE__ ) );
        return $content;
    }
}

function tma_annotate() {
    global $typenow;

    // Works for all custom post types. Uncomment on free version.
    //if ( !in_array($typenow, array('post', 'page')) )
    //    return ;

	// Add as an external TinyMCE plugin
    add_filter('mce_external_plugins', 'tma_annotate_plugin');

    // Add to first row of the TinyMCE buttons
    add_filter('mce_buttons', 'tma_annotate_button');

    // I18n
    load_plugin_textdomain('tinymce-annotate', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
    $current_user = wp_get_current_user();
    wp_register_script( 'tmajs', plugins_url('/plugin.js', __FILE__));
    wp_localize_script( 'tmajs', 'TMA',
        array(
            'id'        => $current_user->ID,
            'author'    => $current_user->display_name,
            'errors'    => array(
                            'missing_fields'            => __('Select the color and the annotation text', 'tinymce-annotate'),
                            'missing_annotation'        => __('Please select some text for creating an annotation', 'tinymce-annotate'),
                            'missing_selected'          => __('Please select the annotation you want to delete', 'tinymce-annotate')
                            ),
            'tooltips'  => array(
                            'annotation_settings'       => __('Annotation settings', 'tinymce-annotate'),
                            'annotation_create'         => __('Create annotation', 'tinymce-annotate'),
                            'annotation_delete'         => __('Delete annotation', 'tinymce-annotate'),
                            'annotation_hide'           => __('Hide annotations', 'tinymce-annotate')
                            ),
            'settings'  => array(
                            'setting_annotationtitle'   => __('Annotation title', 'tinymce-annotate'),
                            'setting_annotation'        => __('Annotation', 'tinymce-annotate'),
                            'setting_background'        => __('Background color', 'tinymce-annotate')
                            )
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