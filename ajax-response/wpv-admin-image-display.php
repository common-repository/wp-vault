<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-file.php");

global $wpv_options;

$error_message = "";

$_file_mode = isset($_POST["file_mode"]) ? $_POST["file_mode"] : "default";

if (isset($_POST["file_id"])) {
    $_file_id = $_POST["file_id"];
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

$target_image_size = $wpv_options->GetOption("target_image_size");
$ratio = min($target_image_size/$resultset[0]->file_image_width, $target_image_size/$resultset[0]->file_image_height);
$ratio = min($ratio, 1.0);
?>
<table id="wpv-image-view" colspan="0" border="0" cellspacing="0">
    <tr class="dialog-header" style="height: 25px">
    <td class="file-name"><?php echo $file_name; ?></td>
    <td class="close-button"><a class="close-button" href="javascript:void(0)" onclick="WpvAdmin.closeImageDisplay()" title="Close">&nbsp;X&nbsp;</a></td>
    </tr>
    <tr>
    <td colspan="2">
    <?php
    if ($error_message == "") {
    ?>
        <div id="wpv-image-loading">Loading image...</div>
        <img id="wpv-loaded-image" style="opacity: 0; filter:alpha(opacity=0); -khtml-opacity: 0; -moz-opacity: 0;" width="<?php echo $ratio * $resultset[0]->file_image_width; ?>" height="<?php echo $ratio * $resultset[0]->file_image_height; ?>" src="<?php echo get_bloginfo("siteurl"); ?>/?wpv_file_id=<?php echo $_file_id; ?>&file_mode=<?php echo $_file_mode; ?>&hash=<?php echo WpvUtils::GetHashCode($_file_id); ?>"/>
    <?php
    } 
    else {
    ?>
        <div id="wpv-image-loading"><?php echo $error_message; ?></div>
    <?php
    }
    ?>    
    </td>
    </tr>
</table>
