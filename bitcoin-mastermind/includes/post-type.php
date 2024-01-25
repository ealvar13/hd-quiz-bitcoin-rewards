<?php
/*
    * Create Bitcoin Mastermind custom post type and taxonomy
    - NOTE: Wish I didn't name the CPT something so stupid
*/

/* Register Forms
------------------------------------------------------- */
function bitc_cpt_quizzes()
{
    $labels = array(
        'name'                => _x('Questions', 'Post Type General Name', 'text_domain'),
        'singular_name'       => _x('Bitcoin Mastermind', 'Post Type Singular Name', 'text_domain'),
        'menu_name'           => __('Bitcoin Mastermind', 'text_domain'),
        'name_admin_bar'      => __('Bitcoin Mastermind', 'text_domain'),
        'parent_item_colon'   => __('Parent Question:', 'text_domain'),
        'all_items'           => __('All Questions', 'text_domain'),
        'add_new_item'        => __('Add New Question', 'text_domain'),
        'add_new'             => __('Add New Question', 'text_domain'),
        'new_item'            => __('New Question', 'text_domain'),
        'edit_item'           => __('Edit Question', 'text_domain'),
        'update_item'         => __('Update Question', 'text_domain'),
        'view_item'           => __('View Question', 'text_domain'),
        'search_items'        => __('Search Question', 'text_domain'),
        'not_found'           => __('Not found', 'text_domain'),
        'not_found_in_trash'  => __('Not found in Trash', 'text_domain'),
    );
    $args = array(
        'label'               => __('Bitcoin Mastermind', 'text_domain'),
        'description'         => __('Post Type Description', 'text_domain'),
        'labels'              => $labels,
        'supports'            => array('title', 'thumbnail', 'quiz'),
        'hierarchical'        => false,
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => false,
        'menu_position'       => 5,
        'menu_icon'           => 'dashicons-clipboard',
        'show_in_admin_bar'   => false,
        'show_in_nav_menus'   => false,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'capability_type'     => 'page',
    );
    register_post_type('post_type_questionna', $args);
}
add_action('init', 'bitc_cpt_quizzes', 0);


// Register Quiz taxonomy
function bitc_tax_quizzes()
{
    $labels = array(
        'name'                       => _x('Quizzes', 'Taxonomy General Name', 'text_domain'),
        'singular_name'              => _x('Quiz', 'Taxonomy Singular Name', 'text_domain'),
        'menu_name'                  => __('Quizzes', 'text_domain'),
        'all_items'                  => __('All Quizzes', 'text_domain'),
        'parent_item'                => __('Parent Quiz', 'text_domain'),
        'parent_item_colon'          => __('Parent Quiz:', 'text_domain'),
        'new_item_name'              => __('New Quiz Name', 'text_domain'),
        'add_new_item'               => __('Add A New Quiz', 'text_domain'),
        'edit_item'                  => __('Edit Quiz', 'text_domain'),
        'update_item'                => __('Update Quiz', 'text_domain'),
        'view_item'                  => __('View Quiz', 'text_domain'),
        'separate_items_with_commas' => __('Separate Quizzes with commas', 'text_domain'),
        'add_or_remove_items'        => __('Add or remove Quizzes', 'text_domain'),
        'choose_from_most_used'      => __('Choose from the most used', 'text_domain'),
        'popular_items'              => __('Popular Quizzes', 'text_domain'),
        'search_items'               => __('Search Quizzes', 'text_domain'),
        'not_found'                  => __('Not Found', 'text_domain'),
    );
    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => false,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => false,
        'show_tagcloud'              => false,
        'rewrite'                    => false,
    );
    register_taxonomy('quiz', array('post_type_questionna'), $args);
}
add_action('init', 'bitc_tax_quizzes', 0);

function bitc_add_warning()
{
?>
    <style>
        #bitc_quiz_tax_warning {
            padding: 12px;
            border: 2px solid #ff6666;
        }

        #bitc_quiz_tax_warning * {
            font-size: 1.4em;
        }

        .bitc_button4 {
            font-size: 0.8em;
            display: inline-block;
            padding: 12px 14px;
            background: #ff6666;
            color: #fff;
            margin: 12px 8px;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
    </style>
    <script>
        window.onload = function(e) {
            if (jQuery("body").hasClass("post-type-post_type_questionna") && jQuery("body").hasClass("taxonomy-quiz")) {
                // add warning to quiz taxonomy page
                let warning = '<div id = "bitc_quiz_tax_warning"><h2>WARNING</h2><p>Please note that deleting a quiz here will NOT delete any attached questions to it. You can delete questions in bulk by clicking the following button</p><a href = "./edit.php?post_type=post_type_questionna" class = "bitc_button4">DELETE QUESTIONS</a></div>';
                jQuery(".form-wrap").append(warning)
            } else if (jQuery("body").hasClass("post-type-post_type_questionna")) {

                // add warning to quiz taxonomy page
                let warning = '<br/><br/><br/><br/><div id = "bitc_quiz_tax_warning"><p>This page is just a quick and easy way to bulk delete questions, or add multiple questions to a quiz at the same time. WordPress already has this functionality built in, so no point in reinventing the wheel :)</p></div><br/>';
                jQuery("#posts-filter").prepend(warning);
                jQuery(".page-title-action").remove();
            }

        };
    </script>
<?php
}


function bitc_add_warning_to_quiz_tax($hook)
{
    if ($hook === "edit-tags.php" || $hook === "edit.php") {
        bitc_add_warning();
    }
}
add_action('admin_enqueue_scripts', 'bitc_add_warning_to_quiz_tax', 10, 1);


function bitc_cpt_question_meta_notice()
{
    add_meta_box('bitc_question_meta', "NOTICE", 'bitc_question_meta_notice', 'post_type_questionna');
}
add_action('add_meta_boxes', 'bitc_cpt_question_meta_notice');

function bitc_question_meta_notice()
{
    echo '<p>the ablity to modify question data from here has been depricated since Bitcoin Mastermind 1.6, and removed since Bitcoin Mastermind 1.8. The bulk question edit page still exists so that you can easily delete questions in bulk, or add questions to quizzes in bulk.</p>';
}
