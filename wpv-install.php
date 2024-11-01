<?php
function wpv_install_db() {
    global $wpdb;
    global $wpv_file_table;
    global $wpv_post2file_table;
    global $wpv_option_table;
    global $wpv_display_option_table;
    global $wpv_file2tag_table;
    global $wpv_tag_table;
    global $wpv_options;   
    global $wpv_version;

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpv_display_option_table'") != $wpv_display_option_table) {
        $install_sql = "";
        $install_sql .= "CREATE TABLE $wpv_display_option_table (";
        $install_sql .= "  post_id bigint(20) unsigned NOT NULL,";
        $install_sql .= "  column_count tinyint(2) unsigned NOT NULL default '3',";
        $install_sql .= "  display_text enum('File Name','Comment','Both','None') collate latin1_general_ci NOT NULL default 'File Name',";
        $install_sql .= "  display_thumbnail enum('Top','Bottom','Left','Right','Stagger','None') collate latin1_general_ci NOT NULL default 'Top',";
        $install_sql .= "  display_align enum('Left','Right','Center','Justify') collate latin1_general_ci NOT NULL default 'Center',";
        $install_sql .= "  display_vertical_align enum('Top','Bottom','Middle') collate latin1_general_ci NOT NULL default 'Top',";
        $install_sql .= "  target_thumbnail_size int(4) unsigned NOT NULL default '150',";
        $install_sql .= "  target_image_size int(4) unsigned NOT NULL default '640',";
        $install_sql .= "  display_status enum('Published','Draft') collate latin1_general_ci NOT NULL default 'Draft',";
        $install_sql .= "  display_table_border_color varchar(11) collate latin1_general_ci NOT NULL default '#ffffff',";
        $install_sql .= "  display_table_border_style enum('solid', 'dotted', 'dashed', 'double', 'groove', 'ridge', 'inset', 'outset') collate latin1_general_ci NOT NULL default 'solid',";
        $install_sql .= "  display_table_border_width tinyint(2) unsigned NOT NULL default '0',";
        $install_sql .= "  display_table_location enum('Left', 'Right', 'Float Left', 'Float Right', 'Top', 'Bottom') collate latin1_general_ci NOT NULL default 'Bottom',";
        $install_sql .= "  display_table_width int(4) unsigned NOT NULL default '90',";
        $install_sql .= "  display_table_width_unit enum('percent', 'px') collate latin1_general_ci NOT NULL default 'percent',";
        $install_sql .= "  display_table_margin_top int(3) unsigned NOT NULL default '5',";
        $install_sql .= "  display_table_margin_right int(3) unsigned NOT NULL default '5',";
        $install_sql .= "  display_table_margin_bottom int(3) unsigned NOT NULL default '5',";
        $install_sql .= "  display_table_margin_left int(3) unsigned NOT NULL default '5',";
        $install_sql .= "  cell_background_color varchar(11) collate latin1_general_ci NOT NULL default '#ffffff',";
        $install_sql .= "  cell_background_color_hover varchar(11) collate latin1_general_ci NOT NULL default '#ffffff',";
        $install_sql .= "  border_color varchar(11) collate latin1_general_ci NOT NULL default '#ffffff',";
        $install_sql .= "  border_color_hover varchar(11) collate latin1_general_ci NOT NULL default '#0000ff',";
        $install_sql .= "  border_width smallint(2) unsigned NOT NULL default '2',";
        $install_sql .= "  name_font_bold tinyint(1) NOT NULL default '1',";
        $install_sql .= "  name_font_size smallint(2) unsigned NOT NULL default '11',";
        $install_sql .= "  name_font_color varchar(11) collate latin1_general_ci NOT NULL default '#000000',";
        $install_sql .= "  name_font_underline tinyint(1) NOT NULL default '0',";
        $install_sql .= "  comment_font_color varchar(11) collate latin1_general_ci NOT NULL default '#000000',";
        $install_sql .= "  comment_font_size tinyint(2) unsigned NOT NULL default '10',";
        $install_sql .= "  image_display_background_color varchar(11) collate latin1_general_ci NOT NULL default '#ffffff',";
        $install_sql .= "  image_display_font_color varchar(11) collate latin1_general_ci NOT NULL default '#000000',";
        $install_sql .= "  image_display_name_font_size tinyint(2) NOT NULL default '11',";
        $install_sql .= "  image_display_border_color varchar(11) collate latin1_general_ci NOT NULL default '#000000',";
        $install_sql .= "  last_update_by bigint(20) NOT NULL,";
        $install_sql .= "  last_update datetime NOT NULL,";
        $install_sql .= "  PRIMARY KEY  (post_id)";
        $install_sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
        if ($wpdb->query($install_sql) === FALSE) {
            return FALSE;
        }
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpv_file_table'") != $wpv_file_table) {
        $install_sql = "";
        $install_sql .= "CREATE TABLE $wpv_file_table (";
        $install_sql .= "  file_id bigint(20) unsigned NOT NULL auto_increment,";
        $install_sql .= "  file_name varchar(255) collate latin1_general_ci NOT NULL,";
        $install_sql .= "  file_ext varchar(20) collate latin1_general_ci NOT NULL,";
        $install_sql .= "  file_size bigint(20) unsigned NOT NULL default '0',";
        $install_sql .= "  file_image_width int(5) unsigned NOT NULL default '0',";
        $install_sql .= "  file_image_height int(5) unsigned NOT NULL default '0',";
        $install_sql .= "  stored_datetime datetime NOT NULL,";
        $install_sql .= "  stored_name varchar(20) collate latin1_general_ci NOT NULL,";
        $install_sql .= "  mime_type varchar(50) collate latin1_general_ci NOT NULL,";
        $install_sql .= "  owner_id bigint(20) unsigned NOT NULL,";
        $install_sql .= "  PRIMARY KEY  (file_id),";
        $install_sql .= "  UNIQUE KEY stored_name (stored_name),";
        $install_sql .= "  UNIQUE KEY file_name (file_name),";
        $install_sql .= "  KEY owner_id (owner_id)";
        $install_sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=135 ;";
        if ($wpdb->query($install_sql) === FALSE) {
            return FALSE;
        }
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpv_file2tag_table'") != $wpv_file2tag_table) {
        $install_sql = "";
        $install_sql .= "CREATE TABLE $wpv_file2tag_table (";
        $install_sql .= "  file_id bigint(20) unsigned NOT NULL,";
        $install_sql .= "  tag_id bigint(20) unsigned NOT NULL,";
        $install_sql .= "  PRIMARY KEY  (file_id,tag_id)";
        $install_sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
        if ($wpdb->query($install_sql) === FALSE) {
            return FALSE;
        }
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpv_post2file_table'") != $wpv_post2file_table) {
        $install_sql = "";
        $install_sql .= "CREATE TABLE $wpv_post2file_table (";
        $install_sql .= "  post_id bigint(20) unsigned NOT NULL,";
        $install_sql .= "  file_id bigint(20) unsigned NOT NULL,";
        $install_sql .= "  sequence_num bigint(20) unsigned NOT NULL,";
        $install_sql .= "  added_datetime datetime NOT NULL,";
        $install_sql .= "  last_update_datetime datetime NOT NULL,";
        $install_sql .= "  comment_text text collate latin1_general_ci NOT NULL,";
        $install_sql .= "  action_type enum('Default','Download') collate latin1_general_ci NOT NULL default 'Default',";
        $install_sql .= "  PRIMARY KEY  (post_id,file_id)";
        $install_sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";
        if ($wpdb->query($install_sql) === FALSE) {
            return FALSE;
        }
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpv_tag_table'") != $wpv_tag_table) {
        $install_sql = "";
        $install_sql .= "CREATE TABLE $wpv_tag_table (";
        $install_sql .= "  tag_id bigint(20) unsigned NOT NULL auto_increment,";
        $install_sql .= "  tag_name varchar(25) collate latin1_general_ci NOT NULL,";
        $install_sql .= "  PRIMARY KEY  (tag_id),";
        $install_sql .= "  UNIQUE KEY parent_tag_list (tag_name)";
        $install_sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci AUTO_INCREMENT=54 ;";
        if ($wpdb->query($install_sql) === FALSE) {
            return FALSE;
        }
    }

    if ($wpdb->get_var("SHOW TABLES LIKE '$wpv_option_table'") != $wpv_option_table) {
        $install_sql = "";
        $install_sql .= "CREATE TABLE $wpv_option_table (";
        $install_sql .= "  option_id varchar(25) collate latin1_general_ci NOT NULL,";
        $install_sql .= "  option_value varchar(255) collate latin1_general_ci NOT NULL,";
        $install_sql .= "  PRIMARY KEY  (option_id)";
        $install_sql .= ") ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci;";

        if ($wpdb->query($install_sql) === FALSE) {
            return FALSE;
        }

        $file_path_hash = "wp-vault-" . md5(WpvUtils::GetRandomString());
        $install_sql = "";
        $install_sql .= "INSERT INTO $wpv_option_table (option_id, option_value) ";
        $install_sql .= "VALUES ";
        $install_sql .= "('file_path', '" . preg_replace("/[\\\]+/", "/", dirname(__FILE__)) . "/PLEASE.ENTER.A.PATH/'),";
        $install_sql .= "('file_path_hash', '$file_path_hash'),";
        $install_sql .= "('role_access', 'author'),";
        $install_sql .= "('target_image_size', '700'),";
        $install_sql .= "('target_thumbnail_size', '100'),";
        $install_sql .= "('version', '$wpv_version');";
        if ($wpdb->query($install_sql) === FALSE) {
            return FALSE;
        }
    }

    if (wpv_upgrade($wpv_version) === FALSE) {
        return FALSE;
    }

    $wpv_options = new WpvOptions();

    wpv_clear_directories();
    wpv_copy_index_file();
    
    return TRUE;
}

