<?php
class WpvTagTable {
    function DisplayTagTable($tag_table, $css="", $select_style="", $name="selected_tag") {
        echo "<div id='wpv-tag-list' style='$css'>";
        if (count($tag_table) == 0) {
            echo "No tags";
        }
        else if ($select_style == "checkbox") {
            foreach ($tag_table as $tag_data) {
            ?>
                <div class="tag-entry"><label for="tag-checkbox-<?php echo $tag_data->tag_id; ?>">&nbsp;&nbsp;<input type="checkbox" id="tag-checkbox-<?php echo $tag_data->tag_id; ?>" value="<?php echo $tag_data->tag_id; ?>" name="<?php echo $name; ?>[]"/>&nbsp;&nbsp;<?php echo $tag_data->tag_name; ?></label></div>
            <?php
            }
        }
        else if ($select_style == "radio") {
            echo '<div class="tag-entry"><label for="tag-radio-none">&nbsp;&nbsp;<input type="radio" id="tag-radio-none" value="0" name="' . $name . '" checked/>&nbsp;&nbsp;None</label></div>';
            echo '<br />';
            foreach ($tag_table as $tag_data) {
            ?>
                <div class="tag-entry"><label for="tag-radio-<?php echo $tag_data->tag_id; ?>">&nbsp;&nbsp;<input type="radio" id="tag-radio-<?php echo $tag_data->tag_id; ?>" value="<?php echo $tag_data->tag_id; ?>" name="<?php echo $name; ?>"/>&nbsp;&nbsp;<?php echo $tag_data->tag_name; ?></label></div>
            <?php
            }
        }
        else {
            foreach ($tag_table as $tag_data) {
            ?>
                <div class="tag-entry<?php echo preg_match("/(\'$tag_data->tag_name\')/", $new_tag) ? " new-tag" : ""; ?>">&nbsp;&nbsp;<?php echo $tag_data->tag_name; ?></div>
            <?php
            }
        }
        echo "</div>";
    }
}
?>
