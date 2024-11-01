<?php
require_once(dirname(__FILE__) . "/lib/wpv-function-file.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-post2file.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-tag.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-file2tag.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-image.php");
require_once(dirname(__FILE__) . "/lib/wpv-table-file.php");
require_once(dirname(__FILE__) . "/lib/wpv-table-post2file.php");

$_post_id = $_POST["post_id"];
$wpv_message = new WpvMessage();

if (!isset($_POST["proc"])) {
    echo "Invalid Request";
    die;
}
else if ($_POST["proc"] == "file-selection-page") {
}
else if ($_POST["proc"] == "file-selection-update") {
    link_files($_post_id, $wpv_message);
}
else {
    echo "Improper Parameters. <br />";
    die;
}

$wpv_message->WriteMessages();

include(dirname(__FILE__) . "/wpv-link-manager-header.php");
?>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-link-manager-file"; ?>"></script>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-paging"; ?>"></script>

<div style="float: left">
    <form name="file_form" id="file-form" action="" method="post" onsubmit="return false">
    <div id="wpv-modal-dialog"></div>
    <div id="wpv-gray-out"></div>
    <?php
    $elem_array = array();
    array_push($elem_array, "<input type='button' value='Link Selected' onclick='submitLink(this.form)' />");
    WpvFileTable::DisplayFileTable($elem_array);
    ?>
    <input type="hidden" name="proc" value="file-selection-page" />
    <input type="hidden" name="page" value="wp-vault/wpv-link-manager.php" />
    <input type="hidden" name="post_id" value="<?php echo $_post_id; ?>" />
    <input type="hidden" name="action" value=""/>
    <input type="hidden" name="cookie" value="" />
    <input type="hidden" name="requestUri" value="<?php echo get_settings("siteurl")?>/wp-admin/admin-ajax.php"/>
    <input type="hidden" name="no_cookie" value="no_cookie" />
    </form>
</div>

<div style="float: left">
    <?php
    $post2file_table = WpvPost2File::GetPost2FileTable($_post_id);
    WpvPost2FileTable::DisplayBriefPost2FileTable($post2file_table);
    ?>
</div>
<?php

function link_files($post_id, &$wpv_message) {
    if (count($_POST["selected_file_id"]) > 0) {
        global $wpdb;
        global $wpv_options;
        global $wpv_post2file_table;
        
        require_once(dirname(__FILE__) . "/lib/wpv-function-display-option.php");
        // Get starting sequence number by getting the count of current data.
        $start_sequence_num = WpvPost2File::GetPost2FileCount($post_id) + 1;

        // Do insert.
        $wpdb->query("START TRANSACTION;");
        $display_option = WpvDisplayOption::GetDisplayOption($post_id, TRUE);
        if ($display_option !== FALSE) {
            $insert_sql = "INSERT INTO $wpv_post2file_table ";
            $insert_sql .= "(post_id, file_id, added_datetime, last_update_datetime, sequence_num) ";
            $insert_sql .= "VALUES ";
            for ($i = 0; $i < count($_POST["selected_file_id"]); $i++) {
                $file_id = $_POST["selected_file_id"][$i];
                $insert_sql .= "($post_id, $file_id, NOW(), NOW(), $start_sequence_num+$i),";
            }
            if ($wpdb->query(rtrim($insert_sql, ",")) === FALSE) {
                $wpdb->query("ROLLBACK;");
                $wpv_message->AddErrorMessageLine("Link failed because of a database error");
                return;
            }
            else if (WpvPost2File::UpdateFileSequence($post_id) === FALSE) {
                $wpdb->query("ROLLBACK;");
                $wpv_message->AddErrorMessageLine("Link failed because of a database error");
                return;
            }
            else {
                $wpdb->query("COMMIT;");
                $file_name_array = WpvFile::GetFileNameArray($_POST["selected_file_id"]);
                $wpv_message->AddMessage("Successfully linked: ");
                $wpv_message->AddMessageLine(" '" . implode($file_name_array, "', '") . "'");
                wp_cache_delete("post2file_table_$post_id", "wp-vault");
            }
        }
        else {
            $wpdb->query("ROLLBACK;");
            $wpv_message->AddErrorMessageLine("Link failed because of a database error");
        }
    }
}
?>
