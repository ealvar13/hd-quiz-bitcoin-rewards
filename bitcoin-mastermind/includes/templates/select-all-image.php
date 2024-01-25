<?php
    // question type template
    // Select All Apply image

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
	echo '<div class = "hdq_question_answers_images">';
    for ($i = 0; $i < count($answers); $i++) {
        if ($answers[$i]["answer"] != "" && $answers[$i]["answer"] != null) {
            $selected = 0;
            if ($answers[$i]["correct"]) {
                $selected = 1;
            } ?>
			<div class = "hdq_row hdq_row_image">
				<label role = "button" aria-labelledby = "hda_label_<?php echo $i . '_' . $question_ID; ?>"  id = "hda_label_<?php echo $i . '_' . $question_ID; ?>" class="hdq_label_answer" data-type = "image" data-id = "hdq_question_<?php echo $question_ID; ?>" for="hdq_option_<?php echo $i . '_' . $question_ID; ?>">
				<?php
                $image = "";
            if ($answers[$i]["image"] != "" && $answers[$i]["image"] != 0) {
                $image = hdq_get_answer_image_url($answers[$i]["image"]);
            }
            if ($image != "" && $image != null) {
                echo '<img src = "'.$image.'" alt = "'.htmlentities($answers[$i]["answer"]).'"/>';
            } ?>
					<div>					
						<div class="hdq-options-check">
							<input type="checkbox" autocomplete="off" data-id = "<?php echo $question_ID; ?>" class="hdq_option hdq_check_input" data-type = "radio_multi" value="<?php echo $selected; ?>" name="hdq_option_<?php echo $i . '_' . $question_ID; ?>" id="hdq_option_<?php echo $i . '_' . $question_ID; ?>">
							<span class = "hdq_toggle"><span class = "hdq_aria_label"><?php echo $answers[$i]["answer"]; ?></span></span>
						</div>
					<?php echo $answers[$i]["answer"]; ?>
					</div>
				</label>
			</div>
	<?php
        }
    }
	echo '</div>';
	echo '</div>';	
