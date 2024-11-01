<?php
class WpvMenu {
    function Display() {
        global $wpv_options;
        
        WpvUtils::AssignRoles($wpv_options->GetOption("role_access"));
        
        if (function_exists('add_menu_page')) {
            add_menu_page(__('wp-Vault', 'wp-vault'), __('Vault', 'wp-vault'), 'wpv_browse_own_files', 'wp-vault/wpv-file-browser.php');
        }
        if (function_exists('add_submenu_page')) {
            add_submenu_page('wp-vault/wpv-file-browser.php', __('Link Manager', 'wp-vault'), __('Link Manager', 'wp-vault'), 'wpv_access_own_posts', 'wp-vault/wpv-link-manager.php');
            add_submenu_page('wp-vault/wpv-file-browser.php', __('File Upload', 'wp-vault'), __('File Upload', 'wp-vault'), 'wpv_upload_files', 'wp-vault/wpv-file-upload.php');
            add_submenu_page('wp-vault/wpv-file-browser.php', __('HTTP Get', 'wp-vault'), __('HTTP Get', 'wp-vault'), 'wpv_get_http_files', 'wp-vault/wpv-http-get.php');
            if (function_exists("ftp_connect")) {
                add_submenu_page('wp-vault/wpv-file-browser.php', __('FTP Get', 'wp-vault'), __('FTP Get', 'wp-vault'), 'wpv_get_ftp_files', 'wp-vault/wpv-ftp-get.php');
            }
            add_submenu_page('wp-vault/wpv-file-browser.php', __('Tag Manager', 'wp-vault'), __('Tag Manager', 'wp-vault'), 'wpv_edit_tags', 'wp-vault/wpv-tag-manager.php');
            add_submenu_page('wp-vault/wpv-file-browser.php', __('Option', 'wp-vault'), __('Option', 'wp-vault'), 'wpv_edit_options', 'wp-vault/wpv-option.php');
        }
    }
}
?>
