<?php
/* 
Plugin Name: WP Vault
Plugin URI: http://software.y-zone.net
Description: WP Vault allows you to store any files to WordPress installation, and link them to a post or page. Please read <a href="http://software.y-zone.net/?page_id=4">installation instruction</a> completely before proceding with activation.
Author: Motoo Yasui
Author URI: http://software.y-zone.net
Version: 0.8.6.6


    Copyright 2007 Motoo Yasui ()

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

global $wpv_version;
global $table_prefix;
global $wpv_file_table;
global $wpv_post2file_table;
global $wpv_option_table;
global $wpv_display_option_table;
global $wpv_file2tag_table;
global $wpv_tag_table;
global $wpv_options;

require_once(dirname(__FILE__) . "/lib/wpv-function.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-admin-menu.php");

$wpv_version = "0.8.6";    

// WPV database tables
$wpv_file_table = $table_prefix . "wpv_file";
$wpv_post2file_table = $table_prefix . "wpv_post2file";
$wpv_display_option_table = $table_prefix . "wpv_display_option";
$wpv_option_table = $table_prefix . "wpv_option";
$wpv_file2tag_table = $table_prefix . "wpv_file2tag";
$wpv_tag_table = $table_prefix . "wpv_tag";

// WP Hooks and Filters
add_action('activate_wp-vault/wp-vault.php', 'wpv_install');
add_action('deactivate_wp-vault/wp-vault.php', 'wpv_uninstall');
add_action('init', 'wpv_init');
add_action('admin_head', 'wpv_admin_head');
add_action('admin_menu', array(WpvMenu, 'Display'));
add_action('wp_head', 'wpv_head');
add_action('plugins_loaded', 'wpv_load_textdomain');
add_action('wp_ajax_wpv_file_edit', 'wpv_file_edit');
add_action('wp_ajax_wpv_tag_assign', 'wpv_tag_action');
add_action('wp_ajax_wpv_tag_unassign', 'wpv_tag_action');
add_action('wp_ajax_wpv_post2file_edit', 'wpv_post2file_edit');
add_action('wp_ajax_wpv_post2file_sequence', 'wpv_post2file_sequence');
add_action('wp_ajax_wpv_file_table_page', 'wpv_file_table_page');
add_action('wp_ajax_wpv_post_table_page', 'wpv_post_table_page');
add_action('wp_ajax_wpv_admin_image_display', 'wpv_admin_image_display');
add_action('wp_ajax_wpv_color_picker', 'wpv_color_picker');
add_action('wp_ajax_wpv_linked_image_list', 'wpv_linked_image_list');
add_action('wp_ajax_wpv_quick_tag', 'wpv_quick_tag');
add_action('wp_ajax_wpv_ftp_action', 'wpv_ftp_action');
add_action('wp_ajax_wpv_display_status', 'wpv_display_status');

add_action('load_wp-vault', 'wpv_admin_head');
add_action('delete_post', 'wpv_delete_post');
add_action('delete_user', 'wpv_delete_user');
add_action('dbx_post_advanced', 'wpv_advanced');
add_action('dbx_page_advanced', 'wpv_advanced');

add_action('wpv_admin_init_wpv-link-manager', 'wpv_load_link_manager_cookie');

add_filter('the_content', 'wpv_add_file_table');

$wpv_options = new WpvOptions();

// WP Vault admin page hook.
if (isset($_GET["page"]) && preg_match("/^wp-vault\/([a-z\-]+)\.php$/", $_GET["page"], $match) > 0) {
    do_action("wpv_admin_init");
    do_action("wpv_admin_init_" . $match[1]);
}

/*
    Action Hook and Filter Functions
*/
function wpv_advanced() {
    include(dirname(__FILE__) . "/wpv-advanced-bar.php");
}

function wpv_install() {
    require_once(dirname(__FILE__) . "/wpv-install.php");
    
    wpv_install_db();
}

function wpv_uninstall() {
    require_once(dirname(__FILE__) . "/wpv-install.php");

    wp_cache_flush();
    wpv_clear_directories();
}

