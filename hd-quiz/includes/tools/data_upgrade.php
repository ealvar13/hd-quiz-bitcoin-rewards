<?php
function hdq_register_tools__data_upgrade_page_callback()
{
    if (!current_user_can('edit_others_pages')) {
        die();
    }


    wp_enqueue_style(
        'hdq_admin_style',
        plugin_dir_url(__FILE__) . '../css/hdq_admin.css?v=' . HDQ_PLUGIN_VERSION
    );

    wp_enqueue_script(
        'hdq_admin_script',
        plugins_url('../js/hdq_admin.js?v=' . HDQ_PLUGIN_VERSION, __FILE__),
        array('jquery', 'jquery-ui-draggable'),
        HDQ_PLUGIN_VERSION,
        true
    );

    wp_enqueue_script(
        'hdq_admin_script_data_update',
        plugins_url('../js/hdq_data_update.js?v=' . HDQ_PLUGIN_VERSION, __FILE__),
        array('jquery'),
        HDQ_PLUGIN_VERSION,
        true
    );

    wp_nonce_field('hdq_tools_nonce', 'hdq_tools_nonce');

    $questions = wp_count_posts('post_type_questionna');
    $questions = $questions->publish;
    $quizzes = wp_count_terms("quiz"); ?>

    <div id="main" style="max-width: 800px; background: #f3f3f3; border: 1px solid #ddd; margin-top: 2rem">
        <div id="header">
            <h1 id="heading_title" style="margin-top:0">
                Quiz and Question Data Upgrader
            </h1>
        </div>

        <p>HD Quiz has grown considerably in features and complexity over the years making this page necessary. This tool is only needed for users upgrading from HD Quiz 1.7 or lower. <strong>DO NOT USE if you are upgrading from a version higher than 1.7</strong></p>
		<p>
			
		</p>

        <div class="hdq_highlight">
            <p>

                <strong>NOTE:</strong> If for any reason, the data migration is not working, <strong>do no
                    worry</strong>. None of your old data has been deleted or modified in any way. In fact, you can
                easily replace this version of HD Quiz by downloading the <a href="https://wordpress.org/plugins/hd-quiz/advanced/" target="_blank">previous version of HD Quiz
                    here</a> <span class="hdq_tooltip">
                    ?
                    <span class="hdq_tooltip_content">
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
            Thank you for your patience and understanding. Needing to change data like this is hopefully a once in a lifetime event for HD Quiz. This new method opens up a lot of doors to increase speed, security, and feature set of HD Quiz.
        </p>
        <center>
            <div data-quizzes="<?php echo $quizzes; ?>" data-questions="<?php echo $questions; ?>" id="hdq_tool_update_data_start" class="hdq_button" role="button" title="Start update">
                BEGIN UPDATE
            </div>
        </center>

        <div id="hdq_message_logs"></div>


    </div>

<?php
}

