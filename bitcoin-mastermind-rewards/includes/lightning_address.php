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
	wp_enqueue_script('hdq-lightning-script', $script_path, array('jquery'), '', true);

	// Localize the script including the sats value and the BTCPay Server URL and API Key
	wp_localize_script('hdq-lightning-script', 'bitc_data', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'satsPerAnswer' => $sats_value,
		'btcpayUrl' => $btcpay_url,
		'btcpayApiKey' => $btcpay_api_key,
		'save_quiz_results_nonce'    => wp_create_nonce('save_quiz_results_nonce'),
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

// 🔐 AJAX handler to generate secure BOLT11 nonce
function generate_bolt11_nonce() {
    $nonce = wp_create_nonce('get_bolt11_nonce');
    wp_send_json_success($nonce);
}
add_action('wp_ajax_generate_bolt11_nonce', 'generate_bolt11_nonce');
add_action('wp_ajax_nopriv_generate_bolt11_nonce', 'generate_bolt11_nonce');


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
	error_log("➡ checking retry limit");
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


function count_attempts_by_lightning_address_ajax() {
    global $wpdb;

    // Require parameters
    if ( empty($_POST['lightningAddress']) || empty($_POST['quizID']) ) {
        wp_send_json_error(['message' => 'Missing lightningAddress or quizID']);
        return;
    }

    // Sanitize inputs
    $lightning_address = sanitize_text_field($_POST['lightningAddress']);
    $quiz_id           = intval($_POST['quizID']);

    // Count attempts
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_name} WHERE lightning_address = %s AND quiz_id = %d",
        $lightning_address,
        $quiz_id
    ));

    // Calculate remaining
    $max_retries        = intval(get_option("max_retries_for_{$quiz_id}", 0));
    $remaining_attempts = max(0, $max_retries - intval($count));

    wp_send_json_success([
        'count'              => intval($count),
        'max_retries'        => $max_retries,
        'remaining_attempts' => $remaining_attempts,
    ]);
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

// Securely generate LNURL-pay URL
function get_pay_url($email) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return null;
    [$user, $domain] = explode('@', $email);
    return "https://" . esc_attr($domain) . "/.well-known/lnurlp/" . esc_attr($user);
}

// Securely fetch URL data
function fetch_url($url) {
    $response = wp_remote_get($url);
    if (is_wp_error($response)) return null;
    return json_decode(wp_remote_retrieve_body($response), true);
}

// Generate BOLT11 invoice securely
function generate_bolt11_invoice($email, $amount, $caller_type) {
    $pay_url = get_pay_url($email);
    if (!$pay_url) return null;

    $lnurl_data = fetch_url($pay_url);
    if (!$lnurl_data || $lnurl_data['tag'] !== 'payRequest') return null;

    $callback_url = add_query_arg([
        'amount' => intval($amount) * 1000, // Convert sats to millisats
    ], $lnurl_data['callback']);

    $invoice_response = fetch_url($callback_url);
    return $invoice_response['pr'] ?? null;
}

function calculate_correct_answers($quiz_id, $user_answers_array) {
	global $wpdb;

	$correct_answers_count = 0;

	// Get quiz settings
	$quiz_settings = get_bitc_quiz($quiz_id);

	// Loop through each submitted question/answer pair
	foreach ($user_answers_array as $entry) {
		$question_id = intval($entry['key']);
		$selected_value = sanitize_text_field($entry['value']);

		$question = get_bitc_question($question_id);
		if (!$question || empty($question["answers"]["value"])) {
			continue;
		}

		// Get correct answers based on quiz settings
		$answers = $question["answers"]["value"];
		$correct_answers = bitc_get_question_answers($answers, $question["selected"]["value"], $quiz_settings["randomize_answers"]["value"][0]);

		// Check if user selected a correct answer
		foreach ($correct_answers as $answer_option) {
			if (!empty($answer_option['correct']) && $answer_option['correct'] == 1 && trim($answer_option['answer']) === trim($selected_value)) {
				$correct_answers_count++;
				break;
			}
		}
	}

	return $correct_answers_count;
}


