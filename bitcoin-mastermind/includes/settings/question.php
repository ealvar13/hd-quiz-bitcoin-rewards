<?php
if (bitc_user_permission()) {
    bitc_load_question_tabs();
}

function bitc_load_question_tabs()
{
    function bitc_display_question($questionID, $quizID, $fields)
    {
?>
        <input type="hidden" style="display:none" class="hderp_input" data-type="integer" id="question_id" value="<?php echo $questionID; ?>" />
        <div id="bitc_question_buttons">
            <div id="bitc_question_edit_left">
                <div role="button" title="back to quiz screen" class="bitc_button2" data-id="<?php echo $quizID; ?>" data-question-id="<?php echo $questionID; ?>" id="bitc_back_to_quiz">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    BACK TO QUIZ
                </div>
                <div role="button" title="add another question to this quiz" class="bitc_button2" data-id="<?php echo $quizID; ?>" id="bitc_add_question">
                    <span class="dashicons dashicons-plus"></span>
                    ADD NEW QUESTION
                </div>
            </div>
            <div id="bitc_question_edit_right">
                <div role="button" title="delete this question" class="bitc_button_warning" id="bitc_delete_question" data-question="<?php echo $questionID; ?>" data-quiz="<?php echo $quizID; ?>">
                    <span class="dashicons dashicons-trash"></span>
                </div>
                <div role="button" title="save this question" class="bitc_button" data-id="<?php echo $questionID; ?>" id="bitc_save_question">
                    <span class="dashicons dashicons-sticky"></span>
                    SAVE QUESTION
                </div>
            </div>
        </div>

        <div id="bitc_question_admin_top">
            <div class="bitc_row">
                <input type="text" id="title" class="bitc_question_title hderp_input" data-type="title" data-tab="" placeholder="enter question..." data-required="required" value="<?php echo bitc_getValue(array("name" => "title"), $fields); ?>">
            </div>
        </div>
        <div id="content_tabs">
            <div id="tab_nav_wrapper">
                <div id="bitc_logo">
                    <span class="bitc_logo_tooltip"><img src="<?php echo plugins_url('../images/hd-logo.png', __FILE__); ?>" alt="Harmonic Design logo">
                        <span class="bitc_logo_tooltip_content">
                            <span><strong>Bitcoin Mastermind</strong> is developed by Harmonic Design. Check out the addons page to see how you can extend Bitcoin Mastermind even further.</span>
                        </span>
                    </span>
                </div>
                <div id="tab_nav">
                    <?php bitc_print_question_tabs(); ?>
                </div>
            </div>
            <div id="tab_content">
                <input type="hidden" class="hderp_input" id="quiz_id" style="display:none" data-required="true" data-type="integer" value="<?php echo $quizID; ?>" />
                <?php bitc_print_question_tab_content($fields); ?>
            </div>
        </div>



<?php
    }
    // TODO: Send QUIZ ID as well for actionable buttons
    $questionID = 0;
    if (isset($_POST['question'])) {
        $questionID = intval($_POST['question']);
    }
    $quizID = 0;
    if (isset($_POST['quiz'])) {
        $quizID = intval($_POST['quiz']);
    }

    if ($quizID > 0) {
        $fields = get_bitc_question($questionID);
        bitc_display_question($questionID, $quizID, $fields);
    } else {
        echo 'ERROR: No quiz ID was provided';
    }
}
