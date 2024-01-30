<?php
// question type template
// default multiple choice text

// print question title
bitc_print_question_title($question_number, $question);

// print out answers
$answers = $question["answers"]["value"];
//echo"-----------";print_r($answers);die;
echo '<div class = "bitc_answers">';
for ($i = 0; $i < count($answers); $i++) {
    if ($answers[$i]["answer"] != "" && $answers[$i]["answer"] != null) {
        $selected = 0;
        if ($answers[$i]["correct"]) {
            $selected = 1;
        } ?>
        <div class="bitc_row">
            <label class="bitc_label_answer" id="hda_label_<?php echo $i . '_' . $question_ID; ?>" data-type="radio" data-id="bitc_question_<?php echo $question_ID; ?>" for="bitc_option_<?php echo $i . '_' . $question_ID; ?>">
                <div class="hdq-options-check">
                    <input type="checkbox" aria-labelledby="hda_label_<?php echo $i . '_' . $question_ID; ?>" autocomplete="off" title="<?php echo htmlentities($answers[$i]["answer"]); ?>" data-id="<?php echo $question_ID; ?>" class="bitc_option bitc_check_input" data-type="radio" value="<?php echo $selected; ?>" name="bitc_option_<?php echo $i . '_' . $question_ID; ?>" id="bitc_option_<?php echo $i . '_' . $question_ID; ?>">
                    <span class="bitc_toggle"><span class="bitc_aria_label"><?php echo $answers[$i]["answer"]; ?></span></span>
                   
                </div>
                <?php
                if (str_contains($answers[$i]["answer"], "[")) {
                    // likely a shortcode
                    remove_filter('the_content', 'wpautop');
                    echo apply_filters('the_content', $answers[$i]["answer"]);
                    add_filter('the_content', 'wpautop');
                } else {
                    echo $answers[$i]["answer"];
                }
                ?>
            </label>
        </div>
<?php
    }
}
echo '</div>';
