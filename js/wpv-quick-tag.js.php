function addTags(e, frm) {
    if (frm.new_tags.value.replace(/^ | $/, "") == "")
        return;

    var oParameters = new XmlRequestParameters();
    var oXmlRequest = new XmlRequest();

    oParameters.add("new_tags", frm.new_tags.value);
    oParameters.add("action", "wpv_quick_tag");
    oParameters.add("cookie", document.cookie);
    oXmlRequest.requestUri = "<?php echo get_settings("siteurl") . "/wp-admin/admin-ajax.php"; ?>";
    oXmlRequest.onRequestSent = addTagsRequestSent;
    oXmlRequest.onSuccess = addTagsRequestSuccess;
    oXmlRequest.onFailure = addTagsRequestFailed;
    oXmlRequest.onTimeOut = addTagsRequestFailed;
    oXmlRequest.onCanceled = addTagsRequestFailed;
    oXmlRequest.sendPostRequest(oParameters);
    frm.new_tags.value = "";
    e = e || window.event;
    WpvPopup.offsetLeft = DraggableObj.mouseCoords(e).x + 50;
    WpvPopup.offsetTop = DraggableObj.mouseCoords(e).y + 10; 
    WpvPopup.canMove = false;
    WpvPopup.show("<div style='padding: 10px 10px 10px 10px; background-color: #D9E2FD; border: 1px solid #14568A; font-weight: bold;'>Please wait...</div>");
}

function addTagsRequestSent() {
    var oQuickAdd = document.getElementById("quick-add-tags");
    
    oQuickAdd.style.visibility = "hidden";
}

function addTagsRequestSuccess(oRequest) {
    var oQuickAdd = document.getElementById("quick-add-tags");
    var oTagTable = document.getElementById("tag-table");
    
    oQuickAdd.style.visibility = "visible";
    oTagTable.innerHTML = oRequest.getResponseText();
    WpvPopup.updateText("<div style='padding: 10px 10px 10px 10px; background-color: #D9E2FD; border: 1px solid #14568A; font-weight: bold;'>" + document.getElementById("quick-tag-message").innerHTML + "</div>");
    setTimeout(function() { WpvPopup.hide(); }, 5000);
}

function addTagsRequestFailed() {
    var oQuickAdd = document.getElementById("quick-add-tags");
    
    oQuickAdd.style.visibility = "visible";
    WpvPopup.updateText("<div style='padding: 10px 10px 10px 10px; background-color: #ff0000; color: #ffffff; font-weight: bold;'>Failed to add tags due to system error.</div>");
    setTimeout(function() { WpvPopup.hide(); }, 5000);
}
