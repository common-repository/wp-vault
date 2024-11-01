<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function-file.php");
require_once(dirname(__FILE__) . "/../lib/wpv-table-post2file.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-post2file.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-image.php");

if (isset($_POST["post_id"])) {
    $_post_id = $_POST["post_id"];
}
else {
    WpvUtils::ShowPageNotFound();
}

if (current_user_can("wpv_access_own_posts") ||  current_user_can("wpv_access_all_posts")) {
    $post2file_table = WpvPost2File::GetPost2FileTable($_post_id);
    WpvPost2FileTable::DisplayPost2FileImageList($post2file_table);
}
else {
    echo "You are not authorized";
    exit;
}
?>