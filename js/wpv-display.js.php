<?php
require_once(dirname(__FILE__) . "/../lib/wpv-function.php");
require_once(dirname(__FILE__) . "/../lib/wpv-function-display-option.php");

if (isset($_GET["post_id"])) {
    $_post_id = $_GET["post_id"];
}
else {
    WpvUtils::ShowPageNotFound();
}
    
$display_option = WpvDisplayOption::GetDisplayOption($_post_id, TRUE);
if ($display_option === FALSE) {
    WpvUtils::ShowPageNotFound();
}
?>
var WpvFileHandler_<?php echo $_post_id; ?> = {
    intervalId: 0,
    
    mouseOverCell: function (cell) {
        cell.style.borderWidth = "<?php echo $display_option->border_width; ?>px";
        cell.style.borderColor = "<?php echo $display_option->border_color_hover; ?>";
        cell.style.backgroundColor = "<?php echo $display_option->cell_background_color_hover; ?>";
        cell.style.cursor = "pointer";
    },

    mouseOutCell: function (cell) {
        cell.style.borderWidth = "<?php echo $display_option->border_width; ?>px";
        cell.style.borderColor = "<?php echo $display_option->border_color; ?>";
        cell.style.backgroundColor = "<?php echo $display_option->cell_background_color; ?>";
        cell.style.cursor = "default";
    },

    downloadFile: function (sRequest) {
        self.location.href = "<?php echo get_bloginfo("siteurl"); ?>/?" + sRequest;
    },

    showImage: function (file_id, post_id, action_type, hash) {
        var oParameters = new XmlRequestParameters();
        
        oParameters.add("wpv-image-display", true);
        oParameters.add("file_id", file_id);
        oParameters.add("post_id", post_id);
        oParameters.add("action_type", action_type);
        oParameters.add("hash", hash);
        WpvDialog.onLoad = WpvFileHandler_<?php echo $_post_id; ?>.checkImageLoad;
        WpvDialog.openDialog("<?php echo get_bloginfo("siteurl"); ?>/", "POST", oParameters, "wpv-modal-dialog-<?php echo $_post_id; ?>");
    },

    doDefault: function (sRequest) {
        self.location.href = "<?php echo get_bloginfo("siteurl"); ?>/?" + sRequest;
    },
    
    checkImageLoad: function () {
        WpvFileHandler_<?php echo $_post_id; ?>.intervalId = setInterval(
            function() {
                var oImage;
                var sLoadedImage = "wpv-loaded-image-<?php echo $_post_id; ?>";
                
                if ((oImage = document.getElementById(sLoadedImage)) != null) {
                    if (typeof(oImage.complete) == "boolean") {
                        if (oImage.complete) {
                            var oFadeable = new FadeableObj(oImage);
                            
                            document.getElementById("wpv-image-loading-<?php echo $_post_id; ?>").style.display = "none";
                            oFadeable.fadeIn();
                            clearInterval(WpvFileHandler_<?php echo $_post_id; ?>.intervalId);
                        }
                    }
                    else {
                        clearInterval(WpvFileHandler_<?php echo $_post_id; ?>.intervalId);
                        FadeableObj.makeFadeable(oImage, 1.0);
                    }
                }
            }, 500
        );
    }
}
