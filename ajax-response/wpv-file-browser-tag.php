<?php
if (current_user_can("wpv_edit_own_files") || current_user_can("wpv_edit_all_files")) {
}
else {
    exit;
}

require_once(dirname(__FILE__) . "/../lib/wpv-function-tag.php");
require_once(dirname(__FILE__) . "/../lib/wpv-table-tag.php");


$_selected_file_id_array = isset($_POST["selected_file_id"]) ? $_POST["selected_file_id"] : array();
$message = "";

if ($_POST["action"] == "wpv_tag_assign") {
    $title = "Assign tags to selected files";
    $button_text = "Assign Tags";
    $onclick = "assignTags()";
}
else if ($_POST["action"] == "wpv_tag_unassign") {
    $title = "Unassign tags from selected files";
    $button_text = "Unassign Tags";
    $onclick = "unassignTags()";
}
else {
    die -1;
}

if (count($_selected_file_id_array) == 0) {
    $message = "No files were selected.";
}
else if ($_POST["action"] == "wpv_tag_assign") {
    $tag_table = WpvTag::GetTagTable();
}
else if ($_POST["action"] == "wpv_tag_unassign") {
    require_once(dirname(__FILE__) . "/../lib/wpv-function-file2tag.php");
    
    $tag_table = WpvFile2Tag::GetUsedFile2TagNameTable("file_id IN (" . implode(",", $_selected_file_id_array) . ")");
}
?>
<div class="dialog-header" style="width: 320px">
<?php echo $title; ?>
</div>
<div class="dialog-content">
<div class="submit">
    <?php
    if ($message == "" && count($tag_table) > 0) {
    ?>
        <input type="button" value="<?php echo $button_text; ?>" onclick="<?php echo $onclick; ?>" />
    <?php
    }
    ?>
    <input type="button" value="Cancel" onclick="closeDialog()" />
</div>

<?php
if ($message == "")
    WpvTagTable::DisplayTagTable($tag_table, "width: 300px; height: 350px;", "checkbox", "selected_tag");
else {
    echo "<div style='width: 300px'>";
    echo $message;
    echo "</div>";
}
?>

<div class="submit">
    <?php
    if ($message == "" && count($tag_table) > 0) {
    ?>
        <input type="button" value="<?php echo $button_text; ?>" onclick="<?php echo $onclick; ?>" />
    <?php
    }
    ?>
    <input type="button" value="Cancel" onclick="closeDialog()" />
</div>
</div>
