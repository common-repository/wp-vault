<?php
require_once(dirname(__FILE__) . "/lib/wpv-function-post.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-post2file.php");

$_proc = isset($_POST["proc"]) ? $_POST["proc"] : "post";
$_post_id = isset($_POST["post_id"]) ? $_POST["post_id"] : "-1";

?>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-link-manager"; ?>"></script>

<div id="wpv-link-tab-interface">
    <div id="wpv-header">
    <?php
    if ($_post_id >= 0) {
        global $userdata;
        
        $post_data = WpvPost::GetPost($_post_id);
        
        // Security check.
        if ($post_data === FALSE) {
            echo "No Post/Page Selected";
            
            $_POST["proc"] = "post";
            $_POST["post_id"] = "-1";
            $_proc = "post";
            $_post_id = "-1";
        }
        else if ((current_user_can("wpv_access_own_posts") && $post_data->post_author == $userdata->ID) || current_user_can("wpv_access_all_posts")) {
            echo "<strong>\"$post_data->post_title\" [<small>ID = </small>$post_data->post_id] </strong> ";
            echo " (<a href='" . get_settings("siteurl") . "/wp-admin/post.php?action=edit&post=" . $post_data->post_id . "'>Edit This Post</a> / <a href='" . get_permalink($post_data->post_id) . "&preview=true' target='_blank'>Preview</a>)";            
            echo "<div id='wpv-display-status'></div>";
        }
        else {
            echo "You are not authorized.";
            die;
        }
    }
    else {
        echo "No Post/Page Selected";
    }
    ?>
    </div>

    <span <?php echo preg_match("/^post/", $_proc) ? "" : "class='disabled'"; ?>><a href="javascript:gotoLinkPage('post')">Post/Page Selection</a></span>
    <?php
    if ($_post_id >= 0) {
    ?>
        <span <?php echo preg_match("/^file-selection/", $_proc) ? "" : "class='disabled'"; ?>><a href="javascript:gotoLinkPage('file-selection-page')">File Selection</a></span>
        <?php
        if (WpvPost2File::GetPost2FileCount($_post_id) > 0) {
        ?>
        <span <?php echo preg_match("/^file-link/", $_proc) ? "" : "class='disabled'"; ?>><a href="javascript:gotoLinkPage('file-link-page')">Linked Files</a></span>
        <span <?php echo preg_match("/^display-option/", $_proc) ? "" : "class='disabled'"; ?>><a href="javascript:gotoLinkPage('display-option')">Display Option</a></span>
        <?php
        }
        ?>
    <?php
    }
    ?>
    <div>
    <img src="<?php echo get_bloginfo("siteurl") . "/?wpv-image=link-manager-tab.jpg" ?>" width="100%" height="30" />
    </div>
</div>

<form id="link-navigation" action="" method="post">
    <input type="hidden" name="post_id" value="<?php echo $_post_id; ?>" />
    <input type="hidden" name="proc" value="<?php echo $_proc; ?>" />
    <input type="hidden" name="page" value="wp-vault/wpv-link-manager.php" />
</form>

