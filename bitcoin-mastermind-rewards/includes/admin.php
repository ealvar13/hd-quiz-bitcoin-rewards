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
    <div id="bitc_wrapper">
        <div id="bitc_form_wrapper">
            <h1>Bitcoin Mastermind Results - Bitcoin Rewards</h1>
            <p>
                This add-on allows you to send bitcoin rewards over the Lightning Network for correct quiz answers.
            </p>

            <p>
                NOTE: The main Bitcoin Mastermind plugin never stores <em>any</em> user information for submitted quizzes and thus
                is 100% GDPR compliant. The use of this addon, however, requires storing some information when a user
                submits a quiz meaning that you will need to update your privacy policy to disclose this if you wish to
                be GDPR compliant.
            </p>

            <div id="bitc_tabs">
                <ul>
                    <li class="bitc_active_tab" data-hdq-content="bitc_tab_content">Results</li>
                    <li data-hdq-content="bitc_tab_rewards">Rewards</li>
                    <li data-hdq-content="bitc_tab_settings">Settings</li>
                </ul>
                <div class="clear"></div>
            </div>
            <div id="bitc_tab_content" class="bitc_tab">

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
                ?>

                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'bitcoin_quiz_results';

                // Query to count the total number of records
                $total_query = "SELECT COUNT(*) FROM $table_name";
                $total_records = $wpdb->get_var($total_query);

                echo "<h3>" . esc_html($total_records) . " records in table</h3>";
                ?>


                <?php
                global $wpdb;
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
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div id="bitc_tab_settings" class="bitc_tab">
                <form id="bitc_settings" method="post">
                    <input type="hidden" name="bitc_submit_hidden" value="Y">
                    <?php wp_nonce_field('bitc_about_options_nonce', 'bitc_about_options_nonce'); ?>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; grid-gap: 2rem">
                        <div class="bitc_row" style="grid-column: span 2;">
                            <!--<label for="bitc_a_l_members_only">Only save results for logged in users
                                <span class="bitc_tooltip bitc_tooltip_question">?<span class="bitc_tooltip_content"><span>By default, all results will be saved, and non-logged-in users will show up as
                                            <code>--</code></span></span></span></label>
                            
                                <div class="hdq-options-check">
                                    <input type="checkbox" id="bitc_a_l_members_only" name="bitc_a_l_members_only" value="yes" <?php if ($opt_val1 == "yes") {
                                                                                                                                    echo 'checked = ""';
                                                                                                                                } ?> />
                                    <label for="bitc_a_l_members_only"></label>
                                </div>-->
                                <div class="bitc_check_row">
                                    <div role="button" id="bitc_a_light_delete_results" class="bitc_button4" title="clear all of the current results and start from scratch"><span class="dashicons dashicons-trash"></span> DELETE ALL RESULTS</div>

                                    <div id="bitc_a_light_export_csv_wrap">
                                        <div role="button" id="bitc_a_light_export_results" class="bitc_button3" title="clear all of the current results and start from scratch">EXPORT AS CSV</div>
                                    </div>

                                </div>
                        </div>

                        <label style="grid-column: span 2;">Enter either Joltz or BTCPay Server Details and click SAVE
                                <span class="bitc_tooltip bitc_tooltip_question">?<span class="bitc_tooltip_content"><span>Only one is allowed. If one is filled, filling the other will erase the existing info.
                        </label>
                        
                        <div class="bitc_row">
                            <label for="<?php echo $data_field_name_joltz; ?>">Joltz Brand Id:</label>
                            <input type="text" id="<?php echo $data_field_name_joltz; ?>" name="<?php echo $data_field_name_joltz; ?>" value="<?php echo $opt_val_joltz; ?>">
                        </div>

                        <div class="bitc_row">
                            <label for="<?php echo $data_field_name_joltz_secret; ?>">Joltz Secret Key:</label>
                            <input type="text" id="<?php echo $data_field_name_joltz_secret; ?>" name="<?php echo $data_field_name_joltz_secret; ?>" value="<?php echo $opt_val_joltz_secret; ?>">
                        </div>
                        
                        <div class="bitc_row">
                            <label for="<?php echo $data_field_name_btcpay_url; ?>">BTCPay Server URL:</label>
                            <input type="text" id="<?php echo $data_field_name_btcpay_url; ?>" name="<?php echo $data_field_name_btcpay_url; ?>" value="<?php echo $opt_val_btcpay_url; ?>">
                        </div>
                        
                        <div class="bitc_row">
                            <label for="<?php echo $data_field_name_btcpay_store_id; ?>">BTCPay Server Store ID:</label>
                            <input type="text" id="<?php echo $data_field_name_btcpay_store_id; ?>" name="<?php echo $data_field_name_btcpay_store_id; ?>" value="<?php if(!empty($opt_val_btcpay_store_id)): echo $opt_val_btcpay_store_id; endif;?>">
                        </div>

                        <div class="bitc_row">
                            <label for="<?php echo $data_field_name_btcpay_api_key; ?>">BTCPay Server API Key:</label>
                            <input type="text" id="<?php echo $data_field_name_btcpay_api_key; ?>" name="<?php echo $data_field_name_btcpay_api_key; ?>" value="<?php echo $opt_val_btcpay_api_key; ?>">
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
                    <?php wp_nonce_field('bitc_about_options_nonce', 'bitc_about_options_nonce'); ?>

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
        </div>
    </div>
</div>