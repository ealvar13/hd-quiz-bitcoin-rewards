<?php
function hdq_printField_textarea($tab, $tab_slug, $fields)
{
    $value = hdq_getValue($tab, $fields);
    $placeholder = hdq_getPlaceholder($tab, $fields);
    $required = hdq_getRequired($tab, $fields);
    if ($value === "" && isset($tab["default"])) {
        $value = $tab["default"];
    } ?>

<div class="hdq_input_item">
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
    <textarea data-tab="<?php echo $tab_slug; ?>" rows = "5" data-type="text" data-required="<?php echo $required; ?>" type="text"
        class="hdq_input hderp_input" id="<?php echo $tab["name"]; ?>"
        placeholder="<?php echo $placeholder; ?>"><?php echo $value; ?></textarea>
<?php
if(isset($tab["content"])){
	echo $tab["content"];
}	
?>
</div>
<?php
}