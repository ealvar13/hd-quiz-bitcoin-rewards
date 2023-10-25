<?php

/**
 * Functions for configuring bitcoin rewards for each quiz.
 */

// Process form submission
if (isset($_POST['hdq_rewards_save'])) {
    $hdq_nonce = $_POST['hdq_about_options_nonce'];

    // Check if nonce is valid
    if (wp_verify_nonce($hdq_nonce, 'hdq_about_options_nonce') !== false) {
        // Loop through each quiz to save its settings
        $quizzes = fetch_all_quizzes();
        foreach ($quizzes as $quiz) {
            $quiz_id = $quiz['id'];

            // Fetch and sanitize checkbox value
            $reward_enabled_field = "enable_bitcoin_reward_for_" . $quiz_id;
            $reward_enabled_value = isset($_POST[$reward_enabled_field]) ? 'yes' : 'no';
            
            // Fetch and sanitize sats per answer value
            $sats_field = "sats_per_answer_for_" . $quiz_id;
            $sats_value = isset($_POST[$sats_field]) ? sanitize_text_field($_POST[$sats_field]) : "";

            // Fetch and sanitize max retries value
            $retries_field = "max_retries_for_" . $quiz_id;
            $retries_value = isset($_POST[$retries_field]) ? sanitize_text_field($_POST[$retries_field]) : "";

            // Now you'd save these values to the database. You can either save it individually or group it into an array and save as a single option.
            update_option($reward_enabled_field, $reward_enabled_value);
            update_option($sats_field, $sats_value);
            update_option($retries_field, $retries_value);
        }
    }
}

$hdq_functions_path = WP_PLUGIN_DIR . '/hd-quiz/includes/functions.php';
if (file_exists($hdq_functions_path)) {
    include_once $hdq_functions_path;
} else {
    // Log the error
    error_log("HD Quiz Plugin: Unable to find functions.php");
    
    // Attach the admin notice to the action hook
    add_action( 'admin_notices', 'hdq_admin_notice_error' );
}

function hdq_admin_notice_error() {
    $class = 'notice notice-error';
    $message = __( 'HD Quiz Plugin not found! Please ensure it is installed and active for the rewards functionality to work correctly.', 'hdq-rewards-plugin' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

function fetch_all_quizzes() {
    $quizzes = array();

    $taxonomy = 'quiz';
    $term_args = array(
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    );
    $tax_terms = get_terms($taxonomy, $term_args);

    if (!empty($tax_terms) && !is_wp_error($tax_terms)) {
        foreach ($tax_terms as $term) {
            $quiz_name = (function_exists('mb_strimwidth')) ? mb_strimwidth($term->name, 0, 50, "...") : $term->name;
            $quizzes[] = array(
                'name' => $quiz_name,
                'id' => $term->term_id,
                'shortcode' => '[HDquiz quiz = "' . $term->term_id . '"]'
            );
        }
    }

    return $quizzes;
}
