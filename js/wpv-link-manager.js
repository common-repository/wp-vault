function gotoLinkPage(sPage) {
    var frm = document.getElementById("link-navigation");

    frm.proc.value = sPage;
    frm.submit();
}

function getDisplayStatus(sPostId) {
    var oParameters = new XmlRequestParameters();
    var oXmlRequest = new XmlRequest();

    oParameters.add("post_id", sPostId);
    oParameters.add("action", "wpv_display_status");
    oParameters.add("cookie", document.cookie);
    oXmlRequest.requestUri = "<?php echo get_settings("siteurl") . "/wp-admin/admin-ajax.php"; ?>";
    oXmlRequest.onRequestSent = getDisplayStatusSent;
    oXmlRequest.onSuccess = getDisplayStatusSuccess;
    oXmlRequest.onFailure = getDisplayStatusFailed;
    oXmlRequest.onTimeOut = getDisplayStatusFailed;
    oXmlRequest.onCanceled = getDisplayStatusFailed;
    oXmlRequest.sendPostRequest(oParameters);
}

function getDisplayStatusSent() {
    var oDisplayStatusText = document.getElementById("wpv-display-status");
    
    oDisplayStatusText.innerHTML = "Loading...";
}

function getDisplayStatusSuccess(oRequest) {
    var oDisplayStatusText = document.getElementById("wpv-display-status");
    
    oDisplayStatusText.innerHTML = oRequest.getResponseText();
}

function getDisplayStatusFailed() {
    var oDisplayStatusText = document.getElementById("wpv-display-status");
    
    oDisplayStatusText.innerHTML = "Unable to get the display status";
}
