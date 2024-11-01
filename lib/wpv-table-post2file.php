<?php
class WpvPost2FileTable {
    function DisplayPost2FileTable($resultset, $form_element_array=array()) {
        global $wpv_options;
        
        $error_message = "";
        $thumbnail_path = WpvUtils::GetSysThumbnailPath($error_message);
        if ($thumbnail_path === FALSE) {
            echo "<div class='error fade'>";
            echo "Error acquiring the system thumbnail directory: <br />";
            echo $error_message;
            echo "</div>";
            return;
        }
        ?>
        <div class="submit">
        <?php
        foreach ($form_element_array as $element) {
            if (preg_match("/type=(\"button\"|\'button\')/", $element))
                echo $element;
        }
        ?>
        </div>
        <script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-post2file-table"; ?>"></script>
        
        <div class="wpv-tab-interface">
        <span class="wpv-tab-button">Linked Files</span>
        <div class="border">
        <?php
        if (count($resultset) == 0) {
        ?>
            No linked files
        <?php
        }
        else {            
            echo "<div>";
            echo "<a href='javascript:selectAllPost2Files()'>Select All</a> / <a href='javascript:unselectAllPost2Files()'>Unselect All</a>";
            echo "</div>";
            echo "<table id='wpv-post-file-table'>";
            foreach ($resultset as $result) {
                $hash = WpvUtils::GetHashCode($result->file_id);
                echo "<tbody id='post2file-row-group-$result->file_id' class='data-row' onclick='togglePost2FileCheckbox(this, $result->file_id)' onmouseover='this.style.cursor = \"pointer\"' onmouseout='this.style.cursor = \"default\";'>";
                echo "<tr id='$result->file_id'>";
                echo "<td rowspan='3' class='checkbox-cell'><input id='post2file-checkbox-$result->file_id' type='checkbox' value='$result->file_id' name='selected_post_file_id[]' onclick='togglePost2FileCheckbox(this, $result->file_id)'/></td>";
                echo "<td rowspan='3' width='100' class='image-cell'>";
                echo "<img src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$result->file_id&file_mode=sys-thumbnail&action_type=$result->action_type&hash=$hash' title='$result->file_name.$result->file_ext' />";
                if (WpvImage::IsSupportedImage($result->mime_type))
                    echo "<div><a href='javascript:WpvAdmin.openImageDisplay($result->file_id, \"" . get_settings("siteurl") . "\")' onclick='event.cancelBubble = true'>View</a></div> ";
                echo "</td>";
                echo "<td><strong>Sequence #: </strong>$result->sequence_num </td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td>";
                echo "<strong>File ID: </strong>$result->file_id<br />";
                echo "<strong>File Name: </strong>$result->file_name<br />";
                echo "<strong>Action Type: </strong>$result->action_type";
                echo "</td>";
                echo "</tr>";
                echo "<tr><td><strong>Comment: </strong>" . preg_replace("/\\\'/", "'", $result->comment_text) . "</td></tr>\n";
                echo "</tbody>";
                
                echo "<tr><td class='padding' colspan='2'></td></tr>\n";
            }
            echo "<tr><td colspan='3' style='border: 0px; text-align: center;'><b>" . count($resultset) . " linked file(s)</b></td></tr>";
            echo "</table>";
        }
        ?>
        </div>
        </div>
        <div class="submit">
        <?php
        foreach ($form_element_array as $element) {
            echo $element;
        }
        ?>
        </div>
        <?php
    }
    
    function DisplayBriefPost2FileTable($resultset) {
        global $wpv_options;
        
        $error_message = "";
        $thumbnail_path = WpvUtils::GetSysThumbnailPath($error_message);
        if ($thumbnail_path === FALSE) {
            echo "<div class='error fade'>";
            echo "Error acquiring the system thumbnail directory: <br />";
            echo $error_message;
            echo "</div>";
            return;
        }
        ?>
        
        <div style="border: 2px solid #909090; padding: 5px 5px 5px 5px;">
        <span style="font-weight: bold">Linked File Summary</span>
        <div class="border" style="margin-top: 10px">
        <?php
        if (count($resultset) == 0) {
        ?>
            No linked files
        <?php
        }
        else {
            echo "<table id='wpv-post-file-table' style='width: 250px'>";
            foreach ($resultset as $result) {
                echo "<tbody id='post2file-row-group-$result->file_id' class='data-row'>";
                echo "<tr id='$result->file_id'>";
                echo "<td><strong>Seq #: </strong>$result->sequence_num / <strong>File ID: </strong>$result->file_id</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td>";
                echo "<strong>Name: </strong>";
                if (WpvImage::IsSupportedImage($result->mime_type))
                    echo "<a href='javascript:WpvAdmin.openImageDisplay($result->file_id, \"" . get_settings("siteurl") . "\")' onclick='event.cancelBubble = true'>$result->file_name</a><br />";
                else
                    echo "$result->file_name<br />";
                echo "<strong>Action Type: </strong>$result->action_type";
                echo "</td>";
                echo "</tr>";
                echo "</tbody>";
                
                echo "<tr><td class='padding' colspan='2'></td></tr>\n";
            }
            echo "<tr><td colspan='3' style='border: 0px; text-align: center;'><b>" . count($resultset) . " linked file(s)</b></td></tr>";
            echo "</table>";
        }
        ?>
        </div>
        </div>
        <?php
    }

