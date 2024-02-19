<?php

/* Include the basic required files
------------------------------------------------------- */
// require dirname(__FILE__) . '/tools/data_upgrade.php';
require dirname(__FILE__) . '/tools/csv_import.php';

// Register Bitcoin Mastermind pages
function bitc_register_quizzes_page_callback()
{
    require dirname(__FILE__) . '/bitc_quizzes.php';
}

function bitc_register_settings_page_callback()
{
    require dirname(__FILE__) . '/about.php';
}

function bitc_register_tools_page_callbak()
{
    require dirname(__FILE__) . '/tools.php';
}

// Get image ID (for *super* old Bitcoin Mastermind users image as answer)
// taken from https://wpscholar.com/blog/get-attachment-id-from-wp-image-url/
// great work Micah! This is super elegant
function bitc_get_attachment_id($url)
{
    $attachment_id = 0;
    $dir = wp_upload_dir();

    if (false !== strpos($url, $dir['baseurl'] . '/')) { // Is URL in uploads directory?
        $file = basename($url);

        $query_args = array(
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
            'fields'      => 'ids',
            'meta_query'  => array(
                array(
                    'value'   => $file,
                    'compare' => 'LIKE',
                    'key'     => '_wp_attachment_metadata',
                ),
            )
        );

        $query = new WP_Query($query_args);

        if ($query->have_posts()) {
            foreach ($query->posts as $post_id) {
                $meta = wp_get_attachment_metadata($post_id);

                $original_file       = basename($meta['file']);
                $cropped_image_files = wp_list_pluck($meta['sizes'], 'file');

                if ($original_file === $file || in_array($file, $cropped_image_files)) {
                    $attachment_id = $post_id;
                    break;
                }
            }
        }
    }

    return $attachment_id;
}

function bitc_get_settings()
{
    $settings = new bitc_settings();
    $settings = $settings->get();
    return $settings;
}

function bitc_save_settings()
{
    if (!current_user_can('edit_others_pages')) {
        echo '{"error": "User level cannot modify settings"}';
        die();
    }

    $bitc_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($bitc_nonce, 'bitc_about_options_nonce')) {
        echo '{"error": "Nonce was not valid"}';
        die();
    }

    if (!isset($_POST["payload"])) {
        echo '{"error": "Data was not correctly sent"}';
        die();
    }

    $fields = $_POST["payload"];
    $fields = bitc_sanitize_fields($fields);

    update_option("bitc_settings", $fields);

    echo '{"success": true}';
    die();
}
add_action('wp_ajax_bitc_save_settings', 'bitc_save_settings');

function bitc_load_questions_page()
{
    if (bitc_user_permission()) {
        $bitc_nonce = sanitize_text_field($_POST['nonce']);
        if (wp_verify_nonce($bitc_nonce, 'bitc_quiz_nonce') != false) {
            // permission granted
            // send the correct file to load data from
            include dirname(__FILE__) . '/settings/questions.php';
        } else {
            echo 'error: Nonce failed to validate'; // failed nonce
        }
    } else {
        echo 'error: You have insufficient user privilege'; // insufficient user privilege
    }
    die();
}
add_action('wp_ajax_bitc_load_quiz', 'bitc_load_questions_page');


function bitc_load_question()
{
    if (bitc_user_permission()) {
        $bitc_nonce = sanitize_text_field($_POST['nonce']);
        if (wp_verify_nonce($bitc_nonce, 'bitc_quiz_nonce') != false) {
            // permission granted
            // send the correct file to load data from
            include dirname(__FILE__) . '/settings/question.php';
        } else {
            echo 'error: Nonce failed to validate'; // failed nonce
        }
    } else {
        echo 'error: You have insufficient user privilege'; // insufficient user privilege
    }
    die();
}
add_action('wp_ajax_bitc_load_question', 'bitc_load_question');


/* Check acccess level
 * check if authors should be granted access
------------------------------------------------------- */
function bitc_user_permission()
{
    $hasPermission = false;
    $settings = bitc_get_settings();
    $authorsCan = $settings["hd_qu_authors"]["value"][0];
    if ($authorsCan === "yes") {
        if (current_user_can('publish_posts')) {
            $hasPermission = true;
        }
    } else {
        if (current_user_can('edit_others_pages')) {
            $hasPermission = true;
        }
    }
    return $hasPermission;
}

