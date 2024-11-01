<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function-post2file.php");

if (isset($_POST["post_id"])) {
    $_post_id = $_POST["post_id"];
}
else {
    echo "Error acquiring status";
    exit;
}   

if (WpvPost2File::GetPost2FileCount($_post_id) > 0) {
    require_once(dirname(__FILE__) . "/../lib/wpv-function-display-option.php");
    
    $display_option = WpvDisplayOption::GetDisplayOption($_post_id);
    if ($display_option === FALSE || $display_option->display_status == "Draft"){
        echo "<small>Status:</small> <strong>$display_option->display_status</strong> (You can publish the linked files using <a href=\"javascript:gotoLinkPage('display-option')\">Display Option</a>)";
    }
    else {
        echo "<small>Status:</small> <strong>$display_option->display_status</strong>";
    }
}
else {
    echo "<small>Status:</small> <strong>No linked file</strong>";
}
?>