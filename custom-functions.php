<?php
// Ensure this file is being included by a WordPress installation
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

// Register AJAX handler for generating nonce
add_action('wp_ajax_generate_bolt11_nonce', 'generate_bolt11_nonce');
add_action('wp_ajax_nopriv_generate_bolt11_nonce', 'generate_bolt11_nonce');

function generate_bolt11_nonce() {
    $nonce = wp_create_nonce('get_bolt11_nonce');
    wp_send_json_success($nonce);
}


// Register AJAX handler for generating Bolt11 invoice
add_action('wp_ajax_getBolt11', 'getBolt11');
add_action('wp_ajax_nopriv_getBolt11', 'getBolt11'); // Allow non-logged-in users to access this if necessary


function getBolt11() {


	// Check if the required parameters are provided
	if (!isset($_POST['email']) || !isset($_POST['amount'])) {
		wp_send_json_error('Missing required parameters');
	}



	$email = sanitize_email($_POST['email']);
	$amount = intval($_POST['amount']);
    $callerType = sanitize_text_field($_POST['callerType']);

	// Validate email format
	if (!is_email($email)) {
		wp_send_json_error('Invalid email format');
	}



	try {
		$payUrl = get_pay_url($email);
		if (!$payUrl) {
			throw new Exception("Invalid URL generated");
		}

		$lnurlDetails = get_url($payUrl);
		if (!$lnurlDetails || !isset($lnurlDetails->callback)) {
			throw new Exception("LNURL details not found");
		}

		$minAmount = $lnurlDetails->minSendable;
		$payAmount = ($amount * 1000 > $minAmount) ? $amount * 1000 : $minAmount;

		$payQuery = "{$lnurlDetails->callback}?amount={$payAmount}";

		$prData = get_url($payQuery);
		if ($prData && isset($prData->pr)) {
            //error_log('🍩 Server Lightning response:: ' . json_encode($prData->pr));
            $transient_key = ($callerType === 'admin') ? 'admin_bolt11' : 'user_bolt11' ;
            set_transient($transient_key, $prData->pr, 600); // Store the Bolt11 invoice in transient for 10 minutes
			wp_send_json_success(strtoupper($prData->pr));
		} else {
			throw new Exception("Payment request generation failed: " . ($prData->reason ?? 'unknown reason'));
		}
	} catch (Exception $e) {
		wp_send_json_error($e->getMessage());
	}

	wp_die();
}

function get_pay_url($email) {
	$parts = explode('@', $email);
	if (count($parts) !== 2) {
		return null;
	}
	$domain = $parts[1];
	$username = $parts[0];
	return "https://{$domain}/.well-known/lnurlp/{$username}";
}

function get_url($url) {
	$response = wp_remote_get($url);
	if (is_wp_error($response)) {
		return null;
	}
	return json_decode(wp_remote_retrieve_body($response));
}

// Function to calculate admin payout
function calculateAdminPayout($totalSats) {
	if ($totalSats >= 10 && $totalSats <= 20) {
		return 1;
	} else if ($totalSats >= 21 && $totalSats <= 30) {
		return 2;
	} else if ($totalSats >= 31 && $totalSats <= 40) {
		return 3;
	} else if ($totalSats >= 41 && $totalSats <= 50) {
		return 4;
	} else if ($totalSats >= 51 && $totalSats <= 100) {
		return 5;
	} else {
		return round(($totalSats * 5) / 100);
	}
}

// Register AJAX handler for admin payout calculation
add_action('wp_ajax_calculateAdminPayout', 'calculateAdminPayoutAjax');
add_action('wp_ajax_nopriv_calculateAdminPayout', 'calculateAdminPayoutAjax'); // Allow non-logged-in users to access this if necessary

function calculateAdminPayoutAjax() {
	if (!isset($_POST['totalSats'])) {
		wp_send_json_error('Missing required parameter: totalSats');
	}

	$totalSats = intval($_POST['totalSats']);
	$adminPayout = calculateAdminPayout($totalSats);

	wp_send_json_success($adminPayout);
}