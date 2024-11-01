<?php
WpvUtils::VerifyWPVault();

$message = "";
$error_message = "";

require_once('admin.php');
require_once(dirname(__FILE__) . "/lib/wpv-function-file.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-tag.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-file2tag.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-image.php");
require_once(dirname(__FILE__) . "/lib/wpv-table-file.php");

if (!current_user_can("wpv_browse_own_files") && !current_user_can("wpv_browse_all_files"))
    WpvUtils::ShowPageNotFound();

$wpv_message = new WpvMessage();

// Delete selected file from the system.
if ($_POST["proc"] == "browse-file-delete") {
    delete_files($wpv_message);
}
else if ($_POST["proc"] == "browse-file-edit") {
    edit_files($wpv_message);
}
else if ($_POST["proc"] == "browse-tag-assign") {
    assign_tags($wpv_message);
}
else if ($_POST["proc"] == "browse-tag-unassign") {
    unassign_tags($wpv_message);
}

$wpv_message->WriteMessages();
?>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-file-browser"; ?>"></script>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-paging"; ?>"></script>

<div style="padding: 5px 5px 5px 5px">
<form name="file_browser_form" id="file-browser-form" action="" method="post" onsubmit="return false">
<div id="wpv-gray-out"></div>
<div id="wpv-modal-dialog"></div>

<?php
$element_array = array();
if (current_user_can("wpv_assign_files"))
	array_push($element_array, "<input type='button' value='Edit Files' onclick='openDialog(\"wpv_file_edit\")' />");
else
	array_push($element_array, "<input type='button' value='Rename Files' onclick='openDialog(\"wpv_file_edit\")' />");
array_push($element_array, "<input type='button' value='Assign Tags' onclick='openDialog(\"wpv_tag_assign\")' />");
array_push($element_array, "<input type='button' value='Unassign Tags' onclick='openDialog(\"wpv_tag_unassign\")' />");
array_push($element_array, "<input type='button' value='Delete Files' onclick='submitDelete()' />");
WpvFileTable::DisplayFileTable($element_array);
?>

<input type="hidden" name="proc" value="" />
<input type="hidden" name="page" value="wp-vault/wpv-file-browser.php" />
<input type="hidden" name="action" value=""/>
<input type="hidden" name="requestUri" value="<?php echo get_settings("siteurl")?>/wp-admin/admin-ajax.php"/>
<input type="hidden" name="cookie" value="" />

<div id="debug"></div>
</form>
</div>

<?php
function delete_files(&$wpv_message) {
    global $wpdb;
    global $wpv_file_table;
    global $wpv_post2file_table;
    global $wpv_file2tag_table;
    global $wpv_options;
    
    $_selected_file_id_array = isset($_POST["selected_file_id"]) ? $_POST["selected_file_id"] : array();
    $no_access_file_name_array = array();

    check_file_access($_selected_file_id_array, $no_access_file_name_array);

    if (count($no_access_file_name_array) > 0)
        $wpv_message->AddErrorMessageLine("Cannot delete because you are not authorized: " . implode($no_access_file_name_array, ","));

    if (count($_selected_file_id_array) > 0) {
        global $userdata;
        
        $delete_files = "";
        $delete_error_files = "";

        // Make sure not to delete files that are in use.
        $files = WpvFile::GetFileTable("file_id IN (" . implode($_selected_file_id_array, ",") . ")");
        
        foreach ($files as $filerow) {
            $posts = $wpdb->get_results("SELECT post_id, post_title, post_author FROM $wpv_post2file_table, $wpdb->posts posts WHERE file_id = $filerow->file_id AND posts.ID = post_id");
            
            if (count($posts) > 0) {
                $wpv_message->AddErrorMessageLine("Cannot delete because the file is linked: '$filerow->file_name'");
                foreach ($posts as $post) {
                    $wpv_message->AddErrorMessage("&nbsp;&nbsp;&raquo; Linked to post: <strong>\"$post->post_title\"</strong> (ID: $post->post_id)");
                    if ((current_user_can("wpv_access_own_posts") && $post->post_author == $userdata->ID) || current_user_can("wpv_access_all_posts"))
                        $wpv_message->AddErrorMessageLine(" -- <a href='" . get_bloginfo("siteurl") . "/wp-admin/admin.php?page=wp-vault/wpv-redirect.php&post_id=$post->post_id&proc=file-link-page'>View in Link Manager</a>");
                    else
                        $wpv_message->AddErrorMessageLine("");
                }
            }
            else {
                $wpdb->query("START TRANSACTION;");
                if ($wpdb->query("DELETE FROM $wpv_file_table WHERE file_id = $filerow->file_id") === FALSE) {
                    $delete_error_files .= " '$filerow->file_name',";
                    $wpdb->query("ROLLBACK;");
                    break;
                }
                else if ($wpdb->query("DELETE FROM $wpv_file2tag_table WHERE file_id = $filerow->file_id") === FALSE) {
                    $wpdb->query("ROLLBACK;");
                    $delete_error_files .= " '$filerow->file_name',";
                    break;
                }
                else {
                    foreach ($_selected_file_id_array as $selected_file_id) {
                        wp_cache_delete("file_data_$selected_file_id", "wp-vault");
                    }
                    
                    if (file_exists(WpvUtils::GetStoragePath() . $filerow->stored_name)) {
                        if (@unlink(WpvUtils::GetStoragePath() . $filerow->stored_name)) {
                            $wpdb->query("COMMIT;");
                            $delete_files .= " '$filerow->file_name',";
                        }
                        else {
                            $wpdb->query("ROLLBACK;");
                            $delete_error_files .= " '$filerow->file_name',";
                        }
                    }
                    if (file_exists(WpvUtils::GetStoragePath() . ".img/$filerow->stored_name")) {
                        @unlink(WpvUtils::GetStoragePath() . ".thumb/$filerow->stored_name");
                    }
                    if (file_exists(WpvUtils::GetStoragePath() . ".sys/$filerow->stored_name")) {
                        @unlink(WpvUtils::GetStoragePath() . ".sys/$filerow->stored_name");
                    }
                }
            }
        }

        if ($delete_error_files != "") {
            $wpv_message->AddErrorMessageLine("Failed to delete:" . rtrim($delete_error_files, ","));
        }
        if ($delete_files != "") {
            $wpv_message->AddMessageLine("Successfully deleted:" . rtrim($delete_files, ","));;
        }
    }
}

