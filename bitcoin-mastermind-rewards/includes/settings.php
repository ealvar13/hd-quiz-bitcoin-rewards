<?php
$opt_name1 = 'bitc_a_l_members_only';
$hidden_field_name = 'hd_submit_hidden';
$data_field_name1 = 'bitc_a_l_members_only';

// Declare Joltz variables for settings form
$opt_name_joltz = 'bitc_joltz_id';
$opt_name_joltz_secret = 'bitc_joltz_secret';

// Declare BTCPay Server variables for settings form
$opt_name_btcpay_url = 'bitc_btcpay_url';
$opt_name_btcpay_store_id = 'bitc_btcpay_store_id';
$opt_name_btcpay_api_key = 'bitc_btcpay_api_key';

// Declare Alby variables for settings form
$opt_name_alby_url = 'bitc_alby_url';
$opt_name_alby_token = 'bitc_alby_token';

// Declare data field names for settings form
$data_field_name_joltz = 'bitc_joltz_brand_id';
$data_field_name_joltz_secret = 'bitc_joltz_brand_secret';

$data_field_name_btcpay_url = 'bitc_btcpay_brand_url';
$data_field_name_btcpay_store_id = 'bitc_btcpay_brand_store_id';
$data_field_name_btcpay_api_key = 'bitc_btcpay_brand_api_key';

$data_field_name_alby_url = 'bitc_alby_endpoint_url';
$data_field_name_alby_token = 'bitc_alby_access_token';

// Define default value for Alby API Endpoint URL
$default_alby_url = 'https://api.getalby.com';

// Read in existing option values from database
$opt_val1 = sanitize_text_field(get_option($opt_name1));
$opt_val_joltz = sanitize_text_field(get_option($opt_name_joltz));
$opt_val_joltz_secret = sanitize_text_field(get_option($opt_name_joltz_secret));
$opt_val_btcpay_url = sanitize_text_field(get_option($opt_name_btcpay_url));
$opt_val_btcpay_api_key = sanitize_text_field(get_option($opt_name_btcpay_api_key));
$opt_val_btcpay_store_id = sanitize_text_field(get_option($opt_name_btcpay_store_id));
$opt_val_alby_url = sanitize_text_field(get_option($opt_name_alby_url, $default_alby_url)); // Use default if not set
$opt_val_alby_token = sanitize_text_field(get_option($opt_name_alby_token));

if (!empty($_POST[$data_field_name_joltz]) || !empty($_POST[$data_field_name_joltz_secret])) {
    if (!empty($_POST[$data_field_name_btcpay_url]) || !empty($_POST[$data_field_name_btcpay_api_key])) {
        // Possibly notify the user that they can't fill in both sets of fields.
        // You can use WordPress's admin notice mechanism or any other notification system you have in place
       // die("dahgsdhadhgsadhgsadhgsadhgsaghdsadshgadhgsa");
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>You cannot fill in both Joltz and BTCPay Server details. Please fill only one set of fields.</p>';
            echo '</div>';
        });
        return;
    }
}

// See if the user has posted us some information
if (isset($_POST['bitc_about_options_nonce'])) {
    $bitc_nonce = $_POST['bitc_about_options_nonce'];

    if (wp_verify_nonce($bitc_nonce, 'bitc_about_options_nonce') !== false) {
        // Check if the Joltz Brand ID field is set and sanitize its value
        if (isset($_POST[$data_field_name_joltz])) {
            $opt_val_joltz = sanitize_text_field($_POST[$data_field_name_joltz]);
            // Save the sanitized Joltz values to the database
            update_option($opt_name_joltz, $opt_val_joltz);
        } 

        // Check if the Joltz Brand Secret field is set and sanitize its value
        if (isset($_POST[$data_field_name_joltz_secret])) {
            $opt_val_joltz_secret = sanitize_text_field($_POST[$data_field_name_joltz_secret]);
            update_option($opt_name_joltz_secret, $opt_val_joltz_secret);
        } 

        // Check if the BTCPay Server URL field is set and sanitize its value
        if (isset($_POST[$data_field_name_btcpay_url])) {
            $opt_val_btcpay_url = sanitize_text_field($_POST[$data_field_name_btcpay_url]);
            // Save the sanitized BTCPay Server values to the database
            update_option($opt_name_btcpay_url, $opt_val_btcpay_url);
        }

        // Check if the BTCPay Server Store ID field is set and sanitize its value
        if (isset($_POST[$data_field_name_btcpay_store_id])) {
            $opt_val_btcpay_store_id = sanitize_text_field($_POST[$data_field_name_btcpay_store_id]);
            update_option($opt_name_btcpay_store_id, $opt_val_btcpay_store_id);

        } 

        // Check if the BTCPay Server API Key field is set and sanitize its value
        if (isset($_POST[$data_field_name_btcpay_api_key])) {
            $opt_val_btcpay_api_key = sanitize_text_field($_POST[$data_field_name_btcpay_api_key]);
            update_option($opt_name_btcpay_api_key, $opt_val_btcpay_api_key);

        } 

        // Check if the Alby API Endpoint URL field is set and sanitize its value
        if (isset($_POST[$data_field_name_alby_url])) {
            $opt_val_alby_url = sanitize_text_field($_POST[$data_field_name_alby_url]);
            // Save the sanitized Alby values to the database
            update_option($opt_name_alby_url, $opt_val_alby_url);
        }

        // Check if the Alby Account Access Token field is set and sanitize its value
        if (isset($_POST[$data_field_name_alby_token])) {
            $opt_val_alby_token = sanitize_text_field($_POST[$data_field_name_alby_token]);
            update_option($opt_name_alby_token, $opt_val_alby_token);
        }
        
        // Read the posted value for the original field
        if (isset($_POST[$data_field_name1])) {
            $opt_val1 = sanitize_text_field($_POST[$data_field_name1]);
            // Save the original field's value in the database
            update_option($opt_name1, $opt_val1);
        } 

    }
}