/* Settings
------------------------------------------------------- */
function get_bitc_settings()
{
    // TODO: Merge default values
    return bitc_sanitize_fields(get_option("bitc_settings"));
}

/* Quiz Settings
------------------------------------------------------- */
function get_bitc_quiz($quizID)
{
    return bitc_sanitize_fields(get_term_meta($quizID, "quiz_data", true));
}

function bitc_save_quiz()
{
    if (!bitc_user_permission()) {
        echo '{"error": "User level cannot modify products"}';
        die();
    }

    $bitc_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($bitc_nonce, 'bitc_quiz_nonce')) {
        echo '{"error": "Nonce was not valid"}';
        die();
    }

    if (!isset($_POST["payload"])) {
        echo '{"error": "Data was not correctly sent"}';
        die();
    }

    $fields = $_POST["payload"];

    $fields = bitc_sanitize_fields($fields);
    $quiz_ID = $fields["quiz_id"]["value"];

    if ($quiz_ID <= 0) {
        echo '{"error": "Data was not correctly sent"}';
        die();
    }

    // update each question to set question menu_order
    if (isset($fields["question_order"])) {
        foreach ($fields["question_order"]["value"] as $q) {
            $post = array();
            $post['ID'] = intval($q[0]);
            $post['menu_order'] = intval($q[1]);
            wp_update_post($post);
        }
    }


    update_term_meta($quiz_ID, "quiz_data", $fields);

    // incase the quizname was changed
    if (isset($fields["quiz_name"]["value"])) {
        wp_update_term($quiz_ID, 'quiz', array(
            'name' => $fields["quiz_name"]["value"],
        ));
    }


    echo '{"success": true}';
    die();
}
add_action('wp_ajax_bitc_save_quiz', 'bitc_save_quiz');


/* Question Settings
------------------------------------------------------- */
function get_bitc_question($questionID)
{
    return bitc_sanitize_fields(get_post_meta($questionID, "question_data", true));
}

/* Save Question Meta
------------------------------------------------------- */
function bitc_save_question()
{
    if (!bitc_user_permission()) {
        echo '{"error": "User level cannot modify products"}';
        die();
    }


    $bitc_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($bitc_nonce, 'bitc_quiz_nonce')) {
        echo '{"error": "Nonce was not valid"}';
        die();
    }

    if (!isset($_POST["payload"])) {
        echo '{"error": "Data was not correctly sent"}';
        die();
    }

    $fields = $_POST["payload"];
    $fields = bitc_sanitize_fields($fields);

    // if new question
    if ($fields["question_id"]["value"] == "" || $fields["question_id"]["value"] == null) {
        // new question

        $total = wp_count_posts('post_type_questionna');
        $total = $total->publish;

        $post_information = array(
            'post_title' => $fields["title"]["value"],
            'post_content' => '', // post_content is required, so we leave blank
            'post_type' => 'post_type_questionna',
            'post_status' => 'publish',
            'menu_order' => $total // always set as the last question of the quiz
        );
        $fields["question_id"]["value"] = wp_insert_post($post_information);
    }
    // set meta
    update_post_meta($fields["question_id"]["value"], "question_data", $fields);

    // update post title
    $post_main = array(
        'ID'           => $fields["question_id"]["value"],
        'post_title'   => $fields["title"]["value"]
    );
    wp_update_post($post_main);

    // set or update terms
    $terms = wp_set_post_terms($fields["question_id"]["value"], $fields["quizzes"]["value"], "quiz");

    echo '{"success": true, "id": "' . $fields["question_id"]["value"] . '"}';
    die();
}
add_action('wp_ajax_bitc_save_question', 'bitc_save_question');


// delete question
function bitc_delete_question()
{
    if (!bitc_user_permission()) {
        echo '{"error": "User level cannot modify products"}';
        die();
    }


    $bitc_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($bitc_nonce, 'bitc_quiz_nonce')) {
        echo '{"error": "Nonce was not valid"}';
        die();
    }

    if (!isset($_POST["question"])) {
        echo '{"error": "Data was not correctly sent"}';
        die();
    }

    $questionID = intval($_POST["question"]);
    wp_delete_post($questionID); // will move to trash
    echo '{"success": true}';
    die();
}
add_action('wp_ajax_bitc_delete_question', 'bitc_delete_question');

