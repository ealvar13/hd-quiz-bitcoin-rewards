<?php
/*
    * Main page to allow users to add quizzes
    * and manage quizzes
*/

if (bitc_user_permission()) {
    function bitc_print_scripts()
    {
        wp_enqueue_style(
            'bitc_admin_style',
            plugin_dir_url(__FILE__) . 'css/bitc_admin.css',
            array(),
            bitc_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'bitc_admin_script',
            plugins_url('/js/bitc_admin.js', __FILE__),
            array('jquery', 'jquery-ui-draggable'),
            bitc_PLUGIN_VERSION,
            true
        );

        wp_enqueue_media();

        do_action("bitc_global_enqueue"); // enqueue files that need to be on every page
        do_action("bitc_settings_enqueue"); // enqueue files that only need to be on the settings page
    }
    bitc_print_scripts();
    wp_nonce_field('bitc_quiz_nonce', 'bitc_quiz_nonce');
    bitc_load_quizzes_page();
} else {
    die();
}

function bitc_load_quizzes_page()
{
    require dirname(__FILE__) . '/settings/quizzes.php';
}