function wpv_admin_head() {
?>
    <link rel="stylesheet" href="<?php echo get_bloginfo("siteurl"); ?>/?wpv-css=wpv-admin" title="wpv-admin-css" type="text/css" />
    <script type="text/javascript" src="<?php echo get_bloginfo("siteurl"); ?>/?wpv-js=xml-request"></script>
    <script type="text/javascript" src="<?php echo get_bloginfo("siteurl"); ?>/?wpv-js=wpv"></script>
    <script type="text/javascript" src="<?php echo get_bloginfo("siteurl"); ?>/?wpv-js=wpv-admin"></script>
<?php
}

function wpv_head() {
?>
    <script type="text/javascript" src="<?php echo get_bloginfo("siteurl"); ?>/?wpv-js=xml-request"></script>
    <script type="text/javascript" src="<?php echo get_bloginfo("siteurl"); ?>/?wpv-js=wpv"></script>
<?php
}

function wpv_linked_image_list() {
    if (current_user_can("wpv_access_own_posts") || current_user_can("wpv_access_all_posts"))
        include(dirname(__FILE__) . "/ajax-response/wpv-linked-image-list.php");
    exit;
}

function wpv_file_table_page() {
    if (current_user_can("wpv_browse_own_files") || current_user_can("wpv_browse_all_files"))
        include(dirname(__FILE__) . "/ajax-response/wpv-file-table-page.php");
    exit;
}

function wpv_post_table_page() {
    if (current_user_can("wpv_access_own_posts") || current_user_can("wpv_access_all_posts"))
        include(dirname(__FILE__) . "/ajax-response/wpv-post-table-page.php");
    exit;
}

function wpv_file_edit() {
    if (current_user_can("wpv_edit_own_files") || current_user_can("wpv_edit_all_files"))
        include(dirname(__FILE__) . "/ajax-response/wpv-file-browser-edit.php");
    exit;
}

function wpv_tag_action() {
    if (current_user_can("wpv_edit_own_files") || current_user_can("wpv_edit_all_files"))
        include(dirname(__FILE__) . "/ajax-response/wpv-file-browser-tag.php");
    exit;
}

function wpv_post2file_edit() {
    if (current_user_can("wpv_access_own_posts"))
        include(dirname(__FILE__) . "/ajax-response/wpv-link-manager-edit.php");
    exit;
}

function wpv_post2file_sequence() {
    if (current_user_can("wpv_access_own_posts"))
        include(dirname(__FILE__) . "/ajax-response/wpv-link-manager-sequence.php");
    exit;
}

function wpv_admin_image_display() {
    if (current_user_can("wpv_browse_own_files") || current_user_can("wpv_browse_all_files"))
        include(dirname(__FILE__) . "/ajax-response/wpv-admin-image-display.php");
    exit;
}

function wpv_color_picker() {
    include(dirname(__FILE__) . "/ajax-response/wpv-color-picker.php");
    exit;
}

function wpv_quick_tag() {
    if (current_user_can("wpv_edit_tags"))
        include(dirname(__FILE__) . "/ajax-response/wpv-quick-tag.php");
    exit;
}

function wpv_ftp_action() {
    if (current_user_can("wpv_get_ftp_files"))
        include(dirname(__FILE__) . "/ajax-response/wpv-ftp-action.php");
    exit;
}

function wpv_display_status() {
    include(dirname(__FILE__) . "/ajax-response/wpv-display-status.php");
    exit;
}

// Create Text Domain For Translations
function wpv_load_textdomain() {
    load_plugin_textdomain('wp-vault', 'wp-content/plugins/wp-vault');
}

function wpv_load_link_manager_cookie() {
    require_once(dirname(__FILE__) . "/lib/wpv-function-cookie.php");

    if (isset($_POST["proc"]) && isset($_POST["post_id"])) {
        if (!isset($_POST["no_cookie"]))
            WpvLinkManagerCookie::SetCookieData();
    }
    else {
        list($post_id, $proc) = WpvLinkManagerCookie::GetCookieData();
        
        $_POST["post_id"] = $post_id;
        $_POST["proc"] = $proc;
    }
}

