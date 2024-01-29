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

// TODO: Get this working. Right now styling is inline
// Enqueue the front-end stylesheet
function bitc_enqueue_lightning_la_style() {
    wp_enqueue_style(
        'bitc_front_end_style', // Unique handle for your front-end style
        plugin_dir_url(dirname(__FILE__)) . 'includes/css/bitc_a_light_la_style.css',
        array(),
        bitc_A_LIGHT_PLUGIN_VERSION
    );
}
add_action('wp_enqueue_scripts', 'bitc_enqueue_lightning_la_style');

// Enqueue the JavaScript file
function bitc_enqueue_lightning_script() {
    global $post; // Ensure you have access to the global post object
    $quiz_id = $post->ID; // This assumes that you are on a single quiz post. Adjust if necessary.
    
    // Get the Satoshi value for the current quiz
    $sats_field = "sats_per_answer_for_" . $quiz_id;
    $sats_value = get_option($sats_field, 0); // Default to 0 if not set

    // Get the BTCPay Server URL and API Key
    $btcpay_url = get_option('bitc_btcpay_url', '');
    $btcpay_api_key = get_option('bitc_btcpay_api_key', '');

    $script_path = plugin_dir_url(__FILE__) . 'js/bitc_a_light_script.js';
    wp_enqueue_script('hdq-lightning-script', $script_path, array('jquery'), '1.0.0', true);

    // Localize the script with your data including the sats value and the BTCPay Server URL and API Key
    wp_localize_script('hdq-lightning-script', 'bitc_data', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'satsPerAnswer' => $sats_value,
        'btcpayUrl' => $btcpay_url,
        'btcpayApiKey' => $btcpay_api_key,
    ));
}
add_action('wp_enqueue_scripts', 'bitc_enqueue_lightning_script');

// Fetch the total satoshis sent for the current quiz
function get_total_sent_for_quiz($quiz_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';

    // Fetch the quiz name using the quiz ID
    $quiz_term = get_term_by('id', $quiz_id, 'quiz');
    if (!$quiz_term) {
        error_log("Quiz term not found for ID: $quiz_id");
        return 0; // Return 0 if the quiz is not found
    }
    $quiz_name = $quiz_term->name;

    // Fetch total satoshis sent for the specific quiz
    $total_sent = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(satoshis_sent) FROM $table_name WHERE quiz_name = %s",
        $quiz_name
    ));
    return $total_sent;
}


function should_enable_rewards($quiz_id, $lightning_address) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';

    // Check if rewards are enabled for this quiz
    $rewards_enabled = get_option("enable_bitcoin_reward_for_" . $quiz_id) === 'yes';
    
    // Fetch quiz name using the term associated with the quiz ID
    $quiz_term = get_term_by('id', $quiz_id, 'quiz');
    if (!$quiz_term) {
        error_log("Quiz term not found for quiz ID $quiz_id");
        return false; // Return false if the quiz is not found
    }
    $quiz_name = $quiz_term->name;

    // Check if the quiz is over budget
    $max_budget = get_option("max_satoshi_budget_for_" . $quiz_id);
    $total_sent = $wpdb->get_var($wpdb->prepare(
        "SELECT SUM(satoshis_sent) FROM $table_name WHERE quiz_name = %s",
        $quiz_name
    ));
    $over_budget = ($total_sent >= $max_budget);
    error_log("Max budget for quiz ID $quiz_id: $max_budget");
    error_log("Total sent for quiz ID $quiz_id: $total_sent");
    error_log("Quiz ID $quiz_id over budget: " . ($over_budget ? 'Yes' : 'No'));

    return $rewards_enabled && !$over_budget;
}

