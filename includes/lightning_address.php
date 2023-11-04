<?php
/**
 * Lightning Address Add-On: Get User's Lighting Address on the quiz start.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue the stylesheet
function hdq_enqueue_lightning_style() {
    wp_enqueue_style(
        'hdq_admin_style',
        plugin_dir_url(__FILE__) . 'css/hdq_a_light_style.css',
        array(),
        HDQ_A_LIGHT_PLUGIN_VERSION
    );
}
add_action('wp_enqueue_scripts', 'hdq_enqueue_lightning_style');

// Enqueue the JavaScript file
function hdq_enqueue_lightning_script() {
    global $post; // Ensure you have access to the global post object
    $quiz_id = $post->ID; // This assumes that you are on a single quiz post. Adjust if necessary.
    
    // Get the Satoshi value for the current quiz
    $sats_field = "sats_per_answer_for_" . $quiz_id;
    $sats_value = get_option($sats_field, 0); // Default to 0 if not set
    error_log('Quiz ID on the front end: ' . $quiz_id);
    error_log('Sats per answer: ' . $sats_value);

    $script_path = plugin_dir_url(__FILE__) . 'js/hdq_a_light_script.js';
    wp_enqueue_script('hdq-lightning-script', $script_path, array('jquery'), '1.0.0', true);

    // Localize the script with your data including the sats value.
    wp_localize_script('hdq-lightning-script', 'hdq_data', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'satsPerAnswer' => $sats_value
    ));
}
add_action('wp_enqueue_scripts', 'hdq_enqueue_lightning_script');

/**
 * Display a user input form to collect the Lightning Address at the start of the quiz.
 */
function la_input_lightning_address_on_quiz_start() {
    echo '<div class="hdq_row">';
    echo '<label for="lightning_address" class="hdq_input">Enter your Lightning Address: </label>';
    echo '<input type="text" id="lightning_address" name="lightning_address" class="hdq_lightning_input" placeholder="bolt@lightning.com">';
    echo '<input type="submit" class="hdq_button" id="hdq_save_settings" value="SAVE" style="margin-left:10px;" onclick="validateLightningAddress(event);">';
    echo '</div>';
}

// Attach our function to the 'hdq_before' action.
add_action('hdq_before', 'la_input_lightning_address_on_quiz_start');

// Store the lightning address for the session
function store_lightning_address_in_session() {
    if (isset($_POST['address'])) {
        $_SESSION['lightning_address'] = sanitize_text_field($_POST['address']);
        echo 'Address stored successfully.';
    } else {
        echo 'No address provided.';
    }
    wp_die();
}

add_action('wp_ajax_store_lightning_address', 'store_lightning_address_in_session');        // If the user is logged in
add_action('wp_ajax_nopriv_store_lightning_address', 'store_lightning_address_in_session'); // If the user is not logged in