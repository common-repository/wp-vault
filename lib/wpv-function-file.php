<?php
class WpvFile {
    function ReassignFileOwner($user_id) {
        global $wpdb;
        global $wpv_file_table;
        
        $select_sql = "SELECT ID ";
        $select_sql .= "FROM $wpdb->users, $wpdb->usermeta ";
        $select_sql .= "WHERE user_id = $wpdb->users.ID AND "; 
        $select_sql .= "meta_key = 'student_user_level' AND ";
        $select_sql .= "meta_value = '10' ";
        $select_sql .= "ORDER BY user_registered ";
        $select_sql .= "LIMIT 1";
        
        $super_admin_id = $wpdb->get_var($select_sql, 0);
        
        $update_sql = "UPDATE $wpv_file_table ";
        $update_sql .= "SET owner_id = $super_admin_id ";
        $update_sql .= "WHERE owner_id = $user_id ";
        
        $wpdb->query($update_sql);
    }
    
    function GetFileCount($additional_query_string="") {
        global $wpdb;
        global $wpv_file_table;
        global $userdata;

        get_currentuserinfo();

        $select_sql = "SELECT COUNT(file_id) ";
        $select_sql .= "FROM $wpv_file_table ";
        
        if (current_user_can("wpv_browse_all_files")) {
            $select_sql .= "WHERE 1 = 1 ";            
        }
        else if (current_user_can("wpv_browse_own_files")) {
            $select_sql .= "WHERE $wpv_file_table.owner_id = $userdata->ID ";
        }
        else {
            die;
        }
        
        if ($additional_query_string != "")
            $select_sql .= "AND ($additional_query_string) ";
            
        return $wpdb->get_var($select_sql, 0);
    }
    
    function GetUniqueFileName($name, $additional_file_name_array=array()) {
        $file_name_array = WpvFile::GetFileNameArray(null, TRUE);
        foreach ($additional_file_name_array as $file_name) {
            array_push($file_name_array, strtoupper($file_name));
        }
        $name = substr($name, 0, 240);
        if (!in_array(strtoupper($name), $file_name_array))
            return $name;
        else {
            $ix = 1;
            while (in_array(strtoupper("$name ($ix)"), $file_name_array)) {
                $ix++;
            }
            return "$name ($ix)";
        }
    }
    
    function GetFileIdArray($stored_name_array) {
        global $wpdb;
        global $wpv_file_table;

        $select_sql = "SELECT file_id ";
        $select_sql .= "FROM $wpv_file_table ";
        $select_sql .= "WHERE stored_name IN (";
        foreach ($stored_name_array as $stored_name) {
            $select_sql .= "'$stored_name',";
        }
        $select_sql = rtrim($select_sql, ",") . ")";

        $result = $wpdb->get_col($select_sql, 0);
        if (count($result) == 0)
            return array();
        else
            return $result;
    }

    function GetFileNameArray($file_id_array=null, $to_upper=FALSE) {
        global $wpdb;
        global $wpv_file_table;

        $select_sql = "";
        if ($to_upper)
            $select_sql .= "SELECT UPPER(file_name) ";
        else
            $select_sql .= "SELECT file_name ";
        $select_sql .= "FROM $wpv_file_table ";
        if ($file_id_array != null && count($file_id_array) > 0) {
            $select_sql .= "WHERE file_id IN (";
            $select_sql .= implode($file_id_array, ",");
            $select_sql .= ")";
        }

        $result = $wpdb->get_col($select_sql, 0);
        if (count($result) == 0)
            return array();
        else
            return $result;
    }

    function GetFileTable($additional_query_string="", $order_by_string="", $limit_string="") {
        global $wpdb;
        global $wpv_file_table;
        global $userdata;

        get_currentuserinfo();

        $select_sql = "SELECT file_id, file_name, file_ext, file_size, file_image_width, file_image_height, mime_type, stored_name, owner.display_name owner_name, owner_id, DATE_FORMAT(stored_datetime, '%Y/%m/%d %k:%i') stored_datetime_modified ";
        $select_sql .= "FROM $wpv_file_table, $wpdb->users owner ";
        $select_sql .= "WHERE owner.ID = $wpv_file_table.owner_id ";

        if (current_user_can("wpv_browse_all_files")) {}
        else if (current_user_can("wpv_browse_own_files")) {
            $select_sql .= " AND $wpv_file_table.owner_id = $userdata->ID ";
        }
        else if ($userdata != null) {
            die;
        }
        
        if (preg_match("/^file_id = ([0-9]+)$/", $additional_query_string, $matches) > 0 && $order_by_string == "" && $limit_string == "") {
            $resultset = FALSE;
            if (($resultset = wp_cache_get("file_data_" . $matches[1], "wp-vault")) === FALSE) {
                $select_sql .= "AND ($additional_query_string) ";
                $resultset = $wpdb->get_results($select_sql);
                if (count($resultset) > 0) {
                    wp_cache_add("file_data_" . $matches[1], $resultset, "wp-vault");
                }
            }
            return $resultset;
        }

        if ($additional_query_string != "")
            $select_sql .= "AND ($additional_query_string) ";
        if ($order_by_string != "")
            $select_sql .= "ORDER BY $order_by_string ";
        if ($limit_string != "")
            $select_sql .= "LIMIT $limit_string ";
        return $wpdb->get_results($select_sql);
    }

    function GetFileStoredMonthArray() {
        static $stored_month_array;

        if ($stored_month_array == null) {
            global $wpv_file_table;
            global $wpdb;

            $select_sql = "SELECT DISTINCT ";
            $select_sql .= "DATE_FORMAT(stored_datetime, '%Y/%m') stored_month ";
            $select_sql .= "FROM $wpv_file_table ";
            $select_sql .= "ORDER BY stored_month DESC ";

            $stored_month_array = $wpdb->get_col($select_sql, 0);
        }
        return $stored_month_array;
    }

}
?>
