<?php
/**
 * Lightning Address Add-On: Get User's Lighting Address on the quiz start.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue the JavaScript file
function hdq_enqueue_lightning_script() {
    $script_path = plugin_dir_url( __FILE__ ) . 'js/hdq_a_light_script.js';

    wp_enqueue_script('hdq-lightning-script', $script_path, array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'hdq_enqueue_lightning_script');

/**
 * Display a user input form to collect the Lightning Address at the start of the quiz.
 */
function la_input_lightning_address_on_quiz_start() {
    echo '<div class="hdq_row">';
    echo '<label for="lightning_address">Enter your Lightning Address: </label>';
    echo '<input type="text" id="lightning_address" name="lightning_address" placeholder="bolt@lightning.com">';
    echo '<input type="submit" class="hdq_button2" id="hdq_save_settings" value="SAVE" style="margin-left:10px;" onclick="validateEmail(event);">';
    echo '</div>';
}

// Attach our function to the 'hdq_before' action.
add_action('hdq_before', 'la_input_lightning_address_on_quiz_start');
