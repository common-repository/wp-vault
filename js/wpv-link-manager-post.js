var WpvPost = {
    submitPage: function (iPage) {
        var oPageNumber = document.getElementById("page-number");

        try {
            var oPostPaging = new WpvPaging();

            oPageNumber.value = iPage;
            oPageNumber.form.action.value = "wpv_post_table_page";
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
    },
    
    selectPost: function (postID) {
        var linkForm = document.getElementById("link-form");

        linkForm.proc.value = "file-selection-page";
        linkForm.post_id.value = postID;
        linkForm.submit();
    },
    
    submitSort: function (sSortBy) {
        var linkForm = document.getElementById("link-form");

        if (linkForm.sort_by.value == sSortBy && linkForm.sort_modifier.value == "ASC")
            linkForm.sort_modifier.value = "DESC";
        else if (linkForm.sort_by.value == sSortBy && linkForm.sort_modifier.value == "DESC")
            linkForm.sort_modifier.value = "ASC";
        else if (sSortBy == "post_modified" || sSortBy == "post_date")
            linkForm.sort_modifier.value = "DESC";
        else
            linkForm.sort_modifier.value = "ASC";
        linkForm.sort_by.value = sSortBy;

        WpvPost.submitPage(1);
    },
    
    submitTabSelection: function (iTabIndex, obj) {
        var linkForm = document.getElementById("link-form");

        if (obj != null)
            WpvAdmin.changeTabFocus(obj.parentNode);

            linkForm.sort_by.value = "post_modified";
        linkForm.sort_modifier.value = "DESC";
        linkForm.selected_tab_index.value = iTabIndex;

        WpvPost.submitPage(1);
    },

    viewPreview: function (sParam) {
        var previewFrame = document.getElementById("wpv-preview-frame");

        previewFrame.style.display = "inline";
        previewFrame.src = sParam;
    }    
}