// Calculate admin payout securely
function calculate_admin_payout_server($total_sats) {
	error_log("➡ calculating admin payout");
    if ($total_sats <= 20) return 1;
    if ($total_sats <= 30) return 2;
    if ($total_sats <= 40) return 3;
    if ($total_sats <= 50) return 4;
    if ($total_sats <= 100) return 5;
    return round($total_sats * 0.05);
}

// Updates quiz result data and answer details for a specific quiz attempt.
function bitc_update_quiz_results_by_attempt_id($attempt_id, $score_text, $total_sats, $selected_answers) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';
    $table_name2 = $wpdb->prefix . 'bitcoin_survey_results';

    // Update main quiz record
    $wpdb->update(
        $table_name,
        [
            'quiz_result' => $score_text,
            'satoshis_earned' => $total_sats,
            'send_success' => 1,
            'satoshis_sent' => $total_sats
        ],
        ['unique_attempt_id' => $attempt_id],
        ['%s', '%d', '%d', '%d'],
        ['%s']
    );

    // Get updated row ID to update answers
    $row_id = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE unique_attempt_id = %s",
        $attempt_id
    ));

    if (!$row_id) return;

    // Delete old survey answers just in case (optional)
    $wpdb->delete($table_name2, ['result_id' => $row_id]);

    $quiz_settings = get_bitc_quiz($wpdb->get_var($wpdb->prepare(
        "SELECT quiz_id FROM $table_name WHERE id = %d",
        $row_id
    )));

    foreach ($selected_answers as $item) {
        $key = $item['key'];
        $value = $item['value'];
        $question_title = sanitize_text_field(get_the_title($key));
        $question = get_bitc_question($key);
        $correct_answer = "";

        $ans_cor = bitc_get_question_answers($question["answers"]["value"], $question["selected"]["value"], $quiz_settings["randomize_answers"]["value"][0]);
        foreach ($ans_cor as $val) {
            if (!empty($val['correct']) && $val['correct'] == 1) {
                $correct_answer .= $val['answer'] . ",";
            }
        }

        $wpdb->insert(
            $table_name2,
            [
                'result_id' => $row_id,
                'question' => $question_title,
                'selected' => $value,
                'correct' => $correct_answer,
                'quiz_name' => $question["quiz_name"] ?? 'Unknown',
                'user_id' => '', // fill if available
            ]
        );
    }
}


// Calculate user payout securely
function calculate_user_payout_server($selected_answers, $quiz_id) {
    $sats_per_answer = get_option("sats_per_answer_for_" . $quiz_id, 0);
    $quiz_settings = get_bitc_quiz($quiz_id);
    $score = 0;

    foreach ($selected_answers as $answer) {
        $question_id = intval($answer['key']);
        $selected_value = sanitize_text_field($answer['value']);

        $question = get_bitc_question($question_id);
        $correct_answers = bitc_get_question_answers(
            $question["answers"]["value"],
            $question["selected"]["value"],
            $quiz_settings["randomize_answers"]["value"][0]
        );

        foreach ($correct_answers as $correct) {
            if (!empty($correct['correct']) && $correct['correct'] == 1 && $selected_value === $correct['answer']) {
                $score++;
                break;
            }
        }
    }

    $total_sats = $score * intval($sats_per_answer);
    return ['score' => $score, 'total_sats' => $total_sats];
}