function edit_files(&$wpv_message) {
    global $wpdb;
    global $wpv_file_table;

    $_edit_file_id_array = $_POST["edit_file_id"];
    $no_access_file_name_array = array();
    $message = "";
    $error_message = "";
    $transaction_failed = FALSE;

    check_file_access($_edit_file_id_array, $no_access_file_name_array);

    if (count($no_access_file_name_array) > 0)
        $wpv_message->AddErrorMessageLine("Cannot rename because you are not authorized: " . implode($no_access_file_name_array, ","));;

    $wpdb->query("START TRANSACTION;");
    foreach ($_edit_file_id_array as $file_id) {
        if (isset($_POST["new_owner_id_$file_id"]) && isset($_POST["old_owner_id_$file_id"])) {
            $_new_owner_id = $wpdb->escape($_POST["new_owner_id_$file_id"]);
            $_old_owner_id = $_POST["old_owner_id_$file_id"];
            if ($_new_owner_id != $_old_owner_id) {
                if ($wpdb->query("UPDATE $wpv_file_table SET owner_id = $_new_owner_id WHERE file_id = $file_id") === FALSE) {
                    $transaction_failed = TRUE;
                    break;
                }
            }
        }

        if (isset($_POST["new_file_name_$file_id"]) && isset($_POST["old_file_name_$file_id"])) {
            $_new_file_name = $_POST["new_file_name_$file_id"];
            $_old_file_name = $_POST["old_file_name_$file_id"];
            if ($_new_file_name != $_old_file_name) {
                if (strtoupper($_new_file_name) != strtoupper($_old_file_name))
                    $_new_file_name = $wpdb->escape(WpvFile::GetUniqueFileName($_new_file_name));

                if ($wpdb->query("UPDATE $wpv_file_table SET file_name = '$_new_file_name' WHERE file_id = $file_id") === FALSE) {
                    $transaction_failed = TRUE;
                    break;
                }
            }
        }
    }
    if ($transaction_failed) {
        $wpdb->query("ROLLBACK;");
        $wpv_message->AddErrorMessageLine("Failed to edit.");
    }
    else {
        $wpdb->query("COMMIT;");
        foreach ($_edit_file_id_array as $edit_file_id) {
            wp_cache_delete("file_data_$edit_file_id", "wp-vault");
        }
        $wpv_message->AddMessageLine("Successfully edited.");
    }
}

