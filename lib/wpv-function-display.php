<?php

class WpvDisplay {
    function AppendImageList($text, $display_option, $post_id) {
        $htmlstring = "<link rel='stylesheet' href='" . get_bloginfo("siteurl"). "/?wpv-css=wpv-display&post_id=$post_id' title='wpv-display-css' type='text/css'>";            
        $htmlstring .= "<script type='text/javascript' src='" . get_bloginfo("siteurl"). "/?wpv-js=wpv-display&post_id=$post_id'></script>";
        
        if ($display_option->display_table_location == "Left") {
            return $htmlstring . "<table><tr><td style='vertical-align: top'>" . WpvDisplay::DisplayImageList($post_id, $display_option) . "</td><td style='vertical-align: top'>$text</td></tr></table>";
        }
        else if ($display_option->display_table_location == "Right") {
            return $htmlstring . "<table><tr><td style='vertical-align: top'>$text</td><td style='vertical-align: top'>" . WpvDisplay::DisplayImageList($post_id, $display_option) . "</td></tr></table>";
        }
        else if ($display_option->display_table_location == "Top") {
            return $htmlstring . WpvDisplay::DisplayImageList($post_id, $display_option) . "<div style='margin-top: 20px'>$text</div>";
        }
        else if ($display_option->display_table_location == "Bottom") {
            if ((strpos($text, get_permalink()) === FALSE && strpos($text, 'class="more-link"') === FALSE) || is_single() || is_page())
                return $htmlstring . "<div style='margin-bottom: 20px'>$text</div>" . WpvDisplay::DisplayImageList($post_id, $display_option);
            else
                return $text;
        }
        else if ($display_option->display_table_location == "Float Left" || $display_option->display_table_location == "Float Right") {
            return $htmlstring . WpvDisplay::DisplayImageList($post_id, $display_option) . $text;
        }
        else {
            return $text;
        }
    }

    function DisplayImageList($post_id, $display_option) {
        global $wpdb;
        
        require_once(dirname(__FILE__) . "/wpv-function-image.php");
        
        $htmlstring = "";

        $resultset = WpvPost2File::GetPost2FileTable($post_id);
        if (count($resultset) > 0) {
            $column_count = 0;
            $row_count = 0;

            $htmlstring .= "<div id='wpv-wrapper-$post_id'>";
            $htmlstring .= "<table id='wpv-table-$post_id'>";
            foreach ($resultset as $result) {
                if ($column_count == 0) {
                    $htmlstring .= "<tr>";
                    $row_count++;
                }
                if ($display_option->display_thumbnail == "None")
                    $htmlstring .= WpvDisplay::GetThumbnailNone($result, $display_option);
                else if ($display_option->display_thumbnail == "Left")
                    $htmlstring .= WpvDisplay::GetThumbnailLeft($result, $display_option);
                else if ($display_option->display_thumbnail == "Right")
                    $htmlstring .= WpvDisplay::GetThumbnailRight($result, $display_option);
                else if ($display_option->display_thumbnail == "Top")
                    $htmlstring .= WpvDisplay::GetThumbnailTop($result, $display_option);
                else if ($display_option->display_thumbnail == "Bottom")
                    $htmlstring .= WpvDisplay::GetThumbnailBottom($result, $display_option);
                else if ($display_option->display_thumbnail == "Stagger" && $row_count % 2 == 1)
                    $htmlstring .= WpvDisplay::GetThumbnailLeft($result, $display_option);
                else if ($display_option->display_thumbnail == "Stagger" && $row_count % 2 == 0)
                    $htmlstring .= WpvDisplay::GetThumbnailRight($result, $display_option);
                
                $column_count++;
                if ($column_count % $display_option->column_count == 0) {
                    $htmlstring .= "</tr>";
                    $column_count = 0;
                }
            }
            $htmlstring .= "</table>";
            $htmlstring .= "</div>";
            return $htmlstring;
        }
        else {
            return "";
        }
    }
    
