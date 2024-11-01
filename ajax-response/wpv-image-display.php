<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-display-option.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-file.php");

$error_message = "";

if (isset($_POST["post_id"])) {
    $_post_id = $_POST["post_id"];
}
else {
    $error_message = "Missing required parameter(s).";
}   
if (isset($_POST["file_id"])) {
    $_file_id = $_POST["file_id"];
}
else {
    $error_message = "Missing required parameter(s).";
}   
if (isset($_POST["file_id"])) {
    $_hash = $_POST["hash"];
}
else {
    $error_message = "Missing required parameter(s).";
}   

$resultset = WpvFile::GetFileTable("file_id = $_file_id");
if (count($resultset) == 0) {
    $error_message = "Data not found.";
}
$file_name = $resultset[0]->file_name;
$owner_id = $resultset[0]->owner_id;

if (WpvUtils::CheckAccess($owner_id) === FALSE) {
    $error_message = "No access to this file.";
}

$display_option = WpvDisplayOption::GetDisplayOption($_post_id);
$target_image_size = $display_option->target_image_size;
$ratio = min($target_image_size/$resultset[0]->file_image_width, $target_image_size/$resultset[0]->file_image_height);
$ratio = min($ratio, 1.0);

if ($display_option->display_text == "Comment" || $display_option->display_text == "None")
    $file_name = "";
?>
<table id="wpv-image-view-<?php echo $_post_id; ?>" colspan="0" border="0" cellspacing="0">
    <tr>
    <td class="file-name"><?php echo $file_name; ?></td>
    <td class="close-button"><a class="close-button" href="javascript:void(0)" onclick="WpvDialog.closeDialog()" title="Close">&nbsp;X&nbsp;</a></td>
    </tr>
    <tr>
    <td colspan="2">
    <?php
    if ($error_message == "") {
    ?>
        <div id="wpv-image-loading-<?php echo $_post_id; ?>">Loading image...</div>
        <img id="wpv-loaded-image-<?php echo $_post_id; ?>" style="opacity: 0; filter:alpha(opacity=0); -khtml-opacity: 0; -moz-opacity: 0;" width="<?php echo $ratio * $resultset[0]->file_image_width; ?>" height="<?php echo $ratio * $resultset[0]->file_image_height; ?>" src="<?php echo get_bloginfo("siteurl"); ?>/?wpv_file_id=<?php echo $_file_id; ?>&post_id=<?php echo $_post_id; ?>&file_mode=default&hash=<?php echo $_hash; ?>" />
    <?php
    } 
    else {
    ?>
        <div id="wpv-image-loading-<?php echo $_post_id; ?>"><?php echo $error_message; ?></div>
    <?php
    }
    ?>
    </td>
    </tr>
</table>

