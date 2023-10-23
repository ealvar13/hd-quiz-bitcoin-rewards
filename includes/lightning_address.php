<?php
/**
 * Lightning Address Add-On: Display functionality on the quiz start.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display 'Hello World' at the start of the quiz.
 */
function la_hello_world_on_quiz_start() {
    echo 'Hello World';
}

// Attach our function to the 'hdq_before' action.
add_action('hdq_before', 'la_hello_world_on_quiz_start');