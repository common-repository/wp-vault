<?php
require_once(dirname(__FILE__) . "/lib/wpv-function-post2file.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-image.php");
require_once(dirname(__FILE__) . "/lib/wpv-table-post2file.php");

$_post_id = $_POST["post_id"];
$wpv_message = new WpvMessage();

if (!isset($_POST["proc"])) {
    echo "Invalid Request";
    die;
}
else if ($_POST["proc"] == "file-link-page") {
}
else if ($_POST["proc"] == "file-link-unlink") {
    unlink_files($_post_id, $wpv_message);
}
else if ($_POST["proc"] == "file-link-update") {
    update_post2file_info($_post_id, $wpv_message);
}
else if ($_POST["proc"] == "file-link-sequence") {
    update_sequence($_post_id, $wpv_message);
}
else {
    echo "Improper Parameters. <br />";
    die;
}

$wpv_message->WriteMessages();

include(dirname(__FILE__) . "/wpv-link-manager-header.php");
?>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-link-manager-filelink"; ?>"></script>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-paging"; ?>"></script>

<div style="float: left">
    <form name="post2file_form" id="post2file-form" action="" method="post" onsubmit="return false">
    <div id="wpv-modal-dialog"></div>
    <div id="wpv-gray-out"></div>
    <?php
    $elem_array = array();
    array_push($elem_array, "<input type='button' value='Unlink Selected' onclick='submitUnlink(this.form)' />");
    array_push($elem_array, "<input type='button' value='Edit Selected' onclick='openPost2FileEditDialog()' />");
    array_push($elem_array, "<input type='button' value='Move Selected' onclick='openPost2FileSequenceDialog()' />");
    $post2file_table = WpvPost2File::GetPost2FileTable($_post_id);
    WpvPost2FileTable::DisplayPost2FileTable($post2file_table, $elem_array);
    ?>
    <input type="hidden" name="proc" value="file-link-page" />
    <input type="hidden" name="page" value="wp-vault/wpv-link-manager.php" />
    <input type="hidden" name="post_id" value="<?php echo $_post_id; ?>" />
    <input type="hidden" name="requestUri" value="<?php echo get_settings("siteurl")?>/wp-admin/admin-ajax.php"/>
    <input type="hidden" name="action" value="wpv_post2file_edit"/>
    <input type="hidden" name="cookie" value="" />
    <input type="hidden" name="no_cookie" value="no_cookie" />
    <div id="debug"></div>
    </form>
</div>
    

<?php
function unlink_files($post_id, &$wpv_message) {
    $_selected_file_id_array = isset($_POST["selected_post_file_id"]) ? $_POST["selected_post_file_id"] : array();
    if (count($_selected_file_id_array) > 0) {
        global $wpdb;
        global $wpv_post2file_table;

        // Get file name and stored file name before actually deleting.
        $file_name_array = array();
        $stored_name_array = array();
        $file_table = WpvPost2File::GetPost2FileTable($post_id, "$wpv_post2file_table.file_id IN (" . implode(", ", $_selected_file_id_array) . ")");
        foreach ($file_table as $file_data) {
            array_push($file_name_array, $file_data->file_name);
            array_push($stored_name_array, $file_data->stored_name);
        }

        // Delete data.
        $wpdb->query("START TRANSACTION;");
        $delete_sql = "DELETE FROM $wpv_post2file_table ";
        $delete_sql .= "WHERE file_id IN (" . implode(", ", $_selected_file_id_array) . ")";
        if ($wpdb->query($delete_sql) === FALSE) {
            $wpdb->query("ROLLBACK;");
            $wpv_message->AddErrorMessageLine("Failed to unlink because of a database error.");
            return;
        }
        
        if (WpvPost2File::GetPost2FileCount($post_id) == 0) {
            global $wpv_display_option_table;
            
            $delete_sql = "DELETE FROM $wpv_display_option_table WHERE post_id = $post_id";
            if ($wpdb->query($delete_sql) === FALSE) {
                $wpdb->query("ROLLBACK;");
                $wpv_message->AddErrorMessageLine("Failed to unlink because of a database error.");
                return;
            }            
            wp_cache_delete("display_option_$post_id", "wp-vault");
        }
        else if (WpvPost2File::UpdateFileSequence($post_id) === FALSE) {
            $wpdb->query("ROLLBACK;");
            $wpv_message->AddErrorMessageLine("Failed to update sequence # because of a database error.");
            return;
        }
        
        $error_message = "";
        // Delete thumbnail images and cached files from the file system.
        if (WpvUtils::GetImageCachePath($error_message) !== FALSE) {
            foreach ($stored_name_array as $stored_name) {
                $thumbnail_file_path = "";
                $cached_file_path = "";
                if (($thumbnail_file_path = WpvUtils::GetThumbnailFilePath($post_id, $stored_name)) !== FALSE) {
                    if (file_exists($thumbnail_file_path)) {
                        unlink($thumbnail_file_path);
                    }
                }
                if (($cached_file_path = WpvUtils::GetCachedFilePath($post_id, $stored_name)) !== FALSE) {
                    if (file_exists($cached_file_path)) {
                        unlink($cached_file_path);
                    }
                }
            }

            $wpdb->query("COMMIT;");

            $wpv_message->AddMessage("Successfully unlinked:");
            $wpv_message->AddMessageLine(" '" . implode($file_name_array, "', '") . "'");
            wp_cache_delete("post2file_table_$post_id", "wp-vault");
        }
        else {
            $wpdb->query("ROLLBACK;");
            $wpv_message->AddErrorMessageLine($error_message);
        }
    }
    return;
}

