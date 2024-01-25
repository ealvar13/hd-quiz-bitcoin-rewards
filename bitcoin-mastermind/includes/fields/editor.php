<?php
function bitc_printField_editor($tab, $tab_slug, $fields)
{
    $value = bitc_getValue($tab, $fields);
    $placeholder = bitc_getPlaceholder($tab, $fields);
    $required = bitc_getRequired($tab, $fields); ?>

<div class="bitc_input_item" data-tab="<?php echo $tab_slug; ?>" data-required="<?php if ($required) {
        echo "required";
    } ?>">
    <label class="bitc_input_label" for="<?php echo $tab["name"]; ?>">
        <?php
            if ($required) {
                bitc_print_tab_requiredIcon();
                $required = "required";
            }
            echo $tab["label"];
            if (isset($tab["tooltip"]) && $tab["tooltip"] != "") {
                bitc_print_fields_tooltip($tab["tooltip"]);
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
