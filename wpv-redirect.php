<?php
if (isset($_GET["proc"]) && isset($_GET["post_id"])) {
    ?>
    Loading...Please wait
    <form id="wpv-redirect-form" action="<?php echo get_settings("siteurl") . "/wp-admin/admin.php?page=wp-vault/wpv-link-manager.php"; ?>" method="post">
        <input type="hidden" name="post_id" value="<?php echo $_GET["post_id"]; ?>" />
        <input type="hidden" name="proc" value="<?php echo $_GET["proc"]; ?>" />
    </form>
    <script type="text/javascript">
    document.getElementById("wpv-redirect-form").submit();
    </script>
<?php
}
else {
?>
Redirection failed.
<?php
}
?>