// Function to display the quiz rules modal at the start of the quiz
// TODO: remove the inline styles and add them to the stylesheet
function la_modal_html($quiz_id) {
    ?>
    <div id="la-modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
        <div class="la-modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2); border-radius: 5px;">
            <span class="la-close" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            <p>Here are the rules for the quiz:</p>
            <ul>
                <li>Enter a valid Bitcoin Lightning address to receive rewards.</li>
                <li>If you need a Lightning address, get one here for free: <a href="https://lightningaddress.com/#providers" target="_blank">https://lightningaddress.com/#providers</a></li>
                <li>Each correct answer will earn you <?php echo get_option('sats_per_answer_for_' . $quiz_id, 0); ?> satoshis.</li>
                <li>You have <?php echo get_option('max_retries_for_' . $quiz_id, 0); ?> tries.</li>
                <li>Rewards are calculated based on correct answers.</li>
            </ul>
            <button id="la-start-quiz" style="background-color: #FF9900; color: white; padding: 10px 20px; margin: 10px auto; border: none; cursor: pointer; border-radius: 4px; display: block;">Start Quiz</button>
        </div>
    </div>
    <?php
}



/**
 * Check if rewards are and should beenabled. 
 * If so, display a user input form to collect the Lightning Address at the start of the quiz.
 * Display quiz instructions in a modal.
 */
function la_input_lightning_address_on_quiz_start($quiz_id) {
    // Call the modal HTML function
    la_modal_html($quiz_id);
    
    if (should_enable_rewards($quiz_id, '')) {
        echo '<div class="bitc_row">';
        echo '<label for="lightning_address" class="bitc_input" style="font-size: 150%;">Enter your Lightning Address: </label>';
        echo '<input type="text" id="lightning_address" name="lightning_address" class="bitc_lightning_input" placeholder="bolt@lightning.com" style="padding: 0.8rem; font-size: 1.2em; width: 100%; color: #2d2d2d; border-bottom: 1px dashed #aaa; line-height: inherit; height: auto; cursor: initial; margin-bottom: 15px;">';
        echo '<input type="submit" class="bitc_button" id="bitc_save_settings" value="SAVE" style="margin-left:10px;" onclick="validateLightningAddress(event);">';
        echo '</div>';
    } else {
        echo '<div class="bitc_row">Rewards are not currently available for this quiz. You can still take the quiz if you want though ; )</div>';
    }
}

add_action('bitc_before', 'la_input_lightning_address_on_quiz_start', 10, 1);



// Function to count the attempts a user's lightning address has made for a specific quiz
function count_attempts_by_lightning_address($lightning_address, $quiz_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';

    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE lightning_address = %s AND quiz_id = %d",
        $lightning_address, 
        $quiz_id
    ));

    error_log("Retrieved count for $lightning_address, Quiz ID $quiz_id: $count");

    $max_retries = get_option("max_retries_for_" . $quiz_id, 0);
    $max_retries_exceeded = intval($count) >= $max_retries;

    return ['count' => intval($count), 'max_retries_exceeded' => $max_retries_exceeded];
}


function store_lightning_address_in_session() {
    if (isset($_POST['address']) && isset($_POST['quiz_id'])) {
        $lightning_address = sanitize_text_field($_POST['address']);
        $quiz_id = intval($_POST['quiz_id']); // Fetch quiz_id from the POST data
        error_log("POST Data: " . print_r($_POST, true));

        $max_retries = get_option("max_retries_for_" . $quiz_id, 0);
        $attempt_data = count_attempts_by_lightning_address($lightning_address, $quiz_id);
        $attempts = $attempt_data['count']; // Access the count of attempts
        $max_retries_exceeded = $attempt_data['max_retries_exceeded'];

        error_log("Max retries: $max_retries");
        error_log("Attempts: $attempts");
        $_SESSION['max_retries_exceeded'] = $max_retries_exceeded;

        if (!$max_retries_exceeded) {
            $_SESSION['lightning_address'] = $lightning_address;
            echo 'Address stored successfully.';
        } else {
            echo 'Maximum attempts reached for this Lightning Address. You can still take the quiz, but you won\'t get sats ; )';
        }
    } else {
        echo 'No address or quiz ID provided.';
    }
    wp_die();
}

add_action('wp_ajax_store_lightning_address', 'store_lightning_address_in_session');        // If the user is logged in
add_action('wp_ajax_nopriv_store_lightning_address', 'store_lightning_address_in_session'); // If the user is not logged in

