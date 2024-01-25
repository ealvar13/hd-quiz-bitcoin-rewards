<?php
function hqd_printField_float($tab, $tab_slug, $fields)
{
    $value = hqd_getValue($tab, $fields);
    $placeholder = hqd_getPlaceholder($tab, $fields);
    $required = hqd_getRequired($tab, $fields);
	if ($value === "" && isset($tab["default"])) {
        $value = $tab["default"];
    } ?>	
?>

<div class="hqd_input_item">
    <label class="hqd_input_label" for="<?php echo $tab["name"]; ?>">
        <?php
            if ($required) {
                hqd_print_tab_requiredIcon();
                $required = "required";
            }
            echo $tab["label"];
            if (isset($tab["tooltip"]) && $tab["tooltip"] != "") {
                hqd_print_fields_tooltip($tab["tooltip"]);
            }
        ?>
    </label>

    <?php
    $options = "";
    if (isset($tab["options"])) {
        for ($i = 0; $i < count($tab["options"]); $i++) {
            $n = $tab["options"][$i]["name"];
            $v = $tab["options"][$i]["value"];
            $options .= $n . ' = "' . $v . '"';
        }
    } ?>

        <input type="number" data-tab="<?php echo $tab_slug; ?>" data-type="float" <?php echo $options; ?>
            class="hqd_input hderp_input" id="<?php echo $tab["name"]; ?>" value="<?php echo $value; ?>" data-required="<?php echo $required; ?>"
            placeholder="<?php echo $placeholder; ?>">
    ?>
<?php
if(isset($tab["content"])){
	echo $tab["content"];
}	
?>
</div>
<?php
}
