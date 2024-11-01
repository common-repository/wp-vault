<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function-post2file.php");

$_post_id = $_POST["post_id"];
$_selected_post_file_id_array = $_POST["selected_post_file_id"];

$post_file_table = array();
$max_row = 20;
if (count($_selected_post_file_id_array) > 0) {
    global $wpv_file_table;
    
    $post_file_table = WpvPost2File::GetPost2FileTable($_post_id);
}
?>
<div class="dialog-header" style="width: 320px">
Move selected after...
</div>
<div class="dialog-content">
<div class="submit" style="width: 300px">
    <?php
    if (count($_selected_post_file_id_array) > 0) {
    ?>
        <input type='button' value='Save' onclick='submitSaveSequence(this.form);' />
    <?php
    }
    ?>
    <input type='button' value='Cancel' onclick='WpvDialog.closeDialog();' />
</div>
<?php
$i = 1;
echo "<table style='vertical-align: top; border: 1px solid #c0c0c0; width: 300px;'>";

if (count($_selected_post_file_id_array) > 0) {
    foreach ($post_file_table as $post_file_data) {
        if ($i == 1) {
            echo "<tr>";
            echo "<td>";
            echo "<label for='post3file-seq-radio-0'><input type='radio' name='destination_sequence' id='post3file-seq-radio-0' value='0' checked/></label>";
            echo "</td>";
            echo "<td>";
            echo "</td>";
            echo "<td>";
            echo "<label for='post3file-seq-radio-0'>Move to First</label>";
            echo "</td>";
            echo "</tr>";
            echo "<tr>";
            echo "<td colspan='3' style='text-align: center'>--</td>";
            echo "</tr>";
        }
        
        echo "<tr>";
        if (in_array($post_file_data->file_id, $_selected_post_file_id_array)) {
            echo "<td style='width: 10px'>";
            echo "</td>";
            echo "<td style='text-align: right; padding: 0px 10px 0px 0px; width: 30px; color: #c0c0c0'>";
            echo "<strong>#$post_file_data->sequence_num</strong>";
            echo "</td>";
            echo "<td style='overflow: hidden; white-space: nowrap; width: 160px; color: #c0c0c0'>";
            echo "$post_file_data->file_name";
            echo "</td>";
        }
        else {
            echo "<td style='width: 10px'>";
            echo "<label for='post3file-seq-radio-$post_file_data->sequence_num'><input type='radio' name='destination_sequence' id='post3file-seq-radio-$post_file_data->sequence_num' value='$post_file_data->sequence_num' /></label>";
            echo "</td>";
            echo "<td style='text-align: right; padding: 0px 10px 0px 0px; width: 30px;'>";
            echo "<strong><label for='post3file-seq-radio-$post_file_data->sequence_num'>#$post_file_data->sequence_num</label></strong>";
            echo "</td>";
            echo "<td style='overflow: hidden; white-space: nowrap; width: 160px'>";
            echo "<label for='post3file-seq-radio-$post_file_data->sequence_num'>$post_file_data->file_name</label>";
            echo "</td>";
        }
        echo "</tr>";
        $i++;
    }
}
else {
    echo "<td style='width: 200px'>No files were selected.</td>";
}

echo "</table>";
?>

<div class="submit" style="width: 300px">
    <?php
    if (count($_selected_post_file_id_array) > 0) {
    ?>
        <input type='button' value='Save' onclick='submitSaveSequence(this.form);' />
    <?php
    }
    ?>
    <input type='button' value='Cancel' onclick='WpvDialog.closeDialog();' />
</div>
</div>