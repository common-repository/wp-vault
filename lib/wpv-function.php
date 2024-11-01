<?php
class WpvMessage {
    function WpvMessage() {
        $this->message = "";
        $this->error_message = "";
        $this->is_collapsible = TRUE;
    }
    
    function AddMessage($string) {
        $this->message .= "$string";
    }

    function AddMessageLine($string="") {
        $this->message .= "$string<br />";
    }

    function AddErrorMessage($string) {
        $this->error_message .= "$string";
    }

    function AddErrorMessageLine($string="") {
        $this->error_message .= "$string<br />";
    }
    
    function MessageExists() {
        return $this->message != "";
    }
    
    function ErrorMessageExists() {
        return $this->error_message != "";
    }

    function SetIsCollapsible($is_collapsible) {
        $this->is_collapsible = $is_collapsible;
    }
    
    function WriteMessages() {
        if ($this->message != "") {
            if ($this->is_collapsible)
                echo "<div id='wpv-message' class='updated fade' onclick='this.style.display = \"none\"'>";
            else
                echo "<div id='wpv-message' class='updated fade' style='cursor: default'>";
            echo $this->message;
            if ($this->is_collapsible)
                echo "<div>Click to close</div>";
            echo "</div>";
        }
        
        if ($this->error_message != "") {
            if ($this->is_collapsible)
                echo "<div id='wpv-message' class='error fade' onclick='this.style.display = \"none\"'>";
            else
                echo "<div id='wpv-message' class='error fade' style='cursor: default'>";
            echo $this->error_message;
            if ($this->is_collapsible)
                echo "<div>Click to close</div>";
            echo "</div>";
        }
    }
}

class WpvOptions {
    function WpvOptions() {
        $this->Initialize();
    }

    function Initialize() {
        $this->option_array = array();
        $this->OptionExists = FALSE;
        
        if (($resultset = $this->GetOptionTable()) !== FALSE) {
            foreach ($resultset as $result) {
                $this->option_array["$result->option_id"] = $result->option_value;
            }
            $this->OptionExists = TRUE;
        }
    }
    
    function GetOption($option_id) {
        if ($this->OptionExists)
            return $this->option_array["$option_id"];
        else
            return FALSE;
    }

    function GetOptionTable() {
        global $wpdb;
        global $wpv_option_table;

        if ($wpdb->get_var("SHOW TABLES LIKE '$wpv_option_table'") == $wpv_option_table) {
            $select_sql = "SELECT option_id, option_value ";
            $select_sql .= "FROM $wpv_option_table ";
            return $wpdb->get_results($select_sql);
        }
        else {
            return FALSE;
        }
    }
    
    function UpdateOption($key, $value) {
        global $wpdb;
        global $wpv_option_table;
        
        $returned = FALSE;
        $value = $wpdb->escape($value);
        $sql = "UPDATE $wpv_option_table ";
        $sql .= "SET option_value = '$value' ";
        $sql .= "WHERE option_id = '$key' ";
        $returned = $wpdb->query($sql);
        
        if ($returned == TRUE) {
            $this->option_array["$key"] = $value;
        }
        return $returned;
    }
}

class WpvUtils {
    function FilterNumber($num, $min, $max=0) {
        if (is_numeric($num)) {
            $num = max($min, $num);
            if ($max > $min)
                $num = min($num, $max);
            if ($num < 0)
                return FALSE;
            return $num;
        }
        return FALSE;
    }
    
    function GetImageCachePath(&$message) {
        global $wpv_options;
        
        $message = "";
        $message = WpvUtils::TestValidDir(WpvUtils::GetStoragePath());
        if ($message != "")
            return FALSE;
        
        if (!file_exists(WpvUtils::GetStoragePath() . ".img/")) {
            mkdir(WpvUtils::GetStoragePath() . ".img/");
            chmod(WpvUtils::GetStoragePath() . ".img/", 0777);
            copy(dirname(__FILE__) . "/../index.php", WpvUtils::GetStoragePath() . ".img/index.php");
            chmod(WpvUtils::GetStoragePath() . ".img/index.php", 0666);
        }
            
        $message = WpvUtils::TestValidDir(WpvUtils::GetStoragePath() . ".img/");
        if ($message == "")
            return WpvUtils::GetStoragePath() . ".img/";
        else
            return FALSE;
    }
    
