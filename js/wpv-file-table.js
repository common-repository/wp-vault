
function submitPage(iPage) {
    var oPageNumber = document.getElementById("page-number");
    
    try {
        var oPostPaging = new WpvPaging();

        oPageNumber.value = iPage;
        oPageNumber.form.action.value = "wpv_file_table_page";
        oPageNumber.form.cookie.value = document.cookie;

        oPostPaging.targetId = "page-content";
        oPostPaging.loadingMessageId = "page-loading-message";
        oPostPaging.requestUri = oPageNumber.form.requestUri.value;
        oPostPaging.submitForm = oPageNumber.form;
        oPostPaging.refreshPage();
    }
    catch (ex) {
        oPageNumber.form.submit();
    }
}

function submitSort(oSelectOrder) {
    var sOrderBy = oSelectOrder.options[oSelectOrder.selectedIndex].value;

    submitRefresh(sOrderBy, null);
}

function submitDefaultView(obj) {
    if (obj != null)
        WpvAdmin.changeTabFocus(obj.parentNode);
    submitRefresh(null, 0);
}

function submitTagView(obj) {
    if (obj != null)
        WpvAdmin.changeTabFocus(obj.parentNode);
    submitRefresh(null, 1);
}

function submitMonthView(obj) {
    if (obj != null)
        WpvAdmin.changeTabFocus(obj.parentNode);
    submitRefresh(null, 2);
}

function submitNameSearch(obj) {
    if (obj != null)
        WpvAdmin.changeTabFocus(obj.parentNode);
    submitRefresh(null, 3);
}

function submitRefresh(sOrderBy, iSelectedTabIndex) {
    var frm = document.getElementById("selected-tab").form;
    var oPageNumber = document.getElementById("page-number");

    if (sOrderBy != null)
        frm.order_by.value = sOrderBy;
    if (iSelectedTabIndex != null)
        frm.selected_tab.value = iSelectedTabIndex;

    submitPage(1);
}

var iMouseTimeout = 0;

function doFileMouseOver(iId, bEnabled) {
    var oFileTable = document.getElementById("wpv-file-table");
    var oDiv = document.getElementById("file-cell-" + iId);
    var oMouseOverDiv = document.getElementById("wpv-file-cell-popup");
    var oDataDiv = document.getElementById("file-expand-data-" + iId);

    oDiv.style.cursor = "pointer";

    if (iMouseTimeout > 0) {
        clearTimeout(iMouseTimeout);
        iMouseTimeout = 0;
    }

    oMouseOverDiv.tag = iId;
    if (oMouseOverDiv.childNodes.length > 0)
        oMouseOverDiv.removeChild(oMouseOverDiv.firstChild);
    oMouseOverDiv.appendChild(oDataDiv.cloneNode(true));
    oMouseOverDiv.style.left = WpvObjectOffset.getOffsetLeft(oDiv) + 27 + "px";
    oMouseOverDiv.style.top = WpvObjectOffset.getOffsetTop(oDiv) + 27 + "px";
    oMouseOverDiv.style.display = "inline";
    oMouseOverDiv.onmousemove = function() {
        if (iMouseTimeout > 0) {
            clearTimeout(iMouseTimeout);
            iMouseTimeout = 0;
        }
    }

    if (bEnabled) {
        oMouseOverDiv.onclick = function() {
            toggleFileCheckbox(iId);
        }
    }
    else {
        oMouseOverDiv.onclick = null;
    }

    oMouseOverDiv.onmouseout = oDiv.onmouseout = function() {
        doFileMouseOut(iId);
    }
}

function doFileMouseOut(iId) {
    if (iMouseTimeout > 0) {
        clearTimeout(iMouseTimeout);
        iMouseTimeout = 0;
    }
    iMouseTimeout = setTimeout(function() {
        var oMouseOverDiv = document.getElementById("wpv-file-cell-popup");
        var oDiv = document.getElementById("file-cell-" + iId);
        
        oMouseOverDiv.style.display = "none";
    }, 500);
}

function toggleFileCheckbox(iId) {
    var oCheckbox = document.getElementById("file-checkbox-" + iId);
    var oDiv = document.getElementById("file-cell-" + iId);
    
    oCheckbox.checked = !oCheckbox.checked;
    if (oCheckbox.checked) {
        oDiv.className += " selected";
    }
    else {
        oDiv.className = oDiv.className.replace(/( selected)/g, "");
    }
}

function selectAllFiles() {
    var oInputArray = document.getElementsByTagName("INPUT");
    var oDivArray = document.getElementsByTagName("DIV");
    
    for (var i = 0; i < oInputArray.length; i++) {
        if (oInputArray[i].id.indexOf("file-checkbox-") == 0 && !oInputArray[i].disabled)
            oInputArray[i].checked = true;
    }
    for (var i = 0; i < oDivArray.length; i++) {
        if (oDivArray[i].id.indexOf("file-cell-") == 0 && oDivArray[i].className.indexOf("disabled") < 0)
            oDivArray[i].className += " selected";
    }
}

function unselectAllFiles() {
    var oInputArray = document.getElementsByTagName("INPUT");
    var oDivArray = document.getElementsByTagName("DIV");
    
    for (var i = 0; i < oInputArray.length; i++) {
        if (oInputArray[i].id.indexOf("file-checkbox-") == 0)
            oInputArray[i].checked = false;
    }
    for (var i = 0; i < oDivArray.length; i++) {
        if (oDivArray[i].id.indexOf("file-cell-") == 0)
            oDivArray[i].className = oDivArray[i].className.replace(/( selected)/g, "");
    }
}

