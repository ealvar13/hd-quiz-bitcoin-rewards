<?php
$opt_name1 = 'hdq_a_l_members_only';
$hidden_field_name = 'hd_submit_hidden';
$data_field_name1 = 'hdq_a_l_members_only';

// Declare Joltz variables for settings form
$opt_name_joltz = 'hdq_joltz_brand_id';
$opt_name_joltz_secret = 'hdq_joltz_secret';
$data_field_name_joltz = 'hdq_joltz_brand_id';
$data_field_name_joltz_secret = 'hdq_joltz_brand_secret';

// Declare BTCPay Server variables for settings form
$opt_name_btcpay_url = 'hdq_btcpay_url';
$opt_name_btcpay_store_id = 'hdq_btcpay_store_id';
$opt_name_btcpay_api_key = 'hdq_btcpay_api_key';
$data_field_name_btcpay_url = 'hdq_btcpay_url';
$data_field_name_btcpay_store_id = 'hdq_btcpay_store_id';
$data_field_name_btcpay_api_key = 'hdq_btcpay_api_key';

// Read in existing option values from database
$opt_val1 = sanitize_text_field(get_option($opt_name1));
$opt_val_joltz = sanitize_text_field(get_option($opt_name_joltz));
$opt_val_joltz_secret = sanitize_text_field(get_option($opt_name_joltz_secret));
$opt_val_btcpay_url = sanitize_text_field(get_option($opt_name_btcpay_url));
$opt_val_btcpay_api_key = sanitize_text_field(get_option($opt_name_btcpay_api_key));

if (!empty($opt_val_joltz) || !empty($opt_val_joltz_secret)) {
    if (!empty($opt_val_btcpay_url) || !empty($opt_val_btcpay_api_key)) {
        // Possibly notify the user that they can't fill in both sets of fields.
        // You can use WordPress's admin notice mechanism or any other notification system you have in place
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error is-dismissible">';
            echo '<p>You cannot fill in both Joltz and BTCPay Server details. Please fill only one set of fields.</p>';
            echo '</div>';
        });
        return;
    }
}

// See if the user has posted us some information
if (isset($_POST['hdq_about_options_nonce'])) {
    $hdq_nonce = $_POST['hdq_about_options_nonce'];

    if (wp_verify_nonce($hdq_nonce, 'hdq_about_options_nonce') !== false) {
        // Check if the Joltz Brand ID field is set and sanitize its value
        if (isset($_POST[$data_field_name_joltz])) {
            $opt_val_joltz = sanitize_text_field($_POST[$data_field_name_joltz]);
        } else {
            $opt_val_joltz = "";
        }

        // Check if the Joltz Brand Secret field is set and sanitize its value
        if (isset($_POST[$data_field_name_joltz_secret])) {
            $opt_val_joltz_secret = sanitize_text_field($_POST[$data_field_name_joltz_secret]);
        } else {
            $opt_val_joltz_secret = "";
        }

        // Check if the BTCPay Server URL field is set and sanitize its value
        if (isset($_POST[$data_field_name_btcpay_url])) {
            $opt_val_btcpay_url = sanitize_text_field($_POST[$data_field_name_btcpay_url]);
        } else {
            $opt_val_btcpay_url = "";
        }

        // Check if the BTCPay Server Store ID field is set and sanitize its value
        if (isset($_POST[$data_field_name_btcpay_store_id])) {
            $opt_val_btcpay_store_id = sanitize_text_field($_POST[$data_field_name_btcpay_store_id]);
        } else {
            $opt_val_btcpay_store_id = "";
        }

        // Check if the BTCPay Server API Key field is set and sanitize its value
        if (isset($_POST[$data_field_name_btcpay_api_key])) {
            $opt_val_btcpay_api_key = sanitize_text_field($_POST[$data_field_name_btcpay_api_key]);
        } else {
            $opt_val_btcpay_api_key = "";
        }
        
        // Save the sanitized Joltz values to the database
        update_option($opt_name_joltz, $opt_val_joltz);
        update_option($opt_name_joltz_secret, $opt_val_joltz_secret);

        // Save the sanitized BTCPay Server values to the database
        update_option($opt_name_btcpay_url, $opt_val_btcpay_url);
        update_option($opt_name_btcpay_api_key, $opt_val_btcpay_api_key);
        update_option($opt_name_btcpay_store_id, $opt_val_btcpay_store_id);

        // Read the posted value for the original field
        if (isset($_POST[$data_field_name1])) {
            $opt_val1 = sanitize_text_field($_POST[$data_field_name1]);
        } else {
            $opt_val1 = "";
        }

        // Save the original field's value in the database
        update_option($opt_name1, $opt_val1);
    }
}