/* Add New Quiz
------------------------------------------------------- */
function bitc_add_quiz()
{
    if (!bitc_user_permission()) {
        echo '{"error": "User level cannot modify products"}';
        die();
    }


    $bitc_nonce = sanitize_text_field($_POST['nonce']);
    if (!wp_verify_nonce($bitc_nonce, 'bitc_quiz_nonce')) {
        echo '{"error": "Nonce was not valid"}';
        die();
    }

    if (!isset($_POST["quiz"])) {
        echo '{"error": "Quiz name was not sent"}';
        die();
    }

    $quiz = sanitize_text_field($_POST["quiz"]);

    $bitc_new_quiz = wp_insert_term(
        $quiz, // the term
        'quiz' // the taxonomy
    );

    // save current user ID as custom meta
    $user_id = get_current_user_id();
    add_term_meta($bitc_new_quiz["term_id"], "bitc_author_id", $user_id);
    echo '{"success": true, "quiz": "' . bitc_encodeURIComponent($quiz) . '", "id": "' . $bitc_new_quiz["term_id"] . '"}';
    die();
}
add_action('wp_ajax_bitc_add_quiz', 'bitc_add_quiz');

/* Set Default Quiz Meta
------------------------------------------------------- */
function bitc_set_default_quiz_meta()
{
    $quiz_settings = array();
    $quiz_settings["randomize_questions"]["value"][0] = "";
    $quiz_settings["randomize_answers"]["value"][0] = "";
    $quiz_settings["pool_of_questions"]["value"] = "";
    $quiz_settings["wp_paginate"]["value"] = "";
    $quiz_settings["quiz_timer"]["value"] = "";
    $quiz_settings["quiz_timer_question"]["value"][0] = "no";
    $quiz_settings["show_results"]["value"][0] = "yes";
    $quiz_settings["show_results_correct"]["value"][0] = "";
    $quiz_settings["show_extra_text"]["value"][0] = "";
    $quiz_settings["show_results_now"]["value"][0] = "";
    $quiz_settings["stop_answer_reselect"]["value"][0] = "";
    $quiz_settings["quiz_pass_percentage"]["value"] = 70;
    $quiz_settings["share_results"]["value"][0] = "yes";
    $quiz_settings["results_position"]["value"] = "above";
    $quiz_settings["quiz_pass_text"]["value"] = "";
    $quiz_settings["quiz_fail_text"]["value"] = "";
    return $quiz_settings;
}

/* Sanitize all fields read
------------------------------------------------------- */
function bitc_sanitize_fields($fields)
{
    if (!isset($fields) || $fields == "") {
        return bitc_set_default_quiz_meta();
    }

    foreach ($fields as $key => $v) {
        if (!isset($v["value"])) {
            if (isset($v["default"])) {
                $v["value"] = $v["default"];
            }
        }

        if (
            $v["type"] == "text" ||
            $v["type"] == "select" ||
            $v["type"] == "radio"
        ) {
            $fields[$key]["value"] = sanitize_text_field($v["value"]);
        } elseif (
            $v["type"] == "float"
        ) {
            if ($fields[$key]["value"] != "") {
                $fields[$key]["value"] = floatval($v["value"]);
            }
        } elseif ($v["type"] == "integer") {
            if ($fields[$key]["value"] != "") {
                $fields[$key]["value"] = intval($v["value"]);
            }
        } elseif ($v["type"] == "checkbox") {
            if (is_array($v["value"])) {
                for ($i = 0; $i < count($v["value"]); $i++) {
                    $fields[$key]["value"][$i] = sanitize_text_field($v["value"][$i]);
                }
            } else {
                $fields[$key]["value"] = array("");
            }
        } elseif ($v["type"] == "categories") {
            if (isset($v["value"]) && $v["value"] != "") {
                for ($i = 0; $i < count($v["value"]); $i++) {
                    $fields[$key]["value"][$i] = intval($v["value"][$i]);
                }
            }
        } elseif ($v["type"] == "editor") {
            $fields[$key]["value"] = wp_kses_post(stripslashes(urldecode($v["value"])));
        } elseif ($v["type"] == "email") {
            $v["value"] = explode(",", $v["value"]);
            for ($i = 0; $i < count($v["value"]); $i++) {
                $v["value"][$i] = sanitize_email($v["value"][$i]);
            }
            $fields[$key]["value"] = join(",", $v["value"]);
        } elseif ($v["type"] == "question_order") {
            if (isset($v["value"])) {
                for ($i = 0; $i < count($v["value"]); $i++) {
                    $fields[$key]["value"][$i][0] = intval($v["value"][$i][0]);
                    $fields[$key]["value"][$i][1] = intval($v["value"][$i][1]);
                }
            }
        } elseif ($v["type"] == "answers") {
            for ($i = 0; $i < count($v["value"]); $i++) {
                $fields[$key]["value"][$i]["answer"] = wp_kses_post($v["value"][$i]["answer"]);
                $fields[$key]["value"][$i]["image"] = intval($v["value"][$i]["image"]);
            }
        } elseif ($v["type"] == "correct") {
            if (isset($v["value"])) {
                for ($i = 0; $i < count($v["value"]); $i++) {
                    $fields[$key]["value"][$i] = intval($v["value"][$i]);
                }
            } else {
                $fields[$key]["value"] = array();
                $fields[$key]["value"][0] = 1;
            }
        } elseif ($v["type"] == "title") {
            $allowed_html = array(
                'strong' => array(),
                'em' => array(),
                'code' => array(),
                'sup' => array(),
                'sub' => array(),
            );
            $fields[$key]["value"] = wp_kses_post($v["value"]);
        } elseif ($v["type"] == "encode") {
            $fields[$key]["value"] = bitc_encode($v["value"]);
        } else {
            // unknown type, santize as string
            if (!is_array($v["value"])) {
                $fields[$key]["value"] = sanitize_text_field($v["value"]);
            } else {
                // santize array as string
                $fields[$key]["value"] = array_map('bitc_sanitize_array', $v["value"]);
            }
        }
    }
    return $fields;
}

