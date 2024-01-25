<?php
function bitc_register_tools__data_upgrade_page_callback()
{
    if (!current_user_can('edit_others_pages')) {
        die();
    }


    wp_enqueue_style(
        'bitc_admin_style',
        plugin_dir_url(__FILE__) . '../css/bitc_admin.css?v=' . bitc_PLUGIN_VERSION
    );

    wp_enqueue_script(
        'bitc_admin_script',
        plugins_url('../js/bitc_admin.js?v=' . bitc_PLUGIN_VERSION, __FILE__),
        array('jquery', 'jquery-ui-draggable'),
        bitc_PLUGIN_VERSION,
        true
    );

    wp_enqueue_script(
        'bitc_admin_script_data_update',
        plugins_url('../js/bitc_data_update.js?v=' . bitc_PLUGIN_VERSION, __FILE__),
        array('jquery'),
        bitc_PLUGIN_VERSION,
        true
    );

    wp_nonce_field('bitc_tools_nonce', 'bitc_tools_nonce');

    $questions = wp_count_posts('post_type_questionna');
    $questions = $questions->publish;
    $quizzes = wp_count_terms("quiz"); ?>

    <div id="main" style="max-width: 800px; background: #f3f3f3; border: 1px solid #ddd; margin-top: 2rem">
        <div id="header">
            <h1 id="heading_title" style="margin-top:0">
                Quiz and Question Data Upgrader
            </h1>
        </div>

        <p>Bitcoin Mastermind has grown considerably in features and complexity over the years making this page necessary. This tool is only needed for users upgrading from Bitcoin Mastermind 1.7 or lower. <strong>DO NOT USE if you are upgrading from a version higher than 1.7</strong></p>
		<p>
			
		</p>

        <div class="bitc_highlight">
            <p>

                <strong>NOTE:</strong> If for any reason, the data migration is not working, <strong>do no
                    worry</strong>. None of your old data has been deleted or modified in any way. In fact, you can
                easily replace this version of Bitcoin Mastermind by downloading the <a href="https://wordpress.org/plugins/hd-quiz/advanced/" target="_blank">previous version of Bitcoin Mastermind
                    here</a> <span class="bitc_tooltip">
                    ?
                    <span class="bitc_tooltip_content">
                        <span>The download link is at the very bottom of the page</span>
                    </span>
                </span>.
            </p>
            <p>
                Also, if you experience ANY issues with this, please <a href="https://harmonicdesign.ca/hd-quiz/" target="_blank">contact me at the offical HDQ Forum</a> so that I can fix the issue and ensure that no one else has to deal with the problem.
            </p>
        </div>

        <p>
            Upgrades will happen in two steps. The first step will be updating your quiz settings to the new version. Once that's done, the same will happen to all of your questions. <strong><u>DO NOT LEAVE THIS PAGE</u></strong> until the update has completed. The time it will take to update depends on how fast your server is and also how many questions need to be updated.
        </p>
        <p>
            Thank you for your patience and understanding. Needing to change data like this is hopefully a once in a lifetime event for Bitcoin Mastermind. This new method opens up a lot of doors to increase speed, security, and feature set of Bitcoin Mastermind.
        </p>
        <center>
            <div data-quizzes="<?php echo $quizzes; ?>" data-questions="<?php echo $questions; ?>" id="bitc_tool_update_data_start" class="bitc_button" role="button" title="Start update">
                BEGIN UPDATE
            </div>
        </center>

        <div id="bitc_message_logs"></div>


    </div>

<?php
}

