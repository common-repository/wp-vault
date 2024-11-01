<?php
require_once(dirname(__FILE__) . "/lib/wpv-function-display-option.php");

if (isset($_POST["post_id"]))
    $_post_id = $_POST["post_id"];
else 
    WpvUtils::ShowPageNotFound();
    
$display_option = WpvDisplayOption::GetDisplayOption($_post_id, TRUE);
$wpv_message = new WpvMessage();

if ($display_option === FALSE) {
    die;
}

if (isset($_POST["proc"]) && preg_match("/^display-option-(update|publish)/", $_POST["proc"])){
    update_display_option($_post_id, $display_option, $wpv_message);
    $display_option = WpvDisplayOption::GetDisplayOptionFromDB($_post_id);

    wp_cache_delete("display_option_$_post_id", "wp-vault");
}

$display_option_enum = null;

// Use cache to store enum information if the caching is available.
if (($display_option_enum = wp_cache_get("display_option_enum", "wp-vault")) === FALSE) {
    $display_option_enum = new WpvDisplayOptionEnum();
    wp_cache_add("display_option_enum", $display_option_enum, "wp-vault");
}
$wpv_message->WriteMessages();

$display_table_width_unit_enum = $display_option_enum->GetEnum("display_table_width_unit"); 
$display_table_border_style_enum = $display_option_enum->GetEnum("display_table_border_style"); 
$display_table_location_enum = $display_option_enum->GetEnum("display_table_location"); 
$display_thumbnail_enum = $display_option_enum->GetEnum("display_thumbnail"); 
$display_text_enum = $display_option_enum->GetEnum("display_text"); 
$display_align_enum = $display_option_enum->GetEnum("display_align"); 
$display_vertical_align_enum = $display_option_enum->GetEnum("display_vertical_align"); 
$display_status_enum = $display_option_enum->GetEnum("display_status"); 
$thumbnail_alignment_enum = $display_option_enum->GetEnum("thumbnail_alignment"); 

include(dirname(__FILE__) . "/wpv-link-manager-header.php");
?>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "/?wpv-js=wpv-link-manager-option"; ?>"></script>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "/?wpv-js=wpv-color-picker"; ?>"></script>

