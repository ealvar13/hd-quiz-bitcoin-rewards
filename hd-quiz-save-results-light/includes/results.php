<?php
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

$opt_name1 = 'hdq_a_l_members_only';
$hidden_field_name = 'hd_submit_hidden';
$data_field_name1 = 'hdq_a_l_members_only';

// Read in existing option value from database
$opt_val1 = sanitize_text_field(get_option($opt_name1));

// See if the user has posted us some information
if (isset($_POST['hdq_about_options_nonce'])) {
    $hdq_nonce = $_POST['hdq_about_options_nonce'];
    if (wp_verify_nonce($hdq_nonce, 'hdq_about_options_nonce') != false) {
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
<div id="hdq_meta_forms">
    <div id="hdq_wrapper">
        <div id="hdq_form_wrapper">
            <h1>HD Quiz Results - Light</h1>
            <p>
                This is the light version of this plugin and as such, has limited functionality. Generally speaking,
                this version is meant to be used more as an analytical tool so that you can see when users are completing
                quizzes and roughly how well they are performing.
            </p>

            <p>
                NOTE: The main HD Quiz plugin never stores <em>any</em> user information for submitted quizzes and thus
                is 100% GDPR compliant. The use of this addon, however, requires storing some information when a user
                submits a quiz meaning that you will need to update your privacy policy to disclose this if you wish to
                be GDPR compliant.
            </p>

            <div id="hdq_srp">
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
                            <a href="https://harmonicdesign.ca/product/hd-quiz-save-results-pro/?utm_source=HDQuiz&utm_medium=hdql" style="text-decoration:none" class="hdq_button2" target="_blank">VIEW ADDON PAGE</a>
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

            <div id="hdq_tabs">
                <ul>
                    <li class="hdq_active_tab" data-hdq-content="hdq_tab_content">Results</li>
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
                        <div class="hdq_row">
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

                        <div class="hdq_row" style="text-align:right">
                            <input type="submit" class="hdq_button2" id="hdq_save_settings" value="SAVE">
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>
</div>