<?php
function hdq_printField_image($tab, $tab_slug, $fields)
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

            $options = "";
            if (isset($tab["options"])) {
                $options = 'data-options = "' .hdq_encodeURIComponent(json_encode($tab["options"])).'"';
            }
        ?>
    </label>
    <div title = "set or update image" id="<?php echo $tab["name"]; ?>" <?php echo $options; ?> data-value="<?php echo $value; ?>"
        data-tab="<?php echo $tab_slug; ?>" data-type="image" class="hdq_input input_image hderp_input">
        <?php
            if ($value == "" || $value == 0) {
                echo 'set image';
            } else {
                $image = wp_get_attachment_image($value, "large", "", array("class" => "image_field_image"));
				if($image != null){
					echo $image;
				} else {
					echo '<small>image was deleted</small>';
				}				
            } ?>
    </div>
	<?php if ($value != "" && $value != 0) {
                echo '<p class = "remove_image_wrapper" style = "text-align:center"><span class = "remove_image" data-id = "'.$tab["name"].'">remove image</span></p>';
            } ?>
<?php
if(isset($tab["content"])){
	echo $tab["content"];
}	
?>
</div>
<?php
}
