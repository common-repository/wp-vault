<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function-file.php");
require_once(dirname(__FILE__) . "/../lib/wpv-table-file.php");

if (!current_user_can("wpv_edit_own_files") && !current_user_can("wpv_edit_all_files")) {
    WpvUtils::ShowPageNotFound();
}

$_selected_file_id_array = $_POST["selected_file_id"];

$file_table = array();
$elem_array = array();
if (count($_selected_file_id_array) > 0) {
    $file_table = WpvFile::GetFileTable("file_id IN (" . implode(", ", $_selected_file_id_array) . ")");
    array_push($elem_array, "<input type='button' value='Save' onclick='submitEdit()' />");
}
array_push($elem_array, "<input type='button' value='Cancel' onclick='closeDialog()' />");
?>
<div class="dialog-header" style="width: 520px">
<?php echo current_user_can("wpv_assign_files") ? "Edit File Info" : "Rename File"; ?>
</div>
<div class="dialog-content">
<?php
WpvFileTable::DisplayFileEditTable($file_table, $elem_array);
?>
</div>