// Load CSS, JS files, or invoke file handler.
function wpv_init() {
    if (isset($_GET["wpv_file_id"])) {
        include(dirname(__FILE__) . "/wpv-file-handler.php");
        exit;
    }
    else if (isset($_POST["wpv-tooltip"])) {
        include(dirname(__FILE__) . "/ajax-response/wpv-tooltip.php");
        exit;
    }
    else if (isset($_GET["wpv-image"])) {
        include(dirname(__FILE__) . "/images/" . $_GET["wpv-image"]);
        exit;
    }
    else if (isset($_GET["wpv-css"])) {
        if (file_exists(dirname(__FILE__) . "/css/" . $_GET["wpv-css"] . ".css")) {
            header("Content-type: text/css");
            include(dirname(__FILE__) . "/css/" . $_GET["wpv-css"] . ".css");
            exit;
        }
        else if (file_exists(dirname(__FILE__) . "/css/" . $_GET["wpv-css"] . ".css.php")) {
            header("Content-type: text/css");
            include(dirname(__FILE__) . "/css/" . $_GET["wpv-css"] . ".css.php");
            exit;
        }
    }
    else if (isset($_GET["wpv-js"])) {
        if (file_exists(dirname(__FILE__) . "/js/" . $_GET["wpv-js"] . ".js")) {
            header("Content-type: text/javascript");
            include(dirname(__FILE__) . "/js/" . $_GET["wpv-js"] . ".js");
            exit;
        }
        else if (file_exists(dirname(__FILE__) . "/js/" . $_GET["wpv-js"] . ".js.php")) {
            header("Content-type: text/javascript");
            include(dirname(__FILE__) . "/js/" . $_GET["wpv-js"] . ".js.php");
            exit;
        }
    }
    else if (isset($_POST["wpv-thumbnail-table"])) {
        include(dirname(__FILE__) . "/ajax-response/wpv-thumbnail-table-loader.php");
        exit;
    }
    else if (isset($_POST["wpv-image-display"])) {
        include(dirname(__FILE__) . "/ajax-response/wpv-image-display.php");
        exit;
    }
}

// Add file table at the end of a content, if one is available.
function wpv_add_file_table($text) {
    global $post;
    
    require_once(dirname(__FILE__) . "/lib/wpv-function-display-option.php");
    $display_option = WpvDisplayOption::GetDisplayOption($post->ID);

    if ($display_option !== FALSE) {
        require_once(dirname(__FILE__) . "/lib/wpv-function-display.php");
        require_once(dirname(__FILE__) . "/lib/wpv-function-post2file.php");

        if (!empty($post->post_password) && stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) != $post->post_password)
            return $text;
        if ($display_option->display_status == "Published")
            return WpvDisplay::AppendImageList($text, $display_option, $post->ID);
        else if (isset($_GET["preview"]) && $_GET["preview"] == "true") {
            global $userdata;
            
            if ($userdata != null)
                return WpvDisplay::AppendImageList($text, $display_option, $post->ID);
        }
    }
    return $text;
}

function wpv_delete_post($post_id) {
    require_once(dirname(__FILE__) . "/lib/wpv-function.php");
    require_once(dirname(__FILE__) . "/lib/wpv-function-post2file.php");
    require_once(dirname(__FILE__) . "/lib/wpv-function-cookie.php");
    
    WpvPost2File::DeletePost2FileData($post_id);
    list($cookie_post_id, $proc) = WpvLinkManagerCookie::GetCookieData();
    if ($post_id == $cookie_post_id) {
        WpvLinkManagerCookie::DeleteCookieData();
    }
}

function wpv_delete_user($user_id) {
    require_once(dirname(__FILE__) . "/lib/wpv-function.php");
    require_once(dirname(__FILE__) . "/lib/wpv-function-file.php");
    
    WpvFile::ReassignFileOwner($user_id);
}
?>
