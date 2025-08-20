<?php
/**
 * db_operations.php
 *
 * Handles database schema for Bitcoin Quiz & Survey results.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Abort if accessed directly
}

function create_custom_bitcoin_table() {
    global $wpdb;
    
    error_log( '🔄 create_custom_bitcoin_table function called' );
    
    $charset_collate = $wpdb->get_charset_collate();

    $quiz_table   = $wpdb->prefix . 'bitcoin_quiz_results';
    $survey_table = $wpdb->prefix . 'bitcoin_survey_results';
    
    error_log( "🔄 Attempting to create tables: {$quiz_table}, {$survey_table}" );

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $sql_quiz = "
        CREATE TABLE `{$quiz_table}` (
            `id`                 MEDIUMINT(9)     NOT NULL AUTO_INCREMENT,
            `quiz_name`          VARCHAR(255)     NOT NULL DEFAULT '',
            `user_id`            VARCHAR(255)     DEFAULT NULL,
            `lightning_address`  VARCHAR(255)     NOT NULL DEFAULT '',
            `quiz_result`        VARCHAR(255)     NOT NULL DEFAULT '',
            `timestamp`          DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `satoshis_earned`    MEDIUMINT(9)     NOT NULL DEFAULT 0,
            `send_success`       TINYINT(1)       NOT NULL DEFAULT 0,
            `satoshis_sent`      MEDIUMINT(9)     NOT NULL DEFAULT 0,
            `quiz_id`            MEDIUMINT(9)     NOT NULL DEFAULT 0,
            `unique_attempt_id`  VARCHAR(255)     DEFAULT NULL,
            PRIMARY KEY  (`id`),
            KEY `unique_attempt_id` (`unique_attempt_id`)
        ) {$charset_collate};
    ";

    $sql_survey = "
        CREATE TABLE `{$survey_table}` (
            `id`       MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
            `question` VARCHAR(255) NOT NULL DEFAULT '',
            `result_id` MEDIUMINT(9) NOT NULL,
            `selected`  VARCHAR(255) NOT NULL DEFAULT '',
            `correct`   VARCHAR(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
        ) {$charset_collate};
    ";

    error_log( "🔄 SQL for quiz table: " . $sql_quiz );
    
    $result_quiz = dbDelta( $sql_quiz );
    $result_survey = dbDelta( $sql_survey );
    
    error_log( "🔄 dbDelta result for quiz table: " . print_r($result_quiz, true) );
    error_log( "🔄 dbDelta result for survey table: " . print_r($result_survey, true) );
    
    // Check if table was actually created
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$quiz_table}'");
    if ($table_exists) {
        error_log( "✅ Table {$quiz_table} exists after creation attempt" );
    } else {
        error_log( "❌ Table {$quiz_table} does NOT exist after creation attempt" );
        // Try alternative creation method
        $create_result = $wpdb->query($sql_quiz);
        error_log( "🔄 Direct wpdb->query result: " . $create_result );
        if ($wpdb->last_error) {
            error_log( "❌ SQL Error: " . $wpdb->last_error );
        }
    }

    error_log( '✅ create_custom_bitcoin_table triggered' );
}

function ensure_unique_attempt_column() {
    global $wpdb;
    $table = $wpdb->prefix . 'bitcoin_quiz_results';

    // First check if the table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'");
    if (!$table_exists) {
        error_log( "❌ Table {$table} does not exist, cannot add column. Creating table first..." );
        create_custom_bitcoin_table();
        return;
    }

    $exists = $wpdb->get_var(
        "SHOW COLUMNS FROM `{$table}` LIKE 'unique_attempt_id'"
    );

    if ( 'unique_attempt_id' !== $exists ) {
        $wpdb->query(
            "ALTER TABLE `{$table}` 
             ADD COLUMN `unique_attempt_id` VARCHAR(255) DEFAULT NULL"
        );
        if ($wpdb->last_error) {
            error_log( "❌ Error adding column to {$table}: " . $wpdb->last_error );
        } else {
            error_log( "✅ unique_attempt_id column added to {$table}" );
        }
    } else {
        error_log( "ℹ️ unique_attempt_id column already exists in {$table}" );
    }
}