function wpv_clear_directories() {
    $file_path = WpvUtils::GetStoragePath();

    if ($file_path != "" && file_exists($file_path)) {
        if (file_exists("$file_path.thumb")) {
            wpv_remove_directory("$file_path.thumb", TRUE);
        }
        if (file_exists("$file_path.img")) {
            wpv_remove_directory("$file_path.img", TRUE);
        }
        if (file_exists("$file_path.sys")) {
            wpv_remove_directory("$file_path.sys", TRUE);
        }
    }
}

function wpv_copy_index_file() {
    global $wpv_options;
    
    if ($wpv_options->GetOption('file_path') != "" && file_exists($wpv_options->GetOption('file_path') )) {
        copy(dirname(__FILE__) . "/index.php", $wpv_options->GetOption('file_path')  . "index.php");
        chmod($wpv_options->GetOption('file_path')  . "index.php", 0666);

        $file_path = WpvUtils::GetStoragePath();

        if ($file_path != "" && file_exists($file_path)) {
            copy(dirname(__FILE__) . "/index.php", $file_path . "index.php");
            chmod($file_path . "index.php", 0666);
        }
    }
    
}

function wpv_remove_directory($dir, $delete_me) {
    if (!$dh = @opendir($dir)) return;
    
    while (FALSE !== ($obj = readdir($dh))) {
        if($obj == "." || $obj == "..") 
            continue;
        if (!@unlink("$dir/$obj")) 
            remove_directory("$dir/$obj", TRUE);
    }
    if ($delete_me) {
        closedir($dh);
        @rmdir($dir);
    }
}