function bitc_update_legacy_data()
{
    // get total number of questions
    $total = wp_count_posts('post_type_questionna');
    $total = $total->publish;
    // only run auto updated if total questions is less than 200
    // else, offer manual update function with ajax

    // update quiz meta data
    function bitc_update_legacy_quizzes()
    {
        $taxonomy = 'quiz';
        $term_args = array(
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        );
        $tax_terms = get_terms($taxonomy, $term_args);

        if (!empty($tax_terms) && !is_wp_error($tax_terms)) {
            foreach ($tax_terms as $tax_terms) {
                $quiz_id = $tax_terms->term_id;
                $term_meta = get_option("taxonomy_term_$quiz_id");
                if (isset($term_meta) && $term_meta != null && $term_meta != "") {
                    // make sure we do not already have quiz data
                    $existing = get_term_meta($quiz_id, "quiz_data", true);
                    if (!is_array($existing)) {						
                        $q = array();

                        $q["quiz_pass_percentage"]["type"] = "integer";
                        $q["quiz_pass_percentage"]["value"] = intval($term_meta["passPercent"]);
                        $q["quiz_pass_text"]["name"] = "quiz_pass_text";
                        $q["quiz_pass_text"]["type"] = "editor";
                        $q["quiz_pass_text"]["value"] = wp_kses_post($term_meta["passText"]);
                        $q["quiz_fail_text"]["name"] = "quiz_fail_text";
                        $q["quiz_fail_text"]["type"] = "editor";
                        $q["quiz_fail_text"]["value"] = wp_kses_post($term_meta["failText"]);
                        $q["share_results"]["name"] = "share_results";
                        $q["share_results"]["type"] = "checkbox";
                        $q["share_results"]["value"] = array(sanitize_text_field($term_meta["shareResults"]));
                        $q["results_position"]["name"] = "results_position";
                        $q["results_position"]["type"] = "radio";
                        $q["results_position"]["value"] = sanitize_text_field($term_meta["resultPos"]);

                        $q["show_results"]["name"] = "show_results";
                        $q["show_results"]["type"] = "checkbox";
                        $q["show_results"]["value"] = array(sanitize_text_field($term_meta["showResults"]));
                        $q["show_results_correct"]["name"] = "show_results_correct";
                        $q["show_results_correct"]["type"] = "checkbox";
                        $q["show_results_correct"]["value"] = array(sanitize_text_field($term_meta["showResultsCorrect"]));
                        $q["show_results_now"]["name"] = "show_results_now";
                        $q["show_results_now"]["type"] = "checkbox";
                        $q["show_results_now"]["value"] = array(sanitize_text_field($term_meta["immediateMark"]));
                        $q["stop_answer_reselect"]["name"] = "stop_answer_reselect";
                        $q["stop_answer_reselect"]["type"] = "checkbox";
                        $q["stop_answer_reselect"]["value"] = array(sanitize_text_field($term_meta["stopAnswerReselect"]));
                        $q["show_extra_text"]["name"] = "show_extra_text";
                        $q["show_extra_text"]["type"] = "checkbox";
                        $q["show_extra_text"]["value"] = array(sanitize_text_field($term_meta["showIncorrectAnswerText"]));

                        $q["quiz_timer"]["name"] = "quiz_timer";
                        $q["quiz_timer"]["type"] = "integer";
                        $q["quiz_timer"]["value"] = intval($term_meta["quizTimerS"]);

                        $q["quiz_timer_question"]["name"] = "quiz_timer_question";
                        $q["quiz_timer_question"]["type"] = "checkbox";
                        $q["quiz_timer_question"]["value"] = array("no");

                        $q["randomize_questions"]["name"] = "randomize_questions";
                        $q["randomize_questions"]["type"] = "checkbox";
                        $q["randomize_questions"]["value"] = array(sanitize_text_field($term_meta["randomizeQuestions"]));
                        $q["randomize_answers"]["name"] = "randomize_answers";
                        $q["randomize_answers"]["type"] = "checkbox";
                        $q["randomize_answers"]["value"] = array(sanitize_text_field($term_meta["randomizeAnswers"]));
                        $q["pool_of_questions"]["name"] = "pool_of_questions";
                        $q["pool_of_questions"]["type"] = "integer";
                        $q["pool_of_questions"]["value"] = intval($term_meta["pool"]);
                        $q["wp_paginate"]["name"] = "wp_paginate";
                        $q["wp_paginate"]["type"] = "integer";
                        $q["wp_paginate"]["value"] = intval($term_meta["paginate"]);
                        update_term_meta($quiz_id, "quiz_data", $q);
                    }
                }
            }
        }
    }

    // update quiz meta data
    function bitc_update_legacy_questions()
    {
        // WP_Query arguments
        $args = array(
            'post_type' => array('post_type_questionna'),
            'nopaging' => true,
            'posts_per_page' => '-1'
        );

        // The Query
        $query = new WP_Query($args);

        // The Loop
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();

                $allowed_html = array(
                    'strong' => array(),
                    'em' => array(),
                    'code' => array(),
                    'sup' => array(),
                    'sub' => array(),
                );

                $bitc_id = get_the_ID();

                // extra check to make sure we are not overwriting good data
                $existing = get_post_meta($questionID, "question_data", true);
                if (!is_array($existing)) {


                    $bitc_selected = array(intval(get_post_meta($bitc_id, 'hdQue_post_class2', true)));
                    $bitc_image_as_answer = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class23', true));
                    $bitc_question_as_title = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class24', true));
                    $bitc_paginate = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class25', true));
                    $bitc_tooltip = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class12', true));
                    $bitc_after_answer = wp_kses_post(get_post_meta($bitc_id, 'hdQue_post_class26', true));
                    $bitc_featured_image_id = get_post_thumbnail_id($bitc_id);

                    $bitc_1_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class1', true), $allowed_html);

                    $bitc_1_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class13', true));
                    $bitc_2_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class3', true), $allowed_html);
                    $bitc_2_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class14', true));
                    $bitc_3_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class4', true), $allowed_html);
                    $bitc_3_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class15', true));
                    $bitc_4_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class5', true), $allowed_html);
                    $bitc_4_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class16', true));
                    $bitc_5_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class6', true), $allowed_html);
                    $bitc_5_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class17', true));
                    $bitc_6_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class7', true), $allowed_html);
                    $bitc_6_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class18', true));
                    $bitc_7_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class8', true), $allowed_html);
                    $bitc_7_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class19', true));
                    $bitc_8_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class9', true), $allowed_html);
                    $bitc_8_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class20', true));
                    $bitc_9_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class10', true), $allowed_html);
                    $bitc_9_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class21', true));
                    $bitc_10_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class11', true), $allowed_html);
                    $bitc_10_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class22', true));

                    if ($bitc_1_image != "" && !is_numeric($bitc_1_image)) {
                        $bitc_1_image =  bitc_get_attachment_id($bitc_1_image);
                    }
                    if ($bitc_2_image != "" && !is_numeric($bitc_2_image)) {
                        $bitc_2_image =  bitc_get_attachment_id($bitc_2_image);
                    }
                    if ($bitc_3_image != "" && !is_numeric($bitc_3_image)) {
                        $bitc_3_image =  bitc_get_attachment_id($bitc_3_image);
                    }
                    if ($bitc_4_image != "" && !is_numeric($bitc_4_image)) {
                        $bitc_4_image =  bitc_get_attachment_id($bitc_4_image);
                    }
                    if ($bitc_5_image != "" && !is_numeric($bitc_5_image)) {
                        $bitc_5_image =  bitc_get_attachment_id($bitc_5_image);
                    }
                    if ($bitc_6_image != "" && !is_numeric($bitc_6_image)) {
                        $bitc_6_image =  bitc_get_attachment_id($bitc_6_image);
                    }
                    if ($bitc_7_image != "" && !is_numeric($bitc_7_image)) {
                        $bitc_7_image =  bitc_get_attachment_id($bitc_7_image);
                    }
                    if ($bitc_8_image != "" && !is_numeric($bitc_8_image)) {
                        $bitc_8_image =  bitc_get_attachment_id($bitc_8_image);
                    }
                    if ($bitc_9_image != "" && !is_numeric($bitc_9_image)) {
                        $bitc_9_image =  bitc_get_attachment_id($bitc_9_image);
                    }
                    if ($bitc_10_image != "" && !is_numeric($bitc_10_image)) {
                        $bitc_10_image =  bitc_get_attachment_id($bitc_10_image);
                    }


                    $q = array();
                    $q["title"]["value"] = get_the_title($bitc_id);
                    $q["title"]["type"] = "title";

                    $q["question_id"]["value"] = $bitc_id;
                    $q["question_id"]["type"] = "integer";

                    $q["selected"]["value"] = $bitc_selected;
                    $q["selected"]["type"] = "checkbox";

                    $question_type = "multiple_choice_text";
                    if ($bitc_image_as_answer === "yes") {
                        $question_type = "multiple_choice_image";
                    }
                    if ($bitc_question_as_title === "yes") {
                        $question_type = "title";
                    }

                    $q["question_type"]["value"] = $question_type;
                    $q["question_type"]["type"] = "select";

                    $q["paginate"]["value"] = array($bitc_paginate);
                    $q["paginate"]["type"] = "checkbox";
                    $q["tooltip"]["value"] = $bitc_tooltip;
                    $q["tooltip"]["type"] = "text";
                    $q["extra_text"]["value"] = $bitc_after_answer;
                    $q["extra_text"]["type"] = "editor";
                    $q["featured_image"]["value"] = $bitc_featured_image_id;
                    $q["featured_image"]["type"] = "image";

                    $answers = array();
                    array_push($answers, array("answer" => $bitc_1_answer, "image" => $bitc_1_image));
                    array_push($answers, array("answer" => $bitc_2_answer, "image" => $bitc_2_image));
                    array_push($answers, array("answer" => $bitc_3_answer, "image" => $bitc_3_image));
                    array_push($answers, array("answer" => $bitc_4_answer, "image" => $bitc_4_image));
                    array_push($answers, array("answer" => $bitc_5_answer, "image" => $bitc_5_image));
                    array_push($answers, array("answer" => $bitc_6_answer, "image" => $bitc_6_image));
                    array_push($answers, array("answer" => $bitc_7_answer, "image" => $bitc_7_image));
                    array_push($answers, array("answer" => $bitc_8_answer, "image" => $bitc_8_image));
                    array_push($answers, array("answer" => $bitc_9_answer, "image" => $bitc_9_image));
                    array_push($answers, array("answer" => $bitc_10_answer, "image" => $bitc_10_image));

                    $q["answers"]["type"] = "answers";
                    $q["answers"]["value"] = $answers;

                    update_post_meta($bitc_id, "question_data", $q);
                }
            }
        }
        // Restore original Post Data
        wp_reset_postdata();
    }

    if ($total <= 60) {
        bitc_update_legacy_quizzes();
        bitc_update_legacy_questions();
    } else {
        // show alert to get a user to use the ajax tool
        update_option("bitc_data_upgrade", "required");
    }
}


