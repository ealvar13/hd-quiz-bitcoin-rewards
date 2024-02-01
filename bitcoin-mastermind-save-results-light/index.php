<?php
/*
 * Plugin Name: Bitcoin Mastermind - Save Results Light
 * Description: Addon for Bitcoin Mastermind to save quiz results - Light version
 * Plugin URI: https://harmonicdesign.ca/addons/save-results-light/
 * Author: Harmonic Design
 * Author URI: https://harmonicdesign.ca
 * Version: 0.4
*/

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!defined('bitc_A_LIGHT_PLUGIN_VERSION')) {
    define('bitc_A_LIGHT_PLUGIN_VERSION', '0.4');
}

/* Automatically deactivate if Bitcoin Mastermind is not active
------------------------------------------------------- */
function bitc_a_light_check_hd_quiz_active()
{
    if (function_exists('is_plugin_active')) {
        if (!is_plugin_active("hd-quiz/index.php")) {
            deactivate_plugins(plugin_basename(__FILE__));
        }
    }
}
add_action('init', 'bitc_a_light_check_hd_quiz_active');

/* Include the basic required files
------------------------------------------------------- */
require dirname(__FILE__) . '/includes/functions.php'; // general functions


/* Create Bitcoin Mastermind Results light Settings page
------------------------------------------------------- */
function bitc_a_light_create_settings_page()
{
    function bitc_a_light_register_settings_page()
    {
        add_submenu_page('bitc_quizzes', 'Results', 'Results', 'publish_posts', 'bitc_results', 'bitc_a_light_register_quizzes_page_callback');
    }
    add_action('admin_menu', 'bitc_a_light_register_settings_page', 11);
}
//add_action('init', 'bitc_a_light_create_settings_page');

function bitc_a_light_register_quizzes_page_callback()
{
    require dirname(__FILE__) . '/includes/results.php';
}
