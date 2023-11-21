<?php
/**
 * Lightning Address Add-Ons:
 * Get the Lightning Address from the user and store it in the session.
 * Use the Lightning Address to send the reward.
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

    // Get the BTCPay Server URL and API Key
    $btcpay_url = get_option('hdq_btcpay_url', '');
    $btcpay_api_key = get_option('hdq_btcpay_api_key', '');

    $script_path = plugin_dir_url(__FILE__) . 'js/hdq_a_light_script.js';
    wp_enqueue_script('hdq-lightning-script', $script_path, array('jquery'), '1.0.0', true);

    // Localize the script with your data including the sats value and the BTCPay Server URL and API Key
    wp_localize_script('hdq-lightning-script', 'hdq_data', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'satsPerAnswer' => $sats_value,
        'btcpayUrl' => $btcpay_url,
        'btcpayApiKey' => $btcpay_api_key
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

// Function to handle the payment of a BOLT11 invoice via BTCPay Server
function hdq_pay_bolt11_invoice() {
    $btcpayServerUrl = get_option('hdq_btcpay_url', '');
    $apiKey = get_option('hdq_btcpay_api_key', '');
    $storeId = "Avs6wUaaGPvLDJC936LSA3yebqTAuY4TkSexD8pxJXNR"; // Hardcoded for now
    $cryptoCode = "BTC"; // Hardcoded as BTC

    $bolt11 = isset($_POST['bolt11']) ? sanitize_text_field($_POST['bolt11']) : '';

    // Remove any trailing slashes
    $btcpayServerUrl = rtrim($btcpayServerUrl, '/');

    // Construct the correct URL
    $url = $btcpayServerUrl . "/api/v1/stores/" . $storeId . "/lightning/" . $cryptoCode . "/invoices/pay";

    $body = json_encode(['BOLT11' => $bolt11]);

    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'token ' . $apiKey,
        ],
        'body' => $body,
        'data_format' => 'body',
    ]);

    if (is_wp_error($response)) {
        echo json_encode(['error' => 'Payment request failed', 'details' => $response->get_error_message()]);
    } else {
        echo wp_remote_retrieve_body($response);
    }

    wp_die();
}

// Register the new AJAX action
add_action('wp_ajax_pay_bolt11_invoice', 'hdq_pay_bolt11_invoice');        // If the user is logged in
add_action('wp_ajax_nopriv_pay_bolt11_invoice', 'hdq_pay_bolt11_invoice'); // If the user is not logged in
