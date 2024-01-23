<?php
/*
    * Main page to allow users to add quizzes
    * and manage quizzes
*/

if (hdq_user_permission()) {
    function hdq_print_scripts()
    {
        wp_enqueue_style(
            'hdq_admin_style',
            plugin_dir_url(__FILE__) . 'css/hdq_admin.css',
            array(),
            HDQ_PLUGIN_VERSION
        );

        wp_enqueue_script(
            'hdq_admin_script',
            plugins_url('/js/hdq_admin.js', __FILE__),
            array('jquery', 'jquery-ui-draggable'),
            HDQ_PLUGIN_VERSION,
            true
        );

        wp_enqueue_media();

        do_action("hdq_global_enqueue"); // enqueue files that need to be on every page
        do_action("hdq_settings_enqueue"); // enqueue files that only need to be on the settings page
    }
    hdq_print_scripts();
    wp_nonce_field('hdq_quiz_nonce', 'hdq_quiz_nonce');
    hdq_load_quizzes_page();
} else {
    die();
}

function hdq_load_quizzes_page()
{
    require dirname(__FILE__) . '/settings/quizzes.php';
}