function update_post2file_info($post_id, &$wpv_message) {
    global $wpdb;
    global $wpv_post2file_table;

    require_once(dirname(__FILE__) . "/lib/wpv-function-html-checker.php");

    $_selected_file_id_array = $_POST["selected_post_file_id"];
    $message = "";
    $error_message = "";

    $post2file_table = WpvPost2File::GetPost2FileTable($post_id, "$wpv_post2file_table.file_id IN (" . join(",", $_selected_file_id_array) . ")");

    foreach ($post2file_table as $row) {
        if (isset($_POST["action_type_$row->file_id"]) && isset($_POST["comment_text_$row->file_id"])) {
            $_comment_text = $wpdb->escape(WpvHtmlChecker::CleanHtmlTags($_POST["comment_text_$row->file_id"]));
            $_action_type = $_POST["action_type_$row->file_id"];
            if ($row->comment_text != $_comment_text || $row->action_type != $_action_type) {
                $wpdb->query("START TRANSACTION;");
                $update_sql = "UPDATE $wpv_post2file_table ";
                $update_sql .= "SET comment_text = '$_comment_text', action_type = '$_action_type', last_update_datetime = NOW() ";
                $update_sql .= "WHERE post_id = $post_id AND file_id = $row->file_id";
                if ($wpdb->query($update_sql) === FALSE) {
                    $wpdb->query("ROLLBACK;");
                    $wpv_message->AddErrorMessageLine("Failed to update info: '$row->file_name' (File ID: $row->file_id)");
                    return;
                }
                else {
                    if ($row->action_type != $_action_type) {
                        $thumbnail_file_path = WpvUtils::GetThumbnailFilePath($post_id, $row->stored_name);
                        if (file_exists($thumbnail_file_path)) {
                            if (unlink($thumbnail_file_path) === FALSE) {
                                $wpdb->query("ROLLBACK;");
                                $wpv_message->AddMessageLine("Failed to delete thumbnail file: '$row->file_name' (File ID: $row->file_id)");
                                return;
                            }
                        }
                    }
                    $wpdb->query("COMMIT;");
                    $wpv_message->AddMessageLine("Updated info: '$row->file_name' (File ID: $row->file_id)");
                    wp_cache_delete("post2file_table_$post_id", "wp-vault");
                }
            }
        }
    }
    return;
}

function update_sequence($post_id, &$wpv_message) {
    global $wpdb;
    global $wpv_post2file_table;
    
    $_selected_file_id_array = $_POST["selected_post_file_id"];
    $_destination_sequence = $_POST["destination_sequence"];
    
    $old_sequence_table = array();

    $post2file_table = WpvPost2File::GetPost2FileTable($post_id, "$wpv_post2file_table.file_id IN (" . join(",", $_selected_file_id_array) . ")");

    // Store the old sequence number to display upon success.
    foreach ($post2file_table as $file_data) {
        $old_sequence_table["$file_data->file_id"] = $file_data->sequence_num;
    }

    $wpdb->query("START TRANSACTION;");
    $update_sql = "UPDATE $wpv_post2file_table SET sequence_num = sequence_num + " . count($_selected_file_id_array) . " ";
    $update_sql .= "WHERE sequence_num > $_destination_sequence";

    if ($wpdb->query($update_sql) === FALSE) {
        $wpdb->query("ROLLBACK;");
        $wpv_message->AddErrorMessageLine("Failed to update file sequence.");
        return;
    }

    $i = $_destination_sequence + 1;
    foreach ($post2file_table as $file_data) {
        $update_sql = "UPDATE $wpv_post2file_table SET sequence_num = $i ";
        $update_sql .= "WHERE file_id = $file_data->file_id";
        
        if ($wpdb->query($update_sql) === FALSE) {
            $wpdb->query("ROLLBACK;");
            $wpv_message->AddErrorMessageLine("Failed to update sequence #.");
            return;
        }
        $i++;
    }
    if (WpvPost2File::UpdateFileSequence($post_id) === FALSE) {
        $wpdb->query("ROLLBACK;");
        $wpv_message->AddErrorMessageLine("Failed to update file sequence.");
        return;
    }

    $wpdb->query("COMMIT;");
    wp_cache_delete("post2file_table_$post_id", "wp-vault");

    $post2file_table = WpvPost2File::GetPost2FileTable($post_id, "$wpv_post2file_table.file_id IN (" . join(",", $_selected_file_id_array) . ")");
    $wpv_message->AddMessageLine("Successfully Updated - ");
    foreach ($post2file_table as $file_data) {
        $wpv_message->AddMessageLine("&raquo; '$file_data->file_name': #" . $old_sequence_table["$file_data->file_id"] . " <strong>&rarr;</strong> #$file_data->sequence_num");
    }
    return;
}
?>
