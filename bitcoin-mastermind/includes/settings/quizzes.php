<!-- Quizzes Admin Page -->

<div id="bitc_loading">
	<div id="bitc_loading_inner">
		<!-- not sure if I like this -->
		<label>●</label>
		<label>●</label>
		<label>●</label>
		<label>●</label>
		<label>●</label>
		<label>●</label>
	</div>
</div>

<div id="main">
	<?php
	
if (!defined('bitc_EDIT_AUTHORED')) {
    define('bitc_EDIT_AUTHORED', false);
}
	
	
	$wasUpgrade = sanitize_text_field(get_option("bitc_remove_data_upgrade_notice"));

	if ($wasUpgrade === "yes") {
		$taxonomy = 'quiz';
		$term_args = array(
			'hide_empty' => false,
			'orderby' => 'name',
			'order' => 'ASC',
		);
		$tax_terms = get_terms($taxonomy, $term_args);

		if (!empty($tax_terms) && !is_wp_error($tax_terms)) {
			$quiz_id = $tax_terms[0]->term_id;
			$term_meta = get_option("taxonomy_term_$quiz_id");
			if (isset($term_meta) && $term_meta != null && $term_meta != "") {
	?>
				<div class='notice notice-success is-dismissible'>
					<p>Thank you for upgrading Bitcoin Mastermind to v<?php echo bitc_PLUGIN_VERSION; ?>. <strong style="font-weight: bold">This version requires updating your quiz and question data to be compatible with this version.</strong></p>
					<p>If you have less than 60 questions, this should have already been done automaticaly. However, if you have more than 60 questions, or if you are experiencing any issues with your quiz settings, please run the manual upgrade tool located <a href="<?php echo get_admin_url(null, "?page=bitc_tools_data_upgrade"); ?>">here</a>.</p>
					<p>
						<span class="bitc_marker">If you still have issues <strong style="font-weight: bold">do not panik</strong></span>. None of your quiz data has been removed or changed, and you can always <a href="https://wordpress.org/plugins/hd-quiz/advanced/" target="_blank">revert to a previous version of Bitcoin Mastermind</a>, and of course, you can always <a href="http://harmonicdesign.ca/hd-quiz/" target="_blank">contact me for help and support</a>.
					</p>
					<p style="text-align:center">
						<button class="button" id="bitc_update_data_notice" title="dismiss this notification">
							OK, I understand - hide this notification
						</button>
					</p>
				</div>
	<?php
			}
		}
	}
	?>
	<div id="header">
		<h1 id="heading_title">
			Bitcoin Mastermind - Quizzes
		</h1>
		<div id="header_actions">
			<a title="delete or rename quizzes" href="./edit-tags.php?taxonomy=quiz&post_type=post_type_questionna" class="bitc_button_warning"><span class="dashicons dashicons-trash"></span> DELETE QUIZZES</a>
			<a title="add to multiple quizzes, or delete multiple questions" href="./edit.php?post_type=post_type_questionna" class="bitc_button_warning">BULK MODIFY QUESTIONS</a>
			<a href="https://hdplugins.com/learn/hd-quiz/hd-quiz-documentation/?utm_source=hd-quiz" target="_blank" title="Documentation" class="bitc_button2">HD Quiz Documentation</a>
		</div>
	</div>
	<div id="bitc_quizzes_page" class="content">
		<?php
		// if there are warnings or notices based on installation
		if (function_exists('aioseo')) {
			$aioseo = get_option('aioseo_options', true);
			$aioseo = json_decode($aioseo, true);
			if (isset($aioseo["searchAppearance"]) && isset($aioseo["searchAppearance"]["advanced"]) && isset($aioseo["searchAppearance"]["advanced"]["runShortcodes"])) {
				if ($aioseo["searchAppearance"]["advanced"]["runShortcodes"] === true) {
					echo '<div class = "notice notice-warning"><p><strong>Warning</strong>: you have <code>Run Shortcodes</code> enabled within the All In One SEO plugin. This has been known to cause issues on some sites.</p><p>If you are experiencing issues showing quizzes on your site, please disable this feature by going to <strong>Search Appearance</strong> -> <strong>Advanced</strong> -> <strong>Run Shortcodes</strong> and toggle it to <strong>Off</strong> within the All In One SEO plugin settings.</p></div>';
				}
			}
		}

		?>
		<div id="bitc_quiz_create_wrapper">
			<input type="text" id="bitc_new_quiz_name" class="input_enter" title="add new quiz" placeholder="add new quiz" />
			<p>Add a new quiz, or select a quiz below to add / edit questions, or change quiz settings.</p>
		</div>
		<div id="bitc_list_quizzes">
			<?php
			$taxonomy = 'quiz';
			$term_args = array(
				'hide_empty' => false,
				'orderby' => 'name',
				'order' => 'ASC',
			);
			$tax_terms = get_terms($taxonomy, $term_args);
			$user_id = get_current_user_id();
			if (!empty($tax_terms) && !is_wp_error($tax_terms)) {
				foreach ($tax_terms as $tax_terms) {
					if(bitc_EDIT_AUTHORED === true){
						$author_id = intval(get_term_meta($tax_terms->term_id, "bitc_author_id", true));
						if($user_id !== $author_id && !current_user_can('administrator')){
							continue;
						}
					}					
			?>
					<div role="button" class="bitc_quiz_item bitc_quiz_term" data-name="<?php echo $tax_terms->name; ?>" data-id="<?php echo $tax_terms->term_id; ?>">
						<?php
						if (function_exists('mb_strimwidth')) {
							echo mb_strimwidth($tax_terms->name, 0, 50, "...");
						} else {
							echo $tax_terms->name;
						} ?>
						<code>[HDquiz quiz = "<?php echo $tax_terms->term_id; ?>"]</code>
					</div>
			<?php
				}
			}
			?>
		</div>
	</div>

	<p>If you have any questions or need support, please do not hesitate to contact us at <a href="https://velascommerce.com/" target="_blank" rel="noopener noreferrer">Velas Commerce</a>.</p>

</div>

<div id="bitc_footer_highlight_wrapper">
	<div class="bitc_highlight" id="hd_patreon">
		<div id="hd_patreon_icon">
			<img src="<?php echo plugin_dir_url(__FILE__); ?>../images/hd_patreon.png" alt="Donate" />
		</div>
		<p>
			Bitcoin Mastermind is a 100% free plugin. We do not charge anything for downloading it 
			and we don't do add-ons or premium versions.  
			We make bitcoin to fund this project by sending a tiny amount of each reward sent to our Lightning Address.<br /><br />
			If you think that is annoying, this is 100% FOSS.  You can download the code, fork it and maintain it yourself. <br /><br />
			If you are enjoying Bitcoin Mastermind, we are a fork of an existing WP plugin. 
			You can show your support to them by contributing to their 
			<a href="https://www.patreon.com/harmonic_design" target="_blank">patreon page</a>
			to help continued development. <br /><br />
			They are cool and it would be cool of you to support FOSS.
		</p>
	</div>
</div>


<div style="display:none;">
	<?php
	// load editor so that tinymce is loaded
	wp_editor("", "bitc_enqued_editor", array('textarea_name' => 'bitc_enqued_editor', 'teeny' => true, 'media_buttons' => false, 'textarea_rows' => 3, 'quicktags' => false));
	// scripts and styles needed to use WP uploader
	wp_enqueue_media();
	?>
</div>