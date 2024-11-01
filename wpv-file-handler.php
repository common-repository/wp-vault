<?php
global $wpv_options;

require_once(dirname(__FILE__) . "/lib/wpv-function.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-image.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-display-option.php");

$download_thumbnail_file = dirname(__FILE__) . "/images/thumbnails/download.jpg";

if (!isset($_GET["wpv_file_id"])) {
    WpvUtils::ShowPageNotFoundImage();
}
else if (WpvUtils::GetHashCode($_GET["wpv_file_id"]) != $_GET["hash"]) {
    WpvUtils::ShowPageNotFoundImage();
}

if (isset($_GET["file_mode"])) {
    $_file_mode = strtolower($_GET["file_mode"]);
}
else {
    $_file_mode = "default";
}

$_file_id = $_GET["wpv_file_id"];
$mime_type = "";
$stored_name = "";
$file_name = "";
$file_ext = "";
$owner_id = "";
$action_type = "";

if (isset($_GET["post_id"])) {
    require_once(dirname(__FILE__) . "/lib/wpv-function-post2file.php");
    global $wpv_file_table;
    
    $resultset = WpvPost2File::GetPost2FileTable($_GET["post_id"], "$wpv_file_table.file_id = $_file_id");
    $mime_type = $resultset[0]->mime_type;
    $stored_name = $resultset[0]->stored_name;
    $file_name = $resultset[0]->file_name;
    $file_ext = $resultset[0]->file_ext;
    $owner_id = $resultset[0]->owner_id;
    $action_type = $resultset[0]->action_type;
}
else {
    require_once(dirname(__FILE__) . "/lib/wpv-function-file.php");

    $resultset = WpvFile::GetFileTable("file_id = $_file_id");
    $mime_type = $resultset[0]->mime_type;
    $stored_name = $resultset[0]->stored_name;
    $file_name = $resultset[0]->file_name;
    $file_ext = $resultset[0]->file_ext;
    $owner_id = $resultset[0]->owner_id;
}
if (count($resultset) == 0) {
    WpvUtils::ShowPageNotFoundImage();
}
else if (!file_exists(WpvUtils::GetStoragePath() . $stored_name)) {
    WpvUtils::ShowPageNotFoundImage();
}

