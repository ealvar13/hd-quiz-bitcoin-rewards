<?php
// includes/api_endpoints.php

add_action('rest_api_init', function () {
    register_rest_route('hdq/v1', '/sats_per_answer/(?P<quiz_id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'bitc_get_sats_per_answer',
        'permission_callback' => '__return_true', // For public access, or create a permission callback if needed
        'args' => array(
            'quiz_id' => array(
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param);
                }
            ),
        ),
    ));
});

function bitc_get_sats_per_answer($data) {
    $quiz_id = $data['quiz_id'];
    $sats_per_correct_answer = get_option("sats_per_answer_for_" . $quiz_id, 0); // default to 0 if not set
    return new WP_REST_Response(array('sats_per_correct_answer' => $sats_per_correct_answer), 200);
}
