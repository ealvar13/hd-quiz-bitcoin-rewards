<?php
if (bitc_user_permission()) {
    bitc_load_quiz_tabs();
}

function bitc_load_quiz_tabs()
{
    $quizID = 0;
    if (isset($_POST['quiz'])) {
        $quizID = intval($_POST['quiz']);
    }

    $questionID = 0;
    if (isset($_POST['questionID'])) {
        $questionID = intval($_POST['questionID']);
    }

    $quiz = get_term($quizID, "quiz");
    $fields = get_bitc_quiz($quizID); ?>
    <a href="<?php echo get_admin_url(); ?>admin.php?page=bitc_quizzes" title="view all quizzes">&laquo; back to quizzes</a>
    <div id="header">

        <h1 id="heading_title">
            <?php echo $quiz->name; ?>
        </h1>
        <div id="header_actions">
            <div role="button" title="Add a new question" class="bitc_button2" data-id="<?php echo $quizID; ?>" id="bitc_add_question">
                <span class="dashicons dashicons-plus"></span> ADD NEW QUESTION
            </div>
            <div role="button" title="Save quiz settings" id="save" data-id="save-settings" class="bitc_button" title="save settings"><span class="dashicons dashicons-sticky"></span> SAVE QUIZ</div>
        </div>
    </div>

    <p>
        Quiz Shortcode: <span class="bitc_tooltip_code"><code title="click to copy to clipboard" class="bitc_shortcode_copy">[HDquiz quiz = "<?php echo $quizID; ?>"]</code><span class="bitc_tooltip_content">
                <span>click to copy shortcode</span>
            </span></span> <br /><small>You can copy / paste that shortcode (remember to paste without formatting!) onto any post or page to display this quiz or use the built-in Gutenberg block.</small>
    </p>
    <p>
        Add a new question to this quiz, or select a question below to edit it. You can also drag-and-drop to re-order the questions <span class="bitc_tooltip">
            ?
            <span class="bitc_tooltip_content">
                <span>Just remember to save the quiz after reordering</span>
            </span>
        </span>.
    </p>

    <div id="bitc_quiz_tabs">
        <div id="bitc_quiz_tabs_labels">
            <div role="button" data-id="bitc_questions_list" class="bitc_quiz_tab bitc_quiz_tab_active">
                QUESTIONS
            </div>
            <div role="button" data-id="bitc_settings_page" class="bitc_quiz_tab">
                QUIZ SETTINGS
            </div>
        </div>
        <div id="bitc_quiz_tabs_content">

            <div id="bitc_questions_list" class="content">
                <?php
                // WP_Query arguments


                $bitc_per_page = 200;

                if (defined('bitc_PER_PAGE')) {
                    $bitc_per_page = intval(bitc_PER_PAGE);
                }

                $bitc_paged = 1;
                if (isset($_POST["bitc_paged"])) {
                    $bitc_paged = intval($_POST["bitc_paged"]);
                }

                $args = array(
                    'post_type' => array('post_type_questionna'),
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'quiz',
                            'terms' => $quizID,
                        ),
                    ),
                    'posts_per_page' => $bitc_per_page,
                    'order' => 'ASC',
                    'orderby' => 'menu_order',
                    'paged'  => $bitc_paged
                );

                // The Query
                $query = new WP_Query($args);
                $menu_number = 0;

                if ($bitc_paged > 1) {
                    $menu_number = $menu_number + ($bitc_per_page * ($bitc_paged - 1));
                }
                $has_posts = false;
                // The Loop
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();
                        $has_posts = true;

                        $menu_number = $menu_number + 1;

                        $title = get_the_title();
                        if (function_exists("mb_strimwidth")) {
                            $title = mb_strimwidth($title, 0, 70, "...");
                        }

                        $isActive = "";
                        if ($questionID === get_the_ID()) {
                            $isActive = "bitc_question_last_active";
                        }

                        echo '<div role = "button" class = "bitc_quiz_item bitc_quiz_question ' . $isActive . '" data-id = "' . get_the_ID() . '" data-quiz-id = "' . $quizID . '"><span class = "bitc_quiz_item_drag" title = "drag and drop to reorder questions">≡</span>' . $menu_number . ". " . $title . '</div>';
                    }
                } else {
                    echo '<p>Newly added questions will appear here</p>';
                }

                // Restore original Post Data
                wp_reset_postdata()

                ?>

                <div class="bitc_admin_pagination">
                    <?php
                    if ($has_posts) {
                        $max = $query->max_num_pages;
                        if ($max != $bitc_paged) {
                            if ($bitc_paged == 1 && $bitc_paged < $max) {
                                $next_page = $bitc_paged + 1;
                                echo '<div id = "bitc_next_questions" quiz-id = "' . $quizID . '" page-id = "' . $next_page . '" role = "button" title = "View the next set of questions for this quiz" class = "bitc_admin_paginate">NEXT QUESTIONS</div>';
                            } elseif ($bitc_paged > 1 && $bitc_paged < $max) {
                                $next_page = $bitc_paged - 1;
                                echo '<div id = "bitc_prev_questions" quiz-id = "' . $quizID . '" page-id = "' . $next_page . '" role = "button" title = "View the orevious set of questions for this quiz" class = "bitc_admin_paginate">PREV QUESTIONS</div>&nbsp;';

                                $next_page = $bitc_paged + 1;
                                echo '&nbsp;<div id = "bitc_next_questions" quiz-id = "' . $quizID . '" page-id = "' . $next_page . '" role = "button" title = "View the next set of questions for this quiz" class = "bitc_admin_paginate">NEXT QUESTIONS</div>';
                            } else {
                                $next_page = $bitc_paged - 1;
                                echo '<div id = "bitc_prev_questions" quiz-id = "' . $quizID . '" page-id = "' . $next_page . '" role = "button" title = "View the orevious set of questions for this quiz" class = "bitc_admin_paginate">PREV QUESTIONS</div>';
                            }
                        } elseif ($bitc_paged > 1) {
                            $next_page = $bitc_paged - 1;
                            echo '<div id = "bitc_prev_questions" quiz-id = "' . $quizID . '" page-id = "' . $next_page . '" role = "button" title = "View the orevious set of questions for this quiz" class = "bitc_admin_paginate">PREV QUESTIONS</div>';
                        }
                    }
                    ?>
                </div>

            </div>
            <div id="bitc_settings_page" class="content">
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
                            <?php bitc_print_quiz_tabs(); ?>
                        </div>
                    </div>
                    <div id="tab_content">
                        <input type="hidden" class="hderp_input" id="quiz_id" style="display:none" data-required="true" data-type="integer" value="<?php echo $quizID; ?>" />
                        <?php bitc_print_quiz_tab_content($fields); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
