<?php
global $post;
global $userdata;

if ((current_user_can("wpv_access_own_posts") && ($post->ID == null || $post->post_author == $userdata->ID))|| current_user_can("wpv_access_all_posts")) {
}
else {
    exit;
}
?>

<div class='dbx-b-ox-wrapper'>
    <fieldset id='wpvadvanceddiv' class='dbx-box'>
        <div class='dbx-h-andle-wrapper'>
            <h3 class='dbx-handle'>Linked WP Vault Files</h3>
        </div>
        <div class='dbx-c-ontent-wrapper'>
            <div class='dbx-content'>
            <?php
            if ($post->ID == null) {
            ?>
                The post must be saved before linking files using WP Vault.
            <?php
            }
            else {
            ?>
                <div style="text-align: right"><a href='<?php echo get_settings("siteurl") . "/wp-admin/admin.php?page=wp-vault/wpv-redirect.php&post_id=$post->ID&proc=file-selection-page"; ?>'>Link files to this post with WP Vault</a> &raquo;</div>
                <div id='wpv-content'></div>
            <?php
            }
            ?>
            </div>
        </div>
    </fieldset>
</div>

<script type="text/javascript">
<?php
if ($post->ID != null) {
?>
    var oParameters = new XmlRequestParameters();
    var oXmlRequest = new XmlRequest();

    oParameters.add("post_id", <?php echo $post->ID; ?>);
    oParameters.add("action", "wpv_linked_image_list");
    oParameters.add("cookie", document.cookie);
    oXmlRequest.requestUri = "<?php echo get_settings("siteurl") . "/wp-admin/admin-ajax.php"; ?>";
    oXmlRequest.onRequestSent = doRequestSent;
    oXmlRequest.onSuccess = doRequestSuccess;
    oXmlRequest.onFailure = doRequestFailure;
    oXmlRequest.onTimeOut = doRequestFailure;
    oXmlRequest.onCanceled = doRequestFailure;
    oXmlRequest.sendPostRequest(oParameters);

    function doRequestSent() {
        var oWpvContent = document.getElementById("wpv-content");
        
        oWpvContent.innerHTML = "Loading...";
    }

    function doRequestSuccess(oRequest) {
        var oWpvContent = document.getElementById("wpv-content");
        
        oWpvContent.innerHTML = oRequest.getResponseText();
    }

    function doRequestFailure() {
        var oWpvContent = document.getElementById("wpv-content");
        
        oWpvContent.innerHTML = "Unable to get the image list.";
    }
<?php
}
?>
</script>
