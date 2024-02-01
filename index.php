<?php
/**
 * Plugin Name: Bitcoin-Mastermind
 * Description: Add-on for Bitcoin Mastermind that sends bitcoin rewards over the Lightning Network for correct quiz answers.
 * Plugin URI: github link to follow
 * Author: ealvar13
 * License: GPL-2.0+
 * Author URI: https://github.com/ealvar13
 * Version: 0.1.0
 */

require dirname(__FILE__) . '/bitcoin-mastermind/index.php';
require dirname(__FILE__) . '/bitcoin-mastermind-rewards/index.php';
require dirname(__FILE__) . '/bitcoin-mastermind-save-results-light/index.php';



function create_custom_bitcoin_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';
    $table_name2 = $wpdb->prefix . 'bitcoin_survey_results';
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); 

    //die("dsadsa");

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        quiz_name varchar(255) DEFAULT '' NOT NULL,
        user_id varchar(255) DEFAULT NULL,
        lightning_address varchar(255) DEFAULT '' NOT NULL,
        quiz_result varchar(255) DEFAULT '' NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        satoshis_earned mediumint(9) DEFAULT 0 NOT NULL,
        send_success boolean DEFAULT FALSE NOT NULL,      
        satoshis_sent mediumint(9) DEFAULT 0 NOT NULL,
        quiz_id mediumint(9) DEFAULT 0 NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $sql2 = "CREATE TABLE $table_name2 (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        question varchar(255) DEFAULT '' NOT NULL,
        quiz_name varchar(255) DEFAULT '' NOT NULL,
        user_id varchar(255) DEFAULT NULL,
        result_id mediumint(9) NOT NULL,
        selected varchar(255) DEFAULT '' NOT NULL,
        correct varchar(255) DEFAULT '' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    //echo $sql2;die("ddgsadhga");


    dbDelta( $sql );
    dbDelta( $sql2 );

    // Log to debug.log
    error_log("create_custom_bitcoin_table function was triggered!");
}
register_activation_hook(__FILE__, 'create_custom_bitcoin_table');