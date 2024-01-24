<?php
function hdq_register_tools_csv_importer_page_callback()
{
	if (!current_user_can('edit_others_pages')) {
		die();
	}

	wp_enqueue_style(
		'hdq_admin_style',
		plugin_dir_url(__FILE__) . '../css/hdq_admin.css?v=' . HDQ_PLUGIN_VERSION
	);

	wp_enqueue_script(
		'hdq_admin_script_data_update',
		plugins_url('../js/hdq_csv_import.js', __FILE__),
		array('jquery'),
		HDQ_PLUGIN_VERSION,
		true
	);


	$line = 0;
	if (isset($_FILES["hdq_csv_file_upload"])) {
		$line = hdq_accept_csv();
	} ?>

	<div id="main" style="max-width: 800px; background: #f3f3f3; border: 1px solid #ddd; margin-top: 2rem">
		<div id="header">
			<h1 id="heading_title" style="margin-top:0">
				HD Quiz - Bulk Question Importer
			</h1>
		</div>
		<div <?php if ($line !== 0) {
					echo 'style = "display: none"';
				} ?>>
			<p>
				Using this tool, you can upload a CSV to bulk import questions. Please note that due to the complexity of
				creating and formatting a CSV file, I can only offer limited support for this feature. This tool will only set the basic values needed for a question. You will need to manually set Quiz settings, or extra Question options such as question type and images after the import has completed.
			</p>
			<div class="hdq_highlight">
				<h4 style="margin: 0">
					Instructions
				</h4>
				<p>
					Fields should use a comma <code>,</code> as a delimiter and strings should use a double quote <code>"</code>
					as a delimiter. The file name must also not contain any spaces or special characters.
				</p>
				<p>
					<strong>Fields: </strong> <code>Quiz Name</code>, <code>Question Title</code>, <code>Answer 1</code>,
					<code>Answer 2</code>, <code>Answer 3</code>, ... <code>Answer 10</code>, <code>Correct Answer</code>. The
					<code>Correct Answer</code> field should be an integer that corresponds to which answer is correct. So if
					<code>Answer 3</code> is the correct answer, then set <code>Correct Answer</code> to <code>3</code>.
				</p>
				<p style="text-align: center">
					View example CSV to use as a reference. <a href="<?php echo plugin_dir_url(__FILE__) . 'questions.csv'; ?>" target="_blank">example HDQ CSV</a>
				</p>
			</div>
			<br /><br />
			<center>


				<form action="<?php echo get_admin_url(null, "?page=hdq_tools_csv_importer"); ?>" method="post" enctype="multipart/form-data">
					<?php wp_nonce_field('hdq_tools_nonce', 'hdq_tools_nonce'); ?>
					<input type="hidden" style="display:none" name="hdq_line_number" id="hdq_line_number" value="<?php echo $line; ?>" />
					<div style="display: grid; grid-template-columns: 1fr max-content; grid-gap: 2em; align-items: center; width: 100%; max-width: 600px">

						<div>
							<input style="width: 0.1px; height: 0.1px; opacity: 0; overflow: hidden; position: absolute; z-index: -1;" type="file" accept=".csv" name="hdq_csv_file_upload" id="hdq_csv_file_upload" required="">
							<label class="hdq_button2" style="width: 100%; border-radius: 3px; font-weight: 400; text-overflow: ellipsis; white-space: nowrap; cursor: pointer; display: inline-block; overflow: hidden; padding: 0.625rem 1.25rem" for="hdq_csv_file_upload">
								<span class="dashicons dashicons-upload"></span> <span class="hdq_file_label">Choose a
									file…</span>
							</label>
						</div>
						<input type="submit" class="hdq_button" id="hdq_start_csv_upload" title="Begin Import" value="BEGIN IMPORT" />
					</div>
				</form>
			</center>
		</div>
		<div id="hdq_message_logs">
			<?php
			if ($line === "uploading") {
				echo '<div class = "hdq_log_item">Adding questions has begun. Please do not leave this page until complete.</div>';
			} ?>
		</div>
	</div>

	<?php
}




function hdq_accept_csv()
{

	/* Get a file's MIME type (used for importer)
    ------------------------------------------------------- */
	function get_mime($file)
	{
		if (function_exists("finfo_file")) {
			$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
			$mime = finfo_file($finfo, $file);
			finfo_close($finfo);
			return $mime;
		} elseif (function_exists("mime_content_type")) {
			return mime_content_type($file);
		} else {
			return false;
		}
	}

	if (!current_user_can('edit_others_pages')) {
		echo 'permission not granted';
		die();
	}

	$hdq_nonce = sanitize_text_field($_POST['hdq_tools_nonce']);
	if (!wp_verify_nonce($hdq_nonce, 'hdq_tools_nonce')) {
		echo 'permission not granted';
		die();
	}

	$csvFile = "";
	$upload_dir = wp_upload_dir();
	$hdq_upload_dir = $upload_dir['basedir'] . '/hd-quiz/';
	wp_mkdir_p($hdq_upload_dir);

	// check file type extention
	$extention = strtolower(pathinfo($_FILES['hdq_csv_file_upload']['name'], PATHINFO_EXTENSION));
	if ($extention != "csv") {
		echo "Uploaded file was not a CSV";
		die();
	}

	// also check mimetype since extention can be spoofed
	$mime = get_mime($_FILES['hdq_csv_file_upload']['tmp_name']);

	if ($mime === "text/plain" || $mime === "text/csv") {
		if (!move_uploaded_file($_FILES['hdq_csv_file_upload']['tmp_name'], $hdq_upload_dir . sanitize_text_field($_FILES['hdq_csv_file_upload']['name']))) {
			echo 'Error uploading file - check destination is writeable.';
			die();
		}
		$csvFile = $hdq_upload_dir . sanitize_file_name($_FILES['hdq_csv_file_upload']['name']);
	} else {
		echo "Uploaded file was not a CSV";
		die();
	}

	if ($csvFile != "" && $csvFile != null) {
		hdq_parse_csv_data($csvFile, $hdq_nonce);
	}
	return "uploading";
}