function assign_tags(&$wpv_message) {
    global $wpdb;
    global $wpv_file_table;
    global $wpv_file2tag_table;

    $_selected_file_id_array = isset($_POST["selected_file_id"]) ? $_POST["selected_file_id"] : array();
    $_selected_tag_array = isset($_POST["selected_tag"]) ? $_POST["selected_tag"] : array();

    if (count($_selected_file_id_array) == 0) {
        $wpv_message->AddErrorMessageLine("You need to select files to assign tags to.");
    }
    else if (count($_selected_tag_array) == 0) {
        $wpv_message->AddErrorMessageLine("You need to select tags to assign.");
    }
    else {
        $no_access_file_name_array = array();

        check_file_access($_selected_file_id_array, $no_access_file_name_array);

        if (count($no_access_file_name_array) > 0)
            $wpv_message->AddErrorMessageLine("Cannot rename because you are not authorized: " . implode($no_access_file_name_array, ","));

        $tag_sql = "";
        $inserted_file_id_array = array();
        $dup_file_id_array = array();
        foreach ($_selected_tag_array as $selected_tag) {
            foreach ($_selected_file_id_array as $selected_file_id) {
                if (count(WpvFile2Tag::GetFile2TagTable("($wpv_file_table.file_id = $selected_file_id AND tag_id = $selected_tag)")) == 0) {
                    $tag_sql .= "($selected_file_id, $selected_tag),";
                    array_push($inserted_file_id_array, $selected_file_id);
                }
                else {
                    array_push($dup_file_id_array, $selected_file_id);
                }
            }
        }

        if (count($dup_file_id_array) > 0) {
            $dup_file_array = WpvFile::GetFileNameArray($dup_file_id_array);
            $wpv_message->AddErrorMessageLine("Some files already have specified tags assigned: '" . implode($dup_file_array, "', '") . "'");
        }

        if ($tag_sql != "") {
            $tag_sql = "INSERT INTO $wpv_file2tag_table (file_id, tag_id) VALUES $tag_sql";
            if ($wpdb->query(rtrim($tag_sql, ",")) === FALSE) {
                $wpv_message->AddErrorMessageLine("Failed to assign tags to files.");
            }
            else {
                $inserted_file_array = WpvFile::GetFileNameArray($inserted_file_id_array);
                $wpv_message->AddMessageLine("Successfully assigned tags to files: '" . implode($inserted_file_array, "', '") . "'");
            }
        }
    }
}

function unassign_tags(&$wpv_message) {
    global $wpdb;
    global $wpv_file2tag_table;

    $_selected_file_id_array = isset($_POST["selected_file_id"]) ? $_POST["selected_file_id"] : array();
    $_selected_tag = isset($_POST["selected_tag"]) ? implode($_POST["selected_tag"], ",") : "";

    check_file_access($_selected_file_id_array, $no_access_file_name_array);

    if (count($no_access_file_name_array) > 0)
        $wpv_message->AddErrorMessageLine("Cannot unassign tags because you are not authorized: " . implode($no_access_file_name_array, ","));

    if (count($_selected_file_id_array) == 0) {
        $wpv_message->AddErrorMessageLine("You need to select files to remove tags from.");
    }
    else if ($_selected_tag == "") {
        $wpv_message->AddErrorMessageLine("You need to select tags to remove.");
    }
    else {
        $delete_sql = "DELETE FROM $wpv_file2tag_table ";
        $delete_sql .= "WHERE $wpv_file2tag_table.file_id IN (" . implode($_selected_file_id_array, ",") . ") ";
        $delete_sql .= "AND tag_id IN ($_selected_tag)";
        if ($wpdb->query($delete_sql) === FALSE) {
            $wpv_message->AddErrorMessageLine("Failed to unassign tags.");
        }
        else {
            $removed_from_file_array = WpvFile::GetFileNameArray($_selected_file_id_array, FALSE);
            $removed_tag_array = WpvTag::GetTagNameArray(explode(",", $_selected_tag), TRUE);
            $wpv_message->AddMessageLine("Successfully unassigned tags - ");
            $wpv_message->AddMessageLine("&raquo; From files: '" . implode($removed_from_file_array, "', '") . "'");
            $wpv_message->AddMessageLine("&raquo; Removed tags: '" . implode($removed_tag_array, "', '") . "'");
        }
    }
}

function check_file_access(&$file_id_array, &$no_access_file_name_array) {
    global $wpdb;
    global $userdata;
    global $wpv_file_table;

    get_currentuserinfo();

    if (!current_user_can("wpv_browse_all_files")) {
        $files = $wpdb->get_results("SELECT file_id, file_name, stored_name, owner_id FROM $wpv_file_table WHERE file_id IN (" . implode($file_id_array, ", ") . ")");
        $file_id_array = array();
        foreach ($files as $filerow) {
            if ($userdata->ID === $filerow->owner_id && current_user_can("wpv_browse_own_files")) {
                array_push($file_id_array, $filerow->file_id);
            }
            else {
                array_push($no_access_file_name_array, "'$filerow->file_name'");
            }
        }
    }
}
?>