// mimic javaScripts encodeURIComponent
function bitc_encodeURIComponent($str)
{
    $revert = array('%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')');
    return strtr(rawurlencode($str), $revert);
}

// sanitize array
function bitc_sanitize_array($data)
{
    return sanitize_text_field($data);
}



/* Print Template Functions
------------------------------------------------------- */
function bitc_get_results($quiz_settings)
{

    if (!defined('bitc_TWITTER_SHARE_ICON')) {
        define('bitc_TWITTER_SHARE_ICON', false);
    }

    $pass_text = $quiz_settings["quiz_pass_text"]["value"];
    $fail_text = $quiz_settings["quiz_fail_text"]["value"];
    $share_results = $quiz_settings["share_results"]["value"][0];

    $settings = bitc_get_settings();
    if (isset($settings["hd_qu_results"]["value"])) {
        $result_text = $settings["hd_qu_results"]["value"];
    } else {
        $result_text = "";
    }
    if ($result_text == null || $result_text == "") {
        $result_text = "Results";
    }
    // if user has added a Facebook App ID
    $fb_appId = $settings["hd_qu_fb"]["value"];
    // if the user wants to show results as a percentage as well
    $results_percent = $settings["hd_qu_percent"]["value"][0]; ?>

    <div class="bitc_results_wrapper">
        <div class="bitc_results_inner" aria-live="polite">
            <h2 class="bitc_results_title"><?php echo $result_text; ?></h2>
            <div class="bitc_result"><?php if ($results_percent == "yes") {
                                        echo ' - <span class = "bitc_result_percent"></span>';
                                    } ?></div>
            <div class="bitc_result_pass"><?php echo apply_filters('the_content', $pass_text); ?></div>
            <div class="bitc_result_fail"><?php echo apply_filters('the_content', $fail_text); ?></div>
            <?php
            if ($share_results === "yes") {
            ?>
                <div class="bitc_share">
                    <?php
                    if ($fb_appId == "" || $fb_appId == null) {
                    ?>
                        <div class="bitc_social_icon">
                            <a title="share quiz on Facebook" href="http://www.facebook.com/sharer/sharer.php?u=<?php echo the_permalink(); ?>&amp;title=Quiz" target="_blank" class="bitc_facebook">
                                <img src="<?php echo plugins_url('/images/fbshare.png', __FILE__); ?>" alt="Share your score!">
                            </a>
                        </div>
                    <?php
                    } else {
                        bitc_get_fb_app_share($fb_appId);
                    } ?>
                    <div class="bitc_social_icon">
                        <?php
                        if (bitc_TWITTER_SHARE_ICON) {
                        ?>
                            <a href="#" target="_blank" class="bitc_twitter" title="X, formerly Twitter"><img src="<?php echo plugins_url('/images/twshare.png', __FILE__); ?>" alt="Tweet your score!"></a>
                        <?php
                        } else {
                        ?>
                            <a href="#" target="_blank" class="bitc_twitter" title="X, formerly Twitter"><img src="<?php echo plugins_url('/images/xshare.png', __FILE__); ?>" alt="Tweet your score!"></a>
                        <?php
                        }
                        ?>
                    </div>
                    <div class="bitc_social_icon">
                        <a href="#" class="bitc_share_other"><img src="<?php echo plugins_url('/images/share.png', __FILE__); ?>" alt="Share to other"></a>
                    </div>
                </div>
            <?php
            } ?>
        </div>
        <?php
        if (isset($settings["hd_qu_heart"]["value"]) && $settings["hd_qu_heart"]["value"][0] === "yes") {
            echo '<p class = "bitc_heart">Bitcoin Mastermind powered by <a href = "https://hdplugins.com" target = "_blank" title = "Best WordPress Developers">harmonic design</a></p>';
        }
        ?>
    </div>
<?php
}

