<?php
WpvUtils::VerifyWPVault();

if (!current_user_can("wpv_get_http_files"))
    WpvUtils::ShowPageNotFound();

require_once('admin.php');
if (isset($_POST["proc"]) && $_POST["proc"] == "http_get_files") {
    $wpv_message = new WpvMessage();
    WpvHttpGet::processGet($wpv_message);
    $wpv_message->WriteMessages();
}
?>

<div style="padding: 5px 5px 5px 5px">
<form name="wpv_http_get" id="wpv-http-get" action="" method="post">
<div class="submit"><input type="button" value="Get File(s)" onclick="this.form.submit()"/></div>
<div class="wpv-tab-interface">
    <span class="wpv-tab-button">HTTP File Get</span>
    <div class="border" style="padding: 20px 20px 20px 20px; width: 500px;">
        Copy & paste the URLs of the files you want to store into the text box:
        <div style="border: 1px solid #606060; padding: 5px 5px 5px 5px;">
            <input type="text" name="get_files[]" value="" size="60" /><br />
            <input type="text" name="get_files[]" value="" size="60" /><br />
            <input type="text" name="get_files[]" value="" size="60" /><br />
            <input type="text" name="get_files[]" value="" size="60" /><br />
            <input type="text" name="get_files[]" value="" size="60" /><br />
            <input type="hidden" name="proc" value="http_get_files" />
            <input type="hidden" name="page" value="wp-vault/wpv_http_get.php" />
        </div>
    </div>
</div>
<div class="submit"><input type="button" value="Get File(s)" onclick="this.form.submit()"/></div>
</form>
</div>

<?php
class WpvHttpGet {
    function ProcessGet(&$wpv_message) {
        global $wpdb;
        global $wpv_options;
        global $userdata;
        global $wpv_file_table;
        global $wpv_file2tag_table;
        
        require_once(dirname(__FILE__) . "/lib/wpv-function-file.php");
        require_once(dirname(__FILE__) . "/lib/wpv-function-image.php");

        $stored_file_array = array();
        $file_name_array = array();
        $successful_file_array = array();
        $insert_sql = "INSERT INTO $wpv_file_table ";
        $insert_sql .= "(file_name, file_ext, stored_datetime, stored_name, mime_type, owner_id, file_size, file_image_width, file_image_height) ";
        $insert_sql .= " VALUES ";

        // Process all files.
        if (!isset($_POST["get_files"])) {
            $wpv_message->AddMessageLine("No file to get.");
            return;
        }
        
        foreach ($_POST["get_files"] as $file_url) {
            $file_url = trim($file_url);
            if ($file_url != "") {
                if (preg_match("/^[a-zA-Z]+\:[\/]{2}/", $file_url) == 0) {
                    $file_url = "http://$file_url";
                }
                $file_name = WpvHttpGet::GetFileName($file_url);
                $filetype_array = wp_check_filetype($file_name);
                $mime_type = $filetype_array["type"];
                $file_ext = $filetype_array["ext"];
                $file_image_width = 0;
                $file_image_height = 0;

                if (!$mime_type) {
                    $mime_type = "application/octet-stream";
                    $file_ext = WpvUtils::GetFileExtension($file_name);
                }
            
                $stored_name = WpvUtils::GetUniqueStoredName(WpvUtils::GetStoragePath());
                $stored_full_file_name = WpvUtils::GetStoragePath() . $stored_name;
                array_push($stored_file_array, $stored_full_file_name);
                
                $read_mode = WpvUtils::IsAscii($file_name) ? "r" : "rb";
                if (($http_fp = @fopen($file_url, $read_mode)) !== FALSE) {
                    $content = "";
                    while (!@feof($http_fp)) {
                        $content .= @fgets($http_fp, 4096);
                    }                
                    @fclose($http_fp);
                    if (($write_fp = @fopen($stored_full_file_name, "w")) !== FALSE) {
                        @fwrite($write_fp, $content);
                        @fclose($write_fp);
                        chmod($stored_full_file_name, 0600);
                        array_push($successful_file_array, $file_url);
                    }
                    else {
                        $wpv_message->AddErrorMessageLine("Failed to write file: $file_url");
                        $stored_name = "";
                    }
                }
                else {
                    $wpv_message->AddErrorMessageLine("Failed to open file: $file_url");
                    $stored_name = "";
                }
                
                if ($stored_name != "") {
                    $file_name = WpvUtils::RemoveFileExtension($file_name);
                    $file_size = filesize($stored_full_file_name);
                    
                    if (WpvImage::IsSupportedImage($mime_type)) {
                        $image_source = WpvImage::GetImageResource($stored_full_file_name, $mime_type);
                        $file_image_width = imagesx($image_source);
                        $file_image_height = imagesy($image_source);
                    }
                    $file_name = WpvFile::GetUniqueFileName($file_name, $file_name_array);
                    $insert_sql .= "('$file_name', '$file_ext', NOW(), '$stored_name', '$mime_type', $userdata->ID, $file_size, $file_image_width, $file_image_height),";
                    array_push($file_name_array, $file_name);
                }
            }
        }    
        if (count($successful_file_array) > 0) {
            if ($wpdb->query(rtrim($insert_sql, ",")) === FALSE) {
                foreach ($stored_file_array as $stored_file) {
                    if (file_exists($stored_file))
                        unlink($stored_file);
                }
                $wpv_message->AddErrorMessageLine("HTTP Get Failed.");
                return;
            }
            else {
                $wpv_message->AddMessage("HTTP Get Successful: ");
                $wpv_message->AddMessageLine(" '" . implode($successful_file_array, "', '") . "'");
            }
        }
        return;
    }
    
    function GetFileName($url) {
        $url = trim($url);
        
        if (preg_match("/^http:[\/]{2}/", $url) == 0) {
            return WpvHttpGet::GetFileName("http://$url");
        }
        else if (preg_match("/[\/]+$/", $url) > 0) {
            return "Unknown";
        }
        else if (preg_match("/([^\/]+)$/", $url, $match) > 0) {
            return $match[1];
        }
        else {
            return "Unknown";
        }
    }
}
?>