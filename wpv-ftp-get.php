<?php
WpvUtils::VerifyWPVault();

if (!current_user_can("wpv_get_ftp_files"))
    WpvUtils::ShowPageNotFound();
else if (!WpvUtils::EncryptionEnabled()) {
    $wpv_message = new WpvMessage();
    $wpv_message->SetIsCollapsible(FALSE);
    $wpv_message->AddMessageLine();
    $wpv_message->AddMessageLine("mcrypt library is not enabled on your PHP installation.");
    $wpv_message->AddMessageLine();
    $wpv_message->AddMessageLine("For security reason, this feature is disabled without the library.");
    $wpv_message->AddMessageLine();
    $wpv_message->WriteMessages();
    exit;
}

require_once('admin.php');
?>
<script type="text/javascript">
var WpvFTPGet = {
    FTP_GET: "get",
    FTP_CHDIR: "chdir",
    FTP_LS: "ls",
    FTP_CLOSE: "close",
    
    xmlRequest: new XmlRequest(),
    
    sendConnect: function() {
        var frm = document.getElementById("wpv-ftp-connect");
        
        if (frm.host.value.replace(/^[ ]+|[ ]+$/, "") == "") {
            alert("Missing host.");
            frm.host.focus();
            return;
        }
        if (frm.port.value.replace(/^[ ]+|[ ]+$/, "") == "")
            frm.port.value = "21";
        if (frm.user_id.value.replace(/^[ ]+|[ ]+$/, "") == "")
            frm.user_id.value = "ftp";
        frm.cookie.value = document.cookie;
        WpvFTPGet.xmlRequest.requestUri = frm.requestUri.value;
        
        WpvFTPGet.xmlRequest.onRequestSent = WpvFTPGet.connectRequestSent;
        WpvFTPGet.xmlRequest.onSuccess = WpvFTPGet.requestSuccess;
        WpvFTPGet.xmlRequest.onFailed = WpvFTPGet.requestFailed;
        WpvFTPGet.xmlRequest.onTimeOut = WpvFTPGet.requestTimeout;
        WpvFTPGet.xmlRequest.onCanceled = WpvFTPGet.requestFailed;
        
        if (WpvFTPGet.xmlRequest.isReady) {
            WpvFTPGet.xmlRequest.sendPostRequestFromForm(frm);
        }
    },
    
    sendClose: function() {
        var frm = document.getElementById("wpv-ftp");
        
        frm.cookie.value = document.cookie;
        frm.command.value = WpvFTPGet.FTP_CLOSE;
        WpvFTPGet.xmlRequest.requestUri = frm.requestUri.value;

        WpvFTPGet.xmlRequest.onRequestSent = WpvFTPGet.requestSent;
        WpvFTPGet.xmlRequest.onSuccess = WpvFTPGet.sessionClosed;
        WpvFTPGet.xmlRequest.onFailed = WpvFTPGet.sessionClosed;
        WpvFTPGet.xmlRequest.onTimeOut = WpvFTPGet.sessionClosed;
        WpvFTPGet.xmlRequest.onCanceled = WpvFTPGet.sessionClosed;

        if (WpvFTPGet.xmlRequest.isReady) {
            WpvFTPGet.xmlRequest.sendPostRequestFromForm(frm);
        }
    },
    
    sendCommand: function(sCommand, sParam) {
        var frm = document.getElementById("wpv-ftp");
        
        frm.cookie.value = document.cookie;
        frm.command.value = sCommand;
        frm.param.value = sParam == null ? "" : sParam;
        WpvFTPGet.xmlRequest.requestUri = frm.requestUri.value;
        
        WpvFTPGet.xmlRequest.onRequestSent = WpvFTPGet.requestSent;
        WpvFTPGet.xmlRequest.onSuccess = WpvFTPGet.requestSuccess;
        WpvFTPGet.xmlRequest.onFailed = WpvFTPGet.requestFailed;
        WpvFTPGet.xmlRequest.onTimeOut = WpvFTPGet.requestTimeout;
        WpvFTPGet.xmlRequest.onCanceled = WpvFTPGet.requestFailed;
        
        if (WpvFTPGet.xmlRequest.isReady) {
            WpvFTPGet.xmlRequest.sendPostRequestFromForm(frm);
        }
    },
        
    connectRequestSent: function() {
        var oFTPFileList;
        if ((oFTPFileList = document.getElementById("wpv-ftp-file-list")) != null) {
            var frm = document.getElementById("wpv-ftp-connect");
            
            oFTPFileList.innerHTML = "<div class='loading'>Connecting to FTP Server...</div>";
            
            frm.user_id.disabled = true;
            frm.password.disabled = true;
            frm.host.disabled = true;
            frm.port.disabled = true;
            frm.connect.disabled = true;
        }
    },

    requestSent: function() {
        var oFTPFileList;
        if ((oFTPFileList = document.getElementById("wpv-ftp-file-list")) != null) {
            oFTPFileList.innerHTML = "<div class='loading'>Sending FTP Command...</div>";
        }
    },
    
    requestFailed: function() {
        var oFTPFileList;
        var frm = document.getElementById("wpv-ftp-connect");
        
        frm.user_id.disabled = false;
        frm.password.disabled = false;
        frm.host.disabled = false;
        frm.port.disabled = false;
        frm.connect.disabled = false;
        frm.disconnect.disabled = true;        

        if ((oFTPFileList = document.getElementById("wpv-ftp-file-list")) != null) {
            oFTPFileList.innerHTML = "<div class='loading'>Failed to execute the FTP command</div>";
        }
    },

    requestTimeout: function() {
        var oFTPFileList;
        var frm = document.getElementById("wpv-ftp-connect");
        
        frm.user_id.disabled = false;
        frm.password.disabled = false;
        frm.host.disabled = false;
        frm.port.disabled = false;
        frm.connect.disabled = false;
        frm.disconnect.disabled = true;        
        if ((oFTPFileList = document.getElementById("wpv-ftp-file-list")) != null) {
            oFTPFileList.innerHTML = "<div class='loading'>Request Time-out</div>";
        }
    },
    
    requestSuccess: function(oRequest, iStatus, sStatusText) {
        var oFTPFileList;
        if ((oFTPFileList = document.getElementById("wpv-ftp-file-list")) != null) {
            var frm = document.getElementById("wpv-ftp-connect");

            frm.disconnect.disabled = false;
            oFTPFileList.innerHTML = oRequest.getResponseText();
        }
    },
    
    sessionClosed: function(oRequest, iStatus, sStatusText) {
        var frm = document.getElementById("wpv-ftp-connect");
        
        WpvFTPGet.requestSuccess(oRequest, iStatus, sStatusText);

        frm.user_id.disabled = false;
        frm.password.disabled = false;
        frm.host.disabled = false;
        frm.port.disabled = false;
        frm.connect.disabled = false;
        frm.disconnect.disabled = true;        
    }
}
</script>

