<?php
/**
 * Plugin Name: Bitcoin-Mastermind
 * Description: Add-on for Bitcoin Mastermind that sends bitcoin rewards over the Lightning Network for correct quiz answers.
 * Plugin URI: github link to follow
 * Author: ealvar13
 * License: GPL-2.0+
 * Author URI: https://github.com/ealvar13
 * Version: 0.3.0
 */

require dirname(__FILE__) . '/bitcoin-mastermind/index.php';
require dirname(__FILE__) . '/bitcoin-mastermind-rewards/index.php';
require dirname(__FILE__) . '/bitcoin-mastermind-save-results-light/index.php';


// Register Ajax handler for printing a message

add_action('wp_ajax_print_message', 'ajax_print_message');

function ajax_print_message() {
	print_message();
	wp_die();
}
