<?php
function bitc_printField_encode($tab, $tab_slug, $fields)
{
    $value = bitc_getValue($tab, $fields);
    $placeholder = bitc_getPlaceholder($tab, $fields);
    $required = bitc_getRequired($tab, $fields);
	if($value === "" && isset($tab["default"])){
		$value = $tab["default"];
	} elseif ($value != ""){
		$value = bitc_decode(bitc_decode($value));
	}
?>

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
    <textarea data-tab="<?php echo $tab_slug; ?>" data-type="encode" data-required="<?php echo $required; ?>" type="password"
        class="bitc_input hderp_input" id="<?php echo esc_attr($tab["name"]); ?>"
			  rows = "5" placeholder="<?php echo esc_attr($placeholder); ?>"><?php echo esc_textarea($value); ?></textarea>
</div>
<?php
}
