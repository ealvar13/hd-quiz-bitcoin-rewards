<?php
function hdq_printField_integer($tab, $tab_slug, $fields)
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
        <?php
            $options = "";
            if (isset($tab["options"])) {
                for ($i = 0; $i < count($tab["options"]); $i++) {
                    $n = $tab["options"][$i]["name"];
                    $v = $tab["options"][$i]["value"];
                    $options .= $n . ' = "' . $v . '"';
                }
            }
	
			if(isset($tab["prefix"]) || isset($tab["suffix"])){
				$suffix = "";
				if (isset($tab["suffix"])){
					$suffix = "input_prefix_right";
				}
				echo '<div class="input_has_prefix '.$suffix.'">';
				if (isset($tab["prefix"])){
					echo '<div class="input_prefix">'.$tab["prefix"].'</div>';
				}
			}
	
		?>
    <input type="number" data-tab="<?php echo $tab_slug; ?>" data-type="integer" <?php echo $options; ?>
        class="hdq_input hderp_input" id="<?php echo $tab["name"]; ?>" steps="1" data-required="<?php echo $required; ?>" value="<?php echo $value; ?>"
        placeholder="<?php echo $placeholder; ?>">
	<?php
				if (isset($tab["suffix"])){
					echo '<div class="input_prefix">'.$tab["suffix"].'</div>';
				}
				if(isset($tab["prefix"]) || isset($tab["suffix"])){
					echo '</div>';
				}
	?>
<?php
if(isset($tab["content"])){
	echo $tab["content"];
}	
?>
</div>
<?php
}
