<?php
// show results and settings tabs
wp_enqueue_style(
    'bitc_admin_style',
    plugin_dir_url(__FILE__) . './css/bitc_a_light_admin_style.css?v=' . bitc_A_LIGHT_PLUGIN_VERSION
);
wp_enqueue_script(
    'bitc_admin_script',
    plugins_url('./js/bitc_a_light_admin.js?v=' . bitc_A_LIGHT_PLUGIN_VERSION, __FILE__),
    array('jquery'),
    '1.0',
    true
);

$opt_name1 = 'bitc_a_l_members_only';
$hidden_field_name = 'hd_submit_hidden';
$data_field_name1 = 'bitc_a_l_members_only';

// Read in existing option value from database
$opt_val1 = sanitize_text_field(get_option($opt_name1));

// See if the user has posted us some information
if (isset($_POST['bitc_about_options_nonce'])) {
    $bitc_nonce = $_POST['bitc_about_options_nonce'];
    if (wp_verify_nonce($bitc_nonce, 'bitc_about_options_nonce') != false) {
        // Read their posted value
        if (isset($_POST[$data_field_name1])) {
            $opt_val1 = sanitize_text_field($_POST[$data_field_name1]);
        } else {
            $opt_val1 = "";
        }
        // Save the posted value in the database
        update_option($opt_name1, $opt_val1);
    }
}
?>
<div id="bitc_meta_forms">
    

    <div id="bitc_wrapper">
        <div id="bitc_form_wrapper">
            <h1>Bitcoin Mastermind Results - Light</h1>
            <p>
                This is the light version of this plugin and as such, has limited functionality. Generally speaking,
                this version is meant to be used more as an analytical tool so that you can see when users are completing
                quizzes and roughly how well they are performing.
            </p>

            <p>
                NOTE: The main Bitcoin Mastermind plugin never stores <em>any</em> user information for submitted quizzes and thus
                is 100% GDPR compliant. The use of this addon, however, requires storing some information when a user
                submits a quiz meaning that you will need to update your privacy policy to disclose this if you wish to
                be GDPR compliant.
            </p>

            <div id="bitc_srp">
                <div style="display: grid; grid-template-columns: 1fr max-content; grid-gap: 4em">
                    <div>
                        <p>
                            <strong>Announcing the Save Results Pro addon</strong>
                        </p>

                        <p>
                            With the Pro version of this addon, you will be able to add a custom form that either needs to be completed before starting a quiz, or before submitting the quiz to get your results. You also know each individual answer that a user makes, the time taken to complete the quiz, and you'll be able to sort / filter your results by date, quiz name, result (pass or fail), and user.
                        </p>
                        <p>
                            <strong>NEW:</strong> The Pro addon now also has included leaderboard functionality. You can either automatically add a leaderboard after each page, or use a shortcode to add a leaderboard to any page or post you want.
                        </p>
                        <p>
                            <a href="https://harmonicdesign.ca/product/hd-quiz-save-results-pro/?utm_source=HDQuiz&utm_medium=hdql" style="text-decoration:none" class="bitc_button2" target="_blank">VIEW ADDON PAGE</a>
                        </p>
                    </div>
                    <ul style="font-weight: bold; line-height: 1.8">
                        <li>+ save quiz taker name and email</li>
                        <li>+ add custom form fields</li>
                        <li>+ send results via email</li>
                        <li>+ sort and filter results</li>
                        <li>+ save each question result</li>
                        <li>+ NEW: leaderboard</li>
                    </ul>
                </div>
            </div>

            <div id="bitc_tabs">
                <ul>
                    <li class="bitc_active_tab" data-hdq-content="bitc_tab_content">Results</li>
                    <li data-hdq-content="bitc_tab_settings">Settings</li>
                </ul>
                <div class="clear"></div>
            </div>
            <div id="bitc_tab_content" class="bitc_tab">

                <?php
                global $wpdb;
                $table_name = $wpdb->prefix.'bitcoin_quiz_results';
                $data =  $wpdb->get_results("SELECT * FROM $table_name", OBJECT);                
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


                <table class="bitc_a_light_table">
                    <thead>
                        <tr>
                            <th>Quiz Name</th>
                            <th>Datetime (MM-DD-YYY)</th>
                            <th>Score</th>
                            <th>User</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($data != "" && $data != null) {

                            $x = 0;

                            //print_r($data);
                            foreach ($data as $d) {
                                $x++;
                                $quizName = sanitize_text_field($d->quiz_name);
                                $datetime = sanitize_text_field($d->timestamp);
                                $score = sanitize_text_field($d->quiz_result);
                                $user = sanitize_text_field($d->user_id);
                                $passPercent = "";
                                 if (bitc_PLUGIN_VERSION < 1.8) {
                                    $bitc_quiz_options = bitc_get_quiz_options($d->quiz_id);
                                    $passPercent = intval($bitc_quiz_options["passPercent"]);
                                } else {
                                    $bitc_quiz_options = get_bitc_quiz($d->quiz_id);
                                    $passPercent = $bitc_quiz_options["quiz_pass_percentage"]["value"];
                                }
                               //$d["passPercent"] = intval($d["passPercent"]);
                               // echo $passPercent;
                                //echo $score;

                                // Attempt to convert string to integer
                               // Use eval to evaluate the fractional expression
                                $result = eval('return ' . $score . ';');
                                $updated_score = "";
                                // Check if the result is not null (indicating a successful evaluation)
                                if ($result !== null) {
                                    // Print the result
                                    $updated_score =  $result * 100; // Output: 0.2
                                } else {
                                    // Handle the error if the evaluation fails
                                    echo "Error: Unable to evaluate the expression";
                                }

                                $passFail = "fail";
                                $test= (int)$score * 100;
                               // echo $test;
                                if ( $updated_score >= $passPercent) {
                                   $passFail = "pass";
                                } ?>
                                <tr class="<?php echo $passFail; ?>">
                                    <td><?php echo $quizName; ?></td>
                                    <td><?php echo $datetime; ?></td>
                                    <td><?php echo $score; ?></td>
                                    <td><?php echo $user; ?></td>
                                    <td><a href="javascript:void(0);" class="survey-results-details" id="<?php echo $d->id; ?>">Show Details</td>
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
            <div id="bitc_tab_settings" class="bitc_tab">
                <form id="bitc_settings" method="post">
                    <input type="hidden" name="bitc_submit_hidden" value="Y">
                    <?php wp_nonce_field('bitc_about_options_nonce', 'bitc_about_options_nonce'); ?>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; grid-gap: 2rem">
                        <div class="bitc_row">
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

                        <div class="bitc_row" style="text-align:right">
                            <input type="submit" class="bitc_button2" id="bitc_save_settings" value="SAVE">
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>