// Modal to display the steps of the payment process
// TODO remove inline styles and add them to the stylesheet
function la_steps_indicator_modal() {
    ?>
    <div id="steps-modal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
        <div class="modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2); border-radius: 5px;">
            <span class="close-modal" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            <h2 style="margin-top: 0;">Processing Your Rewards</h2>
            <div id="steps-indicator" style="margin-top: 20px;">
                <div id="step-calculating" class="step" style="margin: 10px 0; padding: 5px; border: 1px solid #ddd; border-radius: 5px;">Calculating Rewards</div>
                <div id="step-generating" class="step" style="margin: 10px 0; padding: 5px; border: 1px solid #ddd; border-radius: 5px;">Using your Lightning Address to generate Bolt 11 Invoice</div>
                <div id="step-reward" class="step" style="margin: 10px 0; padding: 5px; border: 1px solid #ddd; border-radius: 5px;">You earned <span id="satoshis-sent-display">0</span> Satoshis.</div>
                <div id="step-sending" class="step" style="margin: 10px 0; padding: 5px; border: 1px solid #ddd; border-radius: 5px;">Sending Reward Payment</div>
                <div id="step-result" class="step" style="margin: 10px 0; padding: 5px; border: 1px solid #ddd; border-radius: 5px;">Awaiting Result...</div>
            </div>
            <button id="close-steps-modal" style="margin-top: 20px; padding: 10px 20px; background-color: #FF9900; color: white; border: none; border-radius: 4px; cursor: pointer;">Close</button>
        </div>
    </div>
    <?php
}

function la_add_steps_indicator_modal($quiz_id) {
    // Log a message for debugging purposes
    error_log("la_add_steps_indicator_modal called for quiz ID: " . $quiz_id);
    
    // Call the steps indicator modal function
    la_steps_indicator_modal();
}

// Add the above function to the 'bitc_after' hook
add_action('bitc_after', 'la_add_steps_indicator_modal', 10, 1);

function bitc_pay_bolt11_invoice() {
    global $wpdb;

    error_log("POST Data: " . print_r($_POST, true)); // Debug log to check all POST data
    // Retrieve quiz_id from POST data
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    error_log("Quiz ID from post data: " . $quiz_id);


    $lightning_address = isset($_POST['lightning_address']) ? sanitize_text_field($_POST['lightning_address']) : '';
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;

    // Get attempt count and check if maximum retries have been exceeded
    $attempt_data = count_attempts_by_lightning_address($lightning_address, $quiz_id);
    error_log("Attempt data: " . print_r($attempt_data, true));
    if ($attempt_data['max_retries_exceeded']) {
        echo json_encode(['error' => 'Maximum attempts reached for this Lightning Address.']);
        wp_die();
    }

    $lightning_address = isset($_POST['lightning_address']) ? sanitize_text_field($_POST['lightning_address']) : '';
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    $btcpayServerUrl = get_option('bitc_btcpay_url', '');
    $apiKey = get_option('bitc_btcpay_api_key', '');
    $storeId = get_option('bitc_btcpay_store_id', '');
    $cryptoCode = "BTC"; // Hardcoded as BTC
    $bolt11 = isset($_POST['bolt11']) ? sanitize_text_field($_POST['bolt11']) : '';

    // Remove any trailing slashes
    $btcpayServerUrl = rtrim($btcpayServerUrl, '/');

    // Construct the correct URL
    $url = $btcpayServerUrl . "/api/v1/stores/" . $storeId . "/lightning/" . $cryptoCode . "/invoices/pay";
    $body = json_encode(['BOLT11' => $bolt11]);

    // Send payment request to BTCPay Server
    $response = wp_remote_post($url, [
        'headers' => [
            'Content-Type'  => 'application/json',
            'Authorization' => 'token ' . $apiKey,
        ],
        'body' => $body,
        'data_format' => 'body',
    ]);

    if (is_wp_error($response)) {
        error_log('Payment request error: ' . $response->get_error_message());
        echo json_encode(['error' => 'Payment request failed', 'details' => $response->get_error_message()]);
    } else {
        $responseBody = wp_remote_retrieve_body($response);
        error_log('BTCPay Server response: ' . $responseBody);

        // Decode JSON response
        $decodedResponse = json_decode($responseBody, true);

        // Check if the payment status is 'Complete'
        if (isset($decodedResponse['status']) && $decodedResponse['status'] === 'Complete') {
            echo json_encode(['success' => true, 'details' => $decodedResponse]);
        } else {
            echo json_encode(['success' => false, 'details' => $decodedResponse]);
        }
    }

    wp_die();
}