    function GetStoragePath() {
        static $path;
        
        if (!isset($path)) {
            global $wpv_options;
            
            $path = $wpv_options->GetOption('file_path') . $wpv_options->GetOption('file_path_hash') . "/";
            if (file_exists($wpv_options->GetOption('file_path'))) {
                if (!file_exists($path)) {
                    mkdir($path);
                    chmod($path, 0777);
                    copy(dirname(__FILE__) . "/../index.php", $path . "index.php");
                    chmod($path . "index.php", 0666);
                }
            }
            else {
                return "";
            }
        }
        $message = WpvUtils::TestValidDir($path);
        if ($message != "") {
            echo $message;
            die;
        }
        return $path;
    }
    
    function GetThumbnailFilePath($post_id, $stored_name) {
        $message = "";
        $path = WpvUtils::GetImageCachePath($message);
        
        if ($path === FALSE)
            return FALSE;
        
        return $path . strtoupper(md5($post_id . "-" . $stored_name));
    }
    
    function GetSysThumbnailPath(&$message) {
        $message = "";
        $message = WpvUtils::TestValidDir(WpvUtils::GetStoragePath());
        if ($message != "")
            return FALSE;
        
        if (!file_exists(WpvUtils::GetStoragePath() . ".sys/")) {
            mkdir(WpvUtils::GetStoragePath() . ".sys/");
            chmod(WpvUtils::GetStoragePath() . ".sys/", 0777);
            copy(dirname(__FILE__) . "/../index.php", WpvUtils::GetStoragePath() . ".sys/index.php");
            chmod(WpvUtils::GetStoragePath() . ".sys/index.php", 0666);
        }
        
        $message = WpvUtils::TestValidDir(WpvUtils::GetStoragePath() . ".sys/");
        if ($message == "")
            return WpvUtils::GetStoragePath() . ".sys/";
        else
            return FALSE;
    }
    
    function GetCachePath(&$message) {
        $message = "";
        $message = WpvUtils::TestValidDir(WpvUtils::GetStoragePath());
        if ($message != "")
            return FALSE;
        
        if (!file_exists(WpvUtils::GetStoragePath() . ".cache/")) {
            mkdir(WpvUtils::GetStoragePath() . ".cache/");
            chmod(WpvUtils::GetStoragePath() . ".cache/", 0777);
            copy(dirname(__FILE__) . "/../index.php", WpvUtils::GetStoragePath() . ".cache/index.php");
            chmod(WpvUtils::GetStoragePath() . ".cache/index.php", 0666);
        }
        
        $message = WpvUtils::TestValidDir(WpvUtils::GetStoragePath() . ".cache/");
        if ($message == "")
            return WpvUtils::GetStoragePath() . ".cache/";
        else
            return FALSE;
    }

    function GetCachedFilePath($post_id, $stored_name) {
        $message = "";
        $path = WpvUtils::GetImageCachePath($message);
        
        if ($path === FALSE)
            return FALSE;
        
        return $path . strtoupper(md5($post_id . "=" . $stored_name));
    }

    function VerifyWPVault() {
        global $wpv_options;
        
        $wpv_message = new WpvMessage();
        $wpv_message->SetIsCollapsible(FALSE);
                
        if (!function_exists("gd_info")) {
            $wpv_message->AddMessageLine();
            $wpv_message->AddMessageLine("GDLib, used for image manipulation, is not installed with your PHP.");
            $wpv_message->AddMessageLine();
            $wpv_message->AddMessageLine("You cannot use WP Vault without the GDLib library.");
            $wpv_message->AddMessageLine();
            $wpv_message->WriteMessages();
            exit;
        }
        else if ($wpv_options->OptionExists) {
            $file_error_message = WpvUtils::TestValidDir($wpv_options->GetOption("file_path"));
            if ($file_error_message != "") {
                $wpv_message->AddMessageLine();
                $wpv_message->AddMessageLine("File directory is not valid - $file_error_message");
                $wpv_message->AddMessageLine();
                $wpv_message->AddMessageLine("Please assign a valid, writable directory in <strong><a href='" . get_settings("siteurl") . "/wp-admin/admin.php?page=wp-vault/wpv-option.php'>Vault Option</a></strong>.");
                $wpv_message->AddMessageLine();
                $wpv_message->WriteMessages();
                exit;
            }
        }
        else {
            $wpv_message->AddMessageLine();
            $wpv_message->AddMessageLine("It appears that the WP Vault database tables were not created properly in the activation process.");
            $wpv_message->AddMessageLine();
            $wpv_message->AddMessageLine("Please make sure that the database user that's assigned to your installtion of WordPress has table create privilege to your WordPress database (database user and password are defined in wp-config.php file), then try re-activating the plugin.");
            $wpv_message->AddMessageLine();
            $wpv_message->AddMessageLine("It's also possible that your MySQL database does not support InnoDB.");
            $wpv_message->AddMessageLine();
            $wpv_message->WriteMessages();
            exit;
        }
    }
    
