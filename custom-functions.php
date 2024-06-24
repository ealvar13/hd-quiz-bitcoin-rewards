<?php
// Ensure this file is being included by a WordPress installation
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function print_message() {
    echo "you did it 🚀 🚀 🚀 🍄 🤑";
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