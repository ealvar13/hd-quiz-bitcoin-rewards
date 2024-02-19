<?php
//error_reporting(-1);
//ini_set('display_errors', 1);
/**
 * Lightning Address Add-Ons:
 * Get the Lightning Address from the user and store it in the session.
 * Use the Lightning Address to send the reward.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enqueue the front-end stylesheet
function bitc_enqueue_lightning_la_style() {
    wp_enqueue_style(
        'bitc_front_end_style', 
        plugin_dir_url(dirname(__FILE__)) . 'includes/css/bitc_a_light_la_style.css',
        array(),
        bitc_A_LIGHT_PLUGIN_VERSION
    );
}
add_action('wp_enqueue_scripts', 'bitc_enqueue_lightning_la_style');

// Enqueue the JavaScript file
function bitc_enqueue_lightning_script() {
    global $post; 
    $quiz_id = $post->ID; 
    
    // Get the Satoshi value for the current quiz
    $sats_field = "sats_per_answer_for_" . $quiz_id;
    $sats_value = get_option($sats_field, 0); // Default to 0 if not set

    // Get the BTCPay Server URL and API Key
    $btcpay_url = get_option('bitc_btcpay_url', '');
    $btcpay_api_key = get_option('bitc_btcpay_api_key', '');

    $script_path = plugin_dir_url(__FILE__) . 'js/bitc_a_light_script.js';
    wp_enqueue_script('hdq-lightning-script', $script_path, array('jquery'), '1.0.0', true);

    // Localize the script including the sats value and the BTCPay Server URL and API Key
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

    return $rewards_enabled && !$over_budget;
}

// Function to display the quiz rules modal at the start of the quiz
function la_modal_html($quiz_id) {
    ?>
    <div id="la-modal" class="la-modal">
        <div class="la-modal-content">
            <span class="la-close">&times;</span>
            <p>Here are the rules:</p>
            <ul>
                <li>Enter a Bitcoin Lightning Address to get rewards.</li>
                <li>If you need a Lightning Address, get one here: <a href="https://lightningaddress.com/#providers" target="_blank">https://lightningaddress.com/#providers</a></li>
                <li>Each complete answer earns you <?php echo get_option('sats_per_answer_for_' . $quiz_id, 0); ?> satoshis.</li>
                <li>Don't worry, if something goes wrong, you still have  <?php echo get_option('max_retries_for_' . $quiz_id, 0); ?> tries per Lightning Address.</li>
                <li>For quizzes, rewards are based on correct answers.</li>
            </ul>
            <button id="la-start-quiz" class="la-start-quiz">Start</button>
            <div class="la-powered-by">Powered by <a href="https://velascommerce.com/bitcoin-mastermind/" target="_blank" class="la-powered-link">Bitcoin Mastermind</a></div>
        </div>
    </div>
    <?php
}


/**
 * Check if rewards are and should be enabled. 
 * If so, display a user input form to collect the Lightning Address at the start of the quiz.
 * Display quiz instructions in a modal.
 */
function la_input_lightning_address_on_quiz_start($quiz_id) {
    // Call the modal HTML function
    la_modal_html($quiz_id);
    
    if (should_enable_rewards($quiz_id, '')) {
        echo '<div class="bitc_input_container">';
            echo '<label for="lightning_address" class="bitc_input_label">Enter your Lightning Address: </label>';
            echo '<input type="text" id="lightning_address" name="lightning_address" class="bitc_lightning_input" placeholder="bolt@lightning.com">';
            echo '<div class="bitc_disclaimer">You need to enter a valid Lightning Address to receive rewards.</div>';
            echo '<input type="submit" class="bitc_button" id="bitc_save_settings" value="SAVE" onclick="validateLightningAddress(event);">';
        echo '</div>';
    } else {
        echo '<div class="bitc_input_container">Rewards are not currently available for this quiz. You can still take the quiz if you want though ; )</div>';
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

    $max_retries = get_option("max_retries_for_" . $quiz_id, 0);
    $max_retries_exceeded = intval($count) >= $max_retries;

    return ['count' => intval($count), 'max_retries_exceeded' => $max_retries_exceeded];
}


// Function to count the attempts a user's lightning address has made for a specific quiz
function count_attempts_by_lightning_address_ajax() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';

    if(!empty($_POST['lightningAddress']) && !empty($_POST['quizID'])){
        $lightning_address = $_POST['lightningAddress'];
        $quiz_id = $_POST['quizID'];
    }
    

    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE lightning_address = %s AND quiz_id = %d",
        $lightning_address, 
        $quiz_id
    ));

    $max_retries = get_option("max_retries_for_" . $quiz_id, 0);
    //$max_retries_exceeded = intval($count) >= $max_retries;
    $remaining_attempts = $max_retries - $count;
    echo json_encode(array('count' => intval($count), 'max_retries' => $max_retries,'remaining_attempts'=>$remaining_attempts));
    die;
}
add_action('wp_ajax_count_attempts_by_lightning_address_ajax', 'count_attempts_by_lightning_address_ajax');
add_action('wp_ajax_nopriv_count_attempts_by_lightning_address_ajax', 'count_attempts_by_lightning_address_ajax');



