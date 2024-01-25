<?php
function bitc_printField_image($tab, $tab_slug, $fields)
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

            $options = "";
            if (isset($tab["options"])) {
                $options = 'data-options = "' .bitc_encodeURIComponent(json_encode($tab["options"])).'"';
            }
        ?>
    </label>
    <div title = "set or update image" id="<?php echo $tab["name"]; ?>" <?php echo $options; ?> data-value="<?php echo $value; ?>"
        data-tab="<?php echo $tab_slug; ?>" data-type="image" class="bitc_input input_image hderp_input">
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