function secure_quiz_payout_handler() {
    error_log('✅ secure_quiz_payout_handler called');

    if (!function_exists('generate_bolt11_invoice')) error_log("❌ generate_bolt11_invoice not found");
    if (!function_exists('bitc_execute_payment')) error_log("❌ bitc_execute_payment not found");
    if (!function_exists('bitc_save_quiz_results_internal')) error_log("❌ bitc_save_quiz_results_internal not found");

    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'get_bolt11_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce']);
        exit;
    }

    // ✅ Input sanitization
    $quiz_id = isset($_POST['quiz_id']) ? intval($_POST['quiz_id']) : 0;
    $lightning_address = isset($_POST['lightning_address']) ? sanitize_text_field($_POST['lightning_address']) : '';
    $selected_answers = isset($_POST['results_details_selections']) ? $_POST['results_details_selections'] : [];

    // ✅ Basic validation
    if (!$quiz_id || empty($lightning_address)) {
        wp_send_json_error(['message' => 'Missing required data.']);
        return;
    }

	// 🔐 Validate attempt_id and prevent replays
	$attempt_id = isset($_POST['attempt_id']) ? sanitize_text_field($_POST['attempt_id']) : '';

	if (empty($attempt_id)) {
		wp_send_json_error(['message' => 'Missing attempt ID.']);
		return;
	}

	global $wpdb;

	$already_paid = $wpdb->get_var($wpdb->prepare(
		"SELECT send_success FROM {$wpdb->prefix}bitcoin_quiz_results 
		WHERE unique_attempt_id = %s",
		$attempt_id
	));

	if ($already_paid == 1) {
		wp_send_json_error(['message' => 'This quiz attempt has already been rewarded.']);
		return;
	}


    // ✅ Check retry limits
    $attempt_data = count_attempts_by_lightning_address($lightning_address, $quiz_id);
    if (!empty($attempt_data['max_retries_exceeded'])) {
        wp_send_json_error(['message' => 'Max attempts exceeded.']);
        return;
    }

    // ✅ Calculate user payout server-side
    $user_payout = calculate_user_payout_server($selected_answers, $quiz_id);
    $score = $user_payout['score'];
    $total_sats = $user_payout['total_sats'];

    if ($total_sats <= 0) {
        wp_send_json_error(['message' => 'No sats earned.']);
        return;
    }

    // ✅ Calculate admin payout
    $admin_email = "ealvar13@coinos.io";
    $admin_sats = calculate_admin_payout_server($total_sats);

    // ✅ Generate invoices
    error_log("➡ generating user invoice");
    $user_invoice = generate_bolt11_invoice($lightning_address, $total_sats, 'user');
    error_log("➡ generating admin invoice");
    $admin_invoice = generate_bolt11_invoice($admin_email, $admin_sats, 'admin');

    if (empty($user_invoice) || empty($admin_invoice)) {
        wp_send_json_error(['message' => 'Invoice generation failed.']);
        return;
    }

    // ✅ Execute payments
    error_log("➡ executing user payment");
	$max_budget = get_option("max_satoshi_budget_for_" . $quiz_id, 0);
	$total_sent_so_far = get_total_sent_for_quiz($quiz_id);

	if (($total_sent_so_far + $total_sats) > $max_budget) {
		wp_send_json_error(['message' => 'This quiz has exceeded its reward budget.']);
		return;
	}

    $user_payment_result = bitc_execute_payment($user_invoice);
    error_log("✅ user payment result: " . print_r($user_payment_result, true));

    error_log("➡ executing admin payment");
    $admin_payment_result = bitc_execute_payment($admin_invoice);
    error_log("✅ admin payment result: " . print_r($admin_payment_result, true));

    if (empty($user_payment_result['success'])) {
        wp_send_json_error(['message' => 'User payment failed.']);
        return;
    }

    if (empty($admin_payment_result['success'])) {
        wp_send_json_error(['message' => 'Admin payment failed.']);
        return;
    }

    // ✅ Save complete results now that payout succeeded
	$score_text = $score . ' / ' . count($selected_answers);
	error_log("➡ saving quiz results post-payment");

	bitc_update_quiz_results_by_attempt_id($attempt_id, $score_text, $total_sats, $selected_answers);

    // ✅ Final response
    $response_data = [
        'message' => 'Payout successful',
        'satoshis_sent' => $total_sats,
        'score' => $score,
    ];
    error_log("✅ sending json: " . print_r($response_data, true));
    wp_send_json_success($response_data);
    exit;
}


add_action('wp_ajax_secure_quiz_payout', 'secure_quiz_payout_handler');
add_action('wp_ajax_nopriv_secure_quiz_payout', 'secure_quiz_payout_handler');



