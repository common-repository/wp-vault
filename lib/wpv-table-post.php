<?php
class WpvPostTable {
    function DisplayPostTable() {
        $_selected_tab_index = isset($_POST["selected_tab_index"]) ? $_POST["selected_tab_index"] : 0;
        $_page_number = isset($_POST["page_number"]) ? $_POST["page_number"] : 1;
        $_post_id = isset($_POST["post_id"]) ? $_POST["post_id"] : -1;
        $_sort_by = isset($_POST["sort_by"]) ? $_POST["sort_by"] : "post_modified";
        $_sort_modifier = isset($_POST["sort_modifier"]) ? $_POST["sort_modifier"] : "DESC";
        ?>
        <div class="wpv-tab-interface">
        <span class="wpv-tab-button<?php echo $_selected_tab_index == 0 ? "" :  "-disabled"; ?>"><a href="javascript:void(0)" onclick="WpvPost.submitTabSelection(0, this)">Posts</a></span>
        <span class="wpv-tab-button<?php echo $_selected_tab_index == 1 ? "" :  "-disabled"; ?>"><a href="javascript:void(0)" onclick="WpvPost.submitTabSelection(1, this)">Pages</a></span>
        <span class="wpv-tab-button<?php echo $_selected_tab_index == 2 ? "" :  "-disabled"; ?>"><a href="javascript:void(0)" onclick="WpvPost.submitTabSelection(2, this)">Posts by Posted Month</a></span>
        <span class="wpv-tab-button<?php echo $_selected_tab_index == 3 ? "" :  "-disabled"; ?>"><a href="javascript:void(0)" onclick="WpvPost.submitTabSelection(3, this)">Posts by Category</a></span>
        <span class="wpv-tab-button<?php echo $_selected_tab_index == 4 ? "" :  "-disabled"; ?>"><a href="javascript:void(0)" onclick="WpvPost.submitTabSelection(4, this)">Search</a></span>
        
        <div class="border">
        <div id="page-loading-message">Loading...</div>
            <div id="page-content">
            <?php
            WpvPostTable::GetPostPage($_selected_tab_index);
            ?>
            </div>
        </div>
        </div>
        <input id='page-number' type='hidden' name='page_number' value='<?php echo $_page_number; ?>' />
        <input type="hidden" name="action" value="" />
        <input type="hidden" name="cookie" value="" />
        <input type="hidden" name="post_id" value="<?php echo $_post_id; ?>" />
        <input type="hidden" name="proc" value="post" />
        <input type="hidden" name="selected_tab_index" value="<?php echo $_selected_tab_index; ?>" />
        <input type="hidden" name="sort_by" value="<? echo $_sort_by; ?>" />
        <input type="hidden" name="sort_modifier" value="<? echo $_sort_modifier; ?>" />
        <input type="hidden" name="page" value="wp-vault/wpv-link-manager.php" />
        <input type="hidden" name="requestUri" value="<?php echo get_bloginfo("siteurl"); ?>/wp-admin/admin-ajax.php" />
    <?php
    }
    
