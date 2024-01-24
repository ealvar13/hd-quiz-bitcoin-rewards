<?php
    // question type template
    // text based answers (user input)

    // print question title
    hdq_print_question_title($question_number, $question);
	$hdq_settings = hdq_get_settings();
	$hint_text = $hdq_settings["hd_qu_text_based_answers"]["value"];
	if($hint_text == "" || $hint_text == null){
		$hint_text = "enter answer here...";
	}

    // if randomize answer order is enabled, then the "correct" result will be chosen random too
    // so we account for this by just getting the question data again
    if ($quiz["randomize_answers"]["value"][0] == "yes") {
        $question = get_hdq_question($question["question_id"]["value"]);
    }

    // print out answers
    $answers = $question["answers"]["value"];
    $answers = $question["answers"]["value"];
    $correct = array();
    for ($i = 0; $i < count($answers); $i++) {
        if ($answers[$i]["answer"] != "" && $answers[$i]["answer"] != null) {
            array_push($correct, strtoupper($answers[$i]["answer"]));
        }
    }
    $correct = hdq_encodeURIComponent(json_encode($correct));
    ?>
	<div class = "hdq_answers">
		<div class = "hdq_row">
			<label for = "hdq_option_<?php echo $question_ID; ?>" class = "hdq_aria_label"><?php echo $hint_text; ?></label>
			<input id="hdq_option_<?php echo $question_ID; ?>" autocomplete="off" data-id = "<?php echo htmlentities($question["question_id"]["value"]); ?>" class = "hdq_label_answer hdq_input hdq_option" data-answers = "<?php echo htmlentities($correct); ?>" data-type = "text" type = "text" title = "<?php echo htmlentities($hint_text); ?>" placeholder = "<?php echo htmlentities($hint_text); ?>" enterkeyhint="done"/>
		</div>
	</div>
