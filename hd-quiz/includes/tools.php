<?php
/*
    HDQuiz Tools Page
*/


if (!current_user_can('edit_others_pages')) {
    die();
}

wp_enqueue_style(
    'hdq_admin_style',
    plugin_dir_url(__FILE__) . 'css/hdq_admin.css?v='.HDQ_PLUGIN_VERSION
);
        
wp_enqueue_script(
    'hdq_admin_script',
    plugins_url('/js/hdq_admin.js?v='.HDQ_PLUGIN_VERSION, __FILE__),
    array('jquery', 'jquery-ui-draggable'),
    HDQ_PLUGIN_VERSION,
    true
);
        
    ?>

<div id="main" style="max-width: 800px; background: #f3f3f3; border: 1px solid #ddd; margin-top: 2rem">
	<div id="header">
		<h1 id="heading_title" style="margin-top:0">
			HD Quiz - Tools
		</h1>
	</div>


	<p>HD Quiz has grown considerably in features and complexity over the years making this page necessary. Most of you
		will never need to use any of the tools on this page, but these tools are still here to help you out when
		needed.</p>

	<div class="hdq_tool_item">
		<div>
			<h4>Quiz and Question Data Updater</h4>
			<p>
				<small>This tool is only needed for users upgrading from HD Quiz 1.7 or lower.<br/><strong>DO NOT USE if you are upgrading from a version higher than 1.7</strong></small>
			</p>

			<div class="hdq_accordion">
				<h3>What does this tool do?</h3>
				<div style="display: none;">
					<p>
						Since HD Quiz 1.8, quiz and question data is saved and retrieved in a completely new way. For
						those of you with less than 200 questions on your site, the data migration and upgrade should
						have happened automatically when you upgraded HD Quiz.
					</p>
					<p>
						However, if you have more than 200 questions, or the automatic update did not work for whatever
						reason, then you can run the manual upgrade here.
					</p>
				</div>
			</div>

			<p>
				<strong>NOTE:</strong> If the data migration does not work for any reason, <strong>do no
					worry</strong>. None of your old data has been deleted or modified in any way. In fact, you can
				easily replace this version of HD Quiz by downloading the <a
					href="https://wordpress.org/plugins/hd-quiz/advanced/" target="_blank">previous version of HD Quiz
					here</a> <span class="hdq_tooltip">
					?
					<span class="hdq_tooltip_content">
						<span>The download link is at the very bottom of the page</span>
					</span>
				</span>.
			</p>
		</div>
		<a href = "<?php echo get_admin_url(null, "?page=hdq_tools_data_upgrade"); ?>" class="hdq_button" role="button" title="init tool">
			RUN TOOL
		</a>
	</div>


	<div class="hdq_tool_item">
		<div>
			<h4>Question CSV Uploader</h4>
			<div class="hdq_accordion">
				<h3>What does this tool do?</h3>
				<div style="display: none;">
					<p>
						If you have a CSV (comma seperated values) file of questions, this tool can help you bulk import all of your questions. Please note that this tool is limited in scope, so you will not be able to automatically set all features (such as images), but can still be used to make adding questions in bulk a <em>lot</em> faster.
					</p>
					<p>
						Instructions on how to format the CSV file are provided on the CSV Uploader tool page.
					</p>
					<p>
						Please also know that if you need to export and import questions, then you should use WordPress' native import or export tool for that. <em>This</em> tool is used for bulk adding brand new questions.
					</p>
				</div>
			</div>

			<p>
				<strong>NOTE:</strong> It is YOUR responsibility to ensure that your CSV is properly formatted. Due to the almost infinte ways that a CSV can be inproperly formatted for HD Quiz, I can only offer a very limited support to help with any issues arising from this feature.
			</p>
		</div>
		<a href = "<?php echo get_admin_url(null, "?page=hdq_tools_csv_importer"); ?>" class="hdq_button" role="button" title="init tool">
			RUN TOOL
		</a>
	</div>
</div>