<?php
/*
    HDQuiz Tools Page
*/


if (!current_user_can('edit_others_pages')) {
    die();
}

wp_enqueue_style(
    'bitc_admin_style',
    plugin_dir_url(__FILE__) . 'css/bitc_admin.css?v='.bitc_PLUGIN_VERSION
);
        
wp_enqueue_script(
    'bitc_admin_script',
    plugins_url('/js/bitc_admin.js?v='.bitc_PLUGIN_VERSION, __FILE__),
    array('jquery', 'jquery-ui-draggable'),
    bitc_PLUGIN_VERSION,
    true
);
        
    ?>

<div id="main" style="max-width: 800px; background: #f3f3f3; border: 1px solid #ddd; margin-top: 2rem">
	<div id="header">
		<h1 id="heading_title" style="margin-top:0">
			Bitcoin Mastermind - Tools
		</h1>
	</div>


	<p>This is where you will find handy tools to make working with Bitcoin Mastermind easier.  
		Check out our CSV question and answer importer.  
		For large amounts of questions and answers this can save you loads of time.</p>

	<div class="bitc_tool_item">
		<div>
			<h4>Question CSV Uploader</h4>
			<div class="bitc_accordion">
				<h3>What does this tool do?</h3>
				<div style="display: none;">
					<p>
						If you have a CSV (comma seperated values) file of questions, this tool can help you bulk import all of your questions. Please note that this tool is limited in scope, so you will not be able to automatically set all features (such as images), but can still be used to make adding questions in bulk a <em>lot</em> faster.
					</p>
					<p>
						Instructions on how to format the CSV file, including an example csv, are provided on the CSV Uploader tool page when you click "RUN TOOL".
					</p>
					<p>
						Please also know that if you need to export and import questions, then you should use WordPress' native import or export tool for that. <em>This</em> tool is used for bulk adding brand new questions.
					</p>
				</div>
			</div>

			<p>
				<strong>NOTE:</strong> It is YOUR responsibility to ensure that your CSV is properly formatted. Due to the almost infinte ways that a CSV can be inproperly formatted for Bitcoin Mastermind, I can only offer a very limited support to help with any issues arising from this feature.
			</p>
		</div>
		<a href = "<?php echo get_admin_url(null, "?page=bitc_tools_csv_importer"); ?>" class="bitc_button" role="button" title="init tool">
			RUN TOOL
		</a>
	</div>
</div>