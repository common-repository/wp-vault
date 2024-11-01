<?php
require_once(dirname(__FILE__) . "/wpv-function.php");

class WpvImage {
    function AddDownloadArrow($source_path, $mime_type) {
        $image = WpvImage::GetImageResource($source_path, $mime_type);
        
        if ($image === FALSE)
            return FALSE;
            
        $arrow_size = min(imagesx($image), imagesy($image)) * 0.8;
        $scale = $arrow_size / 55;  // 55 is arrow's size.
        $base_x = 0;
        $base_y = imagesy($image) - $arrow_size;

        $arrow_point_array = array(
            $base_x + 10*$scale,  $base_y,  
            $base_x + 10*$scale,  $base_y + 15*$scale, 
            $base_x,  $base_y + 15*$scale,  
            $base_x + 30*$scale, $base_y + 40*$scale, 
            $base_x + 60*$scale,  $base_y + 15*$scale,
            $base_x + 50*$scale,  $base_y + 15*$scale, 
            $base_x + 50*$scale,  $base_y  
        );

        $arrow_color = imagecolorallocate($image, 0, 170, 0);
        imagefilledpolygon($image, $arrow_point_array, 7, $arrow_color);
        
        return imagejpeg($image);
    }
    
    function IsSupportedImage($mime_type) {
        if (strpos($mime_type, "jpeg") !== FALSE)
            return TRUE;
        else if (strpos($mime_type, "png") !== FALSE)
            return TRUE;
        else if (strpos($mime_type, "gif") !== FALSE)
            return TRUE;
        else
            return FALSE;
    }
    
    function GetImageResource($source_path, $mime_type) {
        if (strpos($mime_type, "jpeg") !== FALSE)
            return imagecreatefromjpeg($source_path);
        else if (strpos($mime_type, "png") !== FALSE)
            return imagecreatefrompng($source_path);
        else if (strpos($mime_type, "gif") !== FALSE)
            return imagecreatefromgif($source_path);
        else
            return FALSE;
    }
    
    function SendResizedImage($source_path, $target_size, $mime_type) {
        return WpvImage::CreateResizedImage($source_path, null, $target_size, $mime_type, $text);
    }
    
    function CreateResizedImage($source_path, $dest_path, $target_size, $mime_type) {
        if (!file_exists($source_path))
            return FALSE;

        if (!file_exists($source_path))
            return FALSE;
        else if ($target_size <= 0)
            return FALSE;
        else if (($source_image = WpvImage::GetImageResource($source_path, $mime_type)) === FALSE)
            return FALSE;
        
        $image_width = imagesx($source_image);
        $image_height = imagesy($source_image);

        if ($target_size > $image_width && $target_size > $image_height)
            $new_image = $source_image;
        else {
            $ratio = min($target_size/$image_width, $target_size/$image_height);

            $new_image = imagecreatetruecolor($image_width * $ratio, $image_height * $ratio);
            fastimagecopyresampled($new_image, $source_image, 0, 0, 0, 0, $image_width * $ratio, $image_height * $ratio, $image_width, $image_height, 4);
        }
        
        if ($dest_path == null) {
            if ($mime_type == "image/jpeg")
                return imagejpeg($new_image);
            else if ($mime_type == "image/gif")
                return imagegif($new_image);
            else if ($mime_type == "image/png")
                return imagepng($new_image);
            else
                return FALSE;
        }
        else {
            if (!is_writable(dirname($dest_path))) {
                return FALSE;
            }
        
            $result = FALSE;
            if ($mime_type == "image/jpeg")
                $result = imagejpeg($new_image, $dest_path);
            else if ($mime_type == "image/gif")
                $result = imagegif($new_image, $dest_path);
            else if ($mime_type == "image/png")
                $result = imagepng($new_image, $dest_path);

            imagedestroy($source_image);
            
            for ($i = 0; $i < 10 && !file_exists($dest_path); $i++) {
                sleep(2);
            }
            if (!file_exists($dest_path)) {
                return FALSE;
            }
            
            if ($result == TRUE) {
                chmod($dest_path, 0666);
                return $result;
            }
            else {
                return $result;
            }
        }
    }
}

// Copied from http://us.php.net/manual/en/function.imagecopyresampled.php
//
// Plug-and-Play fastimagecopyresampled function replaces much slower imagecopyresampled.
// Just include this function and change all "imagecopyresampled" references to "fastimagecopyresampled".
// Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
// Author: Tim Eckel - Date: 12/17/04 - Project: FreeRingers.net - Freely distributable.
//
// Optional "quality" parameter (defaults is 3).  Fractional values are allowed, for example 1.5.
// 1 = Up to 600 times faster.  Poor results, just uses imagecopyresized but removes black edges.
// 2 = Up to 95 times faster.  Images may appear too sharp, some people may prefer it.
// 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled.
// 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
// 5 = No speedup.  Just uses imagecopyresampled, highest quality but no advantage over imagecopyresampled.
function fastimagecopyresampled (&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
    if (empty($src_image) || empty($dst_image)) { return false; }
    if ($quality <= 1) {
        $temp = imagecreatetruecolor ($dst_w + 1, $dst_h + 1);
        imagecopyresized ($temp, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w + 1, $dst_h + 1, $src_w, $src_h);
        imagecopyresized ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $dst_w, $dst_h);
        imagedestroy ($temp);
    } elseif ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
        $tmp_w = $dst_w * $quality;
        $tmp_h = $dst_h * $quality;
        $temp = imagecreatetruecolor ($tmp_w + 1, $tmp_h + 1);
        imagecopyresized ($temp, $src_image, $dst_x * $quality, $dst_y * $quality, $src_x, $src_y, $tmp_w + 1, $tmp_h + 1, $src_w, $src_h);
        imagecopyresampled ($dst_image, $temp, 0, 0, 0, 0, $dst_w, $dst_h, $tmp_w, $tmp_h);
        imagedestroy ($temp);
    } else {
        imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    }
    return true;
}

?>
