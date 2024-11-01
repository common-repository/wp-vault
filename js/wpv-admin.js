var WpvAdmin = {
    intervalId: 0,
    
    changeTabFocus: function (oTab) {
        var oTabArray = document.getElementsByTagName("SPAN");
        
        for (var i = 0; i < oTabArray.length; i++) {
            if (oTabArray[i].className == "wpv-tab-button")
                oTabArray[i].className = "wpv-tab-button-disabled";
        }
        oTab.className = "wpv-tab-button";
    },

    openImageDisplay: function (iFileId, sSiteUrl) {
        var oParameters = new XmlRequestParameters();
        var oDialog = document.getElementById("wpv-modal-dialog");

        oParameters.add("file_id", iFileId);
        oParameters.add("file_mode", "admin");
        oParameters.add("action", "wpv_admin_image_display");
        oParameters.add("cookie", document.cookie);
        WpvDialog.onLoad = WpvAdmin.checkImageLoad;
        WpvDialog.openDialog(sSiteUrl + "/wp-admin/admin-ajax.php", "POST", oParameters);
    },

    closeImageDisplay: function () {
        var oDialog = document.getElementById("wpv-modal-dialog");
        
        WpvDialog.closeDialog();
    },
    
    checkImageLoad: function () {
        WpvAdmin.intervalId = setInterval(
            function() {
                var oImage;
                
                if ((oImage = document.getElementById("wpv-loaded-image")) != null) {
                    if (typeof(oImage.complete) == "boolean") {
                        if (oImage.complete) {
                            var oFadeable = new FadeableObj(document.getElementById("wpv-loaded-image"));
                            
                            document.getElementById("wpv-image-loading").style.display = "none";
                            oFadeable.fadeIn();
                            clearInterval(WpvAdmin.intervalId);
                        }
                    }
                    else {
                        clearInterval(WpvAdmin.intervalId);
                        FadeableObj.makeFadeable(document.getElementById("wpv-loaded-image"), 1.0);
                    }
                }
            }, 500
        );
    }
}