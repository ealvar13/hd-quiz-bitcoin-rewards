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

    <p>Bitcoin Mastermind is designed to be an easy way to engage your site users with bitcoin rewards. It is a modification by Velas Commerce of an original WordPress plugin by Harmonic Design, to whom we are eternally grateful. If you have any questions, or need support, please contact us at <a href="https://velascommerce.com/">Velas Commerce</a>.</p>

    <hr style="margin-top:2rem" />


    <?php wp_nonce_field('bitc_about_options_nonce', 'bitc_about_options_nonce'); ?>

    <div style="display: grid; grid-template-columns: 1fr max-content; align-items: center;">
        <h2>
            Settings
        </h2>
        <div>
            <a href="https://hdplugins.com/learn/hd-quiz/hd-quiz-documentation/?utm_source=hd-quiz" title="Documentation" class="bitc_button2">HD Quiz Documentation</a>
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
                    <span class="bitc_logo_tooltip"><img src="<?php echo plugins_url('/images/bm-logo.png', __FILE__); ?>" alt="Harmonic Design logo">
                        <span class="bitc_logo_tooltip_content">
                            <span><strong>Bitcoin Mastermind</strong> is developed by Velas Commerce. If you are looking for support or custom implementations, find us at velascommerce.com</span>
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
            Bitcoin Mastermind is a 100% free plugin. We do not charge anything for downloading it and we don't do add ons or premium versions.  We make bitcoin to fund this project based on a sending a tiny amount of each reward sent to our Lightning Address. If you are enjoying Bitcoin Mastermind, we are a fork of an existing WP plugin, if you would like to show your support to them, please consider
            contributing to their <a href="https://www.patreon.com/harmonic_design" target="_blank">patreon page</a> to
            help continued development. They are cool and it would be cool of you to support FOSS.
        </p>
    </div>
    <br />

</div>