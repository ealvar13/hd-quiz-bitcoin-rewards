<?php
function hdq_printField_select($tab, $tab_slug, $fields)
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

    <select data-tab="<?php echo $tab_slug; ?>" data-required="<?php echo $required; ?>" data-type="select" class="hdq_input hderp_input"
        id="<?php echo $tab["name"]; ?>">
        <option value="">-</option>
        <?php
            if (isset($tab["options"])) {

                for ($i = 0; $i < count($tab["options"]); $i++) {
                    $n = $tab["options"][$i]["label"];
                    $v = $tab["options"][$i]["value"];
                    $selected = "";
                    if ($v == $value) {
                        $selected = "selected";
                    } elseif ($value == null && isset($tab["options"][$i]["default"]) && $tab["options"][$i]["default"] == true){	
							$selected = "selected";
					}
                    $data = "";
                    if (isset($tab["options"][$i]["options"]) && $tab["options"][$i]["options"] != "") {
                        foreach ($tab["options"][$i]["options"] as $key => $value) {
                            $data .= $key.' = "'.$value.'" ';
                        }
                    }
                    echo '<option '.$data.' value="' . $v . '" ' . $selected . '>' . $n . '</option>';
                }
            } ?>
    </select>
<?php
if(isset($tab["content"])){
	echo $tab["content"];
}	
?>
</div>
<?php
}
