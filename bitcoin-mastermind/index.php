<?php
/*
    * Plugin Name: Bitcoin Mastermind
    * Description: Bitcoin Mastermind allows you to easily add an unlimited amount of Quizzes to your site.
    * Plugin URI: https://harmonicdesign.ca/hd-quiz/
    * Author: Harmonic Design
    * Author URI: https://harmonicdesign.ca
    * Version: 1.8.12
*/

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!defined('bitc_PLUGIN_VERSION')) {
    define('bitc_PLUGIN_VERSION', '1.8.12');
}

// custom quiz image sizes
add_image_size('hd_qu_size2', 400, 400, true); // image-as-answer

/* Include the basic required files
------------------------------------------------------- */
require dirname(__FILE__) . '/includes/settings.php'; // global settings class
require dirname(__FILE__) . '/includes/post-type.php'; // custom post types
require dirname(__FILE__) . '/includes/meta.php'; // custom meta
require dirname(__FILE__) . '/includes/functions.php'; // general functions

// function to check if Bitcoin Mastermind is active
function bitc_exists()
{
    return;
}

/* Add shortcode
------------------------------------------------------- */
function bitc_add_shortcode($atts)
{
    // Attributes
    extract(
        shortcode_atts(
            array(
                'quiz' => '',
            ),
            $atts
        )
    );

    // Code
    ob_start();
    include plugin_dir_path(__FILE__) . './includes/template.php';
    return ob_get_clean();
}
add_shortcode('HDquiz', 'bitc_add_shortcode');


/* Add Gutenberg block
------------------------------------------------------- */
function bitc_register_block_box()
{
    if (!function_exists('register_block_type')) {
        return; // Gutenberg is not active.
    }
    wp_register_script(
        'hdq-block-quiz',
        plugin_dir_url(__FILE__) . 'includes/js/bitc_block.js',
        array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
        bitc_PLUGIN_VERSION
    );
    register_block_type('hdquiz/hdq-block-quiz', array(
        'style' => 'hdq-block-quiz',
        'editor_style' => 'hdq-block-quiz',
        'editor_script' => 'hdq-block-quiz',
    ));
}
add_action('init', 'bitc_register_block_box');

/* Get Quiz list
 * used for the gutenberg block
------------------------------------------------------- */
function bitc_get_quiz_list()
{
    $taxonomy = 'quiz';
    $term_args = array(
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    );
    $tax_terms = get_terms($taxonomy, $term_args);
    $quizzes = array();
    if (!empty($tax_terms) && !is_wp_error($tax_terms)) {
        foreach ($tax_terms as $tax_terms) {
            $quiz = new stdClass;
            $quiz->value = $tax_terms->term_id;
            $quiz->label = $tax_terms->name;
            array_push($quizzes, $quiz);
        }
    }
    echo json_encode($quizzes);
    die();
}
add_action('wp_ajax_bitc_get_quiz_list', 'bitc_get_quiz_list');

/* Disable Canonical redirection for paginated quizzes
------------------------------------------------------- */
function bitc_disable_redirect_canonical($redirect_url)
{
    global $post;
    if (!isset($post->post_content)) {
        return;
    }
    if (has_shortcode($post->post_content, 'HDquiz')) {
        $redirect_url = false;
    }
    return $redirect_url;
}
add_filter('redirect_canonical', 'bitc_disable_redirect_canonical');

/* Create Bitcoin Mastermind Settings page
------------------------------------------------------- */
function bitc_create_settings_page()
{
    if (bitc_user_permission()) {
        function bitc_register_quizzes_page()
        {
            $icon = plugin_dir_url( __FILE__ ).'img/plugin-img.jpeg';
            add_menu_page('Bitcoin Mastermind', 'Bitcoin Mastermind', 'publish_posts', 'bitc_quizzes', 'bitc_register_quizzes_page_callback', $icon, 5);
            add_menu_page('Bitcoin Mastermind Tools', 'HDQ Tools', 'edit_posts', 'bitc_tools', 'bitc_register_tools_page_callbak', '', 99);
            add_menu_page('Bitcoin Mastermind Tools - CSV Importer', 'HDQ Tools CSV', 'edit_posts', 'bitc_tools_csv_importer', 'bitc_register_tools_csv_importer_page_callback', '', 99);
            add_menu_page('Bitcoin Mastermind Tools - Data Upgrade', 'HDQ Tools DATA', 'edit_posts', 'bitc_tools_data_upgrade', 'bitc_register_tools__data_upgrade_page_callback', '', 99);

            remove_menu_page('bitc_tools');
            remove_menu_page('bitc_tools_csv_importer');
            remove_menu_page('bitc_tools_data_upgrade');
        }
        add_action('admin_menu', 'bitc_register_quizzes_page');

        function bitc_register_settings_page()
        {
            $addon_text = "";  
            add_submenu_page('bitc_quizzes', 'Quizzes', 'Quizzes', 'publish_posts', 'bitc_quizzes', 'bitc_register_quizzes_page_callback');
            add_submenu_page('bitc_quizzes', 'Bitcoin Mastermind About', 'About / Options', 'publish_posts', 'bitc_options', 'bitc_register_settings_page_callback');
            add_submenu_page('bitc_quizzes', 'Tools', 'Tools', 'manage_options', 'admin.php?page=bitc_tools');
        }
        add_action('admin_menu', 'bitc_register_settings_page', 11);
    }

    $bitc_version = sanitize_text_field(get_option('bitc_PLUGIN_VERSION'));

    if ($bitc_version != "" && $bitc_version != null && $bitc_version < "1.8") {
        update_option("bitc_remove_data_upgrade_notice", "yes");
        update_option("bitc_data_upgraded", "occured");
        bitc_update_legacy_data();
    } else {
        update_option("bitc_data_upgraded", "all good");
    }

    if (bitc_PLUGIN_VERSION != $bitc_version) {
        update_option('bitc_PLUGIN_VERSION', bitc_PLUGIN_VERSION);
        wp_clear_scheduled_hook('bitc_check_for_updates');
    }
}
add_action('init', 'bitc_create_settings_page');

function bitc_check_for_updates()
{
    $remote = wp_remote_get("https://hdplugins.com/plugins/hd-quiz/addons_updated.txt");
    $local = intval(get_option("bitc_new_addon"));
    if (is_array($remote)) {
        $remote = intval($remote["body"]);
        update_option("bitc_new_addon", $remote);

        $transient = array(
            "date" => $remote,
            "isNew" => ""
        );

        if ($remote > $local) {
            $transient["isNew"] = "yes";
        }

        set_transient("bitc_new_addon", $transient, WEEK_IN_SECONDS); // only check every week

    } else {
        update_option("bitc_new_addon", "");
        set_transient("bitc_new_addon", array("date" => 0, "isNew" => ""), DAY_IN_SECONDS); // unable to connect. try again tomorrow
    }
}

function hddq_plugin_links($actions, $plugin_file, $plugin_data, $context)
{
    $new = array(
        'settings'    => sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=bitc_options')),
            esc_html__('Settings', 'hdquiz')
        ),
        'help' => sprintf(
            '<a href="%s">%s</a>',
            'https://hdplugins.com/forum/hd-quiz-support/',
            esc_html__('Help', 'hdquiz')
        )
    );
    return array_merge($new, $actions);
}
add_filter("plugin_action_links_" . plugin_basename(__FILE__), 'hddq_plugin_links', 10, 4);


function bitc_deactivation()
{
    wp_clear_scheduled_hook('bitc_check_for_updates');


}
register_deactivation_hook(__FILE__, 'bitc_deactivation');




