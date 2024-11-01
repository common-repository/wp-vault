<?php
WpvUtils::VerifyWPVault();

require_once('admin.php');
require_once(dirname(__FILE__) . "/lib/wpv-function-tag.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-file2tag.php");
require_once(dirname(__FILE__) . "/lib/wpv-table-tag.php");

$wpv_message = new WpvMessage();

// Insert tags.
if ($_POST["proc"] == "new") {
    insert_new_tags($wpv_message);
}
// Delete selected tags from the system.
else if ($_POST["proc"] == "delete") {
    delete_tags($wpv_message);
}

$wpv_message->WriteMessages();

$unused_tag_table = WpvTag::GetTagTable("tag_id NOT IN (SELECT tag_id FROM $wpv_file2tag_table)");
$used_tag_name_table = WpvFile2Tag::GetUsedFile2TagNameTable();
?>

<div style="padding: 5px 5px 5px 5px">

<div style="float: left">
    <form name="new_tag_form" id="new-tag-form" method="post" action="">
    <div class="submit">
    <input type="submit" value="Add New Tag(s)" />
    </div>
    <div class="wpv-tab-interface">
        <span class="wpv-tab-button">New Tags</span>
        <div class="border">
            Tag:<br />
            <input type="text" name="new_tags" maxlength="50" size="20"/><br />
            <small>Separate multiple tags with comma.</small>
        </div>
    </div>
    <div class="submit">
    <input type="submit" value="Add New Tag(s)" />
    </div>

    <input type="hidden" name="proc" value="new" />
    <input type="hidden" name="page" value="wp-vault/wpv-tag-manager.php" />
    </form>
</div>

<div style="float: left">
    <form name="delete_tag_form" id="delete-tag-form" method="post" action="">
    <div class="submit">
    <input type="submit" value="Delete Selected Tag(s)" />
    </div>
    <div class="wpv-tab-interface">
        <span class="wpv-tab-button">Tags</span>
        <div class="border">
        <table>
        <tr>
        <td>
        Unused Tags:
        <?php
        WpvTagTable::DisplayTagTable($unused_tag_table, "width: 200px; height: 300px; white-space: nowrap;", "checkbox", "selected_tag");
        ?>
        </td>
        <td>
        Tags in Use:
        <?php
        WpvTagTable::DisplayTagTable($used_tag_name_table, "width: 200px; height: 300px; white-space: nowrap;");
        ?>
        </td>
        </tr>
        </table>
        </div>
    </div>
    <div class="submit">
    <input type="submit" value="Delete Selected Tag(s)" />
    <input type="hidden" name="proc" value="delete" />
    <input type="hidden" name="page" value="wp-vault/wpv-tag-manager.php" />
    </div>
    </form>
</div>

</div>

<?php
function insert_new_tags(&$wpv_message) {
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
            $wpv_message->AddErrorMessageLine("Invalid characters in tag: '$new_tag'");
        }
        else if (strlen($tag) > 25) {
            $wpv_message->AddErrorMessageLine("Tag is too long: '$new_tag'");
        }
        else if (WpvTag::TagExists($new_tag)) {
            $wpv_message->AddErrorMessageLine("Tag already exists: '$new_tag'");
        }
        else if ($new_tag != "") {
            array_push($new_tag_array, "'$new_tag'");
        }
    }

    if (count($new_tag_array) == 0) {
        return;
    }
    else if ($wpv_message->ErrorMessageExists()) {
        return;
    }

    $new_tag_array = array_unique($new_tag_array);
    $tag_sql = "INSERT INTO $wpv_tag_table (tag_name) VALUES (" . implode("), (", $new_tag_array) . ")";

    if ($wpdb->query($tag_sql) === FALSE) {
        $wpv_message->AddErrorMessageLine("Failed to add tag(s) due to database error.");
    }
    else {
        $wpv_message->AddMessageLine("Added new tag(s): " . implode(",", $new_tag_array));
    }
    return;
}

function delete_tags(&$wpv_message) {
    global $wpdb;
    global $wpv_tag_table;
    
    $_selected_tag = isset($_POST["selected_tag"]) ? $_POST["selected_tag"] : array();

    if (count($_selected_tag) > 0) {
        $tag_delete_sql = "DELETE FROM $wpv_tag_table WHERE tag_id IN (" . implode($_selected_tag, ",") . ")";

        $delete_tag_table = WpvTag::GetTagTable("tag_id IN (" . implode($_selected_tag, ",") . ")");
        $delete_tag = "";
        foreach ($delete_tag_table as $tag_data) {
            $delete_tag .= "'$tag_data->tag_name',";
        }

        // Execute delete.
        if (count(WpvFile2Tag::GetFile2TagTable("tag_id IN (" . implode($_selected_tag, ",") . ")")) > 0) {
            $wpv_message->AddErrorMessageLine("Cannot delete tags that are in use.");
        }
        else if ($wpdb->query($tag_delete_sql) !== FALSE) {
            $wpv_message->AddMessageLine("Successfully deleted: " . rtrim($delete_tag, ","));
        }
        else {
            $wpv_message->AddErrorMessageLine("Failed to delete tags because of a database error.");
        }
    }
}
?>