// Register the new AJAX action
add_action('wp_ajax_pay_bolt11_invoice', 'bitc_pay_bolt11_invoice');        // If the user is logged in
add_action('wp_ajax_nopriv_pay_bolt11_invoice', 'bitc_pay_bolt11_invoice'); // If the user is not logged in

function bitc_save_quiz_results() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';

    // Decode the URL-encoded string
$decodedString = urldecode($_POST['selected_results']);


// Remove any trailing commas
$dataString = rtrim($decodedString, ',');

// Explode the string into key-value pairs
$pairs = explode('&', $dataString);

// Initialize an empty associative array
$resultArray = [];

// Loop through each key-value pair
foreach ($pairs as $pair) {
    // Explode the pair into key and value
    list($key, $value) = explode('=', $pair);

    // URL-decode and assign to the result array
    $resultArray[urldecode($key)] = urldecode($value);
}
// Initialize an empty associative array
$dataResults = array();

// Loop through each key-value pair in the provided array
foreach ($resultArray as $key => $value) {
    // Extract the numeric key from the string
    preg_match('/(\d+)/', $key, $matches);
    $numericKey = $matches[0];

    // Set the key-value pair in the result array
    $dataResults[$resultArray["dataArray[$numericKey][key]"]] = $resultArray["dataArray[$numericKey][value]"];
}

// Output the result
//print_r($dataResults);



   //print_r($_POST['selected_results']);
//die("sudhhhhhhhhhhhhhhhhhhhhh");
    // Get current user information
    $current_user = wp_get_current_user();

    // Collect data from the AJAX request
    $user_id = is_user_logged_in() ? $current_user->user_login : '0';
    $lightning_address = isset($_POST['lightning_address']) ? sanitize_text_field($_POST['lightning_address']) : '';
    $quiz_result = isset($_POST['quiz_result']) ? sanitize_text_field($_POST['quiz_result']) : '';
    $satoshis_earned = isset($_POST['satoshis_earned']) ? intval($_POST['satoshis_earned']) : 0;
    $quiz_id = isset($_POST['quiz_id']) ? sanitize_text_field($_POST['quiz_id']) : '';

    // Fetch quiz name using the term associated with the quiz ID
    $quiz_term = get_term_by('id', $quiz_id, 'quiz');
    $quiz_name = $quiz_term ? $quiz_term->name : 'Unknown Quiz';

    $send_success = isset($_POST['send_success']) ? intval($_POST['send_success']) : 0;
    $satoshis_sent = isset($_POST['satoshis_sent']) ? intval($_POST['satoshis_sent']) : 0;



    // Insert data into the database
    $insert_result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'lightning_address' => $lightning_address,
            'quiz_result' => $quiz_result,
            'satoshis_earned' => $satoshis_earned,
            'quiz_name' => $quiz_term ? $quiz_term->name : 'Unknown Quiz',
            'send_success' => $send_success,
            'satoshis_sent' => $satoshis_sent,
            'quiz_id' => $quiz_id
        ),
        array('%s', '%s', '%s', '%d', '%s', '%d', '%d', '%d')
    );

    // Get the last insert ID
    $last_insert_id = $wpdb->insert_id;

    if ($insert_result !== false) {
        // Success, send back the inserted data
        echo json_encode(array('success' => true, 'satoshis_sent' => $satoshis_sent));
    } else {
        // Error in insertion
        echo json_encode(array('success' => false, 'error' => 'Unable to save quiz results.'));
    }

    wp_die();
}

add_action('wp_ajax_bitc_save_quiz_results', 'bitc_save_quiz_results');
add_action('wp_ajax_nopriv_bitc_save_quiz_results', 'bitc_save_quiz_results');
