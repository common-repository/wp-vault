function WpvPaging() {
    this.targetId = null;
    this.loadingMessageId = null;
    this.requestUri = null;
    this.submitForm = null;
    this.xmlRequest = new XmlRequest();
    
    this.refreshPage = function() {
        if (this.targetId == null)
            throw "Unknown page target Id.";
        else if (this.requestUri == null)
            throw "Unknown request URI.";
        else if (this.submitForm == null)
            throw "Unknown form to submit.";
        else {
            this.xmlRequest.onRequestSent = this.initializePage.bind(this);
            this.xmlRequest.onSuccess = this.writePage.bind(this);
            this.xmlRequest.onFailure = this.notifyPageFailure.bind(this);
            this.xmlRequest.onTimeOut = this.notifyPageTimeout.bind(this);
            this.xmlRequest.onCanceled = this.notifyPageFailure.bind(this);
            this.xmlRequest.requestUri = this.requestUri;
            this.xmlRequest.sendPostRequestFromForm(this.submitForm);
        }
    }

    this.notifyPageFailure = function(oRequest, iStatus, sText) {
        var oPageContentPane = document.getElementById(this.targetId);
        var oPageLoadingMessage = document.getElementById(this.loadingMessageId);

        if (oPageLoadingMessage != null)
            oPageLoadingMessage.style.display = "none";
        oPageContentPane.style.visibility = "visible";
        oPageContentPane.innerHTML = iStatus + ": " + sText;
    }

    this.notifyPageTimeout = function(oRequest, iStatus, sText) {
        var oPageContentPane = document.getElementById(this.targetId);
        var oPageLoadingMessage = document.getElementById(this.loadingMessageId);

        if (oPageLoadingMessage != null)
            oPageLoadingMessage.style.display = "none";
        oPageContentPane.style.visibility = "visible";
        oPageContentPane.innerHTML = "Request Timeout";
    }

    this.initializePage = function() {
        var oPageContentPane = document.getElementById(this.targetId);
        var oPageLoadingMessage = document.getElementById(this.loadingMessageId);

        oPageContentPane.style.visibility = "hidden";
        if (oPageLoadingMessage != null)
            oPageLoadingMessage.style.display = "inline";
    }

    this.writePage = function(oRequest) {
        var oPageContentPane = document.getElementById(this.targetId);
        var oPageLoadingMessage = document.getElementById(this.loadingMessageId);

        oPageContentPane.innerHTML = oRequest.getResponseText();
        if (oPageLoadingMessage != null)
            oPageLoadingMessage.style.display = "none";
        oPageContentPane.style.visibility = "visible";
    }
}