function store_lightning_address_in_session() {
    if (isset($_POST['address']) && isset($_POST['quiz_id'])) {
        $lightning_address = sanitize_text_field($_POST['address']);
        $quiz_id = intval($_POST['quiz_id']); // Fetch quiz_id from the POST data

        $max_retries = get_option("max_retries_for_" . $quiz_id, 0);
        $attempt_data = count_attempts_by_lightning_address($lightning_address, $quiz_id);
        $attempts = $attempt_data['count']; // Access the count of attempts
        $max_retries_exceeded = $attempt_data['max_retries_exceeded'];

        $_SESSION['max_retries_exceeded'] = $max_retries_exceeded;

        if (!$max_retries_exceeded) {
            $_SESSION['lightning_address'] = $lightning_address;
        } else {
            echo 'Maximum attempts reached for this Lightning Address. You can still proceed, you just won’t get sats ; )';
        }
    } else {
        echo 'No address or quiz ID provided.';
    }
    wp_die();
}

add_action('wp_ajax_store_lightning_address', 'store_lightning_address_in_session');        // If the user is logged in
add_action('wp_ajax_nopriv_store_lightning_address', 'store_lightning_address_in_session'); // If the user is not logged in

// Modal to display the steps of the payment process
function la_steps_indicator_modal() {
    ?>
    <div id="steps-modal" class="la-modal">
        <div class="la-modal-content">
            <span class="la-close">&times;</span>
            <h3>Processing Your Rewards</h3>
            <div id="steps-indicator" class="steps-indicator">
                <div id="step-calculating" class="step">Calculating Rewards</div>
                <div id="step-generating" class="step">Using your Lightning Address to generate Bolt 11 Invoice</div>
                <div id="step-reward" class="step">You earned <span id="satoshis-sent-display" class="reward-calculation">Calculating...</span> Satoshis.</div>
                <div id="step-sending" class="step">Sending Reward Payment</div>
                <div id="step-result" class="step">Awaiting Result...</div>
            </div>
            <button id="close-steps-modal" class="la-start-quiz">Close</button>
            <div class="la-powered-by">Powered by <a href="https://velascommerce.com/bitcoin-mastermind/" target="_blank" class="la-powered-link">Bitcoin Mastermind</a></div>
        </div>
    </div>
    <?php
}

function la_add_steps_indicator_modal($quiz_id) {
    // Call the steps indicator modal function
    la_steps_indicator_modal();
}

// Add the above function to the 'bitc_after' hook
add_action('bitc_after', 'la_add_steps_indicator_modal', 10, 1);