function bitc_show_need_to_update_data_message()
{
    $o = sanitize_text_field(get_option("bitc_data_upgrade"));
    if ($o != "required") {
        return;
    } ?>
    <div class="notice notice-error" style="background: darkred; color:#fff;">
        <p style="text-align:center;"><strong>Bitcoin Mastermind</strong>. You need to update your quizzes and questions to be compatible with this version. <a href="<?php echo get_admin_url(null, "?page=bitc_tools_data_upgrade"); ?>" class="button" style="font-weight: bold">BEGIN UPDATE</a></p>
    </div>
<?php
}
add_action('admin_notices', 'bitc_show_need_to_update_data_message');

function bitc_tool_upgrade_quiz_data()
{
    if (!current_user_can('edit_others_pages')) {
        echo 'access denied';
        die();
    }

    $bitc_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($bitc_nonce, 'bitc_tools_nonce')) {
        echo 'access denied';
        die();
    }

    if (!isset($_POST["quizzes"]) || !isset($_POST["atonce"])) {
        echo 'data not sent';
        die();
    }

    $atonce = intval($_POST["atonce"]);
    $quizzes = array(intval($_POST["quizzes"][0]), intval($_POST["quizzes"][1]));
    $taxonomy = 'quiz';
    $term_args = array(
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
        'number' => $quizzes[1] - $quizzes[0],
        'offset' => $quizzes[0]
    );
    $tax_terms = get_terms($taxonomy, $term_args);

    if (!empty($tax_terms) && !is_wp_error($tax_terms)) {
        foreach ($tax_terms as $tax_terms) {
            $quiz_id = $tax_terms->term_id;
			
			
            $term_meta = get_option("taxonomy_term_$quiz_id");
			
			
            if (isset($term_meta) && $term_meta != null && $term_meta != "") {
                $q = array();

                $q["quiz_pass_percentage"]["name"] = "quiz_pass_percentage";
                $q["quiz_pass_percentage"]["type"] = "integer";
                $q["quiz_pass_percentage"]["value"] = intval($term_meta["passPercent"]);
                $q["quiz_pass_text"]["name"] = "quiz_pass_text";
                $q["quiz_pass_text"]["type"] = "editor";
                $q["quiz_pass_text"]["value"] = bitc_encodeURIComponent(wp_kses_post($term_meta["passText"]));
                $q["quiz_fail_text"]["name"] = "quiz_fail_text";
                $q["quiz_fail_text"]["type"] = "editor";
                $q["quiz_fail_text"]["value"] = bitc_encodeURIComponent(wp_kses_post($term_meta["failText"]));
                $q["share_results"]["name"] = "share_results";
                $q["share_results"]["type"] = "checkbox";
                $q["share_results"]["value"] = array(sanitize_text_field($term_meta["shareResults"]));
                $q["results_position"]["name"] = "results_position";
                $q["results_position"]["type"] = "radio";
                $q["results_position"]["value"] = sanitize_text_field($term_meta["resultPos"]);

                $q["show_results"]["name"] = "show_results";
                $q["show_results"]["type"] = "checkbox";
                $q["show_results"]["value"] = array(sanitize_text_field($term_meta["showResults"]));
                $q["show_results_correct"]["name"] = "show_results_correct";
                $q["show_results_correct"]["type"] = "checkbox";
                $q["show_results_correct"]["value"] = array(sanitize_text_field($term_meta["showResultsCorrect"]));
                $q["show_results_now"]["name"] = "show_results_now";
                $q["show_results_now"]["type"] = "checkbox";
                $q["show_results_now"]["value"] = array(sanitize_text_field($term_meta["immediateMark"]));
                $q["stop_answer_reselect"]["name"] = "stop_answer_reselect";
                $q["stop_answer_reselect"]["type"] = "checkbox";
                $q["stop_answer_reselect"]["value"] = array(sanitize_text_field($term_meta["stopAnswerReselect"]));
                $q["show_extra_text"]["name"] = "show_extra_text";
                $q["show_extra_text"]["type"] = "checkbox";
                $q["show_extra_text"]["value"] = array(sanitize_text_field($term_meta["showIncorrectAnswerText"]));

                $q["quiz_timer"]["name"] = "quiz_timer";
                $q["quiz_timer"]["type"] = "integer";
                $q["quiz_timer"]["value"] = intval($term_meta["quizTimerS"]);

                $q["quiz_timer_question"]["name"] = "quiz_timer_question";
                $q["quiz_timer_question"]["type"] = "checkbox";
                $q["quiz_timer_question"]["value"] = array("no");

                $q["randomize_questions"]["name"] = "randomize_questions";
                $q["randomize_questions"]["type"] = "checkbox";
                $q["randomize_questions"]["value"] = array(sanitize_text_field($term_meta["randomizeQuestions"]));
                $q["randomize_answers"]["name"] = "randomize_answers";
                $q["randomize_answers"]["type"] = "checkbox";
                $q["randomize_answers"]["value"] = array(sanitize_text_field($term_meta["randomizeAnswers"]));
                $q["pool_of_questions"]["name"] = "pool_of_questions";
                $q["pool_of_questions"]["type"] = "integer";
                $q["pool_of_questions"]["value"] = intval($term_meta["pool"]);
                $q["wp_paginate"]["name"] = "wp_paginate";
                $q["wp_paginate"]["type"] = "integer";
                $q["wp_paginate"]["value"] = intval($term_meta["paginate"]);
                update_term_meta($quiz_id, "quiz_data", $q);
            }
        }
    }

    die();
}
add_action('wp_ajax_bitc_tool_upgrade_quiz_data', 'bitc_tool_upgrade_quiz_data');