<form id="display-option-form" name="display-option-form" action="" method="post">
    <div class="submit">
    <input type="button" value="Update" onclick="submitData('update')" />
    <?php
    if ($display_option->display_status != "Published") {
    ?>
        <input type="button" value="Update & Publish" onclick="submitData('publish')" />
    <?php
    }
    ?>
    </div>

    <table id="wpv-display-option">
    <tr>
    <td colspan="3" class="section-title">
    Thumbnail Table Properties - <span class="section-desc">The properties for the thumbnail table, linked to a post or page.</span>
    </td>
    </tr>
    
    <tr>
    <td class="item-header"></td>
    <td>
    Table Location: 
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_table_location')"/>
    </td>
    <td>
    <?php
    foreach ($display_table_location_enum as $display_table_location) {
        $checked = "";
        if ($display_table_location == $display_option->display_table_location)
            $checked = " checked";
    ?>
        <span class="selection"><label for="display_table_location-<?php echo $display_table_location; ?>"><input id="display_table_location-<?php echo $display_table_location; ?>" type="radio" name="display_table_location" value="<?php echo $display_table_location; ?>" <?php echo $checked; ?>><?php echo $display_table_location; ?></label></span>
    <?php
    }
    ?>
    <br />
    <strong>* If you use "Float Left" or "Float Right", you should specify the exact table width below in "px".</strong>
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Table Width:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_table_width')"/>
    </td>
    <td>
    <input type="text" size="3" maxlength="3" name="display_table_width" value="<?php echo $display_option->display_table_width; ?>" />
    <?php
    foreach ($display_table_width_unit_enum as $display_table_width_unit) {
        $checked = "";
        if ($display_table_width_unit == $display_option->display_table_width_unit)
            $checked = " checked";
    ?>
        <span class="selection"><label for="display_table_width_unit-<?php echo $display_table_width_unit; ?>"><input id="display_table_width_unit-<?php echo $display_table_width_unit; ?>" type="radio" name="display_table_width_unit" value="<?php echo $display_table_width_unit; ?>" <?php echo $checked; ?>><?php echo $display_table_width_unit == "percent" ? "%" : $display_table_width_unit; ?></label></span>
    <?php
    }
    ?>
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Border:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_table_border')"/>
    </td>
    <td>
    Width: <input type="text" size="2" maxlength="2" name="display_table_border_width" value="<?php echo $display_option->display_table_border_width; ?>" />px
    &nbsp;&nbsp;Style:
    <select name="display_table_border_style">
    <?php
    foreach ($display_table_border_style_enum as $display_table_border_style) {
        $selected = "";
        if ($display_table_border_style == $display_option->display_table_border_style)
            $selected = " selected";
    ?>
        <option value="<?php echo $display_table_border_style; ?>" <?php echo $selected; ?>><?php echo $display_table_border_style; ?></option>
    <?php
    }
    ?>
    </select>
    &nbsp;&nbsp;Color: <?php display_color_picker_control("display_table_border_color", "display-table-border-color", $display_option->display_table_border_color); ?>
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Margin:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_table_margin')"/>
    </td>
    <td>
    Top:<input type="text" size="3" maxlength="3" name="display_table_margin_top" value="<?php echo $display_option->display_table_margin_top; ?>" />px&nbsp;&nbsp;
    Right:<input type="text" size="3" maxlength="3" name="display_table_margin_right" value="<?php echo $display_option->display_table_margin_right; ?>" />px&nbsp;&nbsp;
    Bottom:<input type="text" size="3" maxlength="3" name="display_table_margin_bottom" value="<?php echo $display_option->display_table_margin_bottom; ?>" />px&nbsp;&nbsp;
    Left:<input type="text" size="3" maxlength="3" name="display_table_margin_left" value="<?php echo $display_option->display_table_margin_left; ?>" />px&nbsp;&nbsp;
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Column Count: 
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_column_count')"/>
    </td>
    <td>
    <select name="column_count">
    <?php
    for ($i = 1; $i <= 10; $i++) {
        $selected = "";
        if ($i == $display_option->column_count)
            $selected = " selected";
        echo "<option value='$i'$selected>$i</option>\n";
    }
    ?>
    </select>
    </td>
    </tr>
    
    <tr>
    <td colspan="3" class="section-title">
    Cell Properties - <span class="section-desc">The properties for the cells in the thumbnail table.</span>
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Thumbnail Display Option:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_display_thumbnail')"/>
    </td>
    <td>
    <?php
    foreach ($display_thumbnail_enum as $display_thumbnail) {
        $checked = "";
        if ($display_thumbnail == $display_option->display_thumbnail)
            $checked = " checked";
    ?>
        <span class="selection"><label for="display_thumbnail-<?php echo $display_thumbnail; ?>"><input id="display_thumbnail-<?php echo $display_thumbnail; ?>" type="radio" name="display_thumbnail" value="<?php echo $display_thumbnail; ?>" <?php echo $checked; ?>><?php echo $display_thumbnail; ?></label></span>
    <?php
    }
    ?>
    </td>
    </tr>
    
    <tr>
    <td class="item-header"></td>
    <td>
    Text Display Option:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_text_display')"/>
    </td>
    <td>
    <?php
    foreach ($display_text_enum as $display_text) {
        $checked = "";
        if ($display_text == $display_option->display_text)
            $checked = " checked";
    ?>
        <span class="selection"><label for="display_text-<?php echo $display_text; ?>"><input id="display_text-<?php echo $display_text; ?>" type="radio" name="display_text" value="<?php echo $display_text; ?>" <?php echo $checked; ?>><?php echo $display_text; ?></label></span>
    <?php
    }
    ?>
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Horizontal Alignment:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_horizontal_align')"/>
    </td>
    <td>
    <?php
    foreach ($display_align_enum as $display_align) {
        $checked = "";
        if ($display_align == $display_option->display_align)
            $checked = " checked";
    ?>
        <span class="selection"><label for="display_align-<?php echo $display_align; ?>"><input id="display_align-<?php echo $display_align; ?>" type="radio" name="display_align" value="<?php echo $display_align; ?>" <?php echo $checked; ?>><?php echo $display_align; ?></label></span>
    <?php
    }
    ?>
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Vertical Alignment:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_vertical_align')"/>
    </td>
    <td>
    <?php
    foreach ($display_vertical_align_enum as $display_vertical_align) {
        $checked = "";
        if ($display_vertical_align == $display_option->display_vertical_align)
            $checked = " checked";
    ?>
        <span class="selection"><label for="display_vertical_align-<?php echo $display_vertical_align; ?>"><input id="display_vertical_align-<?php echo $display_vertical_align; ?>" type="radio" name="display_vertical_align" value="<?php echo $display_vertical_align; ?>" <?php echo $checked; ?>><?php echo $display_vertical_align; ?></label></span>
    <?php
    }
    ?>
    </td>
    </tr>
    
    <tr>
    <td class="item-header"></td>
    <td>
    Target Thumbnail Size:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_target_thumbnail_size')"/>
    </td>
    <td>
    <input type="text" size="3" maxlength="3" name="target_thumbnail_size" value="<?php echo $display_option->target_thumbnail_size; ?>" />px (System will make the thumbnail fit in <?php echo $display_option->target_thumbnail_size; ?>-by-<?php echo $display_option->target_thumbnail_size; ?> square.)
    </td>
    </tr>
    
    <tr>
    <td class="item-header"></td>
    <td>
    Cell Background Color:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_cell_background_color')"/>
    </td>
    <td>
    <?php display_color_picker_control("cell_background_color", "cell-background-color", $display_option->cell_background_color); ?>
    &nbsp;&nbsp;Hover: 
    <?php display_color_picker_control("cell_background_color_hover", "cell-background-color-hover", $display_option->cell_background_color_hover); ?>
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Cell Border:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_cell_border')"/>
    </td>
    <td>
    Color: 
    <?php display_color_picker_control("border_color", "border-color", $display_option->border_color); ?>
    &nbsp;&nbsp;Hover Color: 
    <?php display_color_picker_control("border_color_hover", "border-color-hover", $display_option->border_color_hover); ?>
    &nbsp;&nbsp;Width: <input type="text" size="2" maxlength="2" name="border_width" value="<?php echo $display_option->border_width; ?>" />px
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    File Name Text:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_file_name_text')"/>
    </td>
    <td>
    Color: 
    <?php display_color_picker_control("name_font_color", "name-font-color", $display_option->name_font_color); ?>
    &nbsp;&nbsp;Size: <input type="text" size="2" maxlength="2" name="name_font_size" value="<?php echo $display_option->name_font_size; ?>" />pt
    &nbsp;&nbsp;<label for="name-font-bold">Bold? <input id="name-font-bold" type="checkbox" name="name_font_bold" value="1" <?php echo $display_option->name_font_bold ? "checked" : ""; ?> /></label>
    &nbsp;&nbsp;<label for="name-font-underline">Underlined? <input id="name-font-underline" type="checkbox" name="name_font_underline" value="1" <?php echo $display_option->name_font_underline ? "checked" : ""; ?> /></label>
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Comment Text:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_comment_text')"/>
    </td>
    <td>
    Color: 
    <?php display_color_picker_control("comment_font_color", "comment-font-color", $display_option->comment_font_color); ?>
    &nbsp;&nbsp;Size: <input type="text" size="2" maxlength="2" name="comment_font_size" value="<?php echo $display_option->comment_font_size; ?>" />pt
    </td>
    </tr>

    <tr>
    <td colspan="3" class="section-title">
    Image Display Properties - <span class="section-desc">The properties for the image display window, used to display supported image files (jpg, gif, and png).</span>
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Background Color:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_image_display_background_color')"/>
    </td>
    <td>
    <?php display_color_picker_control("image_display_background_color", "image-display-background-color", $display_option->image_display_background_color); ?>
    </td>
    </tr>

    <tr>
    <td class="item-header"></td>
    <td>
    Border Color:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_image_display_border_color')"/>
    </td>
    <td>
    <?php display_color_picker_control("image_display_border_color", "image-display-border-color", $display_option->image_display_border_color); ?>
    </td>
    </tr>
    
    <tr>
    <td class="item-header"></td>
    <td>
    File Name Font:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_image_display_name_font')"/>
    </td>
    <td>
    Color: 
    <?php display_color_picker_control("image_display_font_color", "image-display-font-color", $display_option->image_display_font_color); ?>
    &nbsp;&nbsp;Font Size: <input type="text" size="2" maxlength="2" name="image_display_name_font_size" value="<?php echo $display_option->image_display_name_font_size; ?>" />
    </td>
    </tr>
    
    <tr>
    <td class="item-header"></td>
    <td>
    Target Image Size:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_target_image_size')"/>
    </td>
    <td>
    <input type="text" size="3" maxlength="4" name="target_image_size" value="<?php echo $display_option->target_image_size; ?>" />px (System will make the image fit in <?php echo $display_option->target_image_size; ?>-by-<?php echo $display_option->target_image_size; ?> square.)
    </td>
    </tr>

    <?php
    if ($display_option->display_status == "Published") {
    ?>
        <tr>
        <td colspan="3" class="section-title">
        Display Status - <span class="section-desc">The current status of the linked files.</span>
        </td>
        </tr>

        <tr>
        <td class="item-header"></td>
        <td colspan="2">
        <?php
        foreach ($display_status_enum as $display_status) {
            $checked = "";
            if ($display_status == $display_option->display_status)
                $checked = " checked";
        ?>
            <span class="selection"><label for="display_status-<?php echo $display_status; ?>"><input id="display_status-<?php echo $display_status; ?>" type="radio" name="display_status" value="<?php echo $display_status; ?>" <?php echo $checked; ?>><?php echo $display_status; ?></label></span>
        <?php
        }
        ?>
        <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'display_option_display_status')"/>
        </td>
        </tr>
    <?php
    }
    else {
    ?>
        <tr>
        <td>
        <input type="hidden" name="display_status" value="<?php echo $display_option->display_status; ?>" />
        </td>
        </tr>
    <?php
    }
    ?>
    </table>

    <div class="submit">
    <input type="button" value="Update" onclick="submitData('update')" />
    <?php
    if ($display_option->display_status != "Published") {
    ?>
        <input type="button" value="Update & Publish" onclick="submitData('publish')" />
    <?php
    }
    ?>
    </div>
    <input type="hidden" name="post_id" value="<?php echo $_post_id; ?>" />
    <input type="hidden" name="proc" value="display-option" />
    <input type="hidden" name="page" value="wp-vault/wpv-link-manager.php" />
    <input type="hidden" name="no_cookie" value="no_cookie" />
