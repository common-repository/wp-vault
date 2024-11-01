<?php
if (current_user_can("wpv_edit_tags")) {
    require_once(dirname(__FILE__) . "/../lib/wpv-function-tag.php");
    require_once(dirname(__FILE__) . "/../lib/wpv-table-tag.php");

    $message = "";
    insert_new_tags($message);
    
    $tag_table = WpvTag::GetTagTable();
    WpvTagTable::DisplayTagTable($tag_table, "width: 285px; height: 250px; border: 0px;", "checkbox", "selected_tag");
    
    echo "<div id='quick-tag-message' style='display: none;'>$message</div>";
}

function insert_new_tags(&$message) {
    global $wpdb;
    global $wpv_tag_table;

    $_new_tags = strtolower(trim($_POST["new_tags"]));
    if ($_new_tags == "") {
        return;
    }

    $count = 0;
    $new_tag_array = array();

    foreach (split(",", $_new_tags) as $new_tag) {
        $new_tag = ucwords(trim($new_tag));

        if (preg_match("/[\<\>\\\;\[\]\#\*\%\(\)\"\'\,]/i", $new_tag)) {
            $message .= "Invalid characters in tag: '$new_tag' <br />";
        }
        else if (strlen($tag) > 25) {
            $message .= "Tag is too long: '$new_tag'<br />";
        }
        else if (WpvTag::TagExists($new_tag)) {
            $message .= "Tag already exists: '$new_tag'<br />";
        }
        else if ($new_tag != "") {
            array_push($new_tag_array, "'$new_tag'");
        }
    }

    if (count($new_tag_array) == 0) {
        return;
    }

    $new_tag_array = array_unique($new_tag_array);
    $tag_sql = "INSERT INTO $wpv_tag_table (tag_name) VALUES (" . implode("), (", $new_tag_array) . ")";

    if ($wpdb->query($tag_sql) === FALSE) {
        $message .= "Failed to add tag(s) due to database error.<br />";
    }
    else {
        $message .= "Successfully added: " . implode(", ", $new_tag_array); 
    }
    return;
}
?>
