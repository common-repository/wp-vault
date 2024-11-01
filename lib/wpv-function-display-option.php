<?php
class WpvDisplayOption {
    function CreateDisplayOption($post_id) {
        global $wpdb;
        global $wpv_display_option_table;
        global $wpv_options;
        global $userdata;

        get_currentuserinfo();

        if (WpvDisplayOption::HasPreviousDisplayOption($userdata->ID)) {
            $insert_sql = "INSERT INTO $wpv_display_option_table ";
            $insert_sql .= "(post_id, column_count, display_text, display_thumbnail, display_align, display_vertical_align, target_thumbnail_size, target_image_size, display_table_border_color, display_table_border_style, display_table_border_width, cell_background_color, cell_background_color_hover, border_color, border_color_hover, border_width, name_font_size, name_font_bold, name_font_color, name_font_underline, comment_font_color, comment_font_size, image_display_background_color, image_display_border_color, image_display_font_color, image_display_name_font_size, display_table_width, display_table_width_unit, display_table_margin_top, display_table_margin_right, display_table_margin_bottom, display_table_margin_left, display_table_location, display_status, last_update_by, last_update) ";
            $insert_sql .= "SELECT $post_id, column_count, display_text, display_thumbnail, display_align, display_vertical_align, target_thumbnail_size, target_image_size, display_table_border_color, display_table_border_style, display_table_border_width, cell_background_color, cell_background_color_hover, border_color, border_color_hover, border_width, name_font_size, name_font_bold, name_font_color, name_font_underline, comment_font_color, comment_font_size, image_display_background_color, image_display_border_color, image_display_font_color, image_display_name_font_size, display_table_width, display_table_width_unit, display_table_margin_top, display_table_margin_right, display_table_margin_bottom, display_table_margin_left, display_table_location, 'Draft', $userdata->ID, NOW() ";
            $insert_sql .= "FROM $wpv_display_option_table ";
            $insert_sql .= "WHERE last_update_by = $userdata->ID ";
            $insert_sql .= "ORDER BY last_update DESC ";
            $insert_sql .= "LIMIT 1";

            return $wpdb->query($insert_sql);
        }
        else {
            return $wpdb->query("INSERT INTO $wpv_display_option_table (post_id, target_thumbnail_size, last_update_by, last_update) VALUES ($post_id, ".$wpv_options->GetOption('target_thumbnail_size').", $userdata->ID, NOW())");
        }
    }

    function GetDisplayOptionFromDB($post_id) {
            global $wpdb;
            global $wpv_display_option_table;
            
            if (isset($post_id)) {
                $select_sql = "SELECT post_id, column_count, display_text, display_thumbnail, display_align, display_vertical_align, target_thumbnail_size, target_image_size, display_table_border_color, display_table_border_style, display_table_border_width, cell_background_color, cell_background_color_hover, border_color, border_color_hover, border_width, name_font_size, name_font_bold, name_font_color, name_font_underline, comment_font_color, comment_font_size, image_display_background_color, image_display_border_color, image_display_font_color, image_display_name_font_size, display_table_width, display_table_width_unit, display_table_margin_top, display_table_margin_right, display_table_margin_bottom, display_table_margin_left, display_table_location, display_status ";
                $select_sql .= "FROM $wpv_display_option_table ";
                $select_sql .= "WHERE post_id = $post_id ";

                $resultset = $wpdb->get_results($select_sql);

                if (count($resultset) > 0) {
                    wp_cache_add("display_option_$post_id", $resultset[0], "wp-vault");
                    return $resultset[0];
                }
            }
            return FALSE;
    }
    
    function GetDisplayOptionRow($post_id) {
        $result = null;
        
        if (($result = wp_cache_get("display_option_$post_id", "wp-vault")) === FALSE) {
            $result = WpvDisplayOption::GetDisplayOptionFromDB($post_id);
            if ($result == FALSE)
                return FALSE;
            else {
                wp_cache_add("display_option_$post_id", $result, "wp-vault");
                return $result;
            }
        }
        else {
            return $result;
        }
    }
    
    function GetDisplayOption($post_id, $create_if_needed=FALSE) {
        static $display_option_table;
        
        if (!isset($display_option_table))
            $display_option_table = array();
            
        $display_option = FALSE;

        if (!array_key_exists("$post_id", $display_option_table)) {
            if (($display_option = WpvDisplayOption::GetDisplayOptionRow($post_id)) === FALSE) {
                if ($create_if_needed) {
                    if (WpvDisplayOption::CreateDisplayOption($post_id) === FALSE) {
                        return FALSE;
                    }
                    $display_option = WpvDisplayOption::GetDisplayOption($post_id, FALSE);
                }
            }
            else {
               $display_option_table["$post_id"] = $display_option;
            }
        }
        else {
            $display_option = $display_option_table["$post_id"];
        }
        return $display_option;
    }
    
    function HasPreviousDisplayOption($user_id) {
        global $wpdb;
        global $wpv_display_option_table;

        $select_sql = "SELECT COUNT(post_id) ";
        $select_sql .= "FROM $wpv_display_option_table ";
        $select_sql .= "WHERE last_update_by = $user_id ";
        $select_sql .= "ORDER BY last_update ";

        return $wpdb->get_var($select_sql, 0) > 0;
    }
}
?>
