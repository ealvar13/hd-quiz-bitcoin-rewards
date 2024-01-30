<?php
// general HDQ Addon Save Results Light functions

// Tell Bitcoin Mastermind to send an AJAX request to `bitc_a_light_submit_action()`
// once quiz has been submitted
function bitc_a_light_submit($quizOptions)
{
    array_push($quizOptions->bitc_submit, "bitc_a_light_submit_action");
    return $quizOptions;
}
add_action('bitc_submit', 'bitc_a_light_submit');

// the functon that runs once quiz submitted
function bitc_a_light_submit_action($data)
{
    function bitc_a_i_validate_score($score)
    {
        return intval($score);
    }

    // check if logged-in users only should be saved
    $membersOnly = sanitize_text_field(get_option("bitc_a_l_members_only"));
    if ($membersOnly === "yes" && !is_user_logged_in()) {
        die();
    }

    $result = new stdClass();
    $quizID = intval($_POST['data']["quizID"]);
    $result->quizID = $quizID;
    $score = array_map('bitc_a_i_validate_score', $_POST['data']["score"]);
    $result->score = $score;

    // get quiz meta
    if (bitc_PLUGIN_VERSION < 1.8) {
        $bitc_quiz_options = bitc_get_quiz_options($quizID);
        $passPercent = intval($bitc_quiz_options["passPercent"]);
    } else {
        $bitc_quiz_options = get_bitc_quiz($quizID);
        $passPercent = $bitc_quiz_options["quiz_pass_percentage"]["value"];
    }
    $result->passPercent = $passPercent;

    // get quiz term info
    $term = get_term($quizID, "quiz");
    $quizName = $term->name;
    $result->quizName = $quizName;

    // create the user info
    $quizTaker = array();
    $current_user = wp_get_current_user();
    if ($current_user->ID === 0) {
        $quizTaker[0] = "0";
        $quizTaker[1] = "--";
    } else {
        $quizTaker[0] = $current_user->ID;
        $quizTaker[1] = $current_user->data->display_name;
    }
    $result->quizTaker = $quizTaker;

    // save the date and time
    $timezone = get_option('timezone_string');
    date_default_timezone_set($timezone);
    $result->datetime = date('m-d-Y h:i:s a', time());

    // read in existing results
    $data = get_option("bitc_quiz_results_l");

    if ($data == "" || $data == null) {
        $data = array();
        update_option("bitc_quiz_results_l", "");
    } else {
        $data = json_decode(html_entity_decode($data), true);
    }

    // append new result to data
    array_push($data, $result);

    // re-encode and update record
    $result = json_encode($data);

    //print_r($result );die;
    update_option("bitc_quiz_results_l", sanitize_text_field($result));

    echo "Quiz result has been logged";

    die();
}
add_action('wp_ajax_bitc_a_light_submit_action', 'bitc_a_light_submit_action');
add_action('wp_ajax_nopriv_bitc_a_light_submit_action', 'bitc_a_light_submit_action');




// delete all results
function bitc_a_light_delete_results()
{
    //die('sudh');
    global $wpdb;
    $table_name = $wpdb->prefix.'bitcoin_quiz_results';
    $wpdb->query("TRUNCATE TABLE $table_name");
    echo 'All records deleted successfully';
    update_option("bitc_quiz_results_l", "");
    die();
}
add_action('wp_ajax_bitc_a_light_delete_results', 'bitc_a_light_delete_results');


function fetch_survey_detail_results(){
    global $wpdb;
    $table_name = $wpdb->prefix.'bitcoin_survey_results';
    if(!empty($_POST['id'])){
        // Your WHERE clause condition
        $condition = "result_id=".$_POST['id']; // Replace 'your_column_name' and 'your_value' with the actual column name and value

        // SQL query
        $query = $wpdb->prepare("SELECT * FROM $table_name WHERE $condition");

        // Execute the query
        $results = $wpdb->get_results($query);

       // print_r($results);die;
        $html="<table class='survey-details-table'>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Question</th>
                            <th>Selected</th>
                            <th>Correct</th>
                        </tr>
                    </thead><tbody>";
        if(!empty($results)){
            $i=1;
            foreach ($results as $key => $value) {
                $html .="<tr>";
                $html .="<td>".$i."</td>";
                $html .="<td>".$value->question."</td>";
                $html .="<td>".rtrim($value->selected,',')."</td>";
                $html .="<td>".rtrim($value->correct,',')."</td>";
                $html .="</tr>";
            $i++;}

        }
        $html .="</tbody></table>";
        //$result = json_encode($html);
        echo $html;die;
    }
}
add_action('wp_ajax_fetch_survey_details', 'fetch_survey_detail_results');
add_action('wp_ajax_nopriv_fetch_survey_details', 'fetch_survey_detail_results');
