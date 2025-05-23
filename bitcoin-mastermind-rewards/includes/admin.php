<?php
// Include the logic from settings.php and 
include 'settings.php';
include 'rewards.php';

function compute_rewards($correct_answers, $quiz_id) {
    // Fetch the sats per correct answer for this specific quiz from the database
    $sats_per_correct_answer = get_option("sats_per_answer_for_" . $quiz_id, 0); // default to 0 if not set
    return $correct_answers * $sats_per_correct_answer;
}

function check_quiz_budgets() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bitcoin_quiz_results';

    // Get all quizzes
    $quizzes = fetch_all_quizzes();
    $budgets = [];

    foreach ($quizzes as $quiz) {
        // Fetch total satoshis sent for each quiz
        $total_sent = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(satoshis_sent) FROM $table_name WHERE quiz_name = %s",
            $quiz['name']
        ));

        // Fetch the set budget for the quiz
        $max_budget = get_option("max_satoshi_budget_for_" . $quiz['id'], 0);

        // Compare and store result
        $budgets[$quiz['name']] = [
            'total_sent' => $total_sent,
            'max_budget' => $max_budget,
            'within_budget' => ($total_sent <= $max_budget)
        ];
    }

    return $budgets;
}

// show results and settings tabs
wp_enqueue_style(
    'bitc_admin_style',
    plugin_dir_url(__FILE__) . './css/bitc_a_light_admin_style.css?v=' . bitc_BR_PLUGIN_VERSION
);
wp_enqueue_script(
    'bitc_admin_script',
    plugins_url('./js/bitc_a_light_admin.js?v=' . bitc_BR_PLUGIN_VERSION, __FILE__),
    array('jquery'),
    '1.0',
    true
);

