<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function-cookie.php");

if (!current_user_can("wpv_get_ftp_files"))
    WpvUtils::ShowPageNotFound();

$_command = $_POST["command"];

$conn_id = null;
$host = null;
$port = null;
$user_id = null;
$password = null;

if ($_command == "connect") {
    WpvFTPGetCookie::SetCookieData() or WpvFTP::WriteErrorMessage("Missing parameters.");

    $host = $_POST["host"];
    $port = $_POST["port"];
    $user_id = $_POST["user_id"];
    $password = $_POST["password"];
    
    $conn_id = @ftp_connect($host, $port) or WpvFTP::WriteErrorMessage("Unable to connect: $host:$port");
    @ftp_login($conn_id, $user_id, $password) or WpvFTP::WriteErrorMessage("Unable to login: $user_id");
}
else if ($_command == "chdir") {
    WpvFTPGetCookie::ResetCookieData() or WpvFTP::WriteMessage("Session expired.");

    list($user_id, $password, $host, $port) = WpvFTPGetCookie::GetCookieData();
    WpvFTP::VerifyParameters($host, $port, $user_id, $password) or WpvFTP::WriteErrorMessage("Parameters do not match.");
    $conn_id = @ftp_connect($host, $port) or WpvFTP::WriteErrorMessage("Unable to connect: $host:$port");
    @ftp_login($conn_id, $user_id, $password) or WpvFTP::WriteErrorMessage("Unable to login: $user_id");

    $dest_dir = $_POST["param"];
    if (@ftp_chdir($conn_id, $dest_dir) === FALSE) {
        $wpv_message = new WpvMessage();
        $wpv_message->AddErrorMessageLine("Unable to change directory to $dest_dir");
        $wpv_message->WriteMessages();
    }
}
else if ($_command == "get") {
    WpvFTPGetCookie::ResetCookieData() or WpvFTP::WriteMessage("Session expired.");

    list($user_id, $password, $host, $port) = WpvFTPGetCookie::GetCookieData();
    WpvFTP::VerifyParameters($host, $port, $user_id, $password) or WpvFTP::WriteErrorMessage("Parameters do not match.");
    $conn_id = @ftp_connect($host, $port) or WpvFTP::WriteErrorMessage("Unable to connect: $host:$port");
    @ftp_login($conn_id, $user_id, $password) or WpvFTP::WriteErrorMessage("Unable to login: $user_id");
    
    $current_dir = $_POST["param"];

    $wpv_message = new WpvMessage();
    WpvFTP::ProcessGet($conn_id, $current_dir, $wpv_message);
    $wpv_message->WriteMessages();
    
    @ftp_chdir($conn_id, $current_dir) or WpvFTP::WriteErrorMessage("Unable to change directory.");
}
else if ($_command == "close") {
    WpvFTPGetCookie::DeleteCookieData();
    WpvFTP::WriteMessage("Session closed.");
}
WpvFTP::DisplayList($conn_id);
@ftp_close($conn_id) or WpvFTP::WriteErrorMessage("Error while trying to close connection.");
?>
<input type="hidden" name="host" value="<?php echo md5($host . COOKIEHASH); ?>" />
<input type="hidden" name="port" value="<?php echo md5($port . COOKIEHASH); ?>" />
<input type="hidden" name="user_id" value="<?php echo md5($user_id . COOKIEHASH); ?>" />
<input type="hidden" name="password" value="<?php echo md5($password . COOKIEHASH); ?>" />
<?php
class WpvFTP {
    function DisplayList($conn_id) {
        $row_num = 0;
        $file_array = array();
        $dir_array = array();
        $link_array = array();
        
        $ls_array = ftp_rawlist($conn_id, ".");
        foreach ($ls_array as $line) {
            $regexp = "/";
            $regexp .= "([\-ltdrwxs]{10})"; 
            $regexp .= "\s+";
            $regexp .= "(\d+)";
            $regexp .= "\s+";
            $regexp .= "([\d\w\-_]+)";
            $regexp .= "\s+";
            $regexp .= "([\d\w\-_]+)";
            $regexp .= "\s+";
            $regexp .= "(\d+)";
            $regexp .= "\s+";
            $regexp .= "(";
            $regexp .= "\w{3}";
            $regexp .= "\s+";
            $regexp .= "\d{1,2}";
            $regexp .= "\s+";
            $regexp .= "[\d:]{4,5}";
            $regexp .= ")";
            $regexp .= "\s+";
            $regexp .= "(.+)";
            $regexp .= "/";
            
            if (preg_match($regexp, $line, $match) > 0) {
                if (preg_match("/^d/", $match[1]) > 0) {
                    array_push($dir_array, 
                        array(
                            "name" => $match[7],
                            "perms" => ltrim($match[1], "d"),
                            "num" => $match[2],
                            "user" => $match[3],
                            "group" => $match[4],
                            "size" => $match[5],
                            "date" => $match[6]
                        )
                    );
                }
                else if (preg_match("/^l/", $match[1]) > 0) {
                    array_push($link_array, 
                        array(
                            "name" => $match[7],
                            "perms" => ltrim($match[1], "l"),
                            "num" => $match[2],
                            "user" => $match[3],
                            "group" => $match[4],
                            "size" => $match[5],
                            "date" => $match[6]
                        )
                    );
                }
                else {
                    array_push($file_array, 
                        array(
                            "name" => $match[7],
                            "perms" => ltrim($match[1], "-"),
                            "num" => $match[2],
                            "user" => $match[3],
                            "group" => $match[4],
                            "size" => $match[5],
                            "date" => $match[6]
                        )
                    );
                }
            }
        }
        
        $current_dir = @ftp_pwd($conn_id);
        $current_dir = $current_dir == "/" ? $current_dir : $current_dir . "/";
        ?>
        <div class="submit">
            <input type="button" value="Get Files" onclick="WpvFTPGet.sendCommand(WpvFTPGet.FTP_GET, '<?php echo $current_dir; ?>')" <?php echo count($file_array) > 0 ? "" : "disabled"; ?> />
        </div>
        <table id="wpv-ftp-table" cellspacing="0" cellpadding="0" border="0">
            <tr>
                <td colspan="7" class="name">
                <?php
                if ($current_dir == "/") {
                    echo "<a href='javascript:WpvFTPGet.sendCommand(WpvFTPGet.FTP_CHDIR, \"/\")'>(root)</a>/";
                }
                else {
                    $pwd_array = split("/", $current_dir);
                    $pwd_link = "<a href='javascript:WpvFTPGet.sendCommand(WpvFTPGet.FTP_CHDIR, \"/\")'>(root)</a>/";
                    $pwd_sum = "";
                    foreach ($pwd_array as $pwd) {
                        if ($pwd != "") {
                            $pwd_sum .= "$pwd/";
                            $pwd_link .= "<a href='javascript:WpvFTPGet.sendCommand(WpvFTPGet.FTP_CHDIR, \"" . $pwd_sum . "\")'>" . $pwd . "</a>/";
                        }
                    }
                    echo $pwd_link;
                }
                ?>
                </td>
                <td>
                <?php
                if ($current_dir != "/") { 
                ?>
                    <strong style="white-space: nowrap"><a href="javascript:WpvFTPGet.sendCommand(WpvFTPGet.FTP_CHDIR, '<?php echo preg_replace("'\/[^\/]*?\/$'", "/", $current_dir); ?>')"><?php echo "&laquo; Up One Level" ?></a></strong>
                <?php
                }
                ?>
                </td>
            </tr>

        <?php
        if (count($dir_array) > 2) {
        ?>
            <tr>
                <td class="section" colspan="8">Directories</td>
            </tr>
            <tr class="header">
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td class="permission">Permissions</td>
                <td>Owner</td>
                <td>Group</td>
                <td class="size">Size</td>
                <td class="date">Date</td>
                <td>&nbsp;</td>
            </tr>
        <?php
        }
        foreach ($dir_array as $dir) {
        ?>
            <tr class="<?php echo $row_num++ % 2 == 0 ? "even-row" : "odd-row"; ?>">
                <?php
                if ($dir["name"] != "." && $dir["name"] != "..") {
                ?>
                    <td>&nbsp;&nbsp;</td>
                    <td class="name"><a href="javascript:WpvFTPGet.sendCommand(WpvFTPGet.FTP_CHDIR, '<?php echo $current_dir . $dir["name"]; ?>')"><?php echo $dir["name"]; ?></a></td>
                    <td class="permission"><?php echo $dir["perms"]; ?></td>
                    <td><?php echo $dir["user"]; ?></td>
                    <td><?php echo $dir["group"]; ?></td>
                    <td class="size"><?php echo $dir["size"]; ?></td>
                    <td class="date"><?php echo $dir["date"]; ?></td>
                    <td></td>
                <?php
                }
                ?>
            </tr>
        <?php
        }
        if (count($file_array) > 0) {
        ?>
            <tr>
                <td class="section" colspan="8">Files</td>
            </tr>
        <tr class="header">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="permission">Permissions</td>
            <td>Owner</td>
            <td>Group</td>
            <td class="size">Size</td>
            <td class="date">Date</td>
            <td>Binary?</td>
        </tr>
        <?php
        }
        foreach ($file_array as $file) {
        ?>
            <tr class="<?php echo $row_num++ % 2 == 0 ? "odd-row" : "even-row"; ?>">
                <td><input type="checkbox" id="checkbox-<?php echo $file["name"]; ?>" name="get_files[]" value="<?php echo $file["name"]; ?>"></td>
                <td class="name"><label for="checkbox-<?php echo $file["name"]; ?>"><?php echo $file["name"]; ?></label></td>
                <td class="permission"><?php echo $file["perms"]; ?></td>
                <td><?php echo $file["user"]; ?></td>
                <td><?php echo $file["group"]; ?></td>
                <td class="size"><?php echo $file["size"]; ?></td>
                <td class="date"><?php echo $file["date"]; ?></td>
                <td><input type="checkbox" name="is_bin_<?php echo md5($file["name"]); ?>" value="true" <?php echo !WpvUtils::IsAscii($file["name"]) ? "checked" : ""; ?> /></td>
            </tr>
        <?php
        }
        if (count($link_array) > 0) {
        ?>
        <tr>
            <td class="section" colspan="8">Links</td>
        </tr>
        <tr class="header">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td class="permission">Permissions</td>
            <td>Owner</td>
            <td>Group</td>
            <td class="size">Size</td>
            <td class="date">Date</td>
            <td>&nbsp;</td>
        </tr>
        <?php
        }
        foreach ($link_array as $link) {
        ?>
            <tr class="<?php echo $row_num++ % 2 == 0 ? "even-row" : "odd-row"; ?>">
                <?php
                preg_match("/\-\> (.+)$/", $link["name"], $match);
                $dir = preg_match("/^\//", $match[1]) > 0 ? $match[1] : $current_dir . $match[1];
                ?>
                <td>&nbsp;&nbsp;</td>
                <td class="name"><a href="javascript:WpvFTPGet.sendCommand(WpvFTPGet.FTP_CHDIR, '<?php echo $dir; ?>')"><?php echo $link["name"]; ?></a></td>
                <td class="permission"><?php echo $link["perms"]; ?></td>
                <td><?php echo $link["user"]; ?></td>
                <td><?php echo $link["group"]; ?></td>
                <td class="size"><?php echo $link["size"]; ?></td>
                <td class="date"><?php echo $link["date"]; ?></td>
                <td></td>
            </tr>
        <?php
        }
        ?>
        </table>
        <div class="submit">
            <input type="button" value="Get Files" onclick="WpvFTPGet.sendCommand(WpvFTPGet.FTP_GET, '<?php echo $current_dir; ?>')" <?php echo count($file_array) > 0 ? "" : "disabled"; ?> />
        </div>
        <?php
    }
    