function bitc_tool_upgrade_question_data()
{
    if (!current_user_can('edit_others_pages')) {
        echo 'permission not granted';
        die();
    }

    $bitc_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($bitc_nonce, 'bitc_tools_nonce')) {
        echo 'permission not granted';
        die();
    }

    if (!isset($_POST["questions"]) || !isset($_POST["atonce"])) {
        echo 'data not sent';
        die();
    }

    $atonce = intval($_POST["atonce"]);
    $questions = array(intval($_POST["questions"][0]), intval($_POST["questions"][1]));


    // WP_Query arguments
    $args = array(
        'post_type' => array('post_type_questionna'),
        'posts_per_page' => $atonce,
        'offset' => $questions[0],
    );

    // The Query
    $query = new WP_Query($args);

    // The Loop
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            $allowed_html = array(
                'strong' => array(),
                'em' => array(),
                'code' => array(),
                'sup' => array(),
                'sub' => array(),
            );

            $bitc_id = get_the_ID();

            $new_type = get_bitc_question($bitc_id);

            if (isset($new_type["title"]["value"]) && $new_type["title"]["value"] != "") {
                // already updated this question
            } else {
                $bitc_selected = array(intval(get_post_meta($bitc_id, 'hdQue_post_class2', true)));
                $bitc_image_as_answer = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class23', true));
                $bitc_question_as_title = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class24', true));
                $bitc_paginate = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class25', true));
                $bitc_tooltip = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class12', true));
                $bitc_after_answer = wp_kses_post(get_post_meta($bitc_id, 'hdQue_post_class26', true));
                $bitc_featured_image_id = get_post_thumbnail_id($bitc_id);

                $bitc_1_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class1', true), $allowed_html);
                $bitc_1_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class13', true));
                $bitc_2_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class3', true), $allowed_html);
                $bitc_2_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class14', true));
                $bitc_3_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class4', true), $allowed_html);
                $bitc_3_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class15', true));
                $bitc_4_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class5', true), $allowed_html);
                $bitc_4_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class16', true));
                $bitc_5_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class6', true), $allowed_html);
                $bitc_5_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class17', true));
                $bitc_6_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class7', true), $allowed_html);
                $bitc_6_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class18', true));
                $bitc_7_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class8', true), $allowed_html);
                $bitc_7_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class19', true));
                $bitc_8_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class9', true), $allowed_html);
                $bitc_8_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class20', true));
                $bitc_9_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class10', true), $allowed_html);
                $bitc_9_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class21', true));
                $bitc_10_answer = wp_kses(get_post_meta($bitc_id, 'hdQue_post_class11', true), $allowed_html);
                $bitc_10_image = sanitize_text_field(get_post_meta($bitc_id, 'hdQue_post_class22', true));

                if ($bitc_1_image != "" && !is_numeric($bitc_1_image)) {
                    $bitc_1_image =  bitc_get_attachment_id($bitc_1_image);
                }
                if ($bitc_2_image != "" && !is_numeric($bitc_2_image)) {
                    $bitc_2_image =  bitc_get_attachment_id($bitc_2_image);
                }
                if ($bitc_3_image != "" && !is_numeric($bitc_3_image)) {
                    $bitc_3_image =  bitc_get_attachment_id($bitc_3_image);
                }
                if ($bitc_4_image != "" && !is_numeric($bitc_4_image)) {
                    $bitc_4_image =  bitc_get_attachment_id($bitc_4_image);
                }
                if ($bitc_5_image != "" && !is_numeric($bitc_5_image)) {
                    $bitc_5_image =  bitc_get_attachment_id($bitc_5_image);
                }
                if ($bitc_6_image != "" && !is_numeric($bitc_6_image)) {
                    $bitc_6_image =  bitc_get_attachment_id($bitc_6_image);
                }
                if ($bitc_7_image != "" && !is_numeric($bitc_7_image)) {
                    $bitc_7_image =  bitc_get_attachment_id($bitc_7_image);
                }
                if ($bitc_8_image != "" && !is_numeric($bitc_8_image)) {
                    $bitc_8_image =  bitc_get_attachment_id($bitc_8_image);
                }
                if ($bitc_9_image != "" && !is_numeric($bitc_9_image)) {
                    $bitc_9_image =  bitc_get_attachment_id($bitc_9_image);
                }
                if ($bitc_10_image != "" && !is_numeric($bitc_10_image)) {
                    $bitc_10_image =  bitc_get_attachment_id($bitc_10_image);
                }

                $q = array();
                $q["title"]["value"] = get_the_title($bitc_id);
                $q["title"]["type"] = "title";

                $q["question_id"]["value"] = $bitc_id;
                $q["question_id"]["type"] = "integer";

                $q["selected"]["value"] = $bitc_selected;
                $q["selected"]["type"] = "checkbox";

                $question_type = "multiple_choice_text";
                if ($bitc_image_as_answer === "yes") {
                    $question_type = "multiple_choice_image";
                }
                if ($bitc_question_as_title === "yes") {
                    $question_type = "title";
                }

                $q["question_type"]["value"] = $question_type;
                $q["question_type"]["type"] = "select";

                $q["paginate"]["value"] = array($bitc_paginate);
                $q["paginate"]["type"] = "checkbox";
                $q["tooltip"]["value"] = $bitc_tooltip;
                $q["tooltip"]["type"] = "text";
                $q["extra_text"]["value"] = $bitc_after_answer;
                $q["extra_text"]["type"] = "editor";
                $q["featured_image"]["value"] = $bitc_featured_image_id;
                $q["featured_image"]["type"] = "image";

                $answers = array();
                array_push($answers, array("answer" => $bitc_1_answer, "image" => $bitc_1_image));
                array_push($answers, array("answer" => $bitc_2_answer, "image" => $bitc_2_image));
                array_push($answers, array("answer" => $bitc_3_answer, "image" => $bitc_3_image));
                array_push($answers, array("answer" => $bitc_4_answer, "image" => $bitc_4_image));
                array_push($answers, array("answer" => $bitc_5_answer, "image" => $bitc_5_image));
                array_push($answers, array("answer" => $bitc_6_answer, "image" => $bitc_6_image));
                array_push($answers, array("answer" => $bitc_7_answer, "image" => $bitc_7_image));
                array_push($answers, array("answer" => $bitc_8_answer, "image" => $bitc_8_image));
                array_push($answers, array("answer" => $bitc_9_answer, "image" => $bitc_9_image));
                array_push($answers, array("answer" => $bitc_10_answer, "image" => $bitc_10_image));

                $q["answers"]["type"] = "answers";
                $q["answers"]["value"] = $answers;

                update_post_meta($bitc_id, "question_data", $q);
            }
        }
    }
    // Restore original Post Data
    wp_reset_postdata();
    die();
}
add_action('wp_ajax_bitc_tool_upgrade_question_data', 'bitc_tool_upgrade_question_data');

function bitc_tool_upgrade_question_data_complete()
{
    if (!current_user_can('edit_others_pages')) {
        echo 'permission not granted';
        die();
    }

    $bitc_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($bitc_nonce, 'bitc_tools_nonce')) {
        echo 'permission not granted';
        die();
    }

    update_option("bitc_data_upgrade", "");

    die();
}
add_action('wp_ajax_bitc_tool_upgrade_question_data_complete', 'bitc_tool_upgrade_question_data_complete');

function bitc_remove_data_upgrade_notice()
{
    delete_option("bitc_remove_data_upgrade_notice");
}
add_action('wp_ajax_bitc_remove_data_upgrade_notice', 'bitc_remove_data_upgrade_notice');
