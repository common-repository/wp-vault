<?php
WpvUtils::VerifyWPVault();

require_once('admin.php');
require_once(dirname(__FILE__) . "/lib/wpv-function-tag.php");
require_once(dirname(__FILE__) . "/lib/wpv-table-tag.php");

if (!current_user_can("wpv_upload_files"))
    die;

$wpv_message = new WpvMessage();

// Insert uploaded files.
if ($_POST["proc"] == "upload-get") {
    WpvUpload::ProcessUpload($wpv_message);
    
}

$wpv_message->WriteMessages();

$tag_table = WpvTag::GetTagTable();
?>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "/?wpv-js=wpv-quick-tag"; ?>"></script>
<div style="padding: 5px 5px 5px 5px">
<form name="upload-form" id="upload-form" method="post" action="" enctype="multipart/form-data">
<div class="submit">
<input type="submit" value="Upload" />
</div>
<div class="wpv-tab-interface">
    <span class="wpv-tab-button">File Upload</span>
    <div class="border" style="width: 570px">
    <table>
    <tr>
    <td>Files:</td>
    <td>Assign Tags:</td>
    </tr>
    <tr>
    <td rowspan="2" style="vertical-align: top; ">
        <div style="border: 1px solid #606060; padding: 5px 5px 5px 5px;">
        <input type="file" name="files[]" /><br />
        <input type="file" name="files[]" /><br />
        <input type="file" name="files[]" /><br />
        <input type="file" name="files[]" /><br />
        <input type="file" name="files[]" /><br />
        </div>
    </td>
    <td style="vertical-align: top;">
        <div id="tag-table" style="border: 1px solid #606060; width: 300px;">
        <?php
        WpvTagTable::DisplayTagTable($tag_table, "width: 285px; height: 250px; border: 0px;", "checkbox", "selected_tag");
        ?>
        </div>
    </td>
    </tr>
    
    <?php
    if (current_user_can("wpv_edit_tags")) {
    ?>
        <tr>
        <td>
        Quick add tags:
        <div id="quick-add-tags">
            <input type="text" name="new_tags" value="" size="20"/>
            <span class="submit"><input type="button" value="Quick Add" onclick="addTags(event, this.form)" /></span>
        </div>
        </td>
        </tr>
    <?php
    }
    ?>
    </table>
    </div>
</div>
<div class="submit">
<input type="submit" value="Upload" />
</div>

<input type="hidden" name="proc" value="upload-get" />
<input type="hidden" name="page" value="wp-vault/wpv-file-upload.php" />
</form>
</div>

<?php
class WpvUpload {
    function ProcessUpload(&$wpv_message) {
        global $wpdb;
        global $wpv_options;
        global $userdata;
        global $wpv_file_table;
        global $wpv_file2tag_table;
        
        require_once(dirname(__FILE__) . "/lib/wpv-function-file.php");
        require_once(dirname(__FILE__) . "/lib/wpv-function-image.php");

        $_selected_tag_array = isset($_POST["selected_tag"]) ? $_POST["selected_tag"] : array();

        $stored_name_array = array();
        $file_name_array = array();
        $insert_sql = "INSERT INTO $wpv_file_table ";
        $insert_sql .= "(file_name, file_ext, stored_datetime, stored_name, mime_type, owner_id, file_size, file_image_width, file_image_height) ";
        $insert_sql .= " VALUES ";
        $file_count = 0;

        // Process all files.
        foreach ($_FILES["files"]["error"] as $key => $error) {
            if ($error == UPLOAD_ERR_OK) {
                $tmp_name = $_FILES["files"]["tmp_name"][$key];
                $name = basename($_FILES["files"]["name"][$key]);
                $filetype_array = wp_check_filetype($name);
                $mime_type = $filetype_array["type"];
                $file_ext = $filetype_array["ext"];
                if (!$mime_type) {
                    $mime_type = "application/octet-stream";
                    $file_ext = WpvUtils::GetFileExtension($name);
                }
                $stored_name = WpvUtils::GetUniqueStoredName(WpvUtils::GetStoragePath());

                // Process file data.
                if (move_uploaded_file($_FILES['files']['tmp_name'][$key], WpvUtils::GetStoragePath() . $stored_name)) {
                    $name = WpvUtils::RemoveFileExtension($name);
                    $file_size = filesize(WpvUtils::GetStoragePath() . $stored_name);
                    $file_image_width = 0;
                    $file_image_height = 0;
                    chmod(WpvUtils::GetStoragePath() . $stored_name, 0600);
                    array_push($stored_name_array, $stored_name);

                    // Make sure that the file name is unique.  If not, add number to make it unique.
                    $name = WpvFile::GetUniqueFileName($name, $file_name_array);

                    if (WpvImage::IsSupportedImage($mime_type)) {
                        $image_source = WpvImage::GetImageResource(WpvUtils::GetStoragePath() . $stored_name, $mime_type);
                        $file_image_width = imagesx($image_source);
                        $file_image_height = imagesy($image_source);
                    }
                    $insert_sql .= "('$name', '$file_ext', NOW(), '$stored_name', '$mime_type', $userdata->ID, $file_size, $file_image_width, $file_image_height),";
                    array_push($file_name_array, $name);
                } else {
                    foreach ($stored_name_array as $stored_name) {
                        if (file_exists(WpvUtils::GetStoragePath() . $stored_name))
                            unlink(WpvUtils::GetStoragePath() . $stored_name);
                    }
                    $wpv_message->AddErrorMessageLine("Possible file upload attack!");
                    return;
                }
                $file_count++;
            }
        }

        // Process files, if there are valid uploaded files.
        if ($file_count > 0) {
            if ($wpdb->query(rtrim($insert_sql, ",")) === FALSE) {
                foreach ($stored_name_array as $stored_name) {
                    if (file_exists(WpvUtils::GetStoragePath() . $stored_name))
                        unlink(WpvUtils::GetStoragePath() . $stored_name);
                }
                $wpv_message->AddErrorMessageLine("Upload failed.");
                return;
            }
            else {
                $wpv_message->AddMessage("Successfully uploaded: ");
                $wpv_message->AddMessageLine(" '" . implode($file_name_array, "', '") . "'");

                if (count($_selected_tag_array) > 0) {
                    $stored_id_array = WpvFile::GetFileIdArray($stored_name_array);
                    $tag_sql = "INSERT INTO $wpv_file2tag_table ";
                    $tag_sql .= "(file_id, tag_id) ";
                    $tag_sql .= "VALUES ";
                    foreach ($_selected_tag_array as $selected_tag) {
                        foreach ($stored_id_array as $stored_id) {
                            $tag_sql .= "($stored_id, $selected_tag),";
                        }
                    }

                    if ($wpdb->query(rtrim($tag_sql, ",")) === FALSE) {
                        $wpv_message->AddErrorMessageLine("Failed to assign tags to uploaded files.");
                    }
                }
            }
        }
        return;
    }
}
?>

