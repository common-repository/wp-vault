<?php
WpvUtils::VerifyWPVault();

require_once("admin.php");
require_once(dirname(__FILE__) . "/lib/wpv-function.php");
require_once(dirname(__FILE__) . "/lib/wpv-function-post.php");

$_proc = isset($_POST["proc"]) ? $_POST["proc"] : "post";
$_post_id = isset($_POST["post_id"]) ? $_POST["post_id"] : "-1";

$post_data = WpvPost::GetPost($_post_id);

if ($post_data === FALSE) {
    $_POST["proc"] = "post";
    $_POST["post_id"] = "-1";
    $_proc = "post";
    $_post_id = "-1";
}

if (preg_match("/^file-selection/", $_proc)) {
    include(dirname(__FILE__).'/wpv-link-manager-file.php');
}
else if (preg_match("/^file-link/", $_proc)) {
    include(dirname(__FILE__).'/wpv-link-manager-filelink.php');
}
else if (preg_match("/^display-option/", $_proc)) {
    include(dirname(__FILE__).'/wpv-link-manager-option.php');
}
else {
    include(dirname(__FILE__).'/wpv-link-manager-post.php');
}

if ($_post_id > -1) {
?>
<script type="text/javascript">
    getDisplayStatus("<?php echo $_post_id; ?>");
</script>
<?php
}
?>

