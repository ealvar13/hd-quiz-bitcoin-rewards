<?php

if (!current_user_can('edit_others_pages')) {
    die("Your user account does not have access to these settings");
}

wp_enqueue_style(
    'bitc_admin_style',
    plugin_dir_url(__FILE__) . 'css/bitc_admin.css?v=' . bitc_PLUGIN_VERSION
);

wp_enqueue_script(
    'bitc_admin_script',
    plugins_url('/js/bitc_admin.js?v=' . bitc_PLUGIN_VERSION, __FILE__),
    array('jquery', 'jquery-ui-draggable'),
    bitc_PLUGIN_VERSION,
    true
);
?>
<div id="main" style="max-width: 900px; background: #f3f3f3; border: 1px solid #ddd; margin-top: 2rem">
    <div id="header">
        <h1 id="heading_title" style="margin-top:0">
            Bitcoin Mastermind - About / Options
        </h1>
    </div>

    <p>Bitcoin Mastermind was designed and developed to be one of the easiest and most hassle-free quiz builders for WordPress. If you have any questions, or need support, please contact me at either at the WordPress.org <a href="https://wordpress.org/support/plugin/hd-quiz/" data-type="URL" data-id="https://wordpress.org/support/plugin/hd-quiz/">Bitcoin Mastermind support forum</a>, or <a href="https://hdplugins.com/forum/hd-quiz-support/?utm_source=hd-quiz" data-type="URL" data-id="https://hdplugins.com/forum/hd-quiz-support/&amp;utm_source=hd-quiz">our own support forum</a>.</p>



    <p>As I continue to develop Bitcoin Mastermind, more features, options, customizations, and settings will be introduced. If you have enjoyed Bitcoin Mastermind, then I would sure appreciate it if you could&nbsp;<a href="https://wordpress.org/support/plugin/hd-quiz/reviews/#new-post" target="_blank" rel="noreferrer noopener">leave an honest review</a>. It&#8217;s the little things that make building systems like this worthwhile ❤.</p>

    <hr style="margin-top:2rem" />


    <?php wp_nonce_field('bitc_about_options_nonce', 'bitc_about_options_nonce'); ?>

    <div style="display: grid; grid-template-columns: 1fr max-content; align-items: center;">
        <h2>
            Settings
        </h2>
        <div>
            <a href="https://hdplugins.com/learn/hd-quiz/hd-quiz-documentation/?utm_source=hd-quiz" title="Documentation" class="bitc_button2">Documentation</a>
            <div role="button" title="save HDQ settings" class="bitc_button" id="bitc_save_settings">SAVE</div>
        </div>
    </div>


    <?php
    $fields = bitc_get_settings();
    if (!isset($quizID)) {
        $quizID = "";
    }
    ?>

    <div id="bitc_settings_page" class="content" style="display: block">
        <div id="content_tabs">
            <div id="tab_nav_wrapper">
                <div id="bitc_logo">
                    <span class="bitc_logo_tooltip"><img src="<?php echo plugins_url('/images/hd-logo.png', __FILE__); ?>" alt="Harmonic Design logo">
                        <span class="bitc_logo_tooltip_content">
                            <span><strong>Bitcoin Mastermind</strong> is developed by Harmonic Design. Check out the addons page to see how you can extend Bitcoin Mastermind even further.</span>
                        </span>
                    </span>
                </div>
                <div id="tab_nav">
                    <?php bitc_print_settings_tabs(); ?>
                </div>
            </div>
            <div id="tab_content">
                <input type="hidden" class="hderp_input" id="quiz_id" style="display:none" data-required="true" data-type="integer" value="<?php echo $quizID; ?>" />
                <?php bitc_print_settings_tab_content($fields); ?>
            </div>
        </div>
    </div>



    <div class="bitc_highlight" id="hd_patreon">
        <div id="hd_patreon_icon">
            <img src="<?php echo plugins_url('/images/hd_patreon.png', __FILE__); ?>" alt="Donate">
        </div>
        <p>
            Bitcoin Mastermind is a 100% free plugin developed in my spare time, and as such, I get paid in nothing but good will
            and positive reviews. If you are enjoying Bitcoin Mastermind and would like to show your support, please consider
            contributing to my <a href="https://www.patreon.com/harmonic_design" target="_blank">patreon page</a> to
            help continued development. Every little bit helps, and I am fuelled by ☕.
        </p>
    </div>
    <br />

</div>