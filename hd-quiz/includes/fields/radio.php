<?php
function hdq_printField_radio($tab, $tab_slug, $fields)
{
    $value = hdq_getValue($tab, $fields);
    $placeholder = hdq_getPlaceholder($tab, $fields);
    $required = hdq_getRequired($tab, $fields); ?>

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
<div class="hdq_radio_wrapper">	
    <?php
        if (isset($tab["options"])) {
			
			$default = "";
			$hasValue = false;
			for ($i = 0; $i < count($tab["options"]); $i++) {
				$v = $tab["options"][$i]["value"];				
				if(isset($tab["options"][$i]["default"])){
					if($tab["options"][$i]["default"] == "true"){
						$default = $v;
					}
				}
				
                if ($v == $value) {
                    $hasValue = true;
				}				
			}			
			
			
            for ($i = 0; $i < count($tab["options"]); $i++) {
                $n = $tab["options"][$i]["label"];
                $v = $tab["options"][$i]["value"];
                
                if ($value === "" && isset($tab["default"])) {
                    $value = $tab["default"];
                }
                
                $checked = "";
                if ($v == $value) {
                    $checked = "checked";
                } 
	
				if(!$hasValue && $default != ""){
					if($v == $default){
						$checked = "checked";
					}
				} ?>

                <div class="hdq_radio_container">
                    <div class="hdq_radio">
                        <input 
                            data-tab="<?php echo $tab_slug; ?>" 
                            type="checkbox" 
                            onchange="HDQ.radioFieldSelect(this)" 
                            value="<?php echo $v; ?>" 
                            data-type="radio" 
                            class="hdq_radio_input" 
                            data-id="<?php echo $tab["name"]; ?>" 
                            id="variation_field_<?php echo $tab["name"].$v; ?>" 
                            <?php echo $checked; ?>
                        />                
                        <label class = "hdq_toggle" for="variation_field_<?php echo $tab["name"].$v; ?>"></label>
                    </div>
                    <label for="variation_field_<?php echo $tab["name"].$v; ?>"><?php echo $n; ?></label>
                </div>
                <?php
            }
        } ?>
</div>
<input type="hidden" style="display:none;" class="hderp_input" data-type = "radio" data-id="<?php echo $tab["name"]; ?>" id="<?php echo $tab["name"]; ?>" value="<?php echo $value; ?>">
<?php
if(isset($tab["content"])){
	echo $tab["content"];
}	
?>
</div>
<?php
}