function wpv_move_files($dir, $dest_dir) {
    if (!$dh = @opendir($dir)) return FALSE;
    
    while (FALSE !== ($obj = readdir($dh))) {
        if($obj == "." || $obj == "..") 
            continue;
        @rename("$dir/$obj", "$dest_dir/$obj");
        if ($obj != "index.php")
            @chmod("$dest_dir/$obj", 0600);
    }
    closedir($dh);
    return TRUE;
}

function wpv_upgrade($wpv_version) {
    global $wpdb;
    global $wpv_option_table;
    global $wpv_display_option_table;

    $version = $wpdb->get_var("SELECT option_value FROM $wpv_option_table WHERE option_id = 'version'", 0);
    
    if ($version == null || $version == "0.7") {
        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` CHANGE `background_color` `display_table_border_color` varchar(11) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL default '#ffffff';") === FALSE)
            return FALSE;
        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` ADD `display_table_border_style` enum( 'solid', 'dotted', 'dashed', 'double', 'groove', 'ridge', 'inset', 'outset' ) NOT NULL default 'solid' after `display_table_border_color`;") === FALSE)
            return FALSE; 
        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` ADD `display_table_border_width` tinyint( 2 ) unsigned NOT NULL DEFAULT '0' after `display_table_border_style`;") === FALSE)
            return FALSE;
        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` ADD `display_table_location` enum('Left', 'Right', 'Top', 'Bottom') NOT NULL default 'Bottom' after `display_table_border_width`;") === FALSE)
            return FALSE; 
        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` ADD `display_table_width` int( 4 ) unsigned NOT NULL DEFAULT '90' after `display_table_location`;") === FALSE)
            return FALSE;
        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` ADD `display_table_width_unit` enum('percent', 'px') NOT NULL DEFAULT 'percent' after `display_table_width`;") === FALSE)
            return FALSE;
    }
    if ($version == null || $version == "0.7" || strpos($version, "0.8.5") !== FALSE) {
        global $wpv_options;
        
        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` CHANGE `display_table_location` `display_table_location` enum('Left', 'Right', 'Float Left', 'Float Right', 'Top', 'Bottom') CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL default 'Bottom';") === FALSE)
            return FALSE;

        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` ADD `display_table_margin_top` INT( 3 ) UNSIGNED NOT NULL DEFAULT '5' AFTER `display_table_width_unit`;") === FALSE)
            return FALSE;
        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` ADD `display_table_margin_right` INT( 3 ) UNSIGNED NOT NULL DEFAULT '5' AFTER `display_table_margin_top`;") === FALSE)
            return FALSE;
        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` ADD `display_table_margin_bottom` INT( 3 ) UNSIGNED NOT NULL DEFAULT '5' AFTER `display_table_margin_right`;") === FALSE)
            return FALSE;
        if ($wpdb->query("ALTER TABLE `$wpv_display_option_table` ADD `display_table_margin_left` INT( 3 ) UNSIGNED NOT NULL DEFAULT '5' AFTER `display_table_margin_bottom` ;") === FALSE)
            return FALSE;
        
        $file_path_hash = "wp-vault-" . md5(WpvUtils::GetRandomString());
        if ($wpdb->query("INSERT INTO $wpv_option_table (option_id, option_value) VALUES ('file_path_hash', '$file_path_hash');") === FALSE)
            return FALSE;
        
        @mkdir($wpv_options->GetOption('file_path') . $file_path_hash);
        @chmod($wpv_options->GetOption('file_path') . $file_path_hash, 0777);
        if (wpv_move_files(rtrim($wpv_options->GetOption('file_path'), "/"), $wpv_options->GetOption('file_path') . $file_path_hash) === FALSE) 
            return FALSE;
    }

    if ($version == null) {
        return $wpdb->query("INSERT INTO $wpv_option_table (option_id, option_value) VALUES('version', '$wpv_version')");
    }    
    else {
        return $wpdb->query("UPDATE $wpv_option_table SET option_value = '$wpv_version' WHERE option_id = 'version'");
    }
}
?>