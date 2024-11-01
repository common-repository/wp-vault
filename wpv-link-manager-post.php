<?php
require_once(dirname(__FILE__) . "/lib/wpv-function-post.php");
require_once(dirname(__FILE__) . "/lib/wpv-table-post.php");

include(dirname(__FILE__) . "/wpv-link-manager-header.php");
?>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-link-manager-post"; ?>"></script>
<script type="text/javascript" src="<?php echo get_bloginfo("siteurl") . "?wpv-js=wpv-paging"; ?>"></script>

<form name="link-form" id="link-form" method="post" action="">
<?php
WpvPostTable::DisplayPostTable();
?>
</form>

<iframe name="preview" id="wpv-preview-frame"></iframe>
