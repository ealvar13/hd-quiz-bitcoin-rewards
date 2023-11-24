<?php
// Include the logic from settings.php and 
include 'settings.php';
include 'rewards.php';

function compute_rewards($correct_answers, $quiz_id) {
    // Fetch the sats per correct answer for this specific quiz from the database
    $sats_per_correct_answer = get_option("sats_per_answer_for_" . $quiz_id, 0); // default to 0 if not set
    return $correct_answers * $sats_per_correct_answer;
}


// show results and settings tabs
wp_enqueue_style(
    'hdq_admin_style',
    plugin_dir_url(__FILE__) . './css/hdq_a_light_admin_style.css?v=' . HDQ_A_LIGHT_PLUGIN_VERSION
);
wp_enqueue_script(
    'hdq_admin_script',
    plugins_url('./js/hdq_a_light_admin.js?v=' . HDQ_A_LIGHT_PLUGIN_VERSION, __FILE__),
    array('jquery'),
    '1.0',
    true
);

?>
<div id="hdq_meta_forms">
    <div id="hdq_wrapper">
        <div id="hdq_form_wrapper">
            <h1>HD Quiz Results - Bitcoin Rewards</h1>
            <p>
                This add-on allows you to send bitcoin rewards over the Lightning Network for correct quiz answers.
            </p>

            <p>
                NOTE: The main HD Quiz plugin never stores <em>any</em> user information for submitted quizzes and thus
                is 100% GDPR compliant. The use of this addon, however, requires storing some information when a user
                submits a quiz meaning that you will need to update your privacy policy to disclose this if you wish to
                be GDPR compliant.
            </p>

            <div id="hdq_tabs">
                <ul>
                    <li class="hdq_active_tab" data-hdq-content="hdq_tab_content">Results</li>
                    <li data-hdq-content="hdq_tab_rewards">Rewards</li>
                    <li data-hdq-content="hdq_tab_settings">Settings</li>
                </ul>
                <div class="clear"></div>
            </div>
            <div id="hdq_tab_content" class="hdq_tab">

                <?php
                $data = get_option("hdq_quiz_results_l");
                $data = json_decode(html_entity_decode($data), true);
                $total = 0;
                if (!empty($data)) {
                    $total = count($data);
                    if ($total > 1000) {
                        $total = 1000;
                    }
                }
                ?>

                <h3>
                    <?php echo $total; ?> records in table
                </h3>


                <table class="hdq_a_light_table">
                    <thead>
                        <tr>
                            <th>Quiz Name</th>
                            <th>Datetime (MM-DD-YYY)</th>
                            <th>Score</th>
                            <th>User</th>
                            <th>Rewards</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($data != "" && $data != null) {

                            $data = array_reverse($data);
                            $x = 0;
                            foreach ($data as $d) {
                                $x++;
                                $d["quizName"] = sanitize_text_field($d["quizName"]);
                                $d["datetime"] = sanitize_text_field($d["datetime"]);
                                $d["quizTaker"][1] = sanitize_text_field($d["quizTaker"][1]);
                                $d["score"][0] = intval($d["score"][0]);
                                $d["score"][1] = intval($d["score"][1]);
                                $d["passPercent"] = intval($d["passPercent"]);

                                $passFail = "fail";
                                if ($d["score"][0] / $d["score"][1] * 100 >= $d["passPercent"]) {
                                    $passFail = "pass";
                                } ?>
                                <tr class="<?php echo $passFail; ?>">
                                    <td><?php echo $d["quizName"]; ?></td>
                                    <td><?php echo $d["datetime"]; ?></td>
                                    <td><?php echo $d["score"][0]; ?>/<?php echo $d["score"][1]; ?></td>
                                    <td><?php echo $d["quizTaker"][1]; ?></td>
                                    <td><?php echo compute_rewards($d["score"][0], $d['quizID']); ?></td>
                                </tr>
                        <?php
                                // limit total results for super large datasets
                                if ($x >= 1000) {
                                    break;
                                }
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <div id="hdq_tab_settings" class="hdq_tab">
                <form id="hdq_settings" method="post">
                    <input type="hidden" name="hdq_submit_hidden" value="Y">
                    <?php wp_nonce_field('hdq_about_options_nonce', 'hdq_about_options_nonce'); ?>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; grid-gap: 2rem">
                        <div class="hdq_row" style="grid-column: span 2;">
                            <label for="hdq_a_l_members_only">Only save results for logged in users
                                <span class="hdq_tooltip hdq_tooltip_question">?<span class="hdq_tooltip_content"><span>By default, all results will be saved, and non-logged-in users will show up as
                                            <code>--</code></span></span></span></label>
                            <div class="hdq_check_row">
                                <div class="hdq-options-check">
                                    <input type="checkbox" id="hdq_a_l_members_only" name="hdq_a_l_members_only" value="yes" <?php if ($opt_val1 == "yes") {
                                                                                                                                    echo 'checked = ""';
                                                                                                                                } ?> />
                                    <label for="hdq_a_l_members_only"></label>
                                </div>

                                <div role="button" id="hdq_a_light_delete_results" class="hdq_button4" title="clear all of the current results and start from scratch"><span class="dashicons dashicons-trash"></span> DELETE ALL RESULTS</div>

                                <div id="hdq_a_light_export_csv_wrap">
                                    <div role="button" id="hdq_a_light_export_results" class="hdq_button3" title="clear all of the current results and start from scratch">EXPORT AS CSV</div>
                                </div>

                            </div>
                        </div>

                        <label style="grid-column: span 2;">Enter either Joltz or BTCPay Server Details and click SAVE
                                <span class="hdq_tooltip hdq_tooltip_question">?<span class="hdq_tooltip_content"><span>Only one is allowed. If one is filled, filling the other will erase the existing info.
                        </label>
                        
                        <div class="hdq_row">
                            <label for="<?php echo $data_field_name_joltz; ?>">Joltz Brand Id:</label>
                            <input type="text" id="<?php echo $data_field_name_joltz; ?>" name="<?php echo $data_field_name_joltz; ?>" value="<?php echo $opt_val_joltz; ?>">
                        </div>

                        <div class="hdq_row">
                            <label for="<?php echo $data_field_name_joltz_secret; ?>">Joltz Secret Key:</label>
                            <input type="text" id="<?php echo $data_field_name_joltz_secret; ?>" name="<?php echo $data_field_name_joltz_secret; ?>" value="<?php echo $opt_val_joltz_secret; ?>">
                        </div>
                        
                        <div class="hdq_row">
                            <label for="<?php echo $data_field_name_btcpay_url; ?>">BTCPay Server URL:</label>
                            <input type="text" id="<?php echo $data_field_name_btcpay_url; ?>" name="<?php echo $data_field_name_btcpay_url; ?>" value="<?php echo $opt_val_btcpay_url; ?>">
                        </div>
                        
                        <div class="hdq_row">
                            <label for="<?php echo $data_field_name_btcpay_store_id; ?>">BTCPay Server Store ID:</label>
                            <input type="text" id="<?php echo $data_field_name_btcpay_store_id; ?>" name="<?php echo $data_field_name_btcpay_store_id; ?>" value="<?php echo $opt_val_btcpay_store_id; ?>">
                        </div>

                        <div class="hdq_row">
                            <label for="<?php echo $data_field_name_btcpay_api_key; ?>">BTCPay Server API Key:</label>
                            <input type="text" id="<?php echo $data_field_name_btcpay_api_key; ?>" name="<?php echo $data_field_name_btcpay_api_key; ?>" value="<?php echo $opt_val_btcpay_api_key; ?>">
                        </div>

                        <div class="hdq_row">
                            <input type="submit" class="hdq_button2" id="hdq_save_settings" value="SAVE">
                        </div>

                    </div>
                </form>
            </div>
            <div id="hdq_tab_rewards" class="hdq_tab">
                <form id="hdq_settings" method="post">
                    <input type="hidden" name="hdq_submit_hidden" value="Y">
                    <?php wp_nonce_field('hdq_about_options_nonce', 'hdq_about_options_nonce'); ?>

                    <h3>Available Quizzes</h3>
                    
                    <?php 
                    $quizzes = fetch_all_quizzes();
                    if (!empty($quizzes)) {
                    ?>

                    <table class="hdq_a_light_table">
                        <thead>
                            <tr>
                                <th>Quiz Name</th>
                                <th>Shortcode</th>
                                <th>Bitcoin Rewards Enabled</th>
                                <th>Sats per correct answer</th>
                                <th>Max number retries</th>
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
                            </tr>
                        <?php 
                        }
                        ?>
                        </tbody>
                    </table>
                    
                    <button type="submit" name="hdq_rewards_save" class="hdq_button3">Save Rewards Settings</button>    

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