function hdq_update_legacy_data()
{
    // get total number of questions
    $total = wp_count_posts('post_type_questionna');
    $total = $total->publish;
    // only run auto updated if total questions is less than 200
    // else, offer manual update function with ajax

    // update quiz meta data
    function hdq_update_legacy_quizzes()
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
    function hdq_update_legacy_questions()
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

                $hdq_id = get_the_ID();

                // extra check to make sure we are not overwriting good data
                $existing = get_post_meta($questionID, "question_data", true);
                if (!is_array($existing)) {


                    $hdq_selected = array(intval(get_post_meta($hdq_id, 'hdQue_post_class2', true)));
                    $hdq_image_as_answer = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class23', true));
                    $hdq_question_as_title = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class24', true));
                    $hdq_paginate = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class25', true));
                    $hdq_tooltip = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class12', true));
                    $hdq_after_answer = wp_kses_post(get_post_meta($hdq_id, 'hdQue_post_class26', true));
                    $hdq_featured_image_id = get_post_thumbnail_id($hdq_id);

                    $hdq_1_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class1', true), $allowed_html);

                    $hdq_1_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class13', true));
                    $hdq_2_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class3', true), $allowed_html);
                    $hdq_2_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class14', true));
                    $hdq_3_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class4', true), $allowed_html);
                    $hdq_3_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class15', true));
                    $hdq_4_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class5', true), $allowed_html);
                    $hdq_4_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class16', true));
                    $hdq_5_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class6', true), $allowed_html);
                    $hdq_5_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class17', true));
                    $hdq_6_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class7', true), $allowed_html);
                    $hdq_6_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class18', true));
                    $hdq_7_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class8', true), $allowed_html);
                    $hdq_7_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class19', true));
                    $hdq_8_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class9', true), $allowed_html);
                    $hdq_8_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class20', true));
                    $hdq_9_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class10', true), $allowed_html);
                    $hdq_9_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class21', true));
                    $hdq_10_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class11', true), $allowed_html);
                    $hdq_10_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class22', true));

                    if ($hdq_1_image != "" && !is_numeric($hdq_1_image)) {
                        $hdq_1_image =  hdq_get_attachment_id($hdq_1_image);
                    }
                    if ($hdq_2_image != "" && !is_numeric($hdq_2_image)) {
                        $hdq_2_image =  hdq_get_attachment_id($hdq_2_image);
                    }
                    if ($hdq_3_image != "" && !is_numeric($hdq_3_image)) {
                        $hdq_3_image =  hdq_get_attachment_id($hdq_3_image);
                    }
                    if ($hdq_4_image != "" && !is_numeric($hdq_4_image)) {
                        $hdq_4_image =  hdq_get_attachment_id($hdq_4_image);
                    }
                    if ($hdq_5_image != "" && !is_numeric($hdq_5_image)) {
                        $hdq_5_image =  hdq_get_attachment_id($hdq_5_image);
                    }
                    if ($hdq_6_image != "" && !is_numeric($hdq_6_image)) {
                        $hdq_6_image =  hdq_get_attachment_id($hdq_6_image);
                    }
                    if ($hdq_7_image != "" && !is_numeric($hdq_7_image)) {
                        $hdq_7_image =  hdq_get_attachment_id($hdq_7_image);
                    }
                    if ($hdq_8_image != "" && !is_numeric($hdq_8_image)) {
                        $hdq_8_image =  hdq_get_attachment_id($hdq_8_image);
                    }
                    if ($hdq_9_image != "" && !is_numeric($hdq_9_image)) {
                        $hdq_9_image =  hdq_get_attachment_id($hdq_9_image);
                    }
                    if ($hdq_10_image != "" && !is_numeric($hdq_10_image)) {
                        $hdq_10_image =  hdq_get_attachment_id($hdq_10_image);
                    }


                    $q = array();
                    $q["title"]["value"] = get_the_title($hdq_id);
                    $q["title"]["type"] = "title";

                    $q["question_id"]["value"] = $hdq_id;
                    $q["question_id"]["type"] = "integer";

                    $q["selected"]["value"] = $hdq_selected;
                    $q["selected"]["type"] = "checkbox";

                    $question_type = "multiple_choice_text";
                    if ($hdq_image_as_answer === "yes") {
                        $question_type = "multiple_choice_image";
                    }
                    if ($hdq_question_as_title === "yes") {
                        $question_type = "title";
                    }

                    $q["question_type"]["value"] = $question_type;
                    $q["question_type"]["type"] = "select";

                    $q["paginate"]["value"] = array($hdq_paginate);
                    $q["paginate"]["type"] = "checkbox";
                    $q["tooltip"]["value"] = $hdq_tooltip;
                    $q["tooltip"]["type"] = "text";
                    $q["extra_text"]["value"] = $hdq_after_answer;
                    $q["extra_text"]["type"] = "editor";
                    $q["featured_image"]["value"] = $hdq_featured_image_id;
                    $q["featured_image"]["type"] = "image";

                    $answers = array();
                    array_push($answers, array("answer" => $hdq_1_answer, "image" => $hdq_1_image));
                    array_push($answers, array("answer" => $hdq_2_answer, "image" => $hdq_2_image));
                    array_push($answers, array("answer" => $hdq_3_answer, "image" => $hdq_3_image));
                    array_push($answers, array("answer" => $hdq_4_answer, "image" => $hdq_4_image));
                    array_push($answers, array("answer" => $hdq_5_answer, "image" => $hdq_5_image));
                    array_push($answers, array("answer" => $hdq_6_answer, "image" => $hdq_6_image));
                    array_push($answers, array("answer" => $hdq_7_answer, "image" => $hdq_7_image));
                    array_push($answers, array("answer" => $hdq_8_answer, "image" => $hdq_8_image));
                    array_push($answers, array("answer" => $hdq_9_answer, "image" => $hdq_9_image));
                    array_push($answers, array("answer" => $hdq_10_answer, "image" => $hdq_10_image));

                    $q["answers"]["type"] = "answers";
                    $q["answers"]["value"] = $answers;

                    update_post_meta($hdq_id, "question_data", $q);
                }
            }
        }
        // Restore original Post Data
        wp_reset_postdata();
    }

    if ($total <= 60) {
        hdq_update_legacy_quizzes();
        hdq_update_legacy_questions();
    } else {
        // show alert to get a user to use the ajax tool
        update_option("hdq_data_upgrade", "required");
    }
}


