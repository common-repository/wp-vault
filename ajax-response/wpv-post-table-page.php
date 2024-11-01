<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function-post.php");
require_once(dirname(__FILE__) . "/../lib/wpv-table-post.php");

$_selected_tab_index = isset($_POST["selected_tab_index"]) ? $_POST["selected_tab_index"] : 0;

WpvPostTable::GetPostPage($_selected_tab_index);
?>