    function GetThumbnailNone($result, $display_option) {
        $hash = WpvUtils::GetHashCode($result->file_id);
        $click_function = "";
        
        if ($result->action_type == "Download") {
            $click_function = "WpvFileHandler_$result->post_id.downloadFile(\"wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=$result->action_type&hash=$hash\")";
        }
        else if (WpvImage::IsSupportedImage($result->mime_type)) {
            $click_function = "WpvFileHandler_$result->post_id.showImage($result->file_id, $result->post_id, \"$result->action_type\", \"$hash\")";
        }
        else {
            $click_function = "WpvFileHandler_$result->post_id.doDefault(\"wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=$result->action_type&hash=$hash\")";
        }
        
        $htmlstring = "";
        $htmlstring .= "<td class='display-cell' onmouseover='WpvFileHandler_$result->post_id.mouseOverCell(this)' onmouseout='WpvFileHandler_$result->post_id.mouseOutCell(this)' onclick='$click_function'>";
        if ($display_option->display_text == "File Name" || $display_option->display_text == "Both") {
            $htmlstring .= "<div class='wpv-file-name'>";
            $htmlstring .= "$result->file_name";
            $htmlstring .= "</div>";
        }
        if ($display_option->display_text == "Comment" || $display_option->display_text == "Both") {
            $htmlstring .= "<div class='wpv-comment-text'>" . preg_replace("/\\\'/", "'", $result->comment_text) . "</div>";
        }
        if ($display_option->display_text == "None") {
            $htmlstring .= "<div class='wpv-file-name'>";
            $htmlstring .= "FILE_$result->file_id";
            $htmlstring .= "</div>";
        }
        $htmlstring .= "</td>";
        
        return $htmlstring;
    }

    function GetThumbnailLeft($result, $display_option) {
        $hash = WpvUtils::GetHashCode($result->file_id);
        $click_function = "";
        
        if ($result->action_type == "Download") {
            $click_function = "WpvFileHandler_$result->post_id.downloadFile(\"wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=$result->action_type&hash=$hash\")";
        }
        else if (WpvImage::IsSupportedImage($result->mime_type)) {
            $click_function = "WpvFileHandler_$result->post_id.showImage($result->file_id, $result->post_id, \"$result->action_type\", \"$hash\")";
        }
        else {
            $click_function = "WpvFileHandler_$result->post_id.doDefault(\"wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=$result->action_type&hash=$hash\")";
        }
        
        $htmlstring = "";
        $htmlstring .= "<td class='display-cell' onmouseover='WpvFileHandler_$result->post_id.mouseOverCell(this)' onmouseout='WpvFileHandler_$result->post_id.mouseOutCell(this)' onclick='$click_function'>";
        $htmlstring .= "<table style='width: 100%'><tr><td>";
        $htmlstring .= "<img src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=thumbnail&action_type=$result->action_type&hash=$hash' title='$result->file_name.$result->file_ext' />";
        $htmlstring .= "</td>";
        $htmlstring .= "<td style='width: 100%'>";
        if ($display_option->display_text == "File Name" || $display_option->display_text == "Both") {
            $htmlstring .= "<div class='wpv-file-name'>";
            $htmlstring .= "$result->file_name";
            $htmlstring .= "</div>";
        }
        if ($display_option->display_text == "Comment" || $display_option->display_text == "Both") {
            $htmlstring .= "<div class='wpv-comment-text'>" . preg_replace("/\\\'/", "'", $result->comment_text) . "</div>";
        }
        $htmlstring .= "</td></tr></table>";
        $htmlstring .= "</td>";
        
        return $htmlstring;
    }

    function GetThumbnailRight($result, $display_option) {
        $hash = WpvUtils::GetHashCode($result->file_id);
        $click_function = "";
        
        if ($result->action_type == "Download") {
            $click_function = "WpvFileHandler_$result->post_id.downloadFile(\"wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=$result->action_type&hash=$hash\")";
        }
        else if (WpvImage::IsSupportedImage($result->mime_type)) {
            $click_function = "WpvFileHandler_$result->post_id.showImage($result->file_id, $result->post_id, \"$result->action_type\", \"$hash\")";
        }
        else {
            $click_function = "WpvFileHandler_$result->post_id.doDefault(\"wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=$result->action_type&hash=$hash\")";
        }
        
        $htmlstring = "";
        $htmlstring .= "<td class='display-cell' onmouseover='WpvFileHandler_$result->post_id.mouseOverCell(this)' onmouseout='WpvFileHandler_$result->post_id.mouseOutCell(this)' onclick='$click_function'>";
        $htmlstring .= "<table style='width: 100%'><tr><td style='width: 100%'>";
        if ($display_option->display_text == "File Name" || $display_option->display_text == "Both") {
            $htmlstring .= "<div class='wpv-file-name'>";
            $htmlstring .= "$result->file_name";
            $htmlstring .= "</div>";
        }
        if ($display_option->display_text == "Comment" || $display_option->display_text == "Both") {
            $htmlstring .= "<div class='wpv-comment-text'>" . preg_replace("/\\\'/", "'", $result->comment_text) . "</div>";
        }
        $htmlstring .= "</td>";
        $htmlstring .= "<td>";
        $htmlstring .= "<img src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=thumbnail&action_type=$result->action_type&hash=$hash' title='$result->file_name.$result->file_ext' />";
        $htmlstring .= "</td></tr></table>";
        $htmlstring .= "</td>";
        
        return $htmlstring;
    }

