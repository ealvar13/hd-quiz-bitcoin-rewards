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
        'btcpayApiKey' => $btcpay_api_key,
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
    $storeId = get_option('hdq_btcpay_store_id', '');
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

function hdq_save_quiz_results() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';

    // Get current user information
    $current_user = wp_get_current_user();
    
    // Collect data from the AJAX request
    $user_id = is_user_logged_in() ? $current_user->user_login : '0';
    $lightning_address = isset($_POST['lightning_address']) ? sanitize_text_field($_POST['lightning_address']) : '';
    $quiz_result = isset($_POST['quiz_result']) ? sanitize_text_field($_POST['quiz_result']) : '';
    $satoshis_earned = isset($_POST['satoshis_earned']) ? intval($_POST['satoshis_earned']) : 0;
    $quiz_name = isset($_POST['quiz_name']) ? sanitize_text_field($_POST['quiz_name']) : '';
    $send_success = isset($_POST['send_success']) ? intval($_POST['send_success']) : 0;
    $satoshis_sent = isset($_POST['satoshis_sent']) ? intval($_POST['satoshis_sent']) : 0;

    // Insert data into the database
    $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'lightning_address' => $lightning_address,
            'quiz_result' => $quiz_result,
            'satoshis_earned' => $satoshis_earned,
            'quiz_name' => $quiz_name,
            'send_success' => $send_success,
            'satoshis_sent' => $satoshis_sent
        ),
        array('%s', '%s', '%s', '%d', '%s', '%d', '%d')
    );

    // Send a response back to the AJAX request
    echo json_encode(array('success' => true));
    wp_die();
}

add_action('wp_ajax_hdq_save_quiz_results', 'hdq_save_quiz_results');        // If the user is logged in
add_action('wp_ajax_nopriv_hdq_save_quiz_results', 'hdq_save_quiz_results'); // If the user is not logged in
