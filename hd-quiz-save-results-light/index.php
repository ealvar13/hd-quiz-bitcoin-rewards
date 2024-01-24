<?php
/*
 * Plugin Name: HD Quiz - Save Results Light
 * Description: Addon for HD Quiz to save quiz results - Light version
 * Plugin URI: https://harmonicdesign.ca/addons/save-results-light/
 * Author: Harmonic Design
 * Author URI: https://harmonicdesign.ca
 * Version: 0.4
*/

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!defined('HDQ_A_LIGHT_PLUGIN_VERSION')) {
    define('HDQ_A_LIGHT_PLUGIN_VERSION', '0.4');
}

/* Automatically deactivate if HD Quiz is not active
------------------------------------------------------- */
function hdq_a_light_check_hd_quiz_active()
{
    if (function_exists('is_plugin_active')) {
        if (!is_plugin_active("hd-quiz/index.php")) {
            deactivate_plugins(plugin_basename(__FILE__));
        }
    }
}
add_action('init', 'hdq_a_light_check_hd_quiz_active');

/* Include the basic required files
------------------------------------------------------- */
require dirname(__FILE__) . '/includes/functions.php'; // general functions


/* Create HD Quiz Results light Settings page
------------------------------------------------------- */
function hdq_a_light_create_settings_page()
{
    function hdq_a_light_register_settings_page()
    {
        add_submenu_page('hdq_quizzes', 'Results', 'Results', 'publish_posts', 'hdq_results', 'hdq_a_light_register_quizzes_page_callback');
    }
    add_action('admin_menu', 'hdq_a_light_register_settings_page', 11);
}
add_action('init', 'hdq_a_light_create_settings_page');

function hdq_a_light_register_quizzes_page_callback()
{
    require dirname(__FILE__) . '/includes/results.php';
}