</form>
<iframe name="preview" id="wpv-preview-frame" style="display: inline;" src="<?php echo get_permalink($_post_id) . "&preview=true"; ?>"></iframe>

<?php
class WpvDisplayOptionEnum {
    function WpvDisplayOptionEnum() {
        global $wpv_display_option_table;
        global $wpdb;
        
        $this->enum_table = array();
        
        $this->enum_table["display_table_width_unit"] = WpvUtils::ParseEnum($wpdb->get_var("SHOW COLUMNS FROM $wpv_display_option_table LIKE 'display_table_width_unit'", 1));
        $this->enum_table["display_table_border_style"] = WpvUtils::ParseEnum($wpdb->get_var("SHOW COLUMNS FROM $wpv_display_option_table LIKE 'display_table_border_style'", 1));
        $this->enum_table["display_table_location"] = WpvUtils::ParseEnum($wpdb->get_var("SHOW COLUMNS FROM $wpv_display_option_table LIKE 'display_table_location'", 1));
        $this->enum_table["display_thumbnail"] = WpvUtils::ParseEnum($wpdb->get_var("SHOW COLUMNS FROM $wpv_display_option_table LIKE 'display_thumbnail'", 1));
        $this->enum_table["display_text"] = WpvUtils::ParseEnum($wpdb->get_var("SHOW COLUMNS FROM $wpv_display_option_table LIKE 'display_text'", 1));
        $this->enum_table["display_align"] = WpvUtils::ParseEnum($wpdb->get_var("SHOW COLUMNS FROM $wpv_display_option_table LIKE 'display_align'", 1));
        $this->enum_table["display_vertical_align"] = WpvUtils::ParseEnum($wpdb->get_var("SHOW COLUMNS FROM $wpv_display_option_table LIKE 'display_vertical_align'", 1));
        $this->enum_table["display_status"] = WpvUtils::ParseEnum($wpdb->get_var("SHOW COLUMNS FROM $wpv_display_option_table LIKE 'display_status'", 1));
        $this->enum_table["thumbnail_alignment"] = WpvUtils::ParseEnum($wpdb->get_var("SHOW COLUMNS FROM $wpv_display_option_table LIKE 'thumbnail_alignment'", 1));
    }
    
