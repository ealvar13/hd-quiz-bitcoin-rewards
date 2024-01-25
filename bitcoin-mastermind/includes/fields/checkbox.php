<?php
function bitc_printField_checkbox($tab, $tab_slug, $fields)
{
    $value = bitc_getValue($tab, $fields);
    $placeholder = bitc_getPlaceholder($tab, $fields);
    $required = bitc_getRequired($tab, $fields); ?>

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
<div class="bitc_checkbox_wrapper hderp_input" data-tab = "<?php echo $tab_slug; ?>" data-type = "checkbox" data-id = "<?php echo $tab["name"]; ?>" id = "<?php echo $tab["name"]; ?>">	
    <?php
        if (isset($tab["options"])) {
			// first check if we should set a default value or not
			$default = "";
			$hasValue = false;
			for ($i = 0; $i < count($tab["options"]); $i++) {
				$v = $tab["options"][$i]["value"];				
				if(isset($tab["options"][$i]["default"])){
					if($tab["options"][$i]["default"] == "true"){
						$default = $v;
					}
				}
				
                if (is_array($value) && in_array($v, $value)) {
                    $hasValue = true;
				}				
			}
			

            for ($i = 0; $i < count($tab["options"]); $i++) {
                $n = $tab["options"][$i]["label"];
                $v = $tab["options"][$i]["value"];
				
                $checked = "";
                if (is_array($value) && in_array($v, $value)) {
                    $checked = "checked";
				}
				
				if(!$hasValue && $default != "" && isset($value) && count($value) > 1){
					if($v == $default){
						$checked = "checked";
					}
				}
				?>

                <div class="bitc_checkbox_container">
                    <div class="bitc_checkbox">
                        <input 
                            data-tab="<?php echo $tab_slug; ?>" 
                            type="checkbox" 
                            onchange="HDQ.checkboxFieldSelect(this)" 
                            value="<?php echo $v; ?>" 
                            data-type="radio" 
                            class="bitc_checkbox_input" 
                            data-id="<?php echo $tab["name"]; ?>" 
                            id="variation_field_<?php echo $tab["name"].$v; ?>" 
                            <?php echo $checked; ?>
                        />                
                        <label class = "bitc_toggle" for="variation_field_<?php echo $tab["name"].$v; ?>"></label>
                    </div>
                    <label for="variation_field_<?php echo $tab["name"].$v; ?>"><?php echo $n; ?></label>
                </div>
                <?php
            }
        } ?>
</div>
<?php
if(isset($tab["content"])){
	echo $tab["content"];
}	
?>
</div>
<?php
}
