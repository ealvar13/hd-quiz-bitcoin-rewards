<?php
/**
 * Add a custom table to the WordPress database for storing lightning addresses and rewards.
 */
//die("sdhajsdhajs");
function create_custom_bitcoin_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';
    $table_name2 = $wpdb->prefix . 'bitcoin_survey_results';
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' ); 

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
        result_id mediumint(9) NOT NULL
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


