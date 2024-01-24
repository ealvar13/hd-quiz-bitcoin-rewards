<?php
    // question type template
    // Select All Apply text

    // print question title
    hdq_print_question_title($question_number, $question);

	$hdq_settings = hdq_get_settings();
	$hint_text = $hdq_settings["hd_qu_select_all_apply"]["value"];
	if($hint_text == "" || $hint_text == null){
		$hint_text = "Select all that apply:";
	}

    // print out answers
    $answers = $question["answers"]["value"];
    echo '<div class = "hdq_answers">';	
	echo '<p>'.$hint_text.'</p>';
    for ($i = 0; $i < count($answers); $i++) {
        if ($answers[$i]["answer"] != "" && $answers[$i]["answer"] != null) {
            $selected = 0;
            if ($answers[$i]["correct"]) {
                $selected = 1;
            } ?>
			<div class = "hdq_row">
				<label class="hdq_label_answer" id = "hda_label_<?php echo $i . '_' . $question_ID; ?>" data-type = "radio" data-id = "hdq_question_<?php echo $question_ID; ?>" for="hdq_option_<?php echo $i . '_' . $question_ID; ?>">
					<div class="hdq-options-check">
						<input aria-labelledby = "hda_label_<?php echo $i . '_' . $question_ID; ?>" autocomplete="off" type="checkbox" data-id = "<?php echo $question_ID; ?>" class="hdq_option hdq_check_input" data-type = "radio_multi" value="<?php echo $selected; ?>" name="hdq_option_<?php echo $i . '_' . $question_ID; ?>" id="hdq_option_<?php echo $i . '_' . $question_ID; ?>">
						<span class = "hdq_toggle"><span class = "hdq_aria_label"><?php echo $answers[$i]["answer"]; ?></span></span>						
					</div>
				<?php echo $answers[$i]["answer"]; ?>
				</label>
			</div>
	<?php
        }
    }
	echo '</div>';	
