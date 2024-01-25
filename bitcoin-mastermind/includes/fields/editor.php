<?php
function hdq_printField_editor($tab, $tab_slug, $fields)
{
    $value = hdq_getValue($tab, $fields);
    $placeholder = hdq_getPlaceholder($tab, $fields);
    $required = hdq_getRequired($tab, $fields); ?>

<div class="hdq_input_item" data-tab="<?php echo $tab_slug; ?>" data-required="<?php if ($required) {
        echo "required";
    } ?>">
    <label class="hdq_input_label" for="<?php echo $tab["name"]; ?>">
        <?php
            if ($required) {
                hdq_print_tab_requiredIcon();
                $required = "required";
            }
            echo $tab["label"];
            if (isset($tab["tooltip"]) && $tab["tooltip"] != "") {
                hdq_print_fields_tooltip($tab["tooltip"]);
            }
        ?>
    </label>

    <?php
        $media = true;
        if (isset($tab["media"]) && $tab["media"] == false) {
            $media = false;
        }
        wp_editor(stripslashes(urldecode($value)), $tab["name"], array('textarea_name' => $tab["name"], 'editor_class' => "hderp_editor", 'media_buttons' => $media, 'textarea_rows' => 20, 'quicktags' => true, 'editor_height' => 240));
    ?>
<?php
if(isset($tab["content"])){
	echo $tab["content"];
}	
?>
</div>
<?php
}
