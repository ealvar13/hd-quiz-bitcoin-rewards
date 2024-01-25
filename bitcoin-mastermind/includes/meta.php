<?php

/* Include the field types
------------------------------------------------------- */
require dirname(__FILE__) . '/fields/text.php';
require dirname(__FILE__) . '/fields/textarea.php';
require dirname(__FILE__) . '/fields/editor.php';
require dirname(__FILE__) . '/fields/image.php';
require dirname(__FILE__) . '/fields/integer.php';
require dirname(__FILE__) . '/fields/float.php';
require dirname(__FILE__) . '/fields/email.php';
require dirname(__FILE__) . '/fields/radio.php';
require dirname(__FILE__) . '/fields/checkbox.php';
require dirname(__FILE__) . '/fields/select.php';
require dirname(__FILE__) . '/fields/encrypt.php';
/* Print each field type
------------------------------------------------------- */
function bitc_print_tab_fields($tab, $tab_slug, $fields)
{
	for ($i = 0; $i < count($tab); $i++) {
		if (!isset($tab[$i]["type"])) {
			return;
		}
		if ($tab[$i]["type"] == "row") {
			bitc_printField_row($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "col-1-1") {
			bitc_printField_col11($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "col-1-1-1") {
			bitc_printField_col111($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "heading") {
			bitc_printField_heading($tab[$i], $tab_slug);
		} elseif ($tab[$i]["type"] == "heading2") {
			bitc_printField_heading2($tab[$i], $tab_slug);
		} elseif ($tab[$i]["type"] == "heading3") {
			bitc_printField_heading3($tab[$i], $tab_slug);
		} elseif ($tab[$i]["type"] == "content") {
			bitc_printField_content($tab[$i], $tab_slug);
		} elseif ($tab[$i]["type"] == "hr") {
			bitc_printField_hr($tab[$i], $tab_slug);
		} elseif ($tab[$i]["type"] == "text") {
			bitc_printField_text($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "textarea") {
			bitc_printField_textarea($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "email") {
			bitc_printField_email($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "editor") {
			bitc_printField_editor($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "float") {
			bitc_printField_float($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "integer") {
			bitc_printField_integer($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "select") {
			bitc_printField_select($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "image") {
			bitc_printField_image($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "radio") {
			bitc_printField_radio($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "checkbox") {
			bitc_printField_checkbox($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "action") {
			bitc_printField_action($tab[$i], $tab_slug, $fields);
		} elseif ($tab[$i]["type"] == "encode") {
			bitc_printField_encode($tab[$i], $tab_slug, $fields);
		}
	}
}

/* Create settings tab array
------------------------------------------------------- */
function bitc_get_settings_tabs()
{
	global $tabs;
	$tabs = array();
	$tab = array();
	$tab["slug"] = "general";
	$tab["title"] = "General";
	array_push($tabs, $tab);
	$tab = array();
	$tab["slug"] = "translate";
	$tab["title"] = "Translation";
	array_push($tabs, $tab);

	$tabs = apply_filters("bitc_add_settings_tab", $tabs);

	// sanitize
	for ($i = 0; $i < count($tabs); $i++) {
		$tabs[$i]["slug"] = sanitize_title($tabs[$i]["slug"]);
		$tabs[$i]["title"] = sanitize_text_field($tabs[$i]["title"]);
	}
	return $tabs;
}

/* Print settings tabs
------------------------------------------------------- */
function bitc_print_settings_tabs()
{
	$tabs = bitc_get_settings_tabs();
	for ($i = 0; $i < count($tabs); $i++) {
		$classes = "";
		if ($i == 0) {
			$classes = "tab_nav_item_active";
		}
		echo '<div role = "button" class="tab_nav_item ' . $classes . '" data-id="' . $tabs[$i]["slug"] . '">' . $tabs[$i]["title"] . '</div>';
	}
}

/* Get and print settings tab content
------------------------------------------------------- */
function bitc_print_settings_tab_content($fields)
{
	$tabs = bitc_get_settings_tabs();
	$content = bitc_get_settings_meta();

	for ($i = 0; $i < count($tabs); $i++) {
		$tab = $tabs[$i]["slug"];

		$hasContent = false;
		if (isset($content[$tab])) {
			$hasContent = true;
			$tab = $content[$tab];
		}

		$classes = "";
		if ($i == 0) {
			$classes = "tab_content_active";
		}
		echo '<div id="tab_' . $tabs[$i]["slug"] . '" class="tab_content ' . $classes . '"><h2 class="tab_heading">' . $tabs[$i]["title"] . '</h2>';
		if ($hasContent) {
			bitc_print_tab_fields($tab, $tabs[$i]["slug"], $fields);
		}
		echo '</div>';
	}
}

/* Create quiz tab array
------------------------------------------------------- */
function bitc_get_quiz_tabs()
{
	global $tabs;
	$tabs = array();
	$tab = array();
	$tab["slug"] = "results";
	$tab["title"] = "Results";
	array_push($tabs, $tab);
	$tab = array();
	$tab["slug"] = "marking";
	$tab["title"] = "Marking";
	array_push($tabs, $tab);
	$tab = array();
	$tab["slug"] = "timer";
	$tab["title"] = "Timer";
	array_push($tabs, $tab);
	$tab = array();
	$tab["slug"] = "advanced";
	$tab["title"] = "Advanced";
	array_push($tabs, $tab);

	$tabs = apply_filters("bitc_add_quiz_tab", $tabs);

	// sanitize
	for ($i = 0; $i < count($tabs); $i++) {
		$tabs[$i]["slug"] = sanitize_title($tabs[$i]["slug"]);
		$tabs[$i]["title"] = sanitize_text_field($tabs[$i]["title"]);
	}
	return $tabs;
}

/* Print quiz tabs
------------------------------------------------------- */
function bitc_print_quiz_tabs()
{
	$tabs = bitc_get_quiz_tabs();
	for ($i = 0; $i < count($tabs); $i++) {
		$classes = "";
		if ($i == 0) {
			$classes = "tab_nav_item_active";
		}
		echo '<div role = "button" class="tab_nav_item ' . $classes . '" data-id="' . $tabs[$i]["slug"] . '">' . $tabs[$i]["title"] . '</div>';
	}
}

/* Get and print tab content
------------------------------------------------------- */
function bitc_print_quiz_tab_content($fields)
{
	$tabs = bitc_get_quiz_tabs();
	$content = bitc_get_quiz_meta();

	for ($i = 0; $i < count($tabs); $i++) {
		$tab = $tabs[$i]["slug"];

		$hasContent = false;
		if (isset($content[$tab])) {
			$hasContent = true;
			$tab = $content[$tab];
		}

		$classes = "";
		if ($i == 0) {
			$classes = "tab_content_active";
		}
		echo '<div id="tab_' . $tabs[$i]["slug"] . '" class="tab_content ' . $classes . '"><h2 class="tab_heading">' . $tabs[$i]["title"] . '</h2>';
		if ($hasContent) {
			bitc_print_tab_fields($tab, $tabs[$i]["slug"], $fields);
		}
		echo '</div>';
	}
}


/* Create question tab array
------------------------------------------------------- */
function bitc_get_question_tabs()
{
	global $tabs;
	$tabs = array();
	$tab = array();
	$tab["slug"] = "main";
	$tab["title"] = "Main";
	array_push($tabs, $tab);
	$tab = array();
	$tab["slug"] = "extra";
	$tab["title"] = "Extra";
	array_push($tabs, $tab);
	$tab = array();
	$tab["slug"] = "quizzes";
	$tab["title"] = "Quizzes";
	array_push($tabs, $tab);

	$tabs = apply_filters("bitc_add_question_tab", $tabs);

	// sanitize
	for ($i = 0; $i < count($tabs); $i++) {
		$tabs[$i]["slug"] = sanitize_title($tabs[$i]["slug"]);
		$tabs[$i]["title"] = sanitize_text_field($tabs[$i]["title"]);
	}
	return $tabs;
}

/* Print question tabs
------------------------------------------------------- */
function bitc_print_question_tabs()
{
	$tabs = bitc_get_question_tabs();
	for ($i = 0; $i < count($tabs); $i++) {
		$classes = "";
		if ($i == 0) {
			$classes = "tab_nav_item_active";
		}
		echo '<div role = "button" class="tab_nav_item ' . $classes . '" data-id="' . $tabs[$i]["slug"] . '">' . $tabs[$i]["title"] . '</div>';
	}
}

/* Get and print tab content
------------------------------------------------------- */
function bitc_print_question_tab_content($fields)
{
	$tabs = bitc_get_question_tabs();
	$content = bitc_get_question_meta();

	for ($i = 0; $i < count($tabs); $i++) {
		$tab = $tabs[$i]["slug"];

		$hasContent = false;
		if (isset($content[$tab])) {
			$hasContent = true;
			$tab = $content[$tab];
		}

		$classes = "";
		if ($i == 0) {
			$classes = "tab_content_active";
		}
		echo '<div id="tab_' . $tabs[$i]["slug"] . '" class="tab_content ' . $classes . '"><h2 class="tab_heading">' . $tabs[$i]["title"] . '</h2>';
		if ($hasContent) {
			bitc_print_tab_fields($tab, $tabs[$i]["slug"], $fields);
		}
		echo '</div>';
	}
}

/* Get settings meta
------------------------------------------------------- */
function bitc_get_settings_meta()
{
	// this is just an easier way to manage and edit in the future
	$default = '{
	"general": [
		{
			"type": "content",
			"value": "<h4 style=\"margin-bottom: 1rem\">Social Share</h4>",
			"name": "general_social_share_heading"
		},
		{
			"type": "col-1-1",
			"children": [
				{
					"type": "text",
					"name": "hd_qu_fb",
					"label": "Facebook APP ID",
					"tooltip": "This is needed to allow Facebook to share dynamic content - the results of the quiz. <br/> If this is not used then Facebook will share the page without the results.",
					"content": "<p>leave blank to use default sharing</p><p>UPDATE: It looks like Facebook completely removed the ability to share custom text, so the APP ID doesn\'t add much value anymore.</p>"
				},
				{
					"type": "text",
					"name": "hd_qu_tw",
					"label": "Twitter Handle",
					"tooltip": "This is used if you have sharing results enabled. The sent tweet will contain a mention to your account for extra exposure. ",
					"content": "<p>do NOT include the @ symbol</p>"
				}
			]
		},
		{
			"type": "text",
			"name": "hd_qu_share_text",
			"label": "Share Text",
			"tooltip": "This is the text that will appear when a user shares the quiz on social media ",
			"content": "<p>You can use custom variables here to add dynamic content. <code>%name%</code> will replace the quiz name, and <code>%score%</code> will replace the user\'s score as a fraction.</p>"
		},		
		{
			"type": "content",
			"value": "<h4 style=\"margin-bottom: 1rem\">Other Options</h4>",
			"name": "general_social_share_heading"
		},
		{
			"type": "col-1-1",
			"children": [
				{
					"type": "checkbox",
					"name": "hd_qu_authors",
					"label": "Allow Authors Access To Create Quizzes",
					"tooltip": "By default, only Editors or Admins can add or edit questions. Enabling this will allow Authors to create quizzes as well.",
					"options": [
						{
							"label": "",
							"value": "yes"
						}
					]
				},
				{
					"type": "checkbox",
					"name": "hd_qu_percent",
					"label": "Enable Percent Results",
					"tooltip": "By default, Bitcoin Mastermind will only show the score as a fraction (example: 9/10). Enabling this will also show the score as a percentage (example: 90%)",
					"options": [
						{
							"label": "",
							"value": "yes"
						}
					]
				}
			]
		},
		{
			"type": "col-1-1",
			"children": [				
				{
					"type": "checkbox",
					"name": "hd_qu_legacy_scroll",
					"label": "Enable Legacy Scroll",
					"tooltip": "Bitcoin Mastermind <code>1.8.2</code> introduced a new scroll function that <em>should</em> be far faster and more compatible. If you are having issues with the auto scroll functionalty, enable this to use the older version",
					"options": [
						{
							"label": "",
							"value": "yes"
						}
					]
				},
				{
					"type": "checkbox",
					"name": "hd_qu_heart",
					"label": "I ❤️ Bitcoin Mastermind",
					"tooltip": "If enabled, each quiz will contain a subtle link letting users know that the quizzes are powered by Bitcoin Mastermind. The link is unobtrusive and is only visible once a user has completed a quiz.",
					"options": [
						{
							"label": "",
							"value": "yes"
						}
					]
				}
			]
		},
		{
			"type": "encode",
			"name": "hd_qu_adcode",
			"label": "Adset Code",
			"placeholder": "You can paste your ad code here",
			"tooltip": "Also note that if you are using WP Pagination, you will need to set it to a number greater than 5 or your adcode will never display"
		},
		{
			"type": "content",
			"name": "adset_content",
			"value": "<p><strong>DO NOT USE</strong> if you are already displaying auto ads on your site. Bitcoin Mastermind will place the ad <em>code</em> after every 5th question, but how the ad appears is entirely based on the ads and ad networks themselves.</p>"
		}
	],
	"translate": [
		{
			"type": "col-1-1",
			"children": [
				{
					"type": "text",
					"name": "hd_qu_finish",
					"label": "Rename \"Finish\" Button"
				},
				{
					"type": "text",
					"name": "hd_qu_next",
					"label": "Rename \"Next\" Button"
				},
				{
					"type": "text",
					"name": "hd_qu_results",
					"label": "Rename \"Results\" text"
				},
				{
					"type": "text",
					"name": "hd_qu_start",
					"label": "Rename \"QUIZ START\" text",
					"tooltip": "Used if you are using a timer feature, or for direct links to the quiz on category/search pages"
				},
				{
					"type": "text",
					"name": "hd_qu_text_based_answers",
					"label": "Rename \"enter answer here\" text",
					"content": "<p>This text appears as a placeholder for the \"Text Based Answers\" question type"
				},
				{
					"type": "text",
					"name": "hd_qu_select_all_apply",
					"label": "Rename \"Select all that apply:\" text",
					"content": "<p>This text appears as a placeholder for the \"Select All That Apply\" question type"
				}
			]
		}
	]
}';
	$default = json_decode($default, true);

	$fields = apply_filters("bitc_add_settings_meta", $default);

	$fields = bitc_create_all_fields($fields); // clean and santize
	return $fields;
}

/* Get quiz meta
------------------------------------------------------- */
function bitc_get_quiz_meta()
{
	// this is just an easier way to manage and edit in the future
	$default = '{
	"results": [
		{
			"type": "col-1-1",
			"children": [
				{
					"type": "integer",
					"name": "quiz_pass_percentage",
					"label": "Quiz Pass Percentage",
					"placeholder": "50",
					"suffix": "%",
					"tooltip": "The percentage of the questions does a user need to get correct in order to pass the quiz.",
					"default": 70,
					"required": true,
					"options": [
						{ "name": "step", "value": 1 },
						{ "name": "min", "value": 0 },
						{ "name": "max", "value": 100 }
					]
				},
				{
					"type": "checkbox",
					"name": "hide_questions",
					"label": "Hide all questions",
					"content": "<p>This will automatically hide the questions once a quiz has been completed so that only the results are shown.</p>",
					"value": "",
					"options": [{ "label": "", "value": "yes", "default": "false" }]
				}
			]
		},

		{
			"type": "col-1-1",
			"children": [
				{
					"type": "editor",
					"name": "quiz_pass_text",
					"label": "Quiz Pass text",
					"tooltip": "This content will appear if the user passes the quiz. Feel free to add images,video,links etc.",
					"value": ""
				},
				{
					"type": "editor",
					"name": "quiz_fail_text",
					"label": "Quiz Fail text",
					"tooltip": "This content will appear if the user passes the quiz. Feel free to add images,video,links etc.",
					"value": ""
				}
			]
		}
	],
	"marking": [
		{
			"type": "col-1-1",
			"children": [
				{
					"type": "checkbox",
					"name": "show_results",
					"label": "Highlight correct / incorrect selected answers on completion",
					"content": "<p>This will show the user which questions they got right,and which they got wrong.</p>",
					"value": "",
					"options": [{ "label": "", "value": "yes", "default": "true" }]
				},
				{
					"type": "checkbox",
					"name": "show_results_correct",
					"label": "Show the correct answers on completion",
					"content": "<p>This feature goes the extra step and shows what the correct answer was in the case that the user selected the wrong one.</p>",
					"value": "",
					"options": [{ "label": "", "value": "yes" }]
				},
				{
					"type": "checkbox",
					"name": "show_results_now",
					"label": "Immediately mark answer as correct or incorrect",
					"content": "<p>Enabling this will show if the answer was right or wrong as soon as an answer has been selected.</p>",
					"value": "",
					"options": [{ "label": "", "value": "yes" }]
				},
				{
					"type": "checkbox",
					"name": "stop_answer_reselect",
					"label": "Stop users from changing their answers",
					"content": "<p>Enabling this will stop users from being able to change their answer once one has been selected.</p>",
					"value": "",
					"options": [{ "label": "", "value": "yes" }]
				}
			]
		},
		{
			"type": "checkbox",
			"name": "show_extra_text",
			"label": "Always Show Incorrect Answer Text",
			"content": "<p>Each individual question can have accompanying text that will show if the user selects the wrong answer. Enabling this feature will force this text to show even if the selected answer was correct.</p>",
			"value": "",
			"options": [{ "label": "", "value": "yes" }]
		}
	],
	"timer": [
		{
			"type": "content",
			"name": "timer_content_descriptor",
			"value": "<p>If the timer is enabled, the quiz will be hidden behind a \"START QUIZ\" button. You can rename this button from the Bitcoin Mastermind -> About / Options page</p>"
		},
		{
			"type": "col-1-1",
			"children": [
				{
					"type": "integer",
					"name": "quiz_timer",
					"label": "Timer / Countdown",
					"placeholder": "60",
					"suffix": "seconds",
					"content": "<p>Enter how many seconds total. So 3 minutes would be 180. Please note that the timer will NOT work if the WP Pagination feature is being used.</p>",
					"options": [
						{ "name": "step", "value": 1 },
						{ "name": "min", "value": 0 },
						{ "name": "max", "value": 9999999 }
					]
				},
				{
					"type": "checkbox",
					"name": "quiz_timer_question",
					"label": "Per question",
					"placeholder": "0",
					"content": "<p>Enable this if you want the timer to be per question instead of for the entire quiz. <br/><small>NOTE: Per question timer doesn\'t work very well with the \"Select All That Apply\" question type.</small></p>",
					"options": [{ "label": "", "value": "yes" }]
				}
			]
		}
	],
	"advanced": [
		{
			"type": "col-1-1",
			"children": [
				{
					"type": "checkbox",
					"name": "share_results",
					"label": "Share Quiz Results",
					"tooltip": "This option shows or hides the Facebook and Twitter share buttons that appears when a user completes the quiz.",
					"value": "",
					"options": [{ "label": "", "value": "yes", "default": "true" }]
				},
				{
					"type": "radio",
					"name": "results_position",
					"label": "Show Results Above or Below Quiz",
					"tooltip": "The site will automatically scroll to the position of the results.",
					"value": "",
					"options": [
						{ "label": "Above quiz", "value": "above", "default": "true" },
						{ "label": "Below quiz", "value": "below" }
					]
				}
			]
		},
		{
			"type": "col-1-1",
			"children": [
				{
					"type": "checkbox",
					"name": "randomize_questions",
					"label": "Randomize <u>Question</u> Order",
					"content": "<p>Please note that randomizing the questions is NOT possible if the below WP Pagination feature is being used.</p><p><small>and also not a good idea to use this if you are using the \"questions as title\" option for any questions attached to this quiz</small></p>",
					"value": "",
					"options": [{ "label": "", "value": "yes" }]
				},
				{
					"type": "checkbox",
					"name": "randomize_answers",
					"label": "Randomize <u>Answer</u> Order",
					"content": "<p>This feature will randomize the order that each answer is displayed and <em>is</em> compatible with WP Pagination.</p>",
					"value": "",
					"options": [{ "label": "", "value": "yes" }]
				},
				{
					"type": "integer",
					"name": "pool_of_questions",
					"label": "Use Pool of Questions",
					"placeholder": "leave blank to disable",
					"suffix": "questions",
					"tooltip": "Set to 0 or leave blank to disable",
					"content": "<p>If you want each quiz to randomly grab a number of questions from the quiz,then enter that amount here. So,for example,you might have 100 questions attached to this quiz,but entering 20 here will make the quiz randomly grab 20 of the questions on each load.</p>",
					"default": "",
					"options": [
						{ "name": "step", "value": 1 },
						{ "name": "min", "value": 0 },
						{ "name": "max", "value": 9999999 }
					]
				},
				{
					"type": "integer",
					"name": "wp_paginate",
					"label": "WP Pagination",
					"placeholder": "leave blank to disable",
					"suffix": "per page",
					"tooltip": "WARNING:It is generally not recommended using this feature unless you have a specific use case for it.",
					"content": "<p>WP Paginate will force this number of questions per page,and force new page loads for each new question group. The only benefit of this is for additional ad views. The downside is reduced compatibility of features. It is recommended to use the \"paginate\" option on each question instead.</p>",
					"default": "",
					"options": [
						{ "name": "step", "value": 1 },
						{ "name": "min", "value": 0 },
						{ "name": "max", "value": 9999999 }
					]
				}
			]
		},
		{ "type": "action", "name": "quiz_name", "function": "bitc_quiz_rename", "label": "Rename Quiz", "value": "" }
	]
}
';
	$default = json_decode($default, true);

	$fields = apply_filters("bitc_add_quiz_meta", $default);

	$fields = bitc_create_all_fields($fields); // clean and santize
	return $fields;
}

/* Get question meta
------------------------------------------------------- */
function bitc_get_question_meta()
{
	$default = '{
	"main": [
		{
			"type": "select",
			"name": "question_type",
			"label": "Question Type",
			"required": true,
			"options": [
				{
					"label": "Multiple Choice: Text",
					"value": "multiple_choice_text",
					"default": "true"
				},
				{
					"label": "Multiple Choice: Image",
					"value": "multiple_choice_image"
				},
				{
					"label": "Select All That Apply: Text",
					"value": "select_all_apply_text"
				},
				{
					"label": "Select All That Apply: Image",
					"value": "select_all_apply_image"
				},				
				{
					"label": "Text Based Answers",
					"value": "text_based"
				},
				{
					"label": "Use Question as Title",
					"value": "title"
				}
			]
		},
		{
			"type": "content",
			"value": "",
			"name": "question_type_tip"
		},
		{
			"type": "action",
			"function": "bitc_print_answers_admin",
			"name": "answers"
		}
	],
	"extra": [
		{
			"type": "col-1-1",
			"children": [
				{
					"type": "checkbox",
					"name": "paginate",
					"label": "Paginate",
					"content": "<p>Start a new page with this question <br/> <small>user will need to select \"next\" to see this question or ones below it</small></p>",
					"options": [
						{
							"label": "",
							"value": "yes"
						}
					]
				},
				{
					"type": "text",
					"name": "tooltip",
					"label": "Tooltip Text",
					"tooltip": "This popup is an example of what a tooltip is."
				}
			]
		},
		{
			"type": "image",
			"name": "featured_image",
			"label": "Question Featured Image",
			"options": {
				"title": "Set Question Featured Image",
				"button": "SET FEATURED IMAGE",
				"multiple": false
			}
		},
		{
			"type": "editor",
			"name": "extra_text",
			"label": "Text that appears if the user got the question wrong",
			"tooltip": "You can make it so this text always appears by editing the quiz settings"
		}
	],
	"quizzes": [
		{
			"type": "action",
			"function": "bitc_print_quizzes_admin",
			"name": "quizzes"
		}
	]
}';
	$default = json_decode($default, true);

	$fields = apply_filters("bitc_add_question_meta", $default);

	$fields = bitc_create_all_fields($fields); // clean and santize
	return $fields;
}

/* Get and clean all fields before printing
------------------------------------------------------- */
function bitc_create_all_fields($fields, $tabs = true)
{
	// sanitize all fields
	if ($tabs) {
		foreach ($fields as $key => $v) {
			if (count($v) > 0) {
				for ($i = 0; $i < count($v); $i++) {
					$fields[$key][$i] = bitc_field_array_sanitize($fields[$key][$i], $v[$i]);
					if (isset($v[$i]["children"])) {
						for ($x = 0; $x < count($v[$i]["children"]); $x++) {
							$fields[$key][$i]["children"][$x] = bitc_field_array_sanitize($fields[$key][$i]["children"][$x], $v[$i]["children"][$x]);
						}
					}
				}
			}
		}
	} else {
		for ($i = 0; $i < count($fields); $i++) {
			$fields[$i] = bitc_field_array_sanitize($fields[$i], $fields[$i]);
			if (isset($fields[$i]["children"])) {
				for ($x = 0; $x < count($fields[$i]["children"]); $x++) {
					$fields[$i]["children"][$x] = bitc_field_array_sanitize($fields[$i]["children"][$x], $fields[$i]["children"][$x]);
				}
			}
		}
	}
	return $fields;
}

function bitc_field_array_sanitize($field, $v)
{
	if (isset($v["label"])) {
		$field["label"] = sanitize_text_field($v["label"]);
	}

	if (isset($v["name"])) {
		$field["name"] = sanitize_text_field($v["name"]);
	}

	if (isset($v["type"])) {
		$field["type"] = sanitize_text_field($v["type"]);
	}

	if (isset($v["required"])) {
		$field["required"] = intval($v["required"]);
	}

	if (isset($v["placeholder"])) {
		$field["placeholder"] = sanitize_text_field($v["placeholder"]);
	}

	if (isset($v["tooltop"])) {
		$field["tooltop"] = wp_kses_post($v["tooltop"]);
	}

	if (isset($v["content"])) {
		$field["content"] = wp_kses_post($v["content"]);
	}

	if (isset($v["[prefix]"])) {
		$field["[prefix]"] = sanitize_text_field($v["[prefix]"]);
	}

	if (isset($v["suffix"])) {
		$field["suffix"] = sanitize_text_field($v["suffix"]);
	}

	if (isset($v["default"])) {
		$field["default"] = sanitize_text_field($v["default"]);
	}

	if (isset($v["options"])) {
		if (!is_array($v["options"])) {
			$field["options"] = array();
		} else {
			for ($i = 0; $i < count($v["options"]); $i++) {
				if (isset(($v["options"][$i]["label"]))) {
					$field["options"][$i]["label"] = sanitize_text_field($v["options"][$i]["label"]);
				}
				if (isset(($v["options"][$i]["value"]))) {
					$field["options"][$i]["value"] = sanitize_text_field($v["options"][$i]["value"]);
				}
				if (isset(($v["options"][$i]["default"]))) {
					$field["options"][$i]["default"] = sanitize_text_field($v["options"][$i]["default"]);
				}
			}
		}
	}
	return $field;
}

/* Get field value
------------------------------------------------------- */
function bitc_getValue($tab, $fields)
{
	$value = "";
	if (isset($fields[$tab["name"]])) {
		$value = $fields[$tab["name"]]["value"];
		if (!is_array($value)) {
			if ($tab["type"] != "editor") {
				$value = htmlspecialchars($value);
			}
		}
	}
	return $value;
}

/* Get field placeholder
------------------------------------------------------- */
function bitc_getPlaceholder($tab, $fields)
{
	$placeholder = "";
	if (isset($tab["placeholder"]) && $tab["placeholder"] != "") {
		$placeholder = $tab["placeholder"];
	}
	return $placeholder;
}

/* Get field required status
------------------------------------------------------- */
function bitc_getRequired($tab, $fields)
{
	$required = false;
	if (isset($tab["required"]) && $tab["required"] == true) {
		$required = true;
	}
	return $required;
}

/* Print field required icon
------------------------------------------------------- */
function bitc_print_tab_requiredIcon()
{
	echo '<span class="bitc_required_icon">*</span>';
}

/* Print field tooltip
------------------------------------------------------- */
function bitc_print_fields_tooltip($tooltip)
{
?>
	<span class="bitc_tooltip">
		?
		<span class="bitc_tooltip_content">
			<span><?php echo $tooltip; ?></span>
		</span>
	</span>
<?php
}

function bitc_printField_action($tab, $tab_slug, $fields)
{
	if (!isset($tab["function"])) {
		return;
	}
	if (function_exists($tab["function"])) {
		$tab["function"]($tab, $tab_slug, $fields);
	}
}

/* Print field rows
------------------------------------------------------- */
function bitc_printField_row($tab, $tab_slug, $fields)
{
	echo '<div class = "bitc_row">';
	echo bitc_print_tab_fields($tab["children"], $tab_slug, $fields);
	echo '</div>';
}

function bitc_printField_col11($tab, $tab_slug, $fields)
{
	echo '<div class = "bitc_row bitc_col-1-1">';
	echo bitc_print_tab_fields($tab["children"], $tab_slug, $fields);
	echo '</div>';
}

function bitc_printField_col111($tab, $tab_slug, $fields)
{
	echo '<div class = "bitc_row bitc_col-1-1-1">';
	echo bitc_print_tab_fields($tab["children"], $tab_slug, $fields);
	echo '</div>';
}

function bitc_printField_content($tab, $tab_slug)
{
	echo '<div id = "' . $tab["name"] . '" class = "bitc_row_content">';
	echo $tab["value"];
	echo '</div>';
}

function bitc_printField_hr($tab, $tab_slug)
{
	echo '<hr class = "bitc_hr" />';
}

function bitc_printField_heading($tab, $tab_slug)
{
	echo '<h2>' . $tab["label"] . '</h2>';
}

function bitc_printField_heading2($tab, $tab_slug)
{
	echo '<h2>' . $tab["label"] . '</h2>';
}

function bitc_printField_heading3($tab, $tab_slug)
{
	echo '<h3>' . $tab["label"] . '</h3>';
}

function bitc_print_answers_admin($tab, $tab_slug, $fields)
{
	$answers = $fields["answers"]["value"];
	$question_type = $fields["question_type"]["value"];
	$image_as_answers = "bitc_hide";
	$text_based_answers = "";
	if ($question_type === "multiple_choice_image") {
		$image_as_answers = "";
	} elseif ($question_type === "text_based") {
		$text_based_answers = "bitc_hide";
	}

	$answer_selected = array();
	if (isset($fields["selected"]["value"])) {
		$answer_selected = $fields["selected"]["value"];
	}
?>
	<span class="hderp_input" id="selected" data-type="correct"></span>

	<table class="bitc_table hderp_input" data-tab="main" id="answers" data-type="answers" style="<?php if ($fields["question_type"]["value"] === "title") {
																										echo 'display:none;';
																									} ?>">
		<thead>
			<tr>
				<th>#</th>
				<th>Answers</th>
				<th width="225" class="bitc_answer_as_image <?php echo $image_as_answers; ?>">Featured Image</th>
				<th width="30" class="bitc_answer_selected <?php echo $text_based_answers; ?>">Correct</th>
			</tr>
		</thead>
		<tbody>
			<?php
	
			$bitc_max_answers = 10;
			if(defined('bitc_MAX_ANSWERS')){
				$bitc_max_answers = intval(bitc_MAX_ANSWERS);
			}	
	
			for ($x = 1; $x < $bitc_max_answers + 1; $x++) {
				$v = "";
				if (isset($answers[$x - 1])) {
					$v = $answers[$x - 1]["answer"];
				}
				$i = $answers[$x - 1]["image"]; ?>

				<tr>
					<td width="1"><?php echo $x; ?></td>
					<td>
						<input class="bitc_input bitc_input_answer" placeholder="enter answer..." data-type="text" type="text" value="<?php echo htmlspecialchars($v); ?>">
					</td>
					<td class="textCenter bitc_answer_as_image <?php echo $image_as_answers; ?>">
						<div title="update image" id="bitc_answer_image_<?php echo $x; ?>" data-answer="<?php echo $x; ?>" data-options="%7B%22title%22%3A%22Set%20Answer%20Image%22%2C%22button%22%3A%22SET%20IMAGE%22%2C%22multiple%22%3Afalse%7D" data-value="<?php echo intval($i); ?>" data-tab="extra" data-type="image" class="bitc_input input_image">
							<?php
							if ($i == "") {
								echo 'set image';
							} else {
								$image = wp_get_attachment_image($i, "medium", "", array("class" => "image_field_image"));
								if ($image != null) {
									echo $image;
								} else {
									echo '<small>image was deleted</small>';
								}
							} ?>
						</div>
						<?php if ($i != "") {
							echo '<p class = "remove_image_wrapper" style = "text-align:center"><span class = "remove_image" data-id = "bitc_answer_image_' . $x . '">remove image</span></p>';
						} ?>
					</td>
					<td class="bitc_answer_selected <?php echo $text_based_answers; ?>">
						<div class="bitc_checkbox">
							<?php
							$selected = "";
							if (in_array($x, $answer_selected)) {
								$selected = "checked";
							} ?>
							<input data-tab="main" type="checkbox" value="yes" data-type="checkbox" class="bitc_radio_input" data-id="selected" id="bitc_correct_<?php echo $x; ?>" <?php echo $selected; ?> />
							<label class="bitc_toggle" for="bitc_correct_<?php echo $x; ?>"></label>
						</div>
					</td>
				</tr>
			<?php
			} ?>
		</tbody>
	</table>
<?php
}

function bitc_print_quizzes_admin($tab, $tab_slug, $fields)
{
	$attached_terms = get_the_terms($fields["question_id"]["value"], "quiz");

	$attached = array();
	if (isset($attached_terms) && $attached_terms != "") {
		for ($i = 0; $i < count($attached_terms); $i++) {
			array_push($attached, $attached_terms[$i]->term_id);
		}
	}
?>
	<p>
		You can add this question to more than one quiz
	</p>
	<div class="bitc_table hderp_input" data-tab="quizzes" id="quizzes" data-type="categories">

		<?php
		$terms = get_terms(array(
			'taxonomy' => 'quiz',
			'hide_empty' => false,
			'parent' => 0,
		));

		// if new question, set the quiz
		if ($fields["question_id"]["value"] == "" || $fields["question_id"]["value"] == null) {
			array_push($attached, intval($_POST['quiz']));
		}

		for ($i = 0; $i < count($terms); $i++) {
			$selected = "";
			if (in_array($terms[$i]->term_id, $attached)) {
				$selected = "checked";
			} ?>
			<div class="bitc_category">
				<label class="bitc_full_label" for="cat_<?php echo $terms[$i]->term_id; ?>">
					<div class="bitc_checkbox">
						<input type="checkbox" class="bitc_category_input" data-id="<?php echo $terms[$i]->term_id; ?>" id="cat_<?php echo $terms[$i]->term_id; ?>" value="<?php echo $terms[$i]->term_id; ?>" name="cat_<?php echo $terms[$i]->term_id; ?>" <?php echo $selected; ?> />
						<span class="bitc_toggle" for="cat_<?php echo $terms[$i]->term_id; ?>"></span>
					</div>
					<?php echo $terms[$i]->name; ?>
				</label>
			</div>
		<?php
		}
		echo '</div>';
	}

	function bitc_quiz_rename($tab)
	{

		$quizID = 0;
		if (isset($_POST['quiz'])) {
			$quizID = intval($_POST['quiz']);
		}
		$quiz = get_term($quizID, "quiz");
		$value = $quiz->name;;

		?>

		<div class="bitc_input_item">
			<label class="bitc_input_label" for="<?php echo $tab["name"]; ?>">
				<span class="bitc_required_icon">*</span>Rename Quiz
			</label>
			<input data-tab="advanced" data-type="quiz_name" data-required="required" type="text" class="bitc_input hderp_input" id="<?php echo $tab["name"]; ?>" value="<?php echo $value; ?>" placeholder="">
		</div>


	<?php
	}


	/* decrypt and HDC encoded string
------------------------------------------------------- */
	function bitc_decode($ciphertext = "")
	{
		if ($ciphertext === "") {
			return "";
		}
		$c = base64_decode($ciphertext);
		$ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
		$iv = substr($c, 0, $ivlen);
		$hmac = substr($c, $ivlen, $sha2len = 32);
		$ciphertext_raw = substr($c, $ivlen + $sha2len);
		$original_plaintext = stripslashes(openssl_decrypt($ciphertext_raw, $cipher, wp_salt(), $options = OPENSSL_RAW_DATA, $iv));
		$calcmac = hash_hmac('sha256', $ciphertext_raw, wp_salt(), $as_binary = true);
		if (@hash_equals($hmac, $calcmac)) {
			return $original_plaintext;
		} else {
			return "";
		}
	}

	function bitc_encode($text = "")
	{
		if ($text === "") {
			return "";
		}
		$k = wp_salt();
		$ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
		$iv = openssl_random_pseudo_bytes($ivlen);
		$ciphertext_raw = openssl_encrypt($text, "AES-128-CBC", $k, $options = OPENSSL_RAW_DATA, $iv);
		$hmac = hash_hmac('sha256', $ciphertext_raw, $k, $as_binary = true);
		$ciphertext = base64_encode($iv . $hmac . $ciphertext_raw);
		return $ciphertext;
	}
