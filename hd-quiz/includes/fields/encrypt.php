<?php
function hdq_printField_encode($tab, $tab_slug, $fields)
{
    $value = hdq_getValue($tab, $fields);
    $placeholder = hdq_getPlaceholder($tab, $fields);
    $required = hdq_getRequired($tab, $fields);
	if($value === "" && isset($tab["default"])){
		$value = $tab["default"];
	} elseif ($value != ""){
		$value = hdq_decode(hdq_decode($value));
	}
?>

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
    <textarea data-tab="<?php echo $tab_slug; ?>" data-type="encode" data-required="<?php echo $required; ?>" type="password"
        class="hdq_input hderp_input" id="<?php echo esc_attr($tab["name"]); ?>"
			  rows = "5" placeholder="<?php echo esc_attr($placeholder); ?>"><?php echo esc_textarea($value); ?></textarea>
</div>
<?php
}
