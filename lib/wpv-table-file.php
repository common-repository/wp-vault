<?php
class WpvFileTable {
    
    function GetMaxColumnCount() {
        return 4;
    }
    
    function GetFilePerPage() {
        return 12;
    }
    
    function DisplayFileTable($form_element_array=array()) {
        global $wpv_options;

        $default_order = "stored_datetime DESC, file_name ASC";

        $_selected_tab = isset($_POST["selected_tab"]) ? $_POST["selected_tab"] : 0;
        $_order_by = isset($_POST["order_by"]) && $_POST["order_by"] != "" ? $_POST["order_by"] : $default_order;
        $column_count = 0;
        ?>
        <div class="submit">
        <?php
        foreach ($form_element_array as $element) {
            if (preg_match("/type=(\"button\"|\'button\')/", $element))
                echo $element;
        }
        ?>
        </div>
        <script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-file-table"; ?>"></script>

        <div class="wpv-tab-interface">
        <span class="wpv-tab-button<?php echo $_selected_tab == 0 ? "" : "-disabled"; ?>"><a href="javascript:void(0)" onclick="submitDefaultView(this)">Default View</a></span>
        <span class="wpv-tab-button<?php echo $_selected_tab == 1 ? "" : "-disabled"; ?>"><a href="javascript:void(0)" onclick="submitTagView(this)">View By Tag</a></span>
        <span class="wpv-tab-button<?php echo $_selected_tab == 2 ? "" : "-disabled"; ?>"><a href="javascript:void(0)" onclick="submitMonthView(this)">View By Month</a></span>
        <span class="wpv-tab-button<?php echo $_selected_tab == 3 ? "" : "-disabled"; ?>"><a href="javascript:void(0)" onclick="submitNameSearch(this)">Name Search</a></span>
        <br />
        <input type="hidden" name="selected_tab" id="selected-tab" value="<? echo $_selected_tab; ?>" />
        <input type="hidden" name="order_by" value="<?php echo $_order_by; ?>" />
        <div class="border" style="width: 700px">
        <div id="page-loading-message">Loading...</div>
        <div id="page-content">
        <div class="search-control">
        <?php
        WpvFileTable::DisplayFilePageControl($_selected_tab, $_order_by);
        ?>
        </div>
        
        <?php
        WpvFileTable::DisplayFilePage($_selected_tab, $_order_by);
        ?>
        </div>
        </div>
        </div>
        <div class="submit">
        <?php
        foreach ($form_element_array as $element) {
            echo $element;
        }
        ?>
        </div>
        </div>

        <?php
        return $resultset;
    }

    function DisplayFilePageControl($selected_tab, $order_by) {
        $_name_search_text = isset($_POST["name_search_text"]) ? trim($_POST["name_search_text"]) : "";

        if ($selected_tab == 1) {
            $tag_table = WpvFile2Tag::GetUsedFile2TagNameTable();
            if (count($tag_table) > 0) {
                $_tag = isset($_POST["tag"]) ? $_POST["tag"] : $tag_table[0]->tag_id;
            }
            else {
                $_tag = 0;
            }
            ?>
            Tag:
            <select name="tag" onchange="submitTagView()">
                <?php
                foreach ($tag_table as $tag_data) {
                ?>
                    <option value="<?php echo $tag_data->tag_id; ?>" <?php echo $_tag == $tag_data->tag_id ? "selected" : ""; ?>><?php echo $tag_data->tag_name; ?></option>
                <?php
                }
                ?>
            </select>
        <?php
        }

        if ($selected_tab == 2) {
            $month_array = WpvFile::GetFileStoredMonthArray();
            $_month = isset($_POST["month"]) ? $_POST["month"] : $month_array[0];
            ?>
            Month:
            <select name="month" onchange="submitMonthView()">
            <?php
            foreach ($month_array as $month) {
                echo "<option value='$month'";
                if ($_month == $month)
                    echo " selected";
                echo ">$month</option>";
            }
            ?>
            </select>
        <?php
        }

        if ($selected_tab == 3) {
        ?>
            Name: <input type="text" name="name_search_text" value="<?php echo $_name_search_text; ?>" size="15" /> <input type="button" value="Search" onclick="submitNameSearch()" />
        <?php
        }
        else {
        ?>
            <input type="hidden" name="name_search_text" value="<?php echo $_name_search_text; ?>" />
        <?php
        }
        ?>
        Sort By:
        <select name="order_by" onchange="submitSort(this)">
            <option value="stored_datetime ASC, file_name ASC" <?php echo $order_by == "stored_datetime ASC, file_name ASC" ? "selected" : ""; ?>>Stored Date, Oldest First</option>
            <option value="stored_datetime DESC, file_name ASC" <?php echo $order_by == "stored_datetime DESC, file_name ASC" ? "selected" : ""; ?>>Stored Date, Newest First</option>
            <option value="file_name ASC" <?php echo $order_by == "file_name ASC" ? "selected" : ""; ?>>File Name, A-Z</option>
            <option value="file_name DESC" <?php echo $order_by == "file_name DESC" ? "selected" : ""; ?>>File Name, Z-A</option>
        </select>
    <?php
    }
    
