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
    $charset_collate = $wpdb->get_charset_collate();

    $quiz_table   = $wpdb->prefix . 'bitcoin_quiz_results';
    $survey_table = $wpdb->prefix . 'bitcoin_survey_results';

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

    dbDelta( $sql_quiz );
    dbDelta( $sql_survey );

    error_log( '✅ create_custom_bitcoin_table triggered' );
}

function ensure_unique_attempt_column() {
    global $wpdb;
    $table = $wpdb->prefix . 'bitcoin_quiz_results';

    $exists = $wpdb->get_var(
        "SHOW COLUMNS FROM `{$table}` LIKE 'unique_attempt_id'"
    );

    if ( 'unique_attempt_id' !== $exists ) {
        $wpdb->query(
            "ALTER TABLE `{$table}` 
             ADD COLUMN `unique_attempt_id` VARCHAR(255) DEFAULT NULL"
        );
        error_log( "✅ unique_attempt_id column added to {$table}" );
    }
}
