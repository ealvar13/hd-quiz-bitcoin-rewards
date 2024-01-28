<?php
    // question type template
    // multiple choice image

    // print question title
    bitc_print_question_title($question_number, $question);
    // print out answers
    $answers = $question["answers"]["value"];
	echo '<div class = "bitc_answers">';	
    echo '<div class = "bitc_question_answers_images">';
    for ($i = 0; $i < count($answers); $i++) {
        if ($answers[$i]["answer"] != "" && $answers[$i]["answer"] != null) {
            $selected = 0;
            if ($answers[$i]["correct"]) {
                $selected = 1;
            } ?>
			<div class = "bitc_row bitc_row_image">
				<label role = "button" aria-labelledby = "hda_label_<?php echo $i . '_' . $question_ID; ?>"  id = "hda_label_<?php echo $i . '_' . $question_ID; ?>" class="bitc_label_answer" data-type = "image" data-id = "bitc_question_<?php echo $question_ID; ?>" for="bitc_option_<?php echo $i . '_' . $question_ID; ?>">
				<?php
                $image = "";
            if ($answers[$i]["image"] != "" && $answers[$i]["image"] != 0) {
                $image = bitc_get_answer_image_url($answers[$i]["image"]);
            }
            if ($image != "" && $image != null) {
                echo '<img src = "'.$image.'" alt = "'.htmlentities($answers[$i]["answer"]).'"/>';
            } ?>
					<div>					
						<div class="hdq-options-check">
							<input data-name="<?php echo $answers[$i]["answer"]; ?>" type="checkbox" autocomplete="off" data-id = "<?php echo $question_ID; ?>" class="bitc_option bitc_check_input" data-type = "image" value="<?php echo $selected; ?>" name="bitc_option_<?php echo $i . '_' . $question_ID; ?>" id="bitc_option_<?php echo $i . '_' . $question_ID; ?>">
							<span class = "bitc_toggle"><span class = "bitc_aria_label"><?php echo $answers[$i]["answer"]; ?></span></span>
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
