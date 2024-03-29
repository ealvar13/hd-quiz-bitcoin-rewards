<?php
function bitc_printField_text($tab, $tab_slug, $fields)
{
    $value = bitc_getValue($tab, $fields);
    $placeholder = bitc_getPlaceholder($tab, $fields);
    $required = bitc_getRequired($tab, $fields);
	
    if ($value === "" && isset($tab["default"])) {
        $value = $tab["default"];
    } ?>

<div class="bitc_input_item">
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
    <input data-tab="<?php echo $tab_slug; ?>" data-type="text" data-required="<?php echo $required; ?>" type="text"
        class="bitc_input hderp_input" id="<?php echo $tab["name"]; ?>" value="<?php echo $value; ?>"
        placeholder="<?php echo $placeholder; ?>">
<?php
if(isset($tab["content"])){
	echo $tab["content"];
}	
?>
</div>
<?php
}