<div style="padding: 20px 10px 20px 10px; white-space: nowrap;">
    <form name="wpv_ftp_connect" id="wpv-ftp-connect" action="" method="post">
        FTP Host / Port: <input type="text" name="host" value="" size="20"/> / <input type="text" name="port" value="21" size="4" maxlength="4"/>
        &nbsp;&nbsp;&nbsp;
        User ID / Password: <input type="text" name="user_id" value="ftp" size="10"/>/ <input type="password" name="password" value="" size="10"/>
        <span class="submit">
            <input type="button" value="Connect" name="connect" onclick="WpvFTPGet.sendConnect()" />
            <input type="button" value="Disconnect" name="disconnect" onclick="WpvFTPGet.sendClose()" disabled />
        </span>
        <input type="hidden" name="command" value="connect" />
        <input type="hidden" name="cookie" value="" />
        <input type="hidden" name="requestUri" value="<?php echo get_bloginfo("siteurl"); ?>/wp-admin/admin-ajax.php" />
        <input type="hidden" name="action" value="wpv_ftp_action" />
    </form>
</div>

<form name="wpv_ftp" id="wpv-ftp" action="" method="post">    
    <input type="hidden" name="cookie" value="" />
    <input type="hidden" name="requestUri" value="<?php echo get_bloginfo("siteurl"); ?>/wp-admin/admin-ajax.php" />
    <input type="hidden" name="action" value="wpv_ftp_action" />
    <input type="hidden" name="command" value="" />
    <input type="hidden" name="param" value="" />
    <input type="hidden" name="dir" value="" />

    <div id="wpv-ftp-file-list">
    </div>
</form>