    function TestValidDir($dir) {
        clearstatcache();

        if ($dir == "")
            return "No directory is specified.";
        else if (!file_exists($dir))
            return "Specified directory does not exist: '$dir'";
        else if (!WpvUtils::IsDirectory($dir))
            return "Specified is not a directory: '$dir'";
        else if (!is_writable($dir))
            return "Specified directory is not writable: '$dir'";
        else
            return "";
    }
    
    function IsDirectory($dir) {
        if (preg_match("/Windows/", php_uname('s')) === FALSE) {
            return 'd' != substr(exec("ls -dl '$dir'"),0,1);
        }
        else {
            return is_dir($dir);
        }
    }
    
    function ParseEnum($enum_text) {
        preg_match_all("/\'([a-zA-Z0-9_ \-]+)\'/", $enum_text, $matches);

        for ($i = 1; $i < count($matches); $i++) {
            return $matches[$i];
        }
        return array();
    }
    
    function GetRandomString($len = 10, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
        $string = '';
        for ($i = 0; $i < $len; $i++) {
            $pos = rand(0, strlen($chars)-1);
            $string .= $chars{$pos};
        }
        return $string;
    }
    
    function GetUniqueStoredName($foldername, $ext="") {
        $unique_filename = "";
        
        do {
            if ($ext == "")
                $unique_filename = WpvUtils::GetRandomString();
            else
                $unique_filename = WpvUtils::GetRandomString() . '.' . $ext;
        } while (file_exists($foldername . "/" . $unique_filename));
        
        return $unique_filename;
    }
    
    function RemoveFileExtension($file_name) {
        $ext = WpvUtils::GetFileExtension($file_name);

        return substr($file_name, 0, strlen($file_name) - strlen($ext) - 1);
    }

    function GetFileExtension($file_name) {
        if (preg_match("/\.([a-zA-Z0-9]+)$/", $file_name, $matches) > 0) {
            return strtolower($matches[1]);
        }
        else {
            return "";
        }
    }
    
    function ShowPageNotFound() {
        header("HTTP/1.0 404 Not Found");
        echo "File not found.";
        exit;
    }
    
    function ShowPageNotFoundImage() {
        header("Content-type: image/jpeg");
        $im = imagecreate(250, 50);
        $bgcolor = imagecolorallocate($im, 225, 225, 225);
        $color = imagecolorallocate($im, 0, 0, 0);
        imagestring($im, 16, 10, 12, "Not found or not allowed.", $color);
        imagejpeg($im);
        imagedestroy($im);
        exit;
    }

    function CheckAccess($owner_id) {
        global $userdata;

        $_post_id = -1;
        
        get_currentuserinfo();

        if (isset($_GET["post_id"])) {
           $_post_id = $_GET["post_id"];
        }
        else if (isset($_POST["post_id"])) {
            $_post_id = $_POST["post_id"];
        }
        else if ($userdata->ID == "") {
            return FALSE;
        }
        else if (($userdata->ID === $owner_id && current_user_can("wpv_browse_own_files")) || current_user_can("wpv_browse_all_files")) {
            return TRUE;
        }
        else {
            return FALSE;
        }

        if (($display_option = WpvDisplayOption::GetDisplayOption($_post_id)) === FALSE) {
            return FALSE;
        }
        else if (($userdata->ID === $owner_id && current_user_can("wpv_browse_own_files")) || current_user_can("wpv_browse_all_files")) {
            return TRUE;
        }
        else if ($display_option->display_status == "Published") {
            return TRUE;
        }
        else {
            return FALSE;
        }
    }
    
    function GetLimitClause($rec_count, $rec_per_page, $current_page) {
        if ($rec_count > 0) {
            $total_page_count = ceil($rec_count / $rec_per_page);
            
            if ($current_page == $total_page_count) {
                $displayed_rec_count = $rec_count % $rec_per_page;
                if ($displayed_rec_count == 0)
                    $displayed_rec_count = $rec_per_page;
            }
            else {
                $displayed_rec_count = $rec_per_page;
            }

            return (($current_page - 1)*$rec_per_page) . ", " . $displayed_rec_count;
        }
        return "";
    }
    
