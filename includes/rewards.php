<?php
$hdq_functions_path = WP_PLUGIN_DIR . '/hd-quiz/includes/functions.php';
if (file_exists($hdq_functions_path)) {
    include_once $hdq_functions_path;
} else {
    // Log the error
    error_log("HD Quiz Plugin: Unable to find functions.php");
    
    // Attach the admin notice to the action hook
    add_action( 'admin_notices', 'hdq_admin_notice_error' );
}

function hdq_admin_notice_error() {
    $class = 'notice notice-error';
    $message = __( 'HD Quiz Plugin not found! Please ensure it is installed and active for the rewards functionality to work correctly.', 'hdq-rewards-plugin' );
    printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
}

function fetch_all_quizzes() {
    $quizzes = array();

    $taxonomy = 'quiz';
    $term_args = array(
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
    );
    $tax_terms = get_terms($taxonomy, $term_args);

    if (!empty($tax_terms) && !is_wp_error($tax_terms)) {
        foreach ($tax_terms as $term) {
            $quiz_name = (function_exists('mb_strimwidth')) ? mb_strimwidth($term->name, 0, 50, "...") : $term->name;
            $quizzes[] = array(
                'name' => $quiz_name,
                'id' => $term->term_id,
                'shortcode' => '[HDquiz quiz = "' . $term->term_id . '"]'
            );
        }
    }

    return $quizzes;
}