    function DisplayPost2FileImageList($resultset) {
        if (count($resultset) == 0) {
            echo "No linked files";
        }
        else {
            foreach ($resultset as $result) {
                $hash = WpvUtils::GetHashCode($result->file_id);
                echo "<div style='border: 1px solid #c0c0c0; float: left; width: 110px; height: 110px; text-align: center; vertical-align: middle; margin: 5px 5px 5px 5px;'>";
                echo "<img style='border: 0px; margin: 5px 5px 5px 5px;' src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$result->file_id&file_mode=sys-thumbnail&action_type=$result->action_type&hash=$hash' title='$result->file_name.$result->file_ext' />";
                echo "</div>";
            }
        }
    }
    
    function DisplayPost2FileEditTable($resultset, $form_element_array, $edited_id_array = array()) {
        global $wpv_options;
        global $wpdb;
        global $wpv_post2file_table;
        
        $action_type_enum = WpvUtils::ParseEnum($wpdb->get_var("SHOW COLUMNS FROM $wpv_post2file_table LIKE 'action_type'", 1));

        $error_message = "";
        $thumbnail_path = WpvUtils::GetSysThumbnailPath($error_message);
        if ($thumbnail_path === FALSE) {
            echo "<div class='error fade'>";
            echo "Error acquiring the system thumbnail directory: <br />";
            echo $error_message;
            echo "</div>";
            return;
        }
        ?>
        <div class="submit" style="width: 400px">
        <?php
        foreach ($form_element_array as $element) {
            if (preg_match("/type=(\"button\"|\'button\')/", $element))
                echo $element;
        }
        ?>
        </div>
        <table id="wpv-post-file-table">
        <?php
        if (count($resultset) == 0) {
        ?>
            <tr><td>No linked files to edit</td></tr>
        <?php
        }
        else {
            foreach ($resultset as $row) {
                $hash = WpvUtils::GetHashCode($row->file_id);
                echo "<tr>";
                echo "<td rowspan='3' class='image-cell'>";
                echo "<img src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$row->file_id&file_mode=sys-thumbnail&hash=$hash' title='$row->file_name.$row->file_ext' />";
                echo "</td>";
                echo "<td><strong>Sequence #: </strong>$row->sequence_num</td>";
                echo "</tr>";
                echo "<td>";
                echo "<strong>ID: </strong>$row->file_id<input type='hidden' name='selected_post_file_id[]' value='$row->file_id' /><br />";
                echo "<strong>File Name: </strong>$row->file_name<br />";
                echo "<strong>Action Type: ";
                echo "<select name='action_type_$row->file_id'>";
                foreach ($action_type_enum as $action_type) {
                    echo "<option value='$action_type'";
                    echo $row->action_type == $action_type ? " selected" : "";
                    echo ">$action_type</option>";
                }
                echo "</select>";
                echo "</td>";
                echo "</tr>";
                echo "<tr>";
                echo "<td colspan='2'><textarea name='comment_text_$row->file_id' rows='5' cols='50' onmousedown='event.cancelBubble = true'>" . preg_replace("/\\\'/", "'", $row->comment_text) . "</textarea></td>";
                echo "</tr>";
                echo "<tr><td class='padding' colspan='2'></td></tr>\n";
            }
            echo "<tr><td colspan='2' style='border: 0px; text-align: center;'><b>" . count($resultset) . " linked file(s) selected</b></td></tr>";
        }
        ?>
        </table>
        
        <div class="submit" style="width: 400px">
        <?php 
        foreach ($form_element_array as $element) {
            echo $element;
        }
        ?>
        </div>
        <?php
    }
}
?>
