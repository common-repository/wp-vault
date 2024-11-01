<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-display-option.php");

if (isset($_GET["post_id"])) {
    $_post_id = $_GET["post_id"];
}
else {
    WpvUtils::ShowPageNotFound();
}
    
$display_option = WpvDisplayOption::GetDisplayOption($_post_id, TRUE);
if ($display_option === FALSE) {
    WpvUtils::ShowPageNotFound();
}
?>
#wpv-gray-out {
    background-color: #000000;
    opacity: 0.7;
    -moz-opacity: 0.7;
    filter: alpha(opacity=70);
    -khtml-opacity:0.7;
    position: absolute;
    overflow: hidden;
    top: 0px;
    left: 0px;
    display: none;
    z-index: 10;
}
#wpv-wrapper-<?php echo $_post_id; ?> {
    width: <?php echo $display_option->display_table_width; ?><?php echo $display_option->display_table_width_unit == "px" ? "px" : "%"; ?>;
    margin: <?php echo $display_option->display_table_margin_top; ?>px <?php echo $display_option->display_table_margin_right; ?>px <?php echo $display_option->display_table_margin_bottom; ?>px <?php echo $display_option->display_table_margin_left; ?>px;
    <?php 
    if ($display_option->display_table_location == "Float Left") { 
    ?>
        float: left;
        display: inline;
    <?php 
    }
    else if ($display_option->display_table_location == "Float Right") {
    ?>
        float: right;
        display: inline;
    <?php 
    }
    ?>
}
#wpv-table-<?php echo $_post_id; ?> {
    background-color: <?php echo $display_option->cell_background_color; ?>;
    border: <?php echo $display_option->display_table_border_width; ?>px <?php echo $display_option->display_table_border_style; ?> <?php echo $display_option->display_table_border_color; ?>;
    width: <?php echo $display_option->display_table_width; ?><?php echo $display_option->display_table_width_unit == "px" ? "px" : "%"; ?>;
    margin-left: auto;
    margin-right: auto;
}
#wpv-table-<?php echo $_post_id; ?> td {
    text-align: <?php echo $display_option->display_align; ?>;
    vertical-align: <?php echo $display_option->display_vertical_align; ?>;
}
#wpv-table-<?php echo $_post_id; ?> td.display-cell {
    padding: 5px 5px 5px 5px;
    background-color: <?php echo $display_option->cell_background_color; ?>;
    color: <?php echo $display_option->comment_font_color; ?>;
    font-size: <?php echo $display_option->comment_font_size; ?>pt;
    border: <?php echo $display_option->border_width; ?>px solid <?php echo $display_option->border_color; ?>;
}
#wpv-table-<?php echo $_post_id; ?> td .wpv-file-name {
    color: <?php echo $display_option->name_font_color; ?>;
    font-size: <?php echo $display_option->name_font_size; ?>pt;
    font-weight: <?php echo $display_option->name_font_bold ? "bold" : "normal"; ?>;
    text-decoration: <?php echo $display_option->name_font_underline ? "underline" : "none"; ?>;
}
#wpv-modal-dialog-<?php echo $_post_id; ?> {
    position: absolute;
    visibility: hidden;
    left: 0px;
    top: 0px;
    background-color: <?php echo $display_option->image_display_background_color; ?>;
    border: 2px solid <?php echo $display_option->image_display_border_color; ?>;
    z-index: 1005;
    text-align: center;
    vertical-align: middle;
    padding: 5px 5px 5px 5px;
}
#wpv-modal-dialog-<?php echo $_post_id; ?> div.dialog-message {
    padding: 20px 50px 20px 50px; 
    text-align: center; 
    font-weight: bold; 
    font-size: 12pt; 
    white-space: nowrap;
    color: <?php echo $display_option->image_display_font_color; ?>;
}
#wpv-image-view-<?php echo $_post_id; ?> {
    white-space: nowrap;
    text-align: center;
}
#wpv-image-view-<?php echo $_post_id; ?> img {
    margin: 5px 5px 5px 5px;
}
#wpv-image-view-<?php echo $_post_id; ?> .file-name {
    text-align: left;
    vertical-align: middle;
    font-size: <?php echo $display_option->image_display_name_font_size; ?>pt;
    font-weight: bold;
    color: <?php echo $display_option->image_display_font_color; ?>;
}
#wpv-image-view-<?php echo $_post_id; ?> td.close-button {
    text-align: right;
    vertical-align: middle;
}
#wpv-image-view-<?php echo $_post_id; ?> a.close-button {
    font-family: Arial, sans-serif, Helvetica;
    text-align: center;
    vertical-align: middle;
    font-weight: bold;
    border: 2px solid <?php echo $display_option->image_display_border_color; ?>;
    color: <?php echo $display_option->image_display_border_color; ?>;
    font-family: Arial;
    margin-right: 10px;
    text-decoration: none;
    font-size: 10pt;
}
#wpv-image-view-<?php echo $_post_id; ?> a.close-button:hover {
    font-size: 11pt;
}
#wpv-image-loading-<?php echo $_post_id; ?> {
    color: <?php echo $display_option->image_display_font_color; ?>;
    font-size: 10pt;
    text-align: left;
}
