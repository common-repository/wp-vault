Function.prototype.bind = function(obj) {
    var method = this;
    var temp = function() {
        return method.apply(obj, arguments);
    };

    return temp;
}

var XmlRequestManager = {

}

function XmlRequest() {
    this.timeOut = 90000;
    this.timeOutId = -1;
    this.responseText = null;
    this.httpRequest = null;
    this.requestUri = null;
    this.parameters = null;
    
    try { this.httpRequest = new ActiveXObject("Msxml2.XMLHTTP"); } catch (e) {}
    try { this.httpRequest = new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) {}
    try { this.httpRequest = new XMLHttpRequest(); } catch(e) {}
    
    if (this.httpRequest == null)
        throw new XmlRequestException("Unable to create XmlRequest.");
    
    this.isReady = function() {
        if (this.httpRequest == null)
            return false;
        else if (this.requestUri == null)
            return false;
        else if (this.httpRequest.readyState != 0 && this.httpRequest.readyState != 4)
            return false;
        else
            return true;
    }
    
    this.sendPostRequest = function(oParameters) {
        if (this.isReady()) {
            var theObj = this;
            
            this.parameters = oParameters.getParameters();
            
            this.responseText = null;
            this.httpRequest.open("POST", this.requestUri, true);
            this.httpRequest.onreadystatechange = this.doOnReadyStateChange.bind(this);
            this.httpRequest.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            this.httpRequest.setRequestHeader("Content-length", this.parameters.length);
            this.httpRequest.setRequestHeader("Connection", "close");
            this.httpRequest.send(this.parameters);
            this.timeOutId = setTimeout(this.cancelRequest.bind(this, true), this.timeOut);
            if (this.onRequestSent != null)
                this.onRequestSent(this, this.requestUri, this.parameters);
        }
        else {
            throw new XmlRequestException("Not ready.");
        }
    }
    
    this.sendPostRequestFromForm = function(frm) {
        var oParameters = new XmlRequestParameters();
        
        oParameters.addParametersFromForm(frm);
        this.sendPostRequest(oParameters);
    }
    
    this.sendGetRequest = function (oParameters) {
        if (this.isReady()) {
            this.parameters = oParameters.getParameters();

            this.responseText = null;
            this.httpRequest.open("GET", this.requestUri + "?" + this.parameters, true);
            this.httpRequest.onreadystatechange = this.doOnReadyStateChange.bind(this);
            this.httpRequest.setRequestHeader("Content-length", this.parameters.length);
            this.httpRequest.setRequestHeader("Connection", "close");
            this.httpRequest.send(null);
            this.timeOutId = setTimeout(function() {this.cancelRequest.bind(this, true)}, this.timeOut);
            if (this.onRequestSent != null)
                this.onRequestSent(this, this.requestUri, this.parameters);
        }
        else {
            throw new XmlRequestException("Not ready.");
        }
    }

    this.doOnReadyStateChange = function() {
        if (this.httpRequest.readyState == 4)  {
            clearTimeout(this.timeOutId);
            if (this.httpRequest.status == 200) {
                this.responseText = this.httpRequest.responseText;
                if (this.onSuccess != null) {
                    this.onSuccess(this, this.httpRequest.status, this.httpRequest.statusText);
                }
            }
            else {
                if (this.onFailure != null) {
                    this.onFailure(this, this.httpRequest.status, this.httpRequest.statusText);
                }
            }
        }
    }

    this.onRequestSent = null;
    this.onSuccess = null;
    this.onFailure = null;
    this.onTimeOut = null;
    this.onCanceled = null;

    this.getResponseText = function() {
        return this.responseText;
    }

    this.cancelRequest = function(bTimeout) {
        clearTimeout(this.timeOutId);
        this.httpRequest.abort();
        if (bTimeout == true && this.onTimeOut != null)
            this.onTimeOut(this, -1, "Request Timed-Out");
        if (this.onCanceled != null)
            this.onCanceled(this, -1, "Request canceled");
    }
}

function XmlRequestParameters() {
    this.Parameters = new Array();
    
    this.add = function(sName, sValue) {
        this.Parameters.push(sName + "=" + encodeURIComponent(sValue));
    }
    
    this.getParameters = function() {
        var sParameters = this.Parameters.join("&");
        
        if (/Konqueror|Safari|KHTML/.test(navigator.userAgent)) 
            sParameters += "&_=";
        return sParameters;
    }

    this.addParametersFromForm = function(frm) {
        for (var i = 0; i < frm.elements.length; i++ ) {
            if (frm.elements[i].type.toLowerCase() == "checkbox" || frm.elements[i].type.toLowerCase() == "radio") {
                if (frm.elements[i].checked)
                    this.add(frm.elements[i].name, frm.elements[i].value);
            }
            else {
                this.add(frm.elements[i].name, frm.elements[i].value);
            }
        }
    }
}

function XmlRequestException(sMessage) {
    this.message = sMessage;
    this.name = "XmlRequestException";
}


