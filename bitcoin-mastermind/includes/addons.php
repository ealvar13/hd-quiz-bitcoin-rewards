<?php
/*
    HDQuiz Addons Page - shows available addon plugins for HDQ
*/


if (!current_user_can('edit_others_pages')) {
    die();
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

$today = date("Ymd");
update_option("bitc_new_addon", $today);
set_transient("bitc_new_addon", array("date" => $today, "isNew" => ""), WEEK_IN_SECONDS);
?>


<div id="main" style="max-width: 800px; background: #f3f3f3; border: 1px solid #ddd; margin-top: 2rem">
    <div id="header">
        <h1 id="heading_title" style="margin-top:0">
            Bitcoin Mastermind - Addons
        </h1>
    </div>

    <div id="bitc_addons">
        <?php

        // TODO! convert to ajax for faster initial page load
        $data = wp_remote_get("https://hdplugins.com/plugins/hd-quiz/addons.txt");

        if (is_array($data)) {
            $data = $data["body"];
            $data = stripslashes(html_entity_decode($data));
            $data = json_decode($data);

            if (!empty($data)) {
                foreach ($data as $value) {
                    $title = sanitize_text_field($value->title);
                    $thumb = sanitize_text_field($value->thumb);
                    $description = wp_kses_post($value->description);
                    $url = sanitize_text_field($value->url);
                    $author = sanitize_text_field($value->author);
                    $price = sanitize_text_field($value->price);
                    $slug = sanitize_text_field($value->slug);
                    $verified = sanitize_text_field($value->verified);
                    $subscription = "";
                    if (isset($value->subscription)) {
                        $subscription = sanitize_text_field($value->subscription);
                    }
                    if ($price == 0) {
                        $price = "FREE";
                    } else {
                        $price = "$" . $price;
                    }
                    if ($subscription != "") {
                        $price = $price . ' / ' . $subscription;
                    }

        ?>
                    <div class="bitc_addon_item">
                        <div class="bitc_addon_item_image">
                            <img src="<?php echo $thumb; ?>" alt="<?php echo $title; ?>">
                        </div>
                        <div class="bitc_addon_content">
                            <h2>
                                <?php
                                echo $title;
                                if ($verified == "verified") {
                                    echo '<span class = "bitc_verified bitc_tooltip bitc_tooltip_question">verified<span class="bitc_tooltip_content"><span>This plugin has either been developed by the author of Bitcoin Mastermind or has been audited by the developer.</span></span></span>';
                                } ?> <span class="bitc_price"><?php echo esc_html($price); ?></span></h2>
                            <h4 class="bitc_addon_author">
                                developed by: <?php echo esc_html($author); ?>
                            </h4>

                            <?php echo apply_filters('the_content', $description); ?>
                            <p style="text-align:right">
                                <?php
                                if ($slug != "" && $slug != null) {
                                    echo '<a class = "bitc_button" target = "_blank" href = "plugin-install.php?tab=plugin-information&amp;plugin=' . $slug . '">VIEW ADDON PAGE</a>';
                                } else {
                                    echo '<a href = "' . $url . '?utm_source=HDQuiz&utm_medium=addonsPage" target = "_blank" class = "bitc_button2 bitc_reverse">View Addon Page</a>';
                                } ?>
                            </p>
                        </div>
                    </div>
                <?php
                }
            } else {
                ?>

                <p>Unable to retrieve list of addons. I'm probably having some server issues, please check back later.</p>

        <?php
            }
        } else {
            echo '<p>Unable to retrieve list of addons. I am probably having some server issues, please check back later.</p>';
        }
        ?>


    </div>
</div>