    function VerifyParameters($host, $port, $user_id, $password) {
        if (isset($_POST["host"]) && isset($_POST["user_id"]) && isset($_POST["password"]) && isset($_POST["port"])) {
            if ($_POST["host"] != md5($host . COOKIEHASH))
                return FALSE;
            else if ($_POST["port"] != md5($port . COOKIEHASH))
                return FALSE;
            else if ($_POST["user_id"] != md5($user_id . COOKIEHASH))
                return FALSE;
            else if ($_POST["password"] != md5($password . COOKIEHASH))
                return FALSE;
            else
                return TRUE;
        }
        else {
            return FALSE;
        }
    }
    
    function WriteMessage($message) {
        $wpv_message = new WpvMessage();
        $wpv_message->SetIsCollapsible(FALSE);
        $wpv_message->AddMessageLine();
        $wpv_message->AddMessageLine($message);
        $wpv_message->AddMessageLine();
        $wpv_message->WriteMessages();
        exit;
    }
    
    function WriteErrorMessage($message) {
        $wpv_message = new WpvMessage();
        $wpv_message->SetIsCollapsible(FALSE);
        $wpv_message->AddErrorMessageLine();
        $wpv_message->AddErrorMessageLine($message);
        $wpv_message->AddErrorMessageLine();
        $wpv_message->AddErrorMessageLine("Click 'Disconnect' and try again.");
        $wpv_message->AddErrorMessageLine();
        $wpv_message->WriteMessages();
        exit;
    }

