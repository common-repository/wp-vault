<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function-post2file.php");
require_once(dirname(__FILE__) . "/../lib/wpv-table-post2file.php");

$_post_id = $_POST["post_id"];
$_selected_post_file_id_array = $_POST["selected_post_file_id"];

$post_file_table = array();
$elem_array = array();
if (count($_selected_post_file_id_array) > 0) {
    global $wpv_file_table;

    $post_file_table = WpvPost2File::GetPost2FileTable($_post_id, "$wpv_file_table.file_id IN (" . implode(", ", $_selected_post_file_id_array) . ")");
    array_push($elem_array, "<input type='button' value='Save Changes' onclick='submitSave(this.form)' />");
}
array_push($elem_array, "<input type='button' value='Cancel' onclick='WpvDialog.closeDialog();' />");
?>
<div class="dialog-header" style="width: 600px">
Edit Linked File Info
</div>
<div class="dialog-content">
<?php
WpvPost2FileTable::DisplayPost2FileEditTable($post_file_table, $elem_array);
?>
</div>