if ($_file_mode == "admin" && WpvImage::IsSupportedImage($mime_type)) {
    if (WpvUtils::CheckAccess($owner_id) === FALSE) {
        WpvUtils::ShowPageNotFoundImage();
    }
    
    header("Content-Disposition: inline; filename=\"$file_name.$file_ext\";");
    header("Content-Type: $mime_type");
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Connection: close");
    header("Accept-Ranges: bytes");

    WpvImage::SendResizedImage(WpvUtils::GetStoragePath() . $stored_name, $wpv_options->GetOption("target_image_size"), $mime_type);
}
if ($_file_mode == "default" && WpvImage::IsSupportedImage($mime_type)) {
    $display_option = null;
    
    if (WpvUtils::CheckAccess($owner_id) === FALSE) {
        WpvUtils::ShowPageNotFoundImage();
    }
    else if (isset($_GET["post_id"])) {
        $_post_id = $_GET["post_id"];
    }
    else {
        WpvUtils::ShowPageNotFoundImage();
    }
    
    if (($display_option = WpvDisplayOption::GetDisplayOption($_post_id)) === FALSE) {
        WpvUtils::ShowPageNotFoundImage();
    }

    $target_image_size = $display_option->target_image_size;
    $cached_file_path = WpvUtils::GetCachedFilePath($_post_id, $stored_name);
    
    if (!file_exists($cached_file_path)) {
        if (WpvImage::IsSupportedImage($mime_type)) {
            if (!WpvImage::CreateResizedImage(WpvUtils::GetStoragePath() . $stored_name, $cached_file_path, $target_image_size, $mime_type)) {
                WpvUtils::ShowPageNotFoundImage();
            }
        }
        else {
            $builtin_thumbnail_file_path = WpvThumbnail::GetThumbnailPath($mime_type);
            $mime_type = "image/jpeg";
            if (!WpvImage::CreateResizedImage($builtin_thumbnail_file_path, $cached_file_path, $target_image_size, $mime_type)) {
                WpvUtils::ShowPageNotFoundImage();
            }
        }
        chmod($cached_file_path, 0666);
    }
    
    if (!file_exists($cached_file_path)) {
        WpvUtils::ShowPageNotFoundImage();
    }

    header("Content-Disposition: inline; filename=\"$file_name.$file_ext\";");
    header("Content-Type: $mime_type");
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Connection: close");
    header("Accept-Ranges: bytes");

    readfile($cached_file_path);
}
else if ($_file_mode == "default") {
    if (WpvUtils::CheckAccess($owner_id) === FALSE) {
        WpvUtils::ShowPageNotFoundImage();
    }
    else if (isset($_GET["post_id"])) {
        $_post_id = $_GET["post_id"];
    }
    else {
        WpvUtils::ShowPageNotFoundImage();
    }

    header("Content-Disposition: inline; filename=\"$file_name.$file_ext\";");
    header("Content-Type: $mime_type");
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Connection: close");
    header("Accept-Ranges: bytes");
    header("Content-Length: " . filesize(WpvUtils::GetStoragePath() . $stored_name));

    readfile(WpvUtils::GetStoragePath() . $stored_name);
}
else if ($_file_mode == "thumbnail") {
    $target_thumbnail_size = 0;
    if (WpvUtils::CheckAccess($owner_id) === FALSE) {
        WpvUtils::ShowPageNotFoundImage();
    }
    else if (isset($_GET["post_id"])) {
        $_post_id = $_GET["post_id"];
    }
    else {
        WpvUtils::ShowPageNotFoundImage();
    }

    $error_message = "";
    if (($thumbnail_path = WpvUtils::GetImageCachePath($error_message)) === FALSE) {
        WpvUtils::ShowPageNotFoundImage();
    }
    else if (($display_option = WpvDisplayOption::GetDisplayOption($_post_id)) === FALSE) {
        WpvUtils::ShowPageNotFoundImage();
    }
    
    $target_thumbnail_size = $display_option->target_thumbnail_size;
    $thumbnail_file_path = WpvUtils::GetThumbnailFilePath($_post_id, $stored_name);
    
    if (!file_exists($thumbnail_file_path)) {
        if (isset($_GET["action_type"]) && $_GET["action_type"] == "Download") {
            $mime_type = "image/jpeg";
            if (!WpvImage::CreateResizedImage($download_thumbnail_file, $thumbnail_file_path, $target_thumbnail_size, $mime_type)) {
                WpvUtils::ShowPageNotFoundImage();
            }
            chmod($thumbnail_file_path, 0666);
        }
        else if (WpvImage::IsSupportedImage($mime_type)) {
            if (!WpvImage::CreateResizedImage(WpvUtils::GetStoragePath() . $stored_name, $thumbnail_file_path, $target_thumbnail_size, $mime_type)) {
                WpvUtils::ShowPageNotFoundImage();
            }
            chmod($thumbnail_file_path, 0666);
        }
        else {
            $builtin_thumbnail_file_path = WpvThumbnail::GetThumbnailPath($mime_type);
            $mime_type = "image/jpeg";
            if (!WpvImage::CreateResizedImage($builtin_thumbnail_file_path, $thumbnail_file_path, $target_thumbnail_size, $mime_type)) {
                WpvUtils::ShowPageNotFoundImage();
            }
            chmod($thumbnail_file_path, 0666);
        }
    }
    
    if (!file_exists($thumbnail_file_path)) {
        WpvUtils::ShowPageNotFoundImage();
    }

    header("Content-Disposition: inline; filename=\"$file_name.$file_ext\";");
    header("Content-Type: $mime_type");
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Connection: close");
    header("Accept-Ranges: bytes");
    header("Content-Length: " . filesize($thumbnail_file_path));

    readfile($thumbnail_file_path);
}
else if ($_file_mode == "sys-thumbnail") {
    $error_message = "";
    if (WpvUtils::CheckAccess($owner_id) === FALSE) {
        WpvUtils::ShowPageNotFoundImage();
    }
    else if (($thumbnail_path = WpvUtils::GetSysThumbnailPath($error_message)) === FALSE) {
        WpvUtils::ShowPageNotFoundImage();
    }
    else if (!file_exists("$thumbnail_path$stored_name")) {
        if (WpvImage::IsSupportedImage($mime_type)) {
            if (!WpvImage::CreateResizedImage(WpvUtils::GetStoragePath() . $stored_name, "$thumbnail_path$stored_name", 100, $mime_type)) {
                WpvUtils::ShowPageNotFoundImage();
            }
            chmod("$thumbnail_path$stored_name", 0666);
        }
        else {
            $builtin_thumbnail_file_path = WpvThumbnail::GetThumbnailPath($mime_type);
            $mime_type = "image/jpeg";
            if (!WpvImage::CreateResizedImage($builtin_thumbnail_file_path, "$thumbnail_path$stored_name", 100, $mime_type)) {
                WpvUtils::ShowPageNotFoundImage();
            }
            chmod("$thumbnail_path$stored_name", 0666);
        }
    }

    if (isset($_GET["action_type"]) && $_GET["action_type"] == "Download") {
        header("Content-Disposition: inline; filename=\"$file_name.$file_ext\";");
        header("Content-Type: image/jpeg");
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Connection: close");
        header("Accept-Ranges: bytes");
        
        WpvImage::SendResizedImage($download_thumbnail_file, 100, "image/jpeg");
    }
    else {
        header("Content-Disposition: inline; filename=\"$file_name.$file_ext\";");
        header("Content-Type: $mime_type");
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Cache-Control: private", false);
        header("Connection: close");
        header("Accept-Ranges: bytes");
        header("Content-Length: " . filesize("$thumbnail_path$stored_name"));

        readfile("$thumbnail_path$stored_name");
    }
}
else if ($_file_mode == "download") {
    if (WpvUtils::CheckAccess($owner_id) === FALSE) {
        WpvUtils::ShowPageNotFoundImage();
    }
    else if ($action_type != "" && $action_type != "Download") {
        WpvUtils::ShowPageNotFoundImage();        
    }
    header("Content-Disposition: attachment; filename=\"$file_name.$file_ext\";");
    header("Content-Transfer-Encoding: binary");
    header("Content-Type: $mime_type");
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);
    header("Connection: close");
    header("Accept-Ranges: bytes");
    header("Content-Length: " . filesize(WpvUtils::GetStoragePath() . $stored_name));

    readfile(WpvUtils::GetStoragePath() . $stored_name);
}
else {
    WpvUtils::ShowPageNotFoundImage();
}