    function GetEnum($enum_name) {
        return $this->enum_table["$enum_name"];
    }
}

function update_display_option($post_id, $display_option, &$wpv_message) {
    global $wpdb;
    global $wpv_options;
    global $wpv_display_option_table;
    
    $error_message = "";
    $min_thumbnail_size = 50;
    $max_thumbnail_size = 999;
    $update_sql_array = array();
    $file_table = null;

    $_display_table_location = $_POST["display_table_location"];
    $_display_table_width = $_POST["display_table_width"];
    $_display_table_width_unit = $_POST["display_table_width_unit"];
    $_display_table_margin_top = $_POST["display_table_margin_top"];
    $_display_table_margin_right = $_POST["display_table_margin_right"];
    $_display_table_margin_bottom = $_POST["display_table_margin_bottom"];
    $_display_table_margin_left = $_POST["display_table_margin_left"];
    $_column_count = $_POST["column_count"];
    $_target_thumbnail_size = $_POST["target_thumbnail_size"];
    $_display_thumbnail = $_POST["display_thumbnail"];
    $_display_text = $_POST["display_text"];
    $_display_align = $_POST["display_align"];
    $_display_vertical_align = $_POST["display_vertical_align"];
    $_display_status = $_POST["display_status"];
    $_target_image_size = $_POST["target_image_size"];
    $_display_table_border_width = $_POST["display_table_border_width"];
    $_display_table_border_style = $_POST["display_table_border_style"];
    $_display_table_border_color = trim($_POST["display_table_border_color"]);
    $_cell_background_color = trim($_POST["cell_background_color"]);
    $_cell_background_color_hover = trim($_POST["cell_background_color_hover"]);
    $_border_color = trim($_POST["border_color"]);
    $_border_color_hover = trim($_POST["border_color_hover"]);
    $_border_width = $_POST["border_width"];
    $_name_font_bold = isset($_POST["name_font_bold"]) ? 1 : 0;
    $_name_font_size = $_POST["name_font_size"];
    $_name_font_color = trim($_POST["name_font_color"]);
    $_name_font_underline = isset($_POST["name_font_underline"]) ? 1 : 0;
    $_comment_font_color = trim($_POST["comment_font_color"]);
    $_comment_font_size = $_POST["comment_font_size"];
    $_image_display_background_color = $_POST["image_display_background_color"];
    $_image_display_border_color = $_POST["image_display_border_color"];
    $_image_display_font_color = $_POST["image_display_font_color"];
    $_image_display_name_font_size = $_POST["image_display_name_font_size"];

    // Input error checking.
    if ($_display_table_width_unit == "px") {
        if (($_display_table_width = WpvUtils::FilterNumber($_display_table_width, 100, 999)) === FALSE) {
            $_display_table_width = $display_option->display_table_width;
            $wpv_message->AddErrorMessageLine("Display Table Width must be numeric.");
        }
    }
    else {
        if (($_display_table_width = WpvUtils::FilterNumber($_display_table_width, 10, 100)) === FALSE) {
            $_display_table_width = $display_option->display_table_width;
            $wpv_message->AddErrorMessageLine("Display Table Width must be numeric.");
        }
    }

    if (($_target_thumbnail_size = WpvUtils::FilterNumber($_target_thumbnail_size, $min_thumbnail_size, $max_thumbnail_size)) === FALSE) {
        $_target_thumbnail_size = $display_option->target_thumbnail_size;
        $wpv_message->AddErrorMessageLine("Target Thumbnail Size must be numeric.");
    }
    if (($_target_image_size = WpvUtils::FilterNumber($_target_image_size, $min_thumbnail_size)) === FALSE) {
        $_target_image_size = $display_option->target_image_size;
        $wpv_message->AddErrorMessageLine("Target Image Size must be numeric.");
    }
    if (($_display_table_border_width = WpvUtils::FilterNumber($_display_table_border_width, 0, 10)) === FALSE) {
        $_display_table_border_width = $display_option->display_table_border_width;
        $wpv_message->AddErrorMessageLine("Border Width must be numeric.");
    }
    if (is_numeric($_display_table_margin_top) === FALSE || $_display_table_margin_top < 0) {
        $_display_table_margin_top = $display_option->display_table_margin_top;
        $wpv_message->AddErrorMessageLine("Margin Top must be numeric.");
    }
    if (is_numeric($_display_table_margin_right) === FALSE || $_display_table_margin_right < 0) {
        $_display_table_margin_right = $display_option->display_table_margin_right;
        $wpv_message->AddErrorMessageLine("Margin Right must be numeric.");
    }
    if (is_numeric($_display_table_margin_bottom) === FALSE || $_display_table_margin_bottom < 0) {
        $_display_table_margin_bottom = $display_option->display_table_margin_bottom;
        $wpv_message->AddErrorMessageLine("Margin Bottom must be numeric.");
    }
    if (is_numeric($_display_table_margin_left) === FALSE || $_display_table_margin_left < 0) {
        $_display_table_margin_left = $display_option->display_table_margin_left;
        $wpv_message->AddErrorMessageLine("Margin Left must be numeric.");
    }
    if ($_display_table_border_color == "") {
        $_display_table_border_color = $display_option->display_table_border_color;
        $wpv_message->AddErrorMessageLine("Border Color must be specified.");
    }
    if ($_cell_background_color == "") {
        $_cell_background_color = $display_option->cell_background_color;
        $wpv_message->AddErrorMessageLine("Cell Background Color must be specified.");
    }
    if ($_cell_background_color_hover == "") {
        $_cell_background_color_hover = $display_option->cell_background_color_hover;
        $wpv_message->AddErrorMessageLine("Cell Background Hover Color must be specified.");
    }
    if ($_border_color == "") {
        $_border_color = $display_option->border_color;
        $wpv_message->AddErrorMessageLine("Border Color must be specified.");
    }
    if ($_border_color_hover == "") {
        $_border_color_hover = $display_option->border_color_hover;
        $wpv_message->AddErrorMessageLine("Border Hover Color must be specified.");
    }
    if (is_numeric($_border_width) === FALSE || $_border_width < 0) {
        $_border_width = $display_option->border_width;
        $wpv_message->AddErrorMessageLine("Border Width must be numeric.");
    }
    if (is_numeric($_name_font_size) === FALSE || $_name_font_size < 0) {
        $_name_font_size = $display_option->name_font_size;
        $wpv_message->AddErrorMessageLine("File Name Font Size must be numeric.");
    }
    if ($_name_font_color == "") {
        $_name_font_color = $display_option->name_font_color;
        $wpv_message->AddErrorMessageLine("File Name Font Color must be specified.");
    }
    if (is_numeric($_comment_font_size) === FALSE || $_comment_font_size < 0) {
        $_comment_font_size = $display_option->comment_font_size;
        $wpv_message->AddErrorMessageLine("Comment Font Size must be numeric.");
    }
    if ($_comment_font_color == "") {
        $_comment_font_color = $display_option->comment_font_color;
        $wpv_message->AddErrorMessageLine("Comment Font Color must be specified.");
    }
    if ($_image_display_background_color == "") {
        $_image_display_background_color = $display_option->image_display_background_color;
        $wpv_message->AddErrorMessageLine("Image Display Background Color must be specified.");
    }
    if ($_image_display_border_color == "") {
        $_image_display_border_color = $display_option->image_display_border_color;
        $wpv_message->AddErrorMessageLine("Image Display Border Color must be specified.");
    }
    if ($_image_display_font_color == "") {
        $_image_display_font_color = $display_option->image_display_font_color;
        $wpv_message->AddErrorMessageLine("Image Display Font Color must be specified.");
    }
    if (is_numeric($_image_display_name_font_size) === FALSE || $_image_display_name_font_size < 0) {
        $_image_display_name_font_size = $display_option->image_display_name_font_size;
        $wpv_message->AddErrorMessageLine("Image Display File Name Font Size must be numeric.");
    }

    if ($wpv_message->ErrorMessageExists()) {
        return;
    }
    
    if (preg_match("/^display-option-publish/", $_POST["proc"]))
        $_display_status = "Published";

    // If the target thumbnail size is changed, delete thumbnails, so they may be re-created.
    if ($_target_thumbnail_size != $display_option->target_thumbnail_size) {
        if (WpvUtils::GetImageCachePath($error_message) !== FALSE) {
            if ($file_table == null) {
                require_once(dirname(__FILE__) . "/lib/wpv-function-post2file.php");

                $file_table = WpvPost2File::GetPost2FileTable($post_id);
            }
            foreach ($file_table as $file_data) {
                if (file_exists(WpvUtils::GetThumbnailFilePath($post_id, $file_data->stored_name))) {
                    if (unlink(WpvUtils::GetThumbnailFilePath($post_id, $file_data->stored_name)) === FALSE) {
                        $wpv_message->AddErrorMessageLine("Failed to delete thumbnails from the file system.");
                        return;
                    }
                }
            }
        }
        array_push($update_sql_array, "target_thumbnail_size = $_target_thumbnail_size");
    }

    // If the target image size is changed, delete cached images, so they may be re-created.
    if ($_target_image_size != $display_option->target_image_size) {
        if (WpvUtils::GetImageCachePath($error_message) !== FALSE) {
            if ($file_table == null) {
                require_once(dirname(__FILE__) . "/lib/wpv-function-post2file.php");

                $file_table = WpvPost2File::GetPost2FileTable($post_id);
            }
            foreach ($file_table as $file_data) {
                if (file_exists(WpvUtils::GetCachedFilePath($post_id, $file_data->stored_name))) {
                    if (unlink(WpvUtils::GetCachedFilePath($post_id, $file_data->stored_name)) === FALSE) {
                        $wpv_message->AddErrorMessageLine("Failed to delete cached images from the file system.");
                        return;
                    }
                }
            }
        }
        array_push($update_sql_array, "target_image_size = $_target_image_size");
    }

    if ($error_message != "") {
        $wpv_message->AddErrorMessageLine($error_message);
        return;
    }

    // Check what field needs updating.
    if ($_display_table_location != $display_option->display_table_location) {
        array_push($update_sql_array, "display_table_location = '$_display_table_location'");
    }
    if ($_display_table_width != $display_option->display_table_width) {
        array_push($update_sql_array, "display_table_width = $_display_table_width");
    }
    if ($_display_table_width_unit != $display_option->display_table_width_unit) {
        array_push($update_sql_array, "display_table_width_unit = '$_display_table_width_unit'");
    }
    if ($_display_table_margin_top != $display_option->display_table_margin_top) {
        array_push($update_sql_array, "display_table_margin_top = $_display_table_margin_top");
    }
    if ($_display_table_margin_right != $display_option->display_table_margin_right) {
        array_push($update_sql_array, "display_table_margin_right = $_display_table_margin_right");
    }
    if ($_display_table_margin_bottom != $display_option->display_table_margin_bottom) {
        array_push($update_sql_array, "display_table_margin_bottom = $_display_table_margin_bottom");
    }
    if ($_display_table_margin_left != $display_option->display_table_margin_left) {
        array_push($update_sql_array, "display_table_margin_left = $_display_table_margin_left");
    }
    if ($_column_count != $display_option->column_count) {
        array_push($update_sql_array, "column_count = $_column_count");
    }
    if ($_display_thumbnail != $display_option->display_thumbnail) {
        array_push($update_sql_array, "display_thumbnail = '$_display_thumbnail'");
    }
    if ($_display_text != $display_option->display_text) {
        array_push($update_sql_array, "display_text = '$_display_text'");
    }
    if ($_display_align != $display_option->display_align) {
        array_push($update_sql_array, "display_align = '$_display_align'");
    }
    if ($_display_vertical_align != $display_option->display_vertical_align) {
        array_push($update_sql_array, "display_vertical_align = '$_display_vertical_align'");
    }
    if ($_display_status != $display_option->display_status) {
        array_push($update_sql_array, "display_status = '$_display_status'");
    }
    if ($_display_table_border_width != $display_option->display_table_border_width) {
        array_push($update_sql_array, "display_table_border_width = $_display_table_border_width");
    }
    if ($_display_table_border_style != $display_option->display_table_border_style) {
        array_push($update_sql_array, "display_table_border_style = '$_display_table_border_style'");
    }
    if ($_display_table_border_color != $display_option->display_table_border_color) {
        array_push($update_sql_array, "display_table_border_color = '$_display_table_border_color'");
    }
    if ($_cell_background_color != $display_option->cell_background_color) {
        array_push($update_sql_array, "cell_background_color = '$_cell_background_color'");
    }
    if ($_cell_background_color_hover != $display_option->cell_background_color_hover) {
        array_push($update_sql_array, "cell_background_color_hover = '$_cell_background_color_hover'");
    }
    if ($_border_color != $display_option->border_color) {
        array_push($update_sql_array, "border_color = '$_border_color'");
    }
    if ($_border_color_hover != $display_option->border_color_hover) {
        array_push($update_sql_array, "border_color_hover = '$_border_color_hover'");
    }
    if ($_border_width != $display_option->border_width) {
        array_push($update_sql_array, "border_width = $_border_width");
    }
    if ($_name_font_bold != $display_option->name_font_bold) {
        array_push($update_sql_array, "name_font_bold = $_name_font_bold");
    }
    if ($_name_font_size != $display_option->name_font_size) {
        array_push($update_sql_array, "name_font_size = $_name_font_size");
    }
    if ($_name_font_color != $display_option->name_font_color) {
        array_push($update_sql_array, "name_font_color = '$_name_font_color'");
    }
    if ($_name_font_underline != $display_option->name_font_underline) {
        array_push($update_sql_array, "name_font_underline = $_name_font_underline");
    }
    if ($_comment_font_color != $display_option->comment_font_color) {
        array_push($update_sql_array, "comment_font_color = '$_comment_font_color'");
    }
    if ($_comment_font_size != $display_option->comment_font_size) {
        array_push($update_sql_array, "comment_font_size = $_comment_font_size");
    }
    if ($_image_display_background_color != $display_option->image_display_background_color) {
        array_push($update_sql_array, "image_display_background_color = '$_image_display_background_color'");
    }
    if ($_image_display_border_color != $display_option->image_display_border_color) {
        array_push($update_sql_array, "image_display_border_color = '$_image_display_border_color'");
    }
    if ($_image_display_font_color != $display_option->image_display_font_color) {
        array_push($update_sql_array, "image_display_font_color = '$_image_display_font_color'");
    }
    if ($_image_display_name_font_size != $display_option->image_display_name_font_size) {
        array_push($update_sql_array, "image_display_name_font_size = $_image_display_name_font_size");
    }

    if (count($update_sql_array) > 0) {
        global $userdata;
        
        $update_sql = "UPDATE $wpv_display_option_table SET ";
        $update_sql .= implode(", ", $update_sql_array);
        $update_sql .= ", last_update_by = $userdata->ID, last_update = NOW() ";
        $update_sql .= " WHERE post_id = $post_id ";

        if ($wpdb->query($update_sql) !== FALSE) {
            if (preg_match("/^display-option-publish/", $_POST["proc"]))
                $wpv_message->AddMessageLine("Successfully published.");
            else
                $wpv_message->AddMessageLine("Successfully updated.");
        }
        else {
            $wpv_message->AddErrorMessageLine("Update failed due to a database error.");
        }
    }
    return;
}

function display_color_picker_control($name, $id, $color) {
?>
    <span>
    <input type="text" id="<?php echo $id; ?>-text" size="11" maxlength="11" name="<?php echo $name; ?>" value="<?php echo $color; ?>" onkeydown="document.getElementById('<?php echo $id; ?>').style.backgroundColor = this.value;" onchange="document.getElementById('<?php echo $id; ?>').style.backgroundColor = this.value;"/>  
    <span class="color-picker-control" style="background-color: <?php echo $color; ?>" id="<?php echo $id; ?>" onclick="WpvColorPicker.showPicker(event, '<?php echo get_settings("siteurl"); ?>/', '<?php echo $id; ?>')">&nbsp;</span>
    </span>
<?php    
}
?>

