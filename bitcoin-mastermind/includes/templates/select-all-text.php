<?php
    // question type template
    // Select All Apply text

    // print question title
    bitc_print_question_title($question_number, $question);

	$bitc_settings = bitc_get_settings();
	$hint_text = $bitc_settings["hd_qu_select_all_apply"]["value"];
	if($hint_text == "" || $hint_text == null){
		$hint_text = "Select all that apply:";
	}

    // print out answers
    $answers = $question["answers"]["value"];
    echo '<div class = "bitc_answers">';	
	echo '<p>'.$hint_text.'</p>';
    for ($i = 0; $i < count($answers); $i++) {
        if ($answers[$i]["answer"] != "" && $answers[$i]["answer"] != null) {
            $selected = 0;
            if ($answers[$i]["correct"]) {
                $selected = 1;
            } ?>
			<div class = "bitc_row">
				<label class="bitc_label_answer" id = "hda_label_<?php echo $i . '_' . $question_ID; ?>" data-type = "radio" data-id = "bitc_question_<?php echo $question_ID; ?>" for="bitc_option_<?php echo $i . '_' . $question_ID; ?>">
					<div class="hdq-options-check">
						<input data-name="<?php echo $answers[$i]["answer"]; ?>" aria-labelledby = "hda_label_<?php echo $i . '_' . $question_ID; ?>" autocomplete="off" type="checkbox" data-id = "<?php echo $question_ID; ?>" class="bitc_option bitc_check_input" data-type = "radio_multi" value="<?php echo $selected; ?>" name="bitc_option_<?php echo $i . '_' . $question_ID; ?>" id="bitc_option_<?php echo $i . '_' . $question_ID; ?>">
						<span class = "bitc_toggle"><span class = "bitc_aria_label"><?php echo $answers[$i]["answer"]; ?></span></span>						
					</div>
				<?php echo $answers[$i]["answer"]; ?>
				</label>
			</div>
	<?php
        }
    }
	echo '</div>';	
