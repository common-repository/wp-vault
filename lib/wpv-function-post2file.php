<?php
class WpvPost2File {
    function DeletePost2FileData($post_id) {
        global $wpdb;
        global $wpv_post2file_table;

        $wpdb->query("START TRANSACTION;");
        $delete_sql = "DELETE FROM $wpv_post2file_table ";
        $delete_sql .= "WHERE post_id = $post_id";
        if ($wpdb->query($delete_sql) === FALSE) {
            $wpdb->query("ROLLBACK;");
            return;
        }
        
        if (WpvPost2File::GetPost2FileCount($post_id) == 0) {
            global $wpv_display_option_table;
            
            $delete_sql = "DELETE FROM $wpv_display_option_table WHERE post_id = $post_id";
            if ($wpdb->query($delete_sql) === FALSE) {
                $wpdb->query("ROLLBACK;");
                return;
            }            
        }
        
        $wpdb->query("COMMIT;");
        wp_cache_delete("post2file_table_$post_id", "wp-vault");

        return;
    }

    function GetPost2FileTable($post_id, $additional_query_string="") {
        global $wpdb;
        global $wpv_file_table;
        global $wpv_post2file_table;

        
        $select_sql = "SELECT post_id, sequence_num, file_name, file_size, file_image_width, file_image_height, mime_type, stored_name, action_type, $wpv_post2file_table.file_id, file_name, file_ext, $wpv_post2file_table.comment_text, owner_id ";
        $select_sql .= "FROM $wpv_post2file_table, $wpv_file_table ";
        $select_sql .= "WHERE $wpv_post2file_table.file_id = $wpv_file_table.file_id AND post_id = $post_id ";

        if ($additional_query_string == "") {
            $resultset = null;
            if (($resultset = wp_cache_get("post2file_table_$post_id", "wp-vault")) === FALSE) {
                $resultset = $wpdb->get_results("$select_sql ORDER BY sequence_num, last_update_datetime DESC");
                if (count($resultset) > 0)
                    wp_cache_add("post2file_table_$post_id", $resultset, "wp-vault");
            }
            return $resultset;
        }
        else {
            return $wpdb->get_results("$select_sql AND ($additional_query_string) ORDER BY sequence_num, last_update_datetime DESC");
        }
    }

    function GetPost2FileCount($post_id) {
        $resultset = null;
        if (($resultset = wp_cache_get("post2file_table_$post_id", "wp-vault")) === FALSE) {
            global $wpdb;
            global $wpv_post2file_table;

            $select_sql = "SELECT COUNT(post_id) ";
            $select_sql .= "FROM $wpv_post2file_table ";
            $select_sql .= "WHERE post_id = $post_id ";

            return $wpdb->get_var($select_sql);
        }
        else {
            return count($resultset);
        }
    }

    function UpdateFileSequence($post_id) {
        global $wpdb;
        global $wpv_post2file_table;

        $returned = null;
        $resultset = WpvPost2File::GetPost2FileTable($post_id);
        $i = 1;

        foreach ($resultset as $result) {
            if ($result->sequence_num != $i)
                $returned = $wpdb->query("UPDATE $wpv_post2file_table SET sequence_num = $i WHERE post_id = $post_id AND file_id = $result->file_id");

                if ($returned === FALSE)
                    return $returned;
            $i++;
        }
        return $returned;
    }
    
    function GetUsedFileIdArray($post_id) {
        $resultset = null;
        if (($resultset = wp_cache_get("post2file_table_$post_id", "wp-vault")) === FALSE) {
            global $wpdb;
            global $wpv_post2file_table;

            $select_sql = "SELECT file_id ";
            $select_sql .= "FROM $wpv_post2file_table ";
            $select_sql .= "WHERE post_id = $post_id ";
            $result_array = $wpdb->get_col($select_sql, 0);
            
            if (count($result_array) > 0)
                return $result_array;
            else
                return array();
        }
        else {
            $used_file_id_array = array();
            $resultset = WpvPost2File::GetPost2FileTable($post_id);
            
            foreach ($resultset as $result) {
                array_push($used_file_id_array, $result->file_id);
            }
            return $used_file_id_array;
        }
    }
}
?>