?>
<div id="bitc_meta_forms">
     <div id="survey-modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4);">
        <div class="la-modal-content" style="background-color: #fefefe; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 80%; max-width: 500px; box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2); border-radius: 5px;">
            <span class="la-close" style="color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;">&times;</span>
            <p>Here are the results of the quiz:</p>
            <div id="survey-results-container"></div>            
        </div>
    </div>
    <div id="bitc_wrapper">
        <div id="bitc_form_wrapper">
            <h1>Bitcoin Mastermind - Bitcoin Settings and Rewards</h1>
            <p>
                These settings enable you to send bitcoin rewards over the Lightning Network for correct quiz answers.
            </p>

            <div id="bitc_tabs">
                <ul>
                    <li class="bitc_active_tab" data-hdq-content="bitc_tab_content">Results</li>
                    <li data-hdq-content="bitc_tab_rewards">Rewards</li>
                    <li data-hdq-content="bitc_tab_settings">Settings</li>
                    <li data-hdq-content="bitc_tab_payment_splits">Payment Splits</li>
                </ul>
                <div class="clear"></div>
            </div>
            <div id="bitc_tab_content" class="bitc_tab">

                <div class="bitc_row" style="grid-column: span 2;">
                              <div class="bitc_check_row">
                                    <div role="button" id="bitc_a_light_delete_results" class="bitc_button4" title="clear all of the current results and start from scratch"><span class="dashicons dashicons-trash"></span> DELETE ALL RESULTS</div>

                                    <div style="float: left;" id="bitc_a_light_export_csv_wrap">
                                        <div role="button" id="bitc_a_light_export_results" class="bitc_button3" title="clear all of the current results and start from scratch">EXPORT AS CSV</div>
                                    </div>

                                </div>
                              
                        </div>

                <?php
                $data = get_option("bitc_quiz_results_l");
                $data = json_decode(html_entity_decode($data), true);
                $total = 0;
                if (!empty($data)) {
                    $total = count($data);
                    if ($total > 1000) {
                        $total = 1000;
                    }
                }
                
                global $wpdb;
                $table_name = $wpdb->prefix . 'bitcoin_quiz_results';

                // Query to count the total number of records
                $total_query = "SELECT COUNT(*) FROM $table_name";
                $total_records = $wpdb->get_var($total_query);

                echo "<h3>" . esc_html($total_records) . " records in table</h3>";
                
                $table_name = $wpdb->prefix . 'bitcoin_quiz_results';
                $query = "SELECT * FROM $table_name ORDER BY timestamp DESC LIMIT 1000"; // Limiting to 1000 rows for performance
                $results = $wpdb->get_results($query, ARRAY_A);
                ?>

                <table class="bitc_a_light_table">
                    <thead>
                        <tr>
                            <th>Quiz Name</th>
                            <th>Datetime (MM-DD-YYY)</th>
                            <th>Score</th>
                            <th>Satoshis Earned</th>
                            <th>Send Success</th>
                            <th>Satoshis Sent</th>
                            <th>User</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($results) {
                            foreach ($results as $row) {
                                // Format the timestamp
                                $formatted_date = date("m-d-Y", strtotime($row['timestamp']));

                                // Format send success
                                $send_success = $row['send_success'] ? 'Yes' : 'No';

                                echo "<tr>";
                                echo "<td>" . esc_html($row['quiz_name']) . "</td>";
                                echo "<td>" . esc_html($formatted_date) . "</td>";
                                echo "<td>" . esc_html($row['quiz_result']) . "</td>";
                                echo "<td>" . intval($row['satoshis_earned']) . "</td>";
                                echo "<td>" . esc_html($send_success) . "</td>";
                                echo "<td>" . intval($row['satoshis_sent']) . "</td>";
                                echo "<td>" . esc_html($row['user_id']) . "</td>";
                                echo "<td><a href='javascript:void(0);' class='survey-results-details' id=".$row['id'].">Show Details</a></td>";

                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
                </div>
                
                <?php
                // Initialize variables for BTCPay Server settings
                $opt_val_btcpay_url = get_option('btcpay_url', '');
                $data_field_name_btcpay_url = 'btcpay_url';

                $opt_val_btcpay_store_id = get_option('btcpay_store_id', '');
                $data_field_name_btcpay_store_id = 'btcpay_store_id';

                $opt_val_btcpay_api_key = get_option('btcpay_api_key', '');
                $data_field_name_btcpay_api_key = 'btcpay_api_key';

                // Initialize variables for Alby settings
                $opt_val_alby_url = get_option('alby_url', '');
                $data_field_name_alby_url = 'alby_url';

                $opt_val_alby_token = get_option('alby_token', '');
                $data_field_name_alby_token = 'alby_token';

                // Initialize variables for LNBits settings
                $opt_val_lnbits_url = get_option('lnbits_url', '');
                $data_field_name_lnbits_url = 'lnbits_url';

                $opt_val_lnbits_admin_key = get_option('lnbits_api_key', '');
                $data_field_name_lnbits_admin_key = 'lnbits_api_key';
                ?>

                <div id="bitc_tab_settings" class="bitc_tab">
                    <form id="bitc_settings" method="post">
                        <input type="hidden" name="bitc_submit_hidden" value="Y">
                        <?php wp_nonce_field('bitc_about_options_nonce', 'bitc_about_options_nonce'); ?>

                        <?php
                        // Handle form submission
                        if (isset($_POST['bitc_about_options_nonce'])) {
                            $bitc_nonce = $_POST['bitc_about_options_nonce'];

                            // Verify the nonce to ensure the form was submitted from a valid source
                            if (wp_verify_nonce($bitc_nonce, 'bitc_about_options_nonce') !== false) {
                                // Check if the selected payout option is set and sanitize it
                                if (isset($_POST['wallet_selection'])) {
                                    $selected_payout_option = sanitize_text_field($_POST['wallet_selection']);
                                    // Save the selected payout option to the WordPress database
                                    update_option('selected_payout_option', $selected_payout_option);
                                }

                                // Save the BTCPay options
                                if (isset($_POST['btcpay_url'])) {
                                    update_option('btcpay_url', sanitize_text_field($_POST['btcpay_url']));
                                }
                                if (isset($_POST['btcpay_store_id'])) {
                                    update_option('btcpay_store_id', sanitize_text_field($_POST['btcpay_store_id']));
                                }
                                if (isset($_POST['btcpay_api_key'])) {
                                    update_option('btcpay_api_key', sanitize_text_field($_POST['btcpay_api_key']));
                                }

                                // Save the Alby options
                                if (isset($_POST['alby_url'])) {
                                    update_option('alby_url', sanitize_text_field($_POST['alby_url']));
                                }
                                if (isset($_POST['alby_token'])) {
                                    $sanitizedToken = sanitize_text_field($_POST['alby_token']);
                                    update_option('alby_token', $sanitizedToken);
                                    wp_cache_flush(); // Clear cache
                                    error_log('Alby token saved and cache flushed: ' . $sanitizedToken);
                                
                                    // Double-check the value
                                    $retrievedToken = get_option('alby_token', '');
                                    error_log('Alby token retrieved immediately after saving: ' . $retrievedToken);
                                }
                                

                                // Save the LNBits options
                                if (isset($_POST['lnbits_url'])) {
                                    update_option('lnbits_url', sanitize_text_field($_POST['lnbits_url']));
                                }
                                if (isset($_POST['lnbits_api_key'])) {
                                    update_option('lnbits_api_key', sanitize_text_field($_POST['lnbits_api_key']));
                                }
                            }
                        }

                        // Fetch the currently saved payout option from the database
                        $current_payout_option = get_option('selected_payout_option', 'btcpay'); // Default to 'btcpay' if not set
                        ?>

                        <!-- Hidden input to store the selected payout option -->
                        <input type="hidden" name="selected_payout_option" id="selected_payout_option" value="<?php echo esc_attr($current_payout_option); ?>">

                        <div style="display:grid; grid-template-columns: 1fr 1fr; grid-gap: 2rem">
                            <label style="grid-column: span 2;">Select the wallet connection to enable and click SAVE
                                <span class="bitc_tooltip bitc_tooltip_question">?
                                    <span class="bitc_tooltip_content">
                                        <span>Only one option is allowed. If more than one option is filled, the selected option will be used.</span>
                                    </span>
                                </span>
                            </label>

                            <!-- Radio Buttons to Select Between BTCPay, Alby, and LNBits -->
                            <div class="bitc_row" style="grid-column: span 2;">
                                <label>
                                    <input type="radio" name="wallet_selection" value="btcpay" id="btcpay_radio" <?php checked($current_payout_option, 'btcpay'); ?>>
                                    BTCPay Server
                                </label>
                                <label>
                                    <input type="radio" name="wallet_selection" value="alby" id="alby_radio" <?php checked($current_payout_option, 'alby'); ?>>
                                    Alby Wallet
                                </label>
                                <label>
                                    <input type="radio" name="wallet_selection" value="lnbits" id="lnbits_radio" <?php checked($current_payout_option, 'lnbits'); ?>>
                                    LNBits
                                </label>
                            </div>

                            <!-- BTCPay Server Settings -->
                            <div id="btcpay_settings" style="display: <?php echo ($current_payout_option === 'btcpay') ? 'block' : 'none'; ?>;">
                                <div class="bitc_row">
                                    <label for="<?php echo $data_field_name_btcpay_url; ?>">BTCPay Server URL:</label>
                                    <input type="text" id="<?php echo $data_field_name_btcpay_url; ?>" 
                                        name="btcpay_url" 
                                        value="<?php echo esc_attr($opt_val_btcpay_url); ?>">
                                </div>
                                <div class="bitc_row">
                                    <label for="<?php echo $data_field_name_btcpay_store_id; ?>">BTCPay Store ID:</label>
                                    <input type="text" id="<?php echo $data_field_name_btcpay_store_id; ?>" 
                                        name="btcpay_store_id" 
                                        value="<?php echo esc_attr($opt_val_btcpay_store_id); ?>">
                                </div>
                                <div class="bitc_row">
                                    <label for="<?php echo $data_field_name_btcpay_api_key; ?>">BTCPay API Key:</label>
                                    <input type="password" id="<?php echo $data_field_name_btcpay_api_key; ?>" 
                                        name="btcpay_api_key" 
                                        value="<?php echo esc_attr($opt_val_btcpay_api_key); ?>">
                                </div>
                            </div>

                            <!-- Alby Wallet Settings -->
                            <div id="alby_settings" style="display: <?php echo ($current_payout_option === 'alby') ? 'block' : 'none'; ?>;">
                                <div class="bitc_row">
                                    <label for="<?php echo $data_field_name_alby_url; ?>">Alby API Endpoint URL:</label>
                                    <input type="text" id="<?php echo $data_field_name_alby_url; ?>" 
                                        name="alby_url" 
                                        value="<?php echo esc_attr($opt_val_alby_url); ?>">
                                </div>
                                <div class="bitc_row">
                                    <label for="<?php echo $data_field_name_alby_token; ?>">Alby Account Access Token:</label>
                                    <input type="password" id="<?php echo $data_field_name_alby_token; ?>" 
                                        name="alby_token" 
                                        value="<?php echo esc_attr($opt_val_alby_token); ?>">
                                </div>
                            </div>

                            <!-- LNBits Settings -->
                            <div id="lnbits_settings" style="display: <?php echo ($current_payout_option === 'lnbits') ? 'block' : 'none'; ?>;">
                                <div class="bitc_row">
                                    <label for="<?php echo $data_field_name_lnbits_url; ?>">
                                        LNBits Server URL:
                                        <span class="bitc_tooltip bitc_tooltip_question">?
                                            <span class="bitc_tooltip_content">
                                                <span>Add the main URL for your LNBits server, for example: https://demo.lnbits.com/</span>
                                            </span>
                                        </span>
                                    </label>
                                    <input type="text" id="<?php echo $data_field_name_lnbits_url; ?>" 
                                        name="lnbits_url" 
                                        value="<?php echo esc_attr($opt_val_lnbits_url); ?>">
                                </div>

                                <div class="bitc_row">
                                    <label for="<?php echo $data_field_name_lnbits_admin_key; ?>">LNBits Admin Key:</label>
                                    <input type="password" id="<?php echo $data_field_name_lnbits_admin_key; ?>" 
                                        name="lnbits_api_key" 
                                        value="<?php echo esc_attr($opt_val_lnbits_admin_key); ?>">
                                </div>
                            </div>

                            <div class="bitc_row">
                                <input type="submit" class="bitc_button2" id="bitc_save_settings" value="SAVE">
                            </div>
                        </div>
                    </form>
                </div>


            <div id="bitc_tab_rewards" class="bitc_tab">
                <?php
                $total_sent_values = check_quiz_budgets();
                ?>
                <form id="bitc_rewards" method="post">
                    <input type="hidden" name="bitc_submit_hidden" value="Y">
                    <?php wp_nonce_field('bitc_rewards_nonce', 'bitc_rewards_nonce'); ?>

                    <h3>Available Quizzes</h3>
                    
                    <?php 
                    $quizzes = fetch_all_quizzes();
                    if (!empty($quizzes)) {
                    ?>

                    <table class="bitc_a_light_table">
                        <thead>
                            <tr>
                                <th>Quiz Name</th>
                                <th>Shortcode</th>
                                <th>Bitcoin Rewards Enabled</th>
                                <th>Sats per correct answer</th>
                                <th>Max number retries</th>
                                <th>Max Satoshi Budget</th> 
                                <th>Total Satoshi Sent</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php 
                        foreach ($quizzes as $quiz) {
                            $quiz_id = $quiz['id'];

                            // Fetch saved data for this quiz from the database
                            $reward_enabled_saved_value = get_option("enable_bitcoin_reward_for_" . $quiz_id, '');
                            $sats_saved_value = get_option("sats_per_answer_for_" . $quiz_id, '');
                            $retries_saved_value = get_option("max_retries_for_" . $quiz_id, '');
                            $max_budget_saved_value = get_option("max_satoshi_budget_for_" . $quiz_id, ''); // Fetch max budget value
                            $total_sent = isset($total_sent_values[$quiz['name']]) ? $total_sent_values[$quiz['name']] : 0; // Fetch total sent value
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($quiz['name']); ?></strong></td>
                                <td><code><?php echo esc_html($quiz['shortcode']); ?></code></td>
                                <td>
                                    <div class="hdq-options-check">
                                    <input type="checkbox" id="enable_bitcoin_reward_for_<?php echo esc_attr($quiz['id']); ?>" name="enable_bitcoin_reward_for_<?php echo esc_attr($quiz['id']); ?>" value="yes" <?php checked($reward_enabled_saved_value, 'yes'); ?>>
                                        <label for="enable_bitcoin_reward_for_<?php echo esc_attr($quiz['id']); ?>"></label>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" step="1" min="0" id="sats_per_answer_for_<?php echo esc_attr($quiz['id']); ?>" name="sats_per_answer_for_<?php echo esc_attr($quiz['id']); ?>" placeholder="Enter amount" value="<?php echo esc_attr($sats_saved_value); ?>">
                                </td>
                                <td>
                                    <input type="number" step="1" min="0" id="max_retries_for_<?php echo esc_attr($quiz['id']); ?>" name="max_retries_for_<?php echo esc_attr($quiz['id']); ?>" placeholder="Enter retries" value="<?php echo esc_attr($retries_saved_value); ?>">
                                </td>
                                <td>
                                    <input type="number" step="1" min="0" id="max_satoshi_budget_for_<?php echo esc_attr($quiz['id']); ?>" name="max_satoshi_budget_for_<?php echo esc_attr($quiz['id']); ?>" placeholder="Enter max budget" value="<?php echo esc_attr($max_budget_saved_value); ?>">
                                </td>
                                <td>
                                    <?php echo esc_html($total_sent['total_sent']); ?>
                                </td>
                            </tr>
                        <?php 
                        }
                        ?>
                        </tbody>
                    </table>
                    
                    <button type="submit" name="bitc_rewards_save" class="bitc_button3">Save Rewards Settings</button>    

                    <?php 
                    } else {
                        echo '<p>No quizzes found.</p>';
                    }
                    ?>
                </form>
            </div> 
            <div id="bitc_tab_payment_splits" class="bitc_tab" style="display:none;">
                <p>Bitcoin Mastermind is a 100% free plugin. 
                    We do not charge anything for downloading it and we don't do add-ons or premium versions.
                    We make bitcoin to fund this project by sending a tiny amount of each reward sent to our Lightning Address.
                </p>
                <table class="bitc_a_light_table">
                    <thead>
                        <tr>
                            <th>Amount of Reward</th>
                            <th>Satoshis Sent to us</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1 to 20 satoshis</td>
                            <td>1 satoshi</td>
                        </tr>
                        <tr>
                            <td>21 to 30 satoshis</td>
                            <td>2 satoshis</td>
                        </tr>
                        <tr>
                            <td>31 to 40 satoshis</td>
                            <td>3 satoshis</td>
                        </tr>
                        <tr>
                            <td>41 to 50 satoshis</td>
                            <td>4 satoshis</td>
                        </tr>
                        <tr>
                            <td>51 to 100 satoshis</td>
                            <td>5 satoshis</td>
                        </tr>
                        <tr>
                            <td>Over 100 satoshis</td>
                            <td>5% of Total satoshis (rounded)</td>
                        </tr>
                    </tbody>
                </table>
            </div>                                                                                 
        </div>
    </div>
</div>