class WpvThumbnail {
    function GetThumbnailPath($mime_type) {
        if (strpos($mime_type, "pdf") !== FALSE) {
            return dirname(__FILE__) . "/images/thumbnails/pdf.jpg";
        }
        else if (strpos($mime_type, "msword") !== FALSE) {
            return dirname(__FILE__) . "/images/thumbnails/msword.jpg";
        }
        else if (strpos($mime_type, "ms-powerpoint") !== FALSE) {
            return dirname(__FILE__) . "/images/thumbnails/msppt.jpg";
        }
        else if (strpos($mime_type, "ms-excel") !== FALSE) {
            return dirname(__FILE__) . "/images/thumbnails/msexcel.jpg";
        }
        else if (strpos($mime_type, "x-shockwave-flash") !== FALSE) {
            return dirname(__FILE__) . "/images/thumbnails/flash.jpg";
        }
        else if (strpos($mime_type, "zip") !== FALSE) {
            return dirname(__FILE__) . "/images/thumbnails/compressed.jpg";
        }
        else if (strpos($mime_type, "x-gzip") !== FALSE) {
            return dirname(__FILE__) . "/images/thumbnails/compressed.jpg";
        }
        return dirname(__FILE__) . "/images/thumbnails/default.jpg";
    }
}
?>