    function DisplayFilePage($selected_tab, $order_by) {
        global $userdata;
        
        $max_column_count = WpvFileTable::GetMaxColumnCount();
        $error_message = "";
        $column_count = 0;
        $file_count = 0;
        
        $_page_number = isset($_POST["page_number"]) ? $_POST["page_number"] : 1;
        $_name_search_text = isset($_POST["name_search_text"]) ? trim($_POST["name_search_text"]) : "";

        if (($thumbnail_path = WpvUtils::GetSysThumbnailPath($error_message)) === FALSE) {
            echo "<div class='error fade'>";
            echo "Error acquiring the system thumbnail directory: <br />";
            echo $error_message;
            echo "</div>";
            return;
        }

        if ($selected_tab == 0) {
            $file_count = WpvFile::GetFileCount();
            $limit = WpvUtils::GetLimitClause($file_count, WpvFileTable::GetFilePerPage(), $_page_number);
            $resultset = WpvFile::GetFileTable("", $order_by, $limit);
        }
        else if ($selected_tab == 1) {
            $tag_table = WpvFile2Tag::GetUsedFile2TagNameTable();
            if (isset($_POST["tag"]))
                $_tag = $_POST["tag"];
            else if (count($tag_table) > 0)
                $_tag = $tag_table[0]->tag_id;
            else
                $_tag = -1;
            $file_count = WpvFile2Tag::GetFile2TagCount("tag_id = $_tag");
            $limit = WpvUtils::GetLimitClause($file_count, WpvFileTable::GetFilePerPage(), $_page_number);
            $resultset = WpvFile2Tag::GetFile2TagTable("tag_id = $_tag", $order_by, $limit);
        }
        else if ($selected_tab == 2) {
            $month_array = WpvFile::GetFileStoredMonthArray();
            $_month = isset($_POST["month"]) ? $_POST["month"] : $month_array[0];
            $file_count = WpvFile::GetFileCount("DATE_FORMAT(stored_datetime, '%Y/%m') = '$_month'");
            $limit = WpvUtils::GetLimitClause($file_count, WpvFileTable::GetFilePerPage(), $_page_number);
            $resultset = WpvFile::GetFileTable("DATE_FORMAT(stored_datetime, '%Y/%m') = '$_month'", $order_by, $limit);
        }
        else if ($selected_tab == 3) {
            if ($_name_search_text == "") {
                $resultset = array();
                $file_count = 0;
            }
            else {
                $file_count = WpvFile::GetFileCount("file_name LIKE '%$_name_search_text%' ");
                $limit = WpvUtils::GetLimitClause($file_count, WpvFileTable::GetFilePerPage(), $_page_number);
                $resultset = WpvFile::GetFileTable("file_name LIKE '%$_name_search_text%' ", $order_by, $limit);
            }
        }
        else {
            echo "Invalid tab index <br />";
            die;
        }

        // Create a hash table that contains tags for files being displayed.
        $file_id_array = array();
        $file_tag_table = array();
        if (count($resultset) > 0) {
            foreach ($resultset as $result) {
                array_push($file_id_array, $result->file_id);
            }
            foreach (WpvFile2Tag::GetUsedFile2TagTable("file_id IN (" . implode($file_id_array, ",") . ")") as $file_tag) {
                if (!array_key_exists("$file_tag->file_id", $file_tag_table)) {
                    $file_tag_table["$file_tag->file_id"] = array();
                }
                array_push($file_tag_table["$file_tag->file_id"], $file_tag);
            }
        }
        
        // Gray out files that are used.
        if (class_exists('WpvPost2File') && isset($_POST["post_id"]) && $_POST["post_id"] != "") {
            $disabled_file_id_array = WpvPost2File::GetUsedFileIdArray($_POST["post_id"]);
        }
        else {
            $disabled_file_id_array = array();
        }

        echo "<table id='wpv-file-table'>";
        if (count($resultset) == 0 && $_selected_tab != 3) {
            echo "<tr><td>No file was found.</td></tr>";
        }
        else if (count($resultset) == 0 && $_name_search_text != "") {
            echo "<tr><td>No file was found.</td></tr>";
        }
        else if (count($resultset) == 0) {
            echo "<tr><td>Enter search text above.</td></tr>";
        }
        else {
            echo "<tr>";
            echo "<td style='border: 0px;'></td>";
            for ($i = 0; $i < $max_column_count; $i++) {
                echo "<td class='place-holder-horizontal'><div class='place-holder-horizontal'></div></td>";
            }
            echo "</tr>";
            echo "<tr>";
            echo "<td style='border: 0px;'></td>";
            echo "<td colspan='$max_column_count' style='text-align: left; border: 0px'>";
            echo "<div style='float: left'>";
            echo "<a href='javascript:selectAllFiles()'>Select All</a> / <a href='javascript:unselectAllFiles()'>Unselect All</a>";
            echo "</div>";
            echo "<div style='float: right'>";
            WpvUtils::DisplayPageLinks($file_count, WpvFileTable::GetFilePerPage(), $_page_number, "submitPage");
            echo "</div>";
            echo "</td>";
            echo "</tr>";
            
            foreach ($resultset as $result) {                
                $hash = WpvUtils::GetHashCode($result->file_id);
                $is_diabled = FALSE;
                
                if ($column_count == 0) {
                    echo "<tr>";
                    echo "<td class='place-holder-vertical'><div class='place-holder-vertical'></div></td>";
                }

                if (class_exists('WpvPost2File') && isset($_POST["post_id"]) && $_POST["post_id"] != "") {
                    $is_disabled = in_array($result->file_id, $disabled_file_id_array);
                }
                else if (($userdata->ID === $result->owner_id && current_user_can("wpv_edit_own_files")) || current_user_can("wpv_edit_all_files")) {
                    $is_disabled = FALSE;
                }
                else {
                    $is_disabled = TRUE;
                }

                if ($is_disabled == TRUE) {
                    echo "<td>";
                    echo "<div id='file-cell-$result->file_id' class='file-cell disabled' onmouseover='doFileMouseOver($result->file_id, false)' >";
                    echo "<div class='file-id'><input type='checkbox' id='file-checkbox-$result->file_id' value='$result->file_id' name='selected_file_id[]' disabled='true'/> ID: $result->file_id</div>";
                    echo "<div class='thumnail'>";
                    if ($error_message == "") {
						echo "<img src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$result->file_id&file_mode=sys-thumbnail&hash=$hash' title='$result->file_name.$result->file_ext' />";
					}
                    else {
                        echo $error_message;
					}
                    echo "</div>";
                }
                else {
                    echo "<td>";
                    echo "<div id='file-cell-$result->file_id' class='file-cell' onmouseover='doFileMouseOver($result->file_id, true)' onclick='toggleFileCheckbox(\"$result->file_id\")'>";
                    echo "<div class='file-id'><input type='checkbox' id='file-checkbox-$result->file_id' value='$result->file_id' name='selected_file_id[]' onclick='toggleFileCheckbox(\"$result->file_id\")'/> ID: $result->file_id</div>";
                    echo "<div class='thumbnail'>";
                    if ($error_message == "") {
                        echo "<img src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$result->file_id&file_mode=sys-thumbnail&hash=$hash' title='$result->file_name.$result->file_ext' />";
                    }
                    else {
                        echo $error_message;
                    }
                    echo "</div>";
                }

                echo "<div class='file-name'>$result->file_name</div>";
                
                echo "</div>";
                echo "</td>";
                $column_count++;
                if ($column_count % $max_column_count == 0) {
                    echo "</tr>";
                    $column_count = 0;
                }
            }
        }
        echo "<tr><td colspan='" . ($max_column_count+1) . "' style='border: 0px'>";
        WpvUtils::DisplayPageLocation($file_count, WpvFileTable::GetFilePerPage(), $_page_number);
        echo "</td></tr>";            
        echo "</table>";
        echo "<input id='page-number' type='hidden' name='page_number' value='$_page_number' /> ";
        
        echo "<div id='wpv-file-cell-popup' style='display: none'></div>";
        echo "<div style='visibility: hidden; width: 1px; height: 1px; overflow: hidden;'>";
        foreach ($resultset as $result) {
            $hash = WpvUtils::GetHashCode($result->file_id);
            echo "<div id='file-expand-data-$result->file_id' style='padding-top: 10px;'>";
            if ($error_message == "") {
                echo "<img src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$result->file_id&file_mode=sys-thumbnail&hash=$hash' title='$result->file_name.$result->file_ext' />";
            }
            else {
                echo $error_message;
            }
            echo "<div class='file-name'>$result->file_name</div>";
            echo "      <div><span>Owner:</span> $result->owner_name</div>";
            echo "      <div><span>Stored Date:</span> $result->stored_datetime_modified</div>";
            echo "      <div><span>File Size:</span> $result->file_size bytes</div>";
            if (WpvImage::IsSupportedImage($result->mime_type))
                echo "      <div><span>Dimension:</span> $result->file_image_width x $result->file_image_height</div>";
            echo "      <div><span>Mime Type:</span> $result->mime_type</div>";
            echo "      <div style='white-space: normal'><span>Tags:</span> ";
            if (array_key_exists("$result->file_id", $file_tag_table)) {
                $tags = "";
                foreach ($file_tag_table["$result->file_id"] as $tag_data) {
                    $tags .= $tag_data->tag_name . ", ";
                }
                echo rtrim($tags, ", ");
            }
            else {
                echo "--";
            }
            echo "      </div>";
            echo "      <div>";
            if (WpvImage::IsSupportedImage($result->mime_type)) {
                echo "      <a href='javascript:WpvAdmin.openImageDisplay($result->file_id, \"" . get_settings("siteurl") . "\")' onclick='event.cancelBubble = true'>View</a> / ";
            }
            echo "      <a href='" . get_bloginfo("siteurl") . "/?wpv_file_id=$result->file_id&file_mode=download&hash=$hash' onclick='event.cancelBubble = true'>Download</a>";
            echo "      </div>";
            echo "</div>";
        }
        echo "</div>";
    }
    
