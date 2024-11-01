<?php

class WpvTag {

    function GetTagNameArray($tag_id_array, $add_quotes=FALSE) {
        global $wpdb;
        global $wpv_tag_table;

        $select_sql = "SELECT tag_name ";
        $select_sql .= "FROM $wpv_tag_table ";
        if ($tag_id_array != null && count($tag_id_array) > 0) {
            $select_sql .= "WHERE tag_id IN (";
            $select_sql .= implode($tag_id_array, ",");
            $select_sql .= ")";
        }

        if ($add_quotes) {
            $new_tag_array = array();
            foreach ($wpdb->get_col($select_sql, 0) as $tag) {
                array_push($new_tag_array, "'$tag'");
            }
            return $new_tag_array;
        }
        else {
            return $wpdb->get_col($select_sql, 0);
        }
    }

    function GetTagIdArray($tag_name_array) {
        global $wpdb;
        global $wpv_tag_table;

        $select_sql = "SELECT tag_id ";
        $select_sql .= "FROM $wpv_tag_table ";
        $select_sql .= "WHERE UPPER(tag_name) IN (";
        foreach ($tag_name_array as $tag_name) {
            $select_sql .= "UPPER('$tag_name'),";
        }
        $select_sql = rtrim($select_sql, ",") . ")";

        return $wpdb->get_col($select_sql, 0);
    }

    function TagExists($tag_name) {
        global $wpdb;
        global $wpv_tag_table;

        $select_sql = "SELECT COUNT(tag_id) tag_count ";
        $select_sql .= "FROM $wpv_tag_table ";
        $select_sql .= "WHERE UPPER(tag_name) = UPPER('$tag_name') ";

        return $wpdb->get_var($select_sql) > 0;
    }

    function GetTagTable($additional_query_string="") {
        global $wpdb;
        global $wpv_tag_table;

        $select_sql = "SELECT tag_id, tag_name ";
        $select_sql .= "FROM $wpv_tag_table ";
        if ($additional_query_string != "")
            $select_sql .= "WHERE $additional_query_string ";
        $select_sql .= "ORDER BY tag_name ";
        
        return $wpdb->get_results($select_sql);
    }

}
?>