function hdq_show_need_to_update_data_message()
{
    $o = sanitize_text_field(get_option("hdq_data_upgrade"));
    if ($o != "required") {
        return;
    } ?>
    <div class="notice notice-error" style="background: darkred; color:#fff;">
        <p style="text-align:center;"><strong>HD QUIZ</strong>. You need to update your quizzes and questions to be compatible with this version. <a href="<?php echo get_admin_url(null, "?page=hdq_tools_data_upgrade"); ?>" class="button" style="font-weight: bold">BEGIN UPDATE</a></p>
    </div>
<?php
}
add_action('admin_notices', 'hdq_show_need_to_update_data_message');

function hdq_tool_upgrade_quiz_data()
{
    if (!current_user_can('edit_others_pages')) {
        echo 'access denied';
        die();
    }

    $hdq_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($hdq_nonce, 'hdq_tools_nonce')) {
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
                $q["quiz_pass_text"]["value"] = hdq_encodeURIComponent(wp_kses_post($term_meta["passText"]));
                $q["quiz_fail_text"]["name"] = "quiz_fail_text";
                $q["quiz_fail_text"]["type"] = "editor";
                $q["quiz_fail_text"]["value"] = hdq_encodeURIComponent(wp_kses_post($term_meta["failText"]));
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
add_action('wp_ajax_hdq_tool_upgrade_quiz_data', 'hdq_tool_upgrade_quiz_data');


function hdq_tool_upgrade_question_data()
{
    if (!current_user_can('edit_others_pages')) {
        echo 'permission not granted';
        die();
    }

    $hdq_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($hdq_nonce, 'hdq_tools_nonce')) {
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

            $hdq_id = get_the_ID();

            $new_type = get_hdq_question($hdq_id);

            if (isset($new_type["title"]["value"]) && $new_type["title"]["value"] != "") {
                // already updated this question
            } else {
                $hdq_selected = array(intval(get_post_meta($hdq_id, 'hdQue_post_class2', true)));
                $hdq_image_as_answer = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class23', true));
                $hdq_question_as_title = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class24', true));
                $hdq_paginate = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class25', true));
                $hdq_tooltip = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class12', true));
                $hdq_after_answer = wp_kses_post(get_post_meta($hdq_id, 'hdQue_post_class26', true));
                $hdq_featured_image_id = get_post_thumbnail_id($hdq_id);

                $hdq_1_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class1', true), $allowed_html);
                $hdq_1_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class13', true));
                $hdq_2_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class3', true), $allowed_html);
                $hdq_2_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class14', true));
                $hdq_3_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class4', true), $allowed_html);
                $hdq_3_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class15', true));
                $hdq_4_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class5', true), $allowed_html);
                $hdq_4_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class16', true));
                $hdq_5_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class6', true), $allowed_html);
                $hdq_5_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class17', true));
                $hdq_6_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class7', true), $allowed_html);
                $hdq_6_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class18', true));
                $hdq_7_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class8', true), $allowed_html);
                $hdq_7_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class19', true));
                $hdq_8_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class9', true), $allowed_html);
                $hdq_8_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class20', true));
                $hdq_9_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class10', true), $allowed_html);
                $hdq_9_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class21', true));
                $hdq_10_answer = wp_kses(get_post_meta($hdq_id, 'hdQue_post_class11', true), $allowed_html);
                $hdq_10_image = sanitize_text_field(get_post_meta($hdq_id, 'hdQue_post_class22', true));

                if ($hdq_1_image != "" && !is_numeric($hdq_1_image)) {
                    $hdq_1_image =  hdq_get_attachment_id($hdq_1_image);
                }
                if ($hdq_2_image != "" && !is_numeric($hdq_2_image)) {
                    $hdq_2_image =  hdq_get_attachment_id($hdq_2_image);
                }
                if ($hdq_3_image != "" && !is_numeric($hdq_3_image)) {
                    $hdq_3_image =  hdq_get_attachment_id($hdq_3_image);
                }
                if ($hdq_4_image != "" && !is_numeric($hdq_4_image)) {
                    $hdq_4_image =  hdq_get_attachment_id($hdq_4_image);
                }
                if ($hdq_5_image != "" && !is_numeric($hdq_5_image)) {
                    $hdq_5_image =  hdq_get_attachment_id($hdq_5_image);
                }
                if ($hdq_6_image != "" && !is_numeric($hdq_6_image)) {
                    $hdq_6_image =  hdq_get_attachment_id($hdq_6_image);
                }
                if ($hdq_7_image != "" && !is_numeric($hdq_7_image)) {
                    $hdq_7_image =  hdq_get_attachment_id($hdq_7_image);
                }
                if ($hdq_8_image != "" && !is_numeric($hdq_8_image)) {
                    $hdq_8_image =  hdq_get_attachment_id($hdq_8_image);
                }
                if ($hdq_9_image != "" && !is_numeric($hdq_9_image)) {
                    $hdq_9_image =  hdq_get_attachment_id($hdq_9_image);
                }
                if ($hdq_10_image != "" && !is_numeric($hdq_10_image)) {
                    $hdq_10_image =  hdq_get_attachment_id($hdq_10_image);
                }

                $q = array();
                $q["title"]["value"] = get_the_title($hdq_id);
                $q["title"]["type"] = "title";

                $q["question_id"]["value"] = $hdq_id;
                $q["question_id"]["type"] = "integer";

                $q["selected"]["value"] = $hdq_selected;
                $q["selected"]["type"] = "checkbox";

                $question_type = "multiple_choice_text";
                if ($hdq_image_as_answer === "yes") {
                    $question_type = "multiple_choice_image";
                }
                if ($hdq_question_as_title === "yes") {
                    $question_type = "title";
                }

                $q["question_type"]["value"] = $question_type;
                $q["question_type"]["type"] = "select";

                $q["paginate"]["value"] = array($hdq_paginate);
                $q["paginate"]["type"] = "checkbox";
                $q["tooltip"]["value"] = $hdq_tooltip;
                $q["tooltip"]["type"] = "text";
                $q["extra_text"]["value"] = $hdq_after_answer;
                $q["extra_text"]["type"] = "editor";
                $q["featured_image"]["value"] = $hdq_featured_image_id;
                $q["featured_image"]["type"] = "image";

                $answers = array();
                array_push($answers, array("answer" => $hdq_1_answer, "image" => $hdq_1_image));
                array_push($answers, array("answer" => $hdq_2_answer, "image" => $hdq_2_image));
                array_push($answers, array("answer" => $hdq_3_answer, "image" => $hdq_3_image));
                array_push($answers, array("answer" => $hdq_4_answer, "image" => $hdq_4_image));
                array_push($answers, array("answer" => $hdq_5_answer, "image" => $hdq_5_image));
                array_push($answers, array("answer" => $hdq_6_answer, "image" => $hdq_6_image));
                array_push($answers, array("answer" => $hdq_7_answer, "image" => $hdq_7_image));
                array_push($answers, array("answer" => $hdq_8_answer, "image" => $hdq_8_image));
                array_push($answers, array("answer" => $hdq_9_answer, "image" => $hdq_9_image));
                array_push($answers, array("answer" => $hdq_10_answer, "image" => $hdq_10_image));

                $q["answers"]["type"] = "answers";
                $q["answers"]["value"] = $answers;

                update_post_meta($hdq_id, "question_data", $q);
            }
        }
    }
    // Restore original Post Data
    wp_reset_postdata();
    die();
}
add_action('wp_ajax_hdq_tool_upgrade_question_data', 'hdq_tool_upgrade_question_data');

function hdq_tool_upgrade_question_data_complete()
{
    if (!current_user_can('edit_others_pages')) {
        echo 'permission not granted';
        die();
    }

    $hdq_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($hdq_nonce, 'hdq_tools_nonce')) {
        echo 'permission not granted';
        die();
    }

    update_option("hdq_data_upgrade", "");

    die();
}
add_action('wp_ajax_hdq_tool_upgrade_question_data_complete', 'hdq_tool_upgrade_question_data_complete');

function hdq_remove_data_upgrade_notice()
{
    delete_option("hdq_remove_data_upgrade_notice");
}
add_action('wp_ajax_hdq_remove_data_upgrade_notice', 'hdq_remove_data_upgrade_notice');