    function DisplayFileEditTable($resultset, $form_element_array=array()) {
        global $wpv_options;
        global $wpdb;

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
        <table id="wpv-rename-file-table">
        <?php
        if (count($resultset) == 0) {
        ?>
            <tr><td>No files selected</td></tr>
        <?php
        }
        else {
            $user_table = null;
            
            if (current_user_can("wpv_assign_files")) {
                $user_table = $wpdb->get_results("SELECT ID, display_name from $wpdb->users ORDER BY display_name");
            }
            foreach ($resultset as $row) {
                $hash = WpvUtils::GetHashCode($row->file_id);
                echo "<tr>";
                echo "<td rowspan='2' class='image-cell'>";
                echo "<img src='" . get_bloginfo("siteurl") . "/?wpv_file_id=$row->file_id&file_mode=sys-thumbnail&hash=$hash' title='$row->file_name.$row->file_ext' />";
                echo "</td>";
                echo "<td style='vertical-align: middle'><strong>ID: </strong>$row->file_id<input type='hidden' name='edit_file_id[]' value='$row->file_id' /></td>";
                echo "</tr>";
                echo "<td>";
                echo "<strong>File Name: ";
                echo "<input type='text' name='new_file_name_$row->file_id' value='$row->file_name' maxlength='240' size='20' onmousedown='event.cancelBubble = true' /> ";
                echo "<input type='hidden' name='old_file_name_$row->file_id' value='$row->file_name' />";
                echo "<span class='submit'><input type='button' value='Reset' onclick='this.form.new_file_name_$row->file_id.value = \"$row->file_name\"' /></span>";
                
                if (current_user_can("wpv_assign_files")) {
                    echo "<br />";
                    echo "<strong>Owner: </strong>";
                    echo "<select name='new_owner_id_$row->file_id'>";
                    foreach ($user_table as $user) {
                        echo "<option value='$user->ID'";
                        if ($row->owner_id == $user->ID)
                            echo " selected";
                        echo ">$user->display_name";
                        echo "</option>";
                    }
                    echo "</select>";
                    echo "<input type='hidden' name='old_owner_id_$row->file_id' value='$row->owner_id' />";
                }
                
                echo "</td>";
                echo "</tr>";
                echo "<tr><td class='padding' colspan='2'></td></tr>\n";
            }
            echo "<tr><td colspan='2' style='border: 0px; text-align: center;'><b>" . count($resultset) . " file(s) selected</b></td></tr>";
        }
        ?>
        </table>
        <div class="submit">
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