    function GetPostPage($selected_tab_index) {
        $_page_number = isset($_POST["page_number"]) ? $_POST["page_number"] : 1;
        $_sort_by = isset($_POST["sort_by"]) ? $_POST["sort_by"] : "post_modified";
        $_sort_modifier = isset($_POST["sort_modifier"]) ? $_POST["sort_modifier"] : "DESC";

        $_title_search_type = isset($_POST["title_search_type"]) ? $_POST["title_search_type"] : "post";
        $_title_search_text = isset($_POST["title_search_text"]) ? trim($_POST["title_search_text"]) : "";

        $posts = array();
        $post_per_page = 20;
        $post_type = "";
        $month_array = array();
        $post_count = 0;

        if ($selected_tab_index == 0) {
            $post_type = "post";
            $post_count = WpvPost::GetPostCount("post");
            $posts = WpvPost::GetPostTable($post_type, "", "$_sort_by $_sort_modifier", WpvUtils::GetLimitClause($post_count, $post_per_page, $_page_number));
        }
        else if ($selected_tab_index == 1) {
            $post_type = "page";
            $post_count = WpvPost::GetPostCount("page");
            $posts = WpvPost::GetPostTable($post_type, "", "$_sort_by $_sort_modifier", WpvUtils::GetLimitClause($post_count, $post_per_page, $_page_number));
        }
        else if ($selected_tab_index == 2) {
            $post_type = "post";
            $month_array = WpvPost::GetPostedMonthArray();
            $_month_option = isset($_POST["month_option"]) ? $_POST["month_option"] : $month_array[0];
            
            if ($_month_option == "Draft") {
                $post_count = WpvPost::GetPostCount($post_type, "post_status = 'draft'");
                $posts = WpvPost::GetPostTable($post_type, "post_status = 'draft'", "$_sort_by $_sort_modifier", WpvUtils::GetLimitClause($post_count, $post_per_page, $_page_number));
            }
            else {    
                $post_count = WpvPost::GetPostCount($post_type, "DATE_FORMAT(post_date, '%Y/%m') = '$_month_option' AND post_status != 'draft'");
                $posts = WpvPost::GetPostTable($post_type, "DATE_FORMAT(post_date, '%Y/%m') = '$_month_option' AND post_status != 'draft'", "$_sort_by $_sort_modifier", WpvUtils::GetLimitClause($post_count, $post_per_page, $_page_number));
            }
        }
        else if ($selected_tab_index == 3) {
            $post_type = "post";
            $category_table = WpvPost::GetAvailableCategories();
            $_category_option = isset($_POST["category_option"]) ? $_POST["category_option"] : $category_table[0]->category_id;
            $additional_query_string = WpvPost::GenerateCategorySQL($_category_option);
            $post_count = WpvPost::GetPostCount($post_type, $additional_query_string);
            $posts = WpvPost::GetPostTable($post_type, $additional_query_string, "$_sort_by $_sort_modifier", WpvUtils::GetLimitClause($post_count, $post_per_page, $_page_number));
        }
        else if ($selected_tab_index == 4) {
            $post_type = $_title_search_type;
            if ($_title_search_text != "") {
                $post_count = WpvPost::GetPostCount($post_type, "post_title LIKE '%$_title_search_text%'");
                $posts = WpvPost::GetPostTable($post_type, "post_title LIKE '%$_title_search_text%'", "$_sort_by $_sort_modifier", WpvUtils::GetLimitClause($post_count, $post_per_page, $_page_number));
            }
        }
        
        ?>
        <div class="search-control">
        <?php
        if ($selected_tab_index == 2) {
        ?>
            Month:
            <select name="month_option" onchange="WpvPost.submitPage(1)">
            <?php
            foreach ($month_array as $month) {
                echo "<option value='$month'";
                if (strstr($_month_option, $month) !== FALSE)
                    echo " selected";
                echo ">$month</option>";
            }
            ?>
            </select>
        <?php
        }
        
        if ($selected_tab_index == 3) {
        ?>
            Category:
            <select name="category_option" onchange="WpvPost.submitPage(1)">
            <?php
            foreach ($category_table as $category) {
                echo "<option value='$category->category_id'";
                if ($category->category_id == $_category_option)
                    echo " selected";
                echo ">$category->cat_name</option>";
            }
            ?>
            </select>
        <?php
        }

        if ($selected_tab_index == 4) {
        ?>
        <div>
            Title: <input type="text" name="title_search_text" value="<?php echo $_title_search_text; ?>" size="15" /> 
            <label for="title-search-type1"><input type="radio" id="title-search-type1" name="title_search_type" value="post" <?php echo $_title_search_type == "post" ? "checked" : ""?>/> Post</label>
            <label for="title-search-type2"><input type="radio" id="title-search-type2" name="title_search_type" value="page" <?php echo $_title_search_type == "page" ? "checked" : ""?>/> Page</label>
            <label for="title-search-type3"><input type="radio" id="title-search-type3" name="title_search_type" value="both" <?php echo $_title_search_type == "both" ? "checked" : ""?>/> Both</label>
            <input type="button" value="Search" onclick="WpvPost.submitPage(1)" />
        </div>
        <?php
        }
        else {
        ?>
            <input type="hidden" name="title_search_text" value="<?php echo $_title_search_text; ?>" />
            <input type="hidden" name="title_search_type" value="<?php echo $_title_search_type; ?>" />
        <?php
        }
        ?>
        </div>
        <?php
        if (count($posts) > 0) {
            echo "<div class='page-control'>";
            WpvUtils::DisplayPageLinks($post_count, $post_per_page, $_page_number, "WpvPost.submitPage");
            echo "</div>";
        }
        ?>

        <table id="wpv-post-table" cellspacing="0">
        <?php
        if (count($posts) > 0) {
        ?>
            <tr>
                <td class="header" style='text-align: center'><a href="javascript:void(0)" onclick="WpvPost.submitSort('post_id')">ID</a><?php if ($_sort_by == "post_id") echo $_sort_modifier == "ASC" ? " &darr;" : " &uarr;"; ?></td>
                <td class="header"><a href="javascript:void(0)" onclick="WpvPost.submitSort('post_title')">Title</a><?php if ($_sort_by == "post_title") echo $_sort_modifier == "ASC" ? " &darr;" : " &uarr;"; ?></td>
                <?php
                if ($post_type != "page") {
                ?>
                    <td class="header">Categories</td>
                <?php
                }
                if ($_selected_tab_index == 4) {
                ?>
                    <td class="header">Type</td>
                <?php
                }
                ?>
                <td class="header"><a href="javascript:void(0)" onclick="WpvPost.submitSort('display_name')">Author</a><?php if ($_sort_by == "display_name") echo $_sort_modifier == "ASC" ? " &darr;" : " &uarr;"; ?></td>
                <td class="header" style='text-align: center'><a href="javascript:void(0)" onclick="WpvPost.submitSort('post_status')">Status</a><?php if ($_sort_by == "post_status") echo $_sort_modifier == "ASC" ? " &darr;" : " &uarr;"; ?></td>
                <td class="header"><a href="javascript:void(0)" onclick="WpvPost.submitSort('post_date')">Post Date</a><?php if ($_sort_by == "post_date") echo $_sort_modifier == "ASC" ? " &darr;" : " &uarr;"; ?></td>
                <td class="header"><a href="javascript:void(0)" onclick="WpvPost.submitSort('post_modified')">Last Modified</a><?php if ($_sort_by == "post_modified") echo $_sort_modifier == "ASC" ? " &darr;" : " &uarr;"; ?></td>
                <td class="header">&nbsp;</td>
            </tr>
            <?php
            $i = 0;
            foreach ($posts as $post) {
                if ($i % 2 == 0)
                    echo "<tr class='post-even" . ($post->post_id == $_post_id ? " post-selected" : "") . "'>";
                else
                    echo "<tr class='post-odd" . ($post->post_id == $_post_id ? " post-selected" : "") . "'>";
                echo "<td style='text-align: center'>$post->post_id</td>";
                echo "<td>$post->post_title</td>";
                if ($post_type != "page") {
                    echo "<td style='white-space: nowrap'>";
                    if ($post->post_type == "post")
                        echo WpvPost::GetPostCategories($post->post_id);
                    else
                        echo "--";
                    echo "</td>";
                }
                if ($_selected_tab_index == 4) {
                    echo "<td>$post->post_type</td>";
                }
                echo "<td style='white-space: nowrap'>$post->display_name</td>";
                echo "<td style='text-align: center'>$post->post_status</td>";
                echo "<td>$post->post_date_formatted</td>";
                echo "<td>$post->post_modified_formatted</td>";
                echo "<td style='white-space: nowrap'>";
                if ($post->post_id == $_post_id)
                    echo "Selected / ";
                else
                    echo "<a href='javascript:void(0)' onclick='WpvPost.selectPost($post->post_id)'>Select</a>&nbsp;/&nbsp;";
                echo "<a href='javascript:void(0)' onclick='WpvPost.viewPreview(\"" . get_permalink($post->post_id) . "&preview=true\")'>Preview</a>";
                echo "</td>";
                echo "</tr>\n";
                
                $i++;
            }
            ?>
            <tr>
                <td colspan="8" id="wpv-post-footer" style="padding-top: 20px;">
                    <?php
                    WpvUtils::DisplayPageLocation($post_count, $post_per_page, $_page_number);
                    ?>
                </td>
            </tr>
        <?php
        } 
        else {
        ?>
            <tr>
                <td colspan="8" id="wpv-post-footer">
                    <?php
                    if ($_selected_tab_index != 4 || $_title_search_text != "")
                        echo "No record found.";
                    ?>
                </td>
            </tr>
        <?php
        }
        ?>    
        </table>
    <?php
    }
}
?>