    function ProcessGet($conn_id, $current_dir, &$wpv_message) {
        global $wpdb;
        global $wpv_options;
        global $userdata;
        global $wpv_file_table;
        global $wpv_file2tag_table;
        
        require_once(dirname(__FILE__) . "/../lib/wpv-function-file.php");
        require_once(dirname(__FILE__) . "/../lib/wpv-function-image.php");

        $stored_file_array = array();
        $file_name_array = array();
        $insert_sql = "INSERT INTO $wpv_file_table ";
        $insert_sql .= "(file_name, file_ext, stored_datetime, stored_name, mime_type, owner_id, file_size, file_image_width, file_image_height) ";
        $insert_sql .= " VALUES ";
        $file_count = 0;

        // Process all files.
        if (!isset($_POST["get_files"])) {
            $wpv_message->AddMessageLine("No file to get.");
            return;
        }
        
        foreach ($_POST["get_files"] as $file_name) {
            WpvFTPGetCookie::ResetCookieData();

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
            if (@ftp_get($conn_id, $stored_full_file_name, "$current_dir$file_name", isset($_POST["is_bin_" . md5($file_name)]) ? FTP_BINARY : FTP_ASCII) === FALSE) {
                $wpv_message->AddErrorMessageLine("Error getting file from FTP server: $file_name");
                return;
            }
            chmod($stored_full_file_name, 0600);
        
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
        if ($wpdb->query(rtrim($insert_sql, ",")) === FALSE) {
            foreach ($stored_file_array as $stored_file) {
                if (file_exists($stored_file))
                    unlink($stored_file);
            }
            $wpv_message->AddErrorMessageLine("FTP Get Failed.");
            return;
        }
        else {
            $wpv_message->AddMessage("FTP Get Successful: ");
            $wpv_message->AddMessageLine(" '" . implode($_POST["get_files"], "', '") . "'");
        }
        return;
    }    
}
?>