    function DisplayPageLocation($rec_count, $rec_per_page, $current_page) {
        if ($rec_count > 0) {
            $total_page_count = ceil($rec_count / $rec_per_page);
            
            if ($current_page == $total_page_count) {
                $displayed_rec_count = $rec_count % $rec_per_page;
                if ($displayed_rec_count == 0)
                    $displayed_rec_count = $rec_per_page;
            }
            else {
                $displayed_rec_count = $rec_per_page;
            }
                
            echo (($current_page-1)*$rec_per_page+1) . "-" . (($current_page-1)*$rec_per_page+$displayed_rec_count) . " of " . $rec_count;
        }
    }
    
    function DisplayPageLinks($rec_count, $rec_per_page, $current_page, $onclick) {
        if ($rec_count > 0) {
            echo "Page: ";
            $total_page_count = ceil($rec_count / $rec_per_page);
            $lower_page_limit = $current_page - 5 < 1 ? 1 : $current_page - 5;
            $upper_page_limit = $current_page + 5 > $total_page_count ? $total_page_count : $current_page + 5;
            
            if ($current_page > 1) {
                $prev_page = $current_page - 1;
                echo "<a href='javascript:void(0)' onclick='$onclick($prev_page)'>&laquo; Prev</a> ";
            }
            
            if ($lower_page_limit > 1)
                echo "<a href='javascript:void(0)' onclick='$onclick(1)'>1</a> ... ";
                
            for ($i = $lower_page_limit; $i <= $upper_page_limit; $i++) {
                if ($i == $current_page)
                    echo " <strong>[$i]</strong> ";
                else
                    echo " <a href='javascript:void(0)' onclick='$onclick($i)'>$i</a> ";
            }

            if ($upper_page_limit < $total_page_count)
                echo " ... <a href='javascript:void(0)' onclick='$onclick($total_page_count)'>$total_page_count</a>";
            
            if ($current_page < $total_page_count) {
                $next_page = $current_page + 1;
                echo " <a href='javascript:void(0)' onclick='$onclick($next_page)'>Next &raquo;</a>";
            }
        }
    }
    
    function AssignRoles($lowest_role) {
        global $wp_roles;

        $is_capable = TRUE;
        foreach($wp_roles->role_names as $role => $name) {
            $role_obj = $wp_roles->get_role($role);
            
            if ($role == "administrator") {
                $role_obj->add_cap("wpv_edit_options");
                $role_obj->add_cap("wpv_browse_all_files");
                $role_obj->add_cap("wpv_edit_all_files");
                $role_obj->add_cap("wpv_access_all_posts");
                $role_obj->add_cap("wpv_assign_files");
                $role_obj->add_cap("wpv_get_ftp_files");
                $role_obj->add_cap("wpv_get_http_files");
            }
            else if ($role == "editor") {
                $role_obj->add_cap("wpv_browse_all_files");
                $role_obj->add_cap("wpv_access_all_posts");
            }

            $role_obj->add_cap("wpv_browse_own_files", $is_capable);
            $role_obj->add_cap("wpv_edit_own_files", $is_capable);
            $role_obj->add_cap("wpv_upload_files", $is_capable);
            $role_obj->add_cap("wpv_edit_tags", $is_capable);
            $role_obj->add_cap("wpv_access_own_posts", $is_capable && $role != "subscriber");

            if ($role == $lowest_role)
                $is_capable = FALSE;
        }
        
/*        // DEBUG CODE...Displays all capabilities for all roles.
        foreach($wp_roles->role_names as $role => $name) {
            $role_obj = $wp_roles->get_role($role);
            
            echo "<strong>$role</strong> <br />";
            foreach ($role_obj->capabilities as $cap_name => $cap) {
                echo "- $cap_name = " . ($cap ? "TRUE" : "FALSE") . "<br />";
            }
        }
*/
    }
    
    function GetHashCode($id) {
        $ip_address = preg_replace("/\.+/", "", $_SERVER["REMOTE_ADDR"]);
        $hash = md5("$ip_address$id");
        
        return $hash;
    }
    
    function EncryptionEnabled() {
        return function_exists("mcrypt_encrypt");
    }    

    function IsAscii($file_name) {
        static $ascii_file_type_array;

        if (!isset($ascii_file_type_array)) {
            $ascii_file_type_array = array(
                "txt", "text", "htm", "js", "css", "html", "php", "php3", "asp", "aspx", "java", "cs", 
                "htaccess", "xml", "shtml", "c", "cpp", "vb", "sql", "pl", "pas", "py", "bat", "sh", 
                "bsh", "asm", "xhtml", "phtml", "cgi", "ini"
            );
        }
        $ext = WpvUtils::GetFileExtension($file_name);
        return in_array($ext, $ascii_file_type_array);
    }
}
?>