function hdq_parse_csv_data($csvFile = "", $hdq_nonce = "")
{
	if (!current_user_can('edit_others_pages')) {
		echo 'permission not granted';
		die();
	}

	if ($hdq_nonce == "") {
		$hdq_nonce = sanitize_text_field($_POST['nonce']);
	}

	if (!wp_verify_nonce($hdq_nonce, 'hdq_tools_nonce')) {
		echo 'permission not granted';
		die();
	}
	
	if(!function_exists('str_getcsv')) {
		echo 'CSV function not found on server. Check your PHP version';
		die();
	}

	if ($csvFile == "" && isset($_POST["path"])) {
		$csvFile = sanitize_text_field($_POST["path"]);
	}

	$csvAsArray = array_map(function ($v) {
		return str_getcsv($v, ",", '"');
	}, file($csvFile));

	// sanitize data
	for ($i = 0; $i < count($csvAsArray); $i++) {
		for ($x = 0; $x < count($csvAsArray[$i]); $x++) {
			$csvAsArray[$i][$x] = sanitize_text_field($csvAsArray[$i][$x]);
			if ($x === 12) {
				$csvAsArray[$i][$x] = intval($csvAsArray[$i][$x]);
			}
		}
	}

	$atonce = 1;
	$start = 0;
	if (isset($_POST["start"])) {
		$start = intval($_POST["start"]);
	}

	if ($csvAsArray[$start][0] != "" && $csvAsArray[$start][1] != "") {
		$quizID = term_exists($csvAsArray[$start][0], "quiz");
		if ($quizID == null) {
			// create new quiz
			$quizID = wp_insert_term(
				$csvAsArray[$start][0], // the term
				'quiz' // the taxonomy
			);
			$quizID = $quizID["term_id"];
		} else {
			$quizID = $quizID["term_id"];
		}

		// now add the new question
		$total = wp_count_posts('post_type_questionna');
		$total = $total->publish;

		$post_information = array(
			'post_title' => $csvAsArray[$start][1],
			'post_content' => '', // post_content is required, so we leave blank
			'post_type' => 'post_type_questionna',
			'post_status' => 'publish',
			'menu_order' => $total // always set as the last question of the quiz
		);
		
		// not advertised that we can upload the extra text since
		// we santize most things out (plus hard to support people using HTML and formatting in CSVs)
		$extraText = "";
		if(isset($csvAsArray[$start][13])){
			$extraText = $csvAsArray[$start][13];
		}		
		
		$fields = array();
		$fields["question_id"]["value"] = wp_insert_post($post_information);
		$fields["question_id"]["type"] = "integer";
		$fields["quizzes"]["value"] = array($quizID);
		$fields["quizzes"]["type"] = "quizzes";
		$fields["title"]["value"] = $csvAsArray[$start][1];
		$fields["title"]["type"] = "title";
		$fields["selected"]["value"] = array($csvAsArray[$start][12]);
		$fields["selected"]["type"] = "checkbox";
		$fields["question_type"]["value"] = "multiple_choice_text";
		$fields["question_type"]["type"] = "select";
		$fields["paginate"]["value"] = array("");
		$fields["paginate"]["type"] = "checkbox";
		$fields["tooltip"]["value"] = "";
		$fields["tooltip"]["type"] = "text";
		$fields["extra_text"]["value"] = $extraText;
		$fields["extra_text"]["type"] =  "editor";
		$fields["featured_image"]["value"] = 0;
		$fields["featured_image"]["type"] = "image";

		$answers = array();
		array_push($answers, array("answer" => $csvAsArray[$start][2], "image" => 0));
		array_push($answers, array("answer" => $csvAsArray[$start][3], "image" => 0));
		array_push($answers, array("answer" => $csvAsArray[$start][4], "image" => 0));
		array_push($answers, array("answer" => $csvAsArray[$start][5], "image" => 0));
		array_push($answers, array("answer" => $csvAsArray[$start][6], "image" => 0));
		array_push($answers, array("answer" => $csvAsArray[$start][7], "image" => 0));
		array_push($answers, array("answer" => $csvAsArray[$start][8], "image" => 0));
		array_push($answers, array("answer" => $csvAsArray[$start][9], "image" => 0));
		array_push($answers, array("answer" => $csvAsArray[$start][10], "image" => 0));
		array_push($answers, array("answer" => $csvAsArray[$start][11], "image" => 0));

		$fields["answers"]["type"] = "answers";
		$fields["answers"]["value"] = $answers;
		// set meta
		update_post_meta($fields["question_id"]["value"], "question_data", $fields);
		// set or update terms
		wp_set_post_terms($fields["question_id"]["value"], $fields["quizzes"]["value"], "quiz");
	}


	if ($start == 0) {
	?>
		<script>
			setTimeout(function() {
				// filename, index, total questions
				HDQ.parseNext("<?php echo $csvFile; ?>", <?php echo count($csvAsArray); ?>)
			}, 1000);
		</script>
<?php
	}
}
add_action('wp_ajax_hdq_parse_csv_data', 'hdq_parse_csv_data');
