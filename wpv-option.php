<?php
global $wpv_options;

$wpv_message = new WpvMessage();

if (!$wpv_options->OptionExists) {
    WpvUtils::VerifyWPVault();
}
    
if (isset($_POST["proc"]) && $_POST["proc"] == "option-update") {
    update_options($wpv_message);
}

$wpv_message->WriteMessages();
?>
<div style="padding: 5px 5px 5px 5px">
<form name="option_form" action="" method="post">
    <table>
    <tr>
    <td>File Path:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'option_file_path')"/>
    </td>
    <td><input type="text" name="file_path" value="<?php echo $wpv_options->GetOption("file_path"); ?>" maxlength="255" size="50" /></td>
    </tr>
    <tr>
    <td>Default Target Thumbnail Size:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'option_target_thumbnail_size')"/>
    </td>
    <td><input type="text" name="target_thumbnail_size" value="<?php echo $wpv_options->GetOption("target_thumbnail_size"); ?>" maxlength="3" size="3" />px</td>
    </tr>
    <tr>
    <td>Default Target Image Size:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'option_target_image_size')"/>
    </td>
    <td><input type="text" name="target_image_size" value="<?php echo $wpv_options->GetOption("target_image_size"); ?>" maxlength="4" size="4" />px</td>
    </tr>
    <tr>
    <td>Role Access:
    <img width="12" height="12" src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=help.jpg"; ?>" onmouseover="WpvTooltip.load(event, '<?php echo get_bloginfo("siteurl"); ?>/', 'option_role_access')"/>
    </td>
    <td>
    <select name="role_access">
    <?php wp_dropdown_roles($wpv_options->GetOption("role_access")); ?>
    </select>
    </td>
    </tr>
    </table>

    <input type="hidden" name="proc" value="option-update" />
    <div class="submit">
    <input type="submit" value="Save Options" />
    </div>
</form>
</div>
<?php
function update_options(&$wpv_message) {
    global $wpv_options;
    
    $message = "";
    $error_message = "";
    
    $_target_thumbnail_size = isset($_POST["target_thumbnail_size"]) ? $_POST["target_thumbnail_size"] : 150;
    $_target_image_size = isset($_POST["target_image_size"]) ? $_POST["target_image_size"] : 150;

    $_role_access = $_POST["role_access"];
    $_file_path = $_POST["file_path"];
    if ($wpv_options->GetOption("target_thumbnail_size") != $_target_thumbnail_size) {
        if (!is_numeric($_target_thumbnail_size)) {
            $wpv_message->AddErrorMessageLine("Target thumbnail size must be numeric.");
        }
        else {
            $_target_thumbnail_size = WpvUtils::FilterNumber($_target_thumbnail_size, 50, 999);

            if ($wpv_options->UpdateOption("target_thumbnail_size", $_target_thumbnail_size) === FALSE) {
                $wpv_message->AddErrorMessageLine("Failed to update target thumbnail size.");
            }
            else {
                $wpv_message->AddMessageLine("Successfully updated target thumbnail size.");
            }
        }
    }
    
    if ($wpv_options->GetOption("target_image_size") != $_target_image_size) {
        if (!is_numeric($_target_image_size)) {
            $wpv_message->AddErrorMessageLine("Target image size must be numeric.");
        }
        else {
            $_target_image_size = WpvUtils::FilterNumber($_target_image_size, 100);
            if ($wpv_options->UpdateOption("target_image_size", $_target_image_size) === FALSE) {
                $wpv_message->AddErrorMessageLine("Failed to update target thumbnail size.");
            }
            else {
                $wpv_message->AddMessageLine("Successfully updated target image size.");
            }
        }
    }

    if ($wpv_options->GetOption("role_access") != $_role_access) {
        if ($wpv_options->UpdateOption("role_access", $_role_access) === FALSE) {
            $wpv_message->AddErrorMessageLine("Failed to update role access.");
        }
        else {
            $wpv_message->AddMessageLine("Successfully updated role access.");
        }
    }

    if ($wpv_options->GetOption("file_path") != $_file_path) {
        $_file_path = preg_replace("/[\\\]+/", "/", $_file_path);
        $file_error_message = WpvUtils::TestValidDir($_file_path);
        
        if (preg_match("/\\$|\/$/", $_file_path) == 0)
            $_file_path .= "/";

        if ($file_error_message == "") {
            if ($wpv_options->UpdateOption("file_path", $_file_path) === FALSE) {
                $wpv_message->AddErrorMessageLine("Failed to update file path.");
            }
            else {
                if (!file_exists($_file_path . "index.php")) {
                    copy(dirname(__FILE__) . "/index.php", $_file_path . "index.php");
                    chmod($_file_path . "index.php", 0600); 
                }
                $wpv_message->AddMessageLine("Successfully updated file path.");
            }
        }
        else {
            $wpv_message->AddErrorMessageLine($file_error_message);
        }
    }
}
?>