    function GetThumbnailTop($result, $display_option) {
        $hash = WpvUtils::GetHashCode($result->file_id);
        $click_function = "";
        
        if ($result->action_type == "Download") {
            $click_function = "WpvFileHandler_$result->post_id.downloadFile(\"wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=$result->action_type&hash=$hash\")";
        }
        else if (WpvImage::IsSupportedImage($result->mime_type)) {
            $click_function = "WpvFileHandler_$result->post_id.showImage($result->file_id, $result->post_id, \"$result->action_type\", \"$hash\")";
        }
        else {
            $click_function = "WpvFileHandler_$result->post_id.doDefault(\"wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=$result->action_type&hash=$hash\")";
        }
        
        $htmlstring = "";
        $htmlstring .= "<td class='display-cell' onmouseover='WpvFileHandler_$result->post_id.mouseOverCell(this)' onmouseout='WpvFileHandler_$result->post_id.mouseOutCell(this)' onclick='$click_function'>";
        $htmlstring .= "<img src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=thumbnail&action_type=$result->action_type&hash=$hash' title='$result->file_name.$result->file_ext' />";
        if ($display_option->display_text == "File Name" || $display_option->display_text == "Both") {
            $htmlstring .= "<div class='wpv-file-name'>";
            $htmlstring .= "$result->file_name";
            $htmlstring .= "</div>";
        }
        if ($display_option->display_text == "Comment" || $display_option->display_text == "Both") {
            $htmlstring .= "<div class='wpv-comment-text'>" . preg_replace("/\\\'/", "'", $result->comment_text) . "</div>";
        }
        $htmlstring .= "</td>";
        
        return $htmlstring;
    }
    
    function GetThumbnailBottom($result, $display_option) {
        $hash = WpvUtils::GetHashCode($result->file_id);
        $click_function = "";
        
        if ($result->action_type == "Download") {
            $click_function = "WpvFileHandler_$result->post_id.downloadFile(\"wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=$result->action_type&hash=$hash\")";
        }
        else if (WpvImage::IsSupportedImage($result->mime_type)) {
            $click_function = "WpvFileHandler_$result->post_id.showImage($result->file_id, $result->post_id, \"$result->action_type\", \"$hash\")";
        }
        else {
            $click_function = "WpvFileHandler_$result->post_id.doDefault(\"wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=$result->action_type&hash=$hash\")";
        }
        
        $htmlstring = "";
        $htmlstring .= "<td class='display-cell' onmouseover='WpvFileHandler_$result->post_id.mouseOverCell(this)' onmouseout='WpvFileHandler_$result->post_id.mouseOutCell(this)' onclick='$click_function'>";
        if ($display_option->display_text == "File Name" || $display_option->display_text == "Both") {
            $htmlstring .= "<div class='wpv-file-name'>";
            $htmlstring .= "$result->file_name";
            $htmlstring .= "</div>";
        }
        if ($display_option->display_text == "Comment" || $display_option->display_text == "Both") {
            $htmlstring .= "<div class='wpv-comment-text'>" . preg_replace("/\\\'/", "'", $result->comment_text) . "</div>";
        }
        $htmlstring .= "<img src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$result->file_id&post_id=$result->post_id&file_mode=thumbnail&action_type=$result->action_type&hash=$hash' title='$result->file_name.$result->file_ext' />";
        $htmlstring .= "</td>";
        
        return $htmlstring;
    }
}
?>
