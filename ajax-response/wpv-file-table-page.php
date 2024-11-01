<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-file.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-tag.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-file2tag.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-image.php");
require_once(dirname(__FILE__) . "/../lib/wpv-table-file.php");

if (isset($_POST["post_id"]))
    require_once(dirname(__FILE__) . "/../lib/wpv-function-post2file.php");

$default_order = "stored_datetime DESC, file_name ASC";

$_selected_tab = isset($_POST["selected_tab"]) ? $_POST["selected_tab"] : 0;
$_order_by = isset($_POST["order_by"]) && $_POST["order_by"] != "" ? $_POST["order_by"] : $default_order;
?>
<div class="search-control">
<?php
WpvFileTable::DisplayFilePageControl($_selected_tab, $_order_by);
?>
</div>
<?php
WpvFileTable::DisplayFilePage($_selected_tab, $_order_by);
?>