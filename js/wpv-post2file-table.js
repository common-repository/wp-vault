function submitPostFileDisplay(sMode) {
    var frm = document.getElementById("post-file-display").form;
    
    frm.post_file_display.value = sMode;
    frm.submit();
}

function togglePost2FileCheckbox(oTbody, iFileId) {
    var oCheckbox = document.getElementById("post2file-checkbox-" + iFileId);
    
    oCheckbox.checked = !oCheckbox.checked;
    if (oCheckbox.checked) {
        oTbody.className += " selected";
    }
    else {
        oTbody.className = oTbody.className.replace(/( selected)/g, "");
    }
}

function selectAllPost2Files() {
    var oInputArray = document.getElementsByTagName("INPUT");
    var oTBodyArray = document.getElementsByTagName("TBODY");
    
    for (var i = 0; i < oInputArray.length; i++) {
        if (oInputArray[i].id.indexOf("post2file-checkbox-") > -1)
            oInputArray[i].checked = true;
    }
    for (var i = 0; i < oTBodyArray.length; i++) {
        if (oTBodyArray[i].id.indexOf("post2file-row-group-") > -1)
            oTBodyArray[i].className += " selected";
    }
}

function unselectAllPost2Files() {
    var oInputArray = document.getElementsByTagName("INPUT");
    var oTBodyArray = document.getElementsByTagName("TBODY");
    
    for (var i = 0; i < oInputArray.length; i++) {
        if (oInputArray[i].id.indexOf("post2file-checkbox-") == 0)
            oInputArray[i].checked = false;
    }
    for (var i = 0; i < oTBodyArray.length; i++) {
        if (oTBodyArray[i].id.indexOf("post2file-row-group-") == 0)
            oTBodyArray[i].className = oTBodyArray[i].className.replace(/( selected)/g, "");
    }
}