/**
 * Securely executes payment using the configured payout option (BTCPay, Alby, or LNBits).
 *
 * @param string $bolt11 The BOLT11 invoice to be paid.
 * @return array Payment result containing 'success' and 'details'.
 */
function bitc_execute_payment($bolt11) {
    $selected_payout_option = get_option('selected_payout_option', 'btcpay'); // default to btcpay
    error_log("➡ Executing payment using method: " . $selected_payout_option);

    // Handle BTCPay Server payment
    if ($selected_payout_option === 'btcpay') {
        $btcpay_url = rtrim(get_option('bitc_btcpay_url', ''), '/');
        $btcpay_api_key = get_option('bitc_btcpay_api_key', '');
        $store_id = get_option('bitc_btcpay_store_id', '');
        $crypto = 'BTC';

        $url = "$btcpay_url/api/v1/stores/$store_id/lightning/$crypto/invoices/pay";
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'token ' . $btcpay_api_key,
        ];
        $body = json_encode(['BOLT11' => $bolt11]);

        $response = wp_remote_post($url, [
            'headers' => $headers,
            'body' => $body,
            'timeout' => 45,
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'details' => $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        return (isset($data['status']) && $data['status'] === 'Complete')
            ? ['success' => true, 'details' => $data]
            : ['success' => false, 'details' => $data];
    }

    // Handle Alby payment
    if ($selected_payout_option === 'alby') {
        $alby_token = get_option('alby_token', '');
        $url = 'https://api.getalby.com/payments/bolt11';
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $alby_token,
        ];
        $body = json_encode(['invoice' => $bolt11]);

        $response = wp_remote_post($url, [
            'headers' => $headers,
            'body' => $body,
            'timeout' => 45,
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'details' => $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        return (isset($data['payment_preimage']))
            ? ['success' => true, 'details' => $data]
            : ['success' => false, 'details' => $data];
    }

    // Handle LNBits payment
    if ($selected_payout_option === 'lnbits') {
        $lnbits_url = rtrim(get_option('lnbits_url', ''), '/');
        $lnbits_api_key = get_option('lnbits_api_key', '');
        $url = "$lnbits_url/api/v1/payments";
        $headers = [
            'Content-Type' => 'application/json',
            'X-Api-Key' => $lnbits_api_key,
        ];
        $body = json_encode(['bolt11' => $bolt11]);

        $response = wp_remote_post($url, [
            'headers' => $headers,
            'body' => $body,
            'timeout' => 45,
        ]);

        if (is_wp_error($response)) {
            return ['success' => false, 'details' => $response->get_error_message()];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        return (isset($data['payment_hash']) && isset($data['checking_id']))
            ? ['success' => true, 'details' => $data]
            : ['success' => false, 'details' => $data];
    }

    // Fallback: no configured payout system
    return ['success' => false, 'details' => 'No valid payout system configured.'];
}


// Securely save quiz results internally
function bitc_save_quiz_results_internal($address, $result, $earned, $quiz_id, $success, $sent, $selections) {
    $_POST = [
        'lightning_address' => $address,
        'quiz_result' => $result,
        'satoshis_earned' => $earned,
        'quiz_id' => $quiz_id,
        'send_success' => $success,
        'satoshis_sent' => $sent,
        'selected_results' => http_build_query(['dataArray' => $selections]),
    ];

    ob_start();
    bitc_save_quiz_results();
    $response = json_decode(ob_get_clean(), true);
    return $response['success'] ?? false;
}


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

function bitc_save_quiz_results() {
	check_ajax_referer('save_quiz_results_nonce','nonce');
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

	// Generate a unique quiz attempt ID
	$unique_attempt_id = uniqid('attempt_', true);

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
			'quiz_id' => $quiz_id,
			'unique_attempt_id' => $unique_attempt_id
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
		echo json_encode(array(
			'success' => true,
			'satoshis_sent' => $satoshis_sent,
			'attempt_id' => $unique_attempt_id
		));
		
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