function bitc_get_fb_app_share($fb_appId)
{
?>
    <script>
        window.fbAsyncInit = function() {
            FB.init({
                appId: '<?php echo $fb_appId; ?>',
                autoLogAppEvents: true,
                xfbml: true,
                version: 'v3.2'
            });
        };

        (function(d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement(s);
            js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    </script>


    <div class="bitc_social_icon">
        <img id="bitc_fb_sharer" src="<?php echo plugins_url('/images/fbshare.png', __FILE__); ?>" alt="Share your score!">
    </div>


<?php
}

function bitc_print_question_featured_image($question)
{
    if ($question["featured_image"]["value"] != "" && $question["featured_image"]["value"] != 0) {
        $image = wp_get_attachment_image($question["featured_image"]["value"], "full", "", array("class" => "bitc_featured_image"));
        if ($image != null) {
            echo '<div class = "bitc_question_featured_image">';
            echo $image;
            echo '</div>';
        }
    }
}

// This should be depricated starting Bitcoin Mastermind 1.9
function bitc_get_answer_image_url($image)
{
    if (is_numeric($image)) {
        // if this uses image ID instead of URL
        $image_url = wp_get_attachment_image_src($image, "hd_qu_size2", false);
        if ($image_url[0] == "" || $image_url[0] == null) {
            $image_url = wp_get_attachment_image_src($image, "thumbnail", false);
        } else {
            // check if image is a gif
            // When WP resizes a gif, the gif is no longer animated :(
            $extention =  parse_url($image_url[0], PHP_URL_PATH);
            $extention = pathinfo($extention, PATHINFO_EXTENSION);
            if ($extention === "gif") {
                $image_url = wp_get_attachment_image_src($image, "full", false);
            }
        }
        $image = $image_url[0];
        return $image;
    } else {
        // figure out what the original custom image size was
        // get the extention -400x400
        $image_parts = explode(".", $image);
        $image_extention = end($image_parts);
        unset($image_parts[count($image_parts) - 1]);
        $image_url = implode(".", $image_parts);
        $image_url = $image_url . '-400x400.' . $image_extention;
        return $image_url;
    }
}

function bitc_print_quiz_start($timer, $use_adcode)
{
    if (intval($timer) > 3 && $use_adcode !== true) {
        $label = "START QUIZ";
        $settings = bitc_get_settings();
        $hd_qu_start = $settings["hd_qu_start"]["value"];
        if ($hd_qu_start != "" && $hd_qu_start != null) {
            $label = $hd_qu_start;
        }
        echo '<div class = "bitc_quiz_start bitc_button" role = "button" tabindex = "0" title = "' . $label . '">' . $label . '</div>';
    }
}

function bitc_print_quiz_in_loop()
{
    $label = "START QUIZ";
    $settings = bitc_get_settings();
    $hd_qu_start = $settings["hd_qu_start"]["value"];
    if ($hd_qu_start != "" && $hd_qu_start != null) {
        $label = $hd_qu_start;
    }

    $permalink = get_the_permalink();

    echo '<div class = "bitc_quiz_wrapper"><a href = "' . $permalink . '" rel="noamphtml" class = "bitc_quiz_start bitc_button button" role = "button" title = "' . $label . '">' . $label . '</a></div>';
}

function bitc_print_question_title($question_number, $question)
{
    if (isset($_GET['totalQuestions'])) {
        $question_number = $question_number + intval($_GET['totalQuestions']);
    }
    $tooltip = "";
    if ($question["tooltip"]["value"] != "" && $question["tooltip"]["value"] != null) {
        $tooltip = '<span class="bitc_tooltip">
    ?
    <span class="bitc_tooltip_content">
        <span>' . $question["tooltip"]["value"] . '</span>
    </span>
</span>';
    }
    echo '<h3 class = "bitc_question_heading"><span class = "bitc_question_number">#' . $question_number . '.</span> ' . $question["title"]["value"] . ' ' . $tooltip . '</h3>';
}

function bitc_print_question_extra_text($question)
{
    if ($question["extra_text"]["value"] != "") {
        echo '<div class = "bitc_question_after_text">';
        echo apply_filters('the_content', $question["extra_text"]["value"]);
        echo '</div>';
    }
}

function bitc_get_question_answers($answers, $correct, $randomized)
{
    $n = array();
    if ($correct) {
        for ($i = 0; $i < count($answers); $i++) {
            $a = array();
            $a["answer"] = $answers[$i]["answer"];
            $a["image"] = $answers[$i]["image"];
            $a["correct"] = in_array($i + 1, $correct);
            if ($a["answer"] != "" && $a["answer"] != null) {
                array_push($n, $a);
            }
        }
        if ($randomized == "yes") {
            shuffle($n);
        }
    }
    return $n;
}

function bitc_print_jPaginate($bitc_id)
{
    $settings = bitc_get_settings();
    $next_text = $settings["hd_qu_next"]["value"];
    if ($next_text == "" || $next_text == null) {
        $next_text = "next";
    }
    echo '<div class = "bitc_jPaginate"><div class = "bitc_next_button bitc_jPaginate_button bitc_button" data-id = "' . $bitc_id . '" role = "button" tabindex = "0">' . $next_text . '</div></div>';
}

function bitc_print_finish($bitc_id, $jPaginate)
{
    $settings = bitc_get_settings();
    $finish_text = $settings["hd_qu_finish"]["value"];
    if ($finish_text == "" || $finish_text == null) {
        $finish_text = "finish";
    }
    if ($jPaginate) {
        $jPaginate = "bitc_hidden";
    } else {
        $jPaginate = "";
    }

    do_action("bitc_before_finish_button", $bitc_id);

    echo '<div class = "bitc_finish"><div class = "bitc_finsh_button bitc_button ' . $jPaginate . '" data-id = "' . $bitc_id . '" role = "button" tabindex = "0">' . $finish_text . '</div></div>';
}

function bitc_print_next($bitc_id, $page_num)
{
    $settings = bitc_get_settings();
    $next_text = $settings["hd_qu_next"]["value"];
    if ($next_text == "" || $next_text == null) {
        $next_text = "next";
    }
    $page_num = $page_num + 1;
    $next_page_data = get_the_permalink();
    $next_page_data = $next_page_data . 'page/' . $page_num . '?currentScore=';
    echo '<div class = "bitc_next_page"><a class = "bitc_next_page_button bitc_button" data-id = "' . $bitc_id . '" href = "' . $next_page_data . '">' . $next_text . '</a></div>';
}

function bitc_get_paginate_question_number($i)
{
    if (isset($_GET['totalQuestions'])) {
        return intval($_GET['totalQuestions'] + $i);
    } else {
        return $i;
    }
}

/* Default Question Types
------------------------------------------------------- */
function bitc_multiple_choice_text($question_ID, $question_number, $question, $quiz)
{
    require(dirname(__FILE__) . '/templates/default.php');
}

function bitc_multiple_choice_image($question_ID, $question_number, $question, $quiz)
{
    require(dirname(__FILE__) . '/templates/image.php');
}

function bitc_text_based($question_ID, $question_number, $question, $quiz)
{
    require(dirname(__FILE__) . '/templates/text.php');
}

function bitc_title($question_ID, $question_number, $question, $quiz)
{
    require(dirname(__FILE__) . '/templates/title.php');
}

function bitc_select_all_apply_text($question_ID, $question_number, $question, $quiz)
{
    require(dirname(__FILE__) . '/templates/select-all-text.php');
}

function bitc_select_all_apply_image($question_ID, $question_number, $question, $quiz)
{
    require(dirname(__FILE__) . '/templates/select-all-image.php');
}

// polyfill for < php8
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle)
    {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}
