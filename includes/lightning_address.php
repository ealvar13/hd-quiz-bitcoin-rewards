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
    $script_path = plugin_dir_url( __FILE__ ) . 'js/hdq_a_light_script.js';

    wp_enqueue_script('hdq-lightning-script', $script_path, array('jquery'), '1.0.0', true);
    wp_localize_script('hdq-lightning-script', 'my_ajax_object', array('ajaxurl' => admin_url('admin-ajax.php')));
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