function bitc_pay_bolt11_invoice() {
    global $wpdb;

    // Retrieve quiz_id from POST data
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;

    $lightning_address = isset($_POST['lightning_address']) ? sanitize_text_field($_POST['lightning_address']) : '';
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;

    // Get attempt count and check if maximum retries have been exceeded
    $attempt_data = count_attempts_by_lightning_address($lightning_address, $quiz_id);
    if ($attempt_data['max_retries_exceeded']) {
        echo json_encode(['error' => 'Maximum attempts reached for this Lightning Address.']);
        wp_die();
    }

    // Check which payment option is configured
    $btcpayServerUrl = get_option('bitc_btcpay_url', '');
    $albyAccessToken = get_option('bitc_alby_token', '');

    if (!empty($btcpayServerUrl)) {
        // BTCPay Server is configured, process payment using BTCPay Server
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
            'timeout'     => 45,
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
    } elseif (!empty($albyAccessToken)) {
        // Alby is configured, process payment using Alby
        $bolt11 = isset($_POST['bolt11']) ? sanitize_text_field($_POST['bolt11']) : '';
        if (empty($bolt11)) {
            echo json_encode(['error' => 'Invoice is required.']);
            wp_die();
        }
        
        // Alby endpoint for processing payments
        $url = 'https://api.getalby.com/payments/bolt11';
        
        // Prepare the headers and body for the POST request to Alby
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $albyAccessToken,
        ];
        $body = json_encode(['invoice' => $bolt11]); 
        
        // Send payment request to Alby
        $response = wp_remote_post($url, [
            'headers' => $headers,
            'timeout'     => 45,
            'body' => $body,
            'data_format' => 'body',
        ]);
        
        if (is_wp_error($response)) {
            error_log('Alby payment request error: ' . $response->get_error_message());
            echo json_encode(['error' => 'Alby payment request failed', 'details' => $response->get_error_message()]);
            wp_die();
        }
    
        $responseBody = wp_remote_retrieve_body($response);
    
        // Decode JSON response
        $decodedResponse = json_decode($responseBody, true);
    
        // Check for a successful status or handle errors
        if (isset($decodedResponse['payment_preimage'])) {
            // Assuming 'payment_preimage' presence indicates a successful payment
            echo json_encode(['success' => true, 'details' => $decodedResponse]);
        } else {
            // Handle different errors based on your API response structure
            echo json_encode(['success' => false, 'details' => $decodedResponse]);
        }
    
        wp_die();
    } else {
        // No payment option is configured
        echo json_encode(['error' => 'No payment system is configured.']);
    }    

    wp_die();
}

// Register the new AJAX action
add_action('wp_ajax_pay_bolt11_invoice', 'bitc_pay_bolt11_invoice');        // If the user is logged in
add_action('wp_ajax_nopriv_pay_bolt11_invoice', 'bitc_pay_bolt11_invoice'); // If the user is not logged in

function bitc_save_quiz_results() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';
    $table_name2 = $wpdb->prefix . 'bitcoin_survey_results';

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
    $quiz_settings = get_bitc_quiz($quiz_id);

    foreach($dataResults as $key=>$value){
        $get_question_name = sanitize_text_field(get_the_title($key));
        $question = get_bitc_question($key);
        $answers = $question["answers"]["value"];
        $correct_answer = "";
        $ans_cor = bitc_get_question_answers($question["answers"]["value"], $question["selected"]["value"], $quiz_settings["randomize_answers"]["value"][0]);
        foreach($ans_cor as $val){
            if(!empty($val['correct']) && $val['correct']==1 ){
                 $correct_answer .= $val['answer'].",";
            }
        }

        $wpdb->insert(
        $table_name2,
        array(
            'result_id' => $last_insert_id,
            'question' => $get_question_name,
            'selected' => $value,
            'correct' => $correct_answer,
            'quiz_name' => $quiz_term ? $quiz_term->name : 'Unknown Quiz',
            'user_id' => $user_id

        ),
        array('%s', '%s', '%s', '%s', '%s', '%s')
    );

    }

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


function bitc_export_csv_results(){
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    global $wpdb;
    // Specify your table name
    $table1_name = $wpdb->prefix . 'bitcoin_quiz_results';
    $table2_name = $wpdb->prefix . 'bitcoin_survey_results';    
    // Fetch data from the first table
    $data1 = $wpdb->get_results("SELECT * FROM $table1_name", ARRAY_A);

    // Fetch data from the second table
    $data2 = $wpdb->get_results("SELECT * FROM $table2_name", ARRAY_A);

    // Create a ZIP file
    $zipFileName = 'exported_data.zip';
    $zip = new ZipArchive;
    if ($zip->open($zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        // Add the first CSV file
        $csvData1 = csvFromArray($data1);
        $zip->addFromString('table1_data.csv', $csvData1);

        // Add the second CSV file
        $csvData2 = csvFromArray($data2);
        $zip->addFromString('table2_data.csv', $csvData2);

        // Close the ZIP file
        $zip->close();

        // Respond with the ZIP file name
        echo json_encode(['zipFileName' => $zipFileName]);
    } else {
        echo json_encode(['error' => 'Failed to create ZIP file.']);
    }
    die;
    // Always exit after processing AJAX
    wp_die();
}

add_action('wp_ajax_export_csv_results', 'bitc_export_csv_results');
add_action('wp_ajax_nopriv_export_csv_results', 'bitc_export_csv_results');


// Function to convert array to CSV string
function csvFromArray($data) {
    $output = fopen('php://temp', 'w');
    fputcsv($output, array_keys($data[0])); // Header
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    rewind($output);
    $csv = stream_get_contents($output);
    fclose($output);
    return $csv;
}

