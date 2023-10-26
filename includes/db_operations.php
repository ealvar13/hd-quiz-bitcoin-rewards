<?php
/**
 * Add a custom table to the WordPress database for storing lightning addresses and rewards.
 */

function create_custom_bitcoin_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) DEFAULT NULL,
        lightning_address varchar(255) DEFAULT '' NOT NULL,
        quiz_result varchar(255) DEFAULT '' NOT NULL,
        timestamp datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        satoshis_earned mediumint(9) DEFAULT 0 NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    // Log to debug.log
    error_log("create_custom_bitcoin_table function was triggered!");
}
