<?php
/**
 * Plugin Name: Bitcoin Mastermind - Bitcoin Rewards
 * Description: Add-on for Bitcoin Mastermind that sends bitcoin rewards over the Lightning Network for correct quiz answers.
 * Plugin URI: github link to follow
 * Author: ealvar13
 * License: GPL-2.0+
 * Author URI: https://github.com/ealvar13
 * Version: 0.1.0
 */

// Basic Security Check: If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('Invalid request.');
}

// Set a custom error log location.  REMOVE IN PRODUCTION
ini_set('log_errors', 'On');
ini_set('error_log', dirname(__FILE__) . '/custom_error_log.log');

// Start the PHP session, used to store Lightning Address until completion of quiz.
function start_session() {
    if(!session_id()) {
        session_start();
    }
}
add_action('init', 'start_session', 1);

// Define plugin version
if (!defined('bitc_A_LIGHT_PLUGIN_VERSION')) {
    define('bitc_A_LIGHT_PLUGIN_VERSION', '0.1');
}
// Define plugin version
if (!defined('bitc_BR_PLUGIN_VERSION')) {
    define('bitc_BR_PLUGIN_VERSION', '0.1');
}



/* Automatically deactivate if Bitcoin Mastermind is not active
------------------------------------------------------- */
function bitc_br_check_hd_quiz_active() {
    if (function_exists('is_plugin_active')) {
        if (!is_plugin_active("bitcoin-mastermind/index.php")) {
            deactivate_plugins(plugin_basename(__FILE__));
        }
    }
}
add_action('init', 'bitc_br_check_hd_quiz_active');


/* Include the basic required files
------------------------------------------------------- */
// require dirname(__FILE__) . '/includes/functions.php'; // commenting out for now, general functions for Bitcoin rewards
require dirname(__FILE__) . '/includes/lightning_address.php';
//require dirname(__FILE__) . '/includes/db_operations.php';
require dirname(__FILE__) . '/includes/api_endpoints.php';

/* Create Bitcoin Mastermind Bitcoin Rewards Settings page
------------------------------------------------------- */
function bitc_br_create_settings_page() {
    function bitc_br_register_settings_page() {
        add_submenu_page('bitc_quizzes', 'Bitcoin Settings & Rewards', 'Bitcoin Settings & Rewards', 'publish_posts', 'bitc_bitcoin_rewards', 'bitc_br_settings_page_callback');
    }
    add_action('admin_menu', 'bitc_br_register_settings_page', 11);
}
add_action('init', 'bitc_br_create_settings_page');


// Settings Page Callback: Load the Bitcoin Rewards settings page
function bitc_br_settings_page_callback() {
    require dirname(__FILE__) . '/includes/admin.php';
}



//add_action('admin_init','create_custom_bitcoin_table');

// Hook the table creation function to plugin activation
//register_activation_hook(__FILE__, 'create_custom_bitcoin_table');
// Correct file path example
//register_activation_hook(__DIR__ . '/index.php', 'create_custom_bitcoin_table');
