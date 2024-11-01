<?php
class WpvFile2Tag {
    function GetFile2TagCount($additional_query_string="", $order_by="", $limit="") {
        global $wpdb;
        global $wpv_file_table;
        global $wpv_file2tag_table;

        $select_sql = "SELECT COUNT($wpv_file_table.file_id) ";
        $select_sql .= "FROM $wpv_file2tag_table, $wpv_file_table, $wpdb->users owner ";
        $select_sql .= "WHERE $wpv_file_table.file_id = $wpv_file2tag_table.file_id ";
        $select_sql .= "AND owner.ID = $wpv_file_table.owner_id ";
        if ($additional_query_string != "")
            $select_sql .= "AND ($additional_query_string) ";
            
        return $wpdb->get_var($select_sql, 0);
    }

    function GetFile2TagTable($additional_query_string="", $order_by="", $limit="") {
        global $wpdb;
        global $wpv_file_table;
        global $wpv_file2tag_table;

        $select_sql = "SELECT tag_id, $wpv_file_table.file_id, file_name, file_ext, mime_type, stored_name, owner.display_name owner_name, DATE_FORMAT(stored_datetime, '%Y/%m/%d %k:%i') stored_datetime_modified ";
        $select_sql .= "FROM $wpv_file2tag_table, $wpv_file_table, $wpdb->users owner ";
        $select_sql .= "WHERE $wpv_file_table.file_id = $wpv_file2tag_table.file_id ";
        $select_sql .= "AND owner.ID = $wpv_file_table.owner_id ";
        if ($additional_query_string != "")
            $select_sql .= "AND ($additional_query_string) ";
        if ($order_by != "")
            $select_sql .= "ORDER BY $order_by ";
        if ($limit != "")
            $select_sql .= "LIMIT $limit ";

        return $wpdb->get_results($select_sql);
    }

    function GetUsedFile2TagTable($additional_query_string="") {
        global $wpdb;
        global $wpv_tag_table;
        global $wpv_file2tag_table;

        $select_sql = "SELECT DISTINCT $wpv_tag_table.tag_id, tag_name, file_id ";
        $select_sql .= "FROM $wpv_tag_table, $wpv_file2tag_table ";
        $select_sql .= "WHERE $wpv_tag_table.tag_id = $wpv_file2tag_table.tag_id ";
        if ($additional_query_string != "")
            $select_sql .= "AND ($additional_query_string) ";
        $select_sql .= "ORDER BY tag_name ";

        return $wpdb->get_results($select_sql);
    }

    function GetUsedFile2TagNameTable($additional_query_string="") {
        static $resultset;
        
        if ($resultset == null) {
            global $wpdb;
            global $wpv_tag_table;
            global $wpv_file2tag_table;

            $select_sql = "SELECT DISTINCT $wpv_tag_table.tag_id, tag_name ";
            $select_sql .= "FROM $wpv_tag_table, $wpv_file2tag_table ";
            $select_sql .= "WHERE $wpv_tag_table.tag_id = $wpv_file2tag_table.tag_id ";
            if ($additional_query_string != "")
                $select_sql .= "AND ($additional_query_string) ";
            $select_sql .= "ORDER BY tag_name ";

            $resultset = $wpdb->get_results($select_sql);
        }
        return $resultset;
    }
}
?>
