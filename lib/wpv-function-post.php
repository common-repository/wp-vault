<?php
class WpvPost {
    function GenerateCategorySQL($category_id) {
        global $wpdb;
        
        $select_sql = "posts.ID IN (SELECT DISTINCT post_id ";
        $select_sql .= "FROM $wpdb->post2cat post2cat ";
        $select_sql .= "WHERE category_ID = $category_id) ";
        
        return $select_sql;
    }
        
    function GetPostCount($post_type="post", $additional_query_string="") {
        global $userdata;
        global $wpdb;

        get_currentuserinfo();

        $select_sql = "SELECT COUNT(posts.ID) post_count ";
        $select_sql .= "FROM $wpdb->posts posts ";
        $select_sql .= "WHERE post_type != '' ";
        if ($post_type == "post" || $post_type == "page")
            $select_sql .= "AND post_type = '$post_type' ";
        if ($additional_query_string != "")
            $select_sql .= "AND ($additional_query_string) ";

        if (current_user_can("wpv_access_all_posts")) {}
        else if (current_user_can("wpv_access_own_posts"))
            $select_sql .= "AND post_author = $userdata->ID ";
            
        return $wpdb->get_var($select_sql);
    }

    function GetPost($post_id) {
        static $post_table;
        
        if (!isset($post_table))
            $post_table = array();
            
        if (array_key_exists("$post_id", $post_table)) {
            return $post_table["$post_id"];
        }
        else {
            global $userdata;
            global $wpdb;

            $select_sql = "SELECT ID post_id, post_title, post_type, post_status, DATE_FORMAT(post_modified, '%Y/%m/%d %k:%i') post_modified_formatted, DATE_FORMAT(post_date, '%Y/%m/%d %k:%i') post_date_formatted, post_author ";
            $select_sql .= "FROM $wpdb->posts posts ";
            $select_sql .= "WHERE ID = $post_id ";
            if (current_user_can("wpv_access_all_posts")) {}
            else if (current_user_can("wpv_access_own_posts"))
                $select_sql .= "AND post_author = $userdata->ID ";

            $result = $wpdb->get_results($select_sql);

            if (count($result) > 0) {
                $post_table["$post_id"] = $result[0];
                return $result[0];
            }
            else {
                $post_table["$post_id"] = FALSE;
                return FALSE;
            }
        }
    }

    function GetPostTable($post_type, $additional_query_string="", $order_by_string="post_modified DESC", $limit_string="0, 20") {
        global $userdata;
        global $wpdb;

        get_currentuserinfo();

        $select_sql = "SELECT posts.ID post_id, post_title, post_type, post_status, display_name, users.ID user_id, DATE_FORMAT(post_modified, '%Y/%m/%d %k:%i') post_modified_formatted, DATE_FORMAT(post_date, '%Y/%m/%d %k:%i') post_date_formatted ";
        $select_sql .= "FROM $wpdb->posts posts, $wpdb->users users ";
        $select_sql .= "WHERE post_author = users.ID ";
        if ($post_type == "post" || $post_type == "page")
            $select_sql .= "AND post_type = '$post_type' ";
        if ($additional_query_string != "")
            $select_sql .= "AND ($additional_query_string) ";
        if (current_user_can("wpv_access_all_posts")) {}
        else if (current_user_can("wpv_access_own_posts"))
            $select_sql .= "AND post_author = $userdata->ID ";
        if ($order_by_string != "")
            $select_sql .= "ORDER BY $order_by_string ";
        if ($limit_string != "")
            $select_sql .= "LIMIT $limit_string ";
            
        return $wpdb->get_results($select_sql);
    }

    function GetPostCategories($post_id) {
        static $categories_table;

        if ($categories_table == null)
            $categories_table = array();
            
        if (!array_key_exists("$post_id", $categories_table)) {
            global $wpdb;

            if ($wpdb->get_var("SHOW TABLES LIKE '$wpdb->post2cat'") == $wpdb->post2cat) {            
                // WP 2.2 and before.
                $select_sql = "SELECT DISTINCT ";
                $select_sql .= "post_id, cat_name ";
                $select_sql .= "FROM $wpdb->post2cat post2cat, $wpdb->categories categories ";
                $select_sql .= "WHERE cat_ID = category_id ";
                $select_sql .= "ORDER BY post_id, cat_name ASC ";
                
            }
            else {
                // WP 2.3.
                $select_sql = "SELECT c.object_id post_id, b.name cat_name ";
                $select_sql .= "FROM $wpdb->term_taxonomy a, $wpdb->terms b, $wpdb->term_relationships c ";
                $select_sql .= "WHERE a.term_id = b.term_id ";
                $select_sql .= "AND a.taxonomy = 'category' ";
                $select_sql .= "AND a.term_taxonomy_id = c.term_taxonomy_id ";
                $select_sql .= "ORDER BY post_id, cat_name ASC ";
            }
            $categories = $wpdb->get_results($select_sql);
            foreach ($categories as $category_data) {
                if ($categories_table["$category_data->post_id"] == null) {
                    $categories_table["$category_data->post_id"] = "$category_data->cat_name<br />";
                }
                else {
                    $categories_table["$category_data->post_id"] .= "$category_data->cat_name<br />";
                }
            }
        }
        return $categories_table["$post_id"];
    }

    function GetAvailableCategories() {
        global $wpdb;

        $select_sql = "SELECT DISTINCT ";
        $select_sql .= "category_id, cat_name ";
        $select_sql .= "FROM $wpdb->post2cat post2cat, $wpdb->categories categories ";
        $select_sql .= "WHERE cat_ID = category_id ";
        $select_sql .= "ORDER BY category_nicename, cat_name ASC ";

        return $wpdb->get_results($select_sql);
    }

    function GetPostedMonthArray() {
        global $wpdb;

        $select_sql = "SELECT DISTINCT ";
        $select_sql .= "DATE_FORMAT(post_date, '%Y/%m') post_month ";
        $select_sql .= "FROM $wpdb->posts ";
        $select_sql .= "WHERE DATE_FORMAT(post_date, '%Y/%m') != '0000/00' ";
        $select_sql .= "ORDER BY post_date DESC ";

        $month_array = $wpdb->get_col($select_sql, 0);
        array_push($month_array, "Draft");

        return $month_array;
    }
}
?>
