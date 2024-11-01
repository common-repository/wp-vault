
function FadeableObj(obj) {
    this.intervalId = 0;
    this.fadeableObj = obj;
    this.opacity = obj.style.opacity == null || obj.style.opacity == "" ? 1.0 : parseInt(obj.style.opacity);
    this.pause = 100;
    this.increment = 0.2;
    this.intervalId = 0;
    
    if (obj.id == null || obj.id == "")
        alert("FadeableObj.constructor(): ID property is required for the object.");
    
    this.setOpacity = function(fOpacity) {
        if (this.fadeableObj.style.opacity)
            this.fadeableObj.style.opacity = fOpacity;
        if (this.fadeableObj.style.MozOpacity)
            this.fadeableObj.style.MozOpacity = fOpacity;
        if (this.fadeableObj.style.KhtmlOpacity)
            this.fadeableObj.style.KhtmlOpacity = fOpacity;
        if (this.fadeableObj.style.filter)
            this.fadeableObj.style.filter = "alpha(opacity=" + (fOpacity*100.0) + ")";

        this.opacity = fOpacity;
    }
    
    this.fadeIn = function () {
        FadeableObjManager.fadeIn(this);
    }
}

var FadeableObjManager = {    
    intervalArray: new Array(),
    
    fadeIn: function (obj) {
        FadeableObjManager.intervalArray[obj.id] = 0;
        
        if (obj != null) {
            FadeableObjManager.intervalArray[obj.id] = setInterval(
                function() {
                    var fOpacity = obj.opacity;
                    
                    if (fOpacity >= 1.0) {
                        clearInterval(FadeableObjManager.intervalArray[obj.id]);
                        FadeableObjManager.intervalArray[obj.id] = null;
                    }
                    else {
                        obj.setOpacity(fOpacity+obj.increment);
                    }
                }, obj.pause
            );
        }
    }
}

var DraggableObj = {
    dragObject: null,
    mouseOffset: null,

    mouseCoords: function(ev) {
        if (ev.pageX || ev.pageY) {
            return {x:ev.pageX, y:ev.pageY};
        }
        return {
            x:ev.clientX + ScrollXY.getX() - document.body.clientLeft,
            y:ev.clientY + ScrollXY.getY()  - document.body.clientTop
        };
    },

    makeClickable: function(object) {
            object.onmousedown = function(){
                DraggableObj.dragObject = this;
            }
    },

    getMouseOffset: function(target, ev) {
            ev = ev || window.event;

            var docPos    = DraggableObj.getPosition(target);
            var mousePos  = DraggableObj.mouseCoords(ev);
            return {x:mousePos.x - docPos.x, y:mousePos.y - docPos.y};
    },

    getPosition: function(e) {
            var left = 0;
            var top  = 0;

            while (e.offsetParent){
                left += e.offsetLeft;
                top += e.offsetTop;
                e = e.offsetParent;
            }

            left += e.offsetLeft;
            top  += e.offsetTop;

            return {x:left, y:top};
    },

    mouseMove: function(ev) {
        ev = ev || window.event;
        var mousePos = DraggableObj.mouseCoords(ev);

        if (DraggableObj.dragObject) {
            DraggableObj.dragObject.style.position = 'absolute';
            DraggableObj.dragObject.style.top = mousePos.y - DraggableObj.mouseOffset.y + "px";
            DraggableObj.dragObject.style.left = mousePos.x - DraggableObj.mouseOffset.x + "px";

            return false;
        }
    },

    mouseUp: function() {
            DraggableObj.dragObject = null;
    },

    makeDraggable: function(item) {
            if(!item) return;
            
            document.onmousemove = DraggableObj.mouseMove;
            document.onmouseup = DraggableObj.mouseUp;

            item.style.cursor = "move";
            item.onmousedown = function(ev) {
                DraggableObj.dragObject  = this;
                DraggableObj.mouseOffset = DraggableObj.getMouseOffset(this, ev);
                return false;
            }
    }
}

var WpvTooltip = {
    timeOutId: 0,
    xmlRequest: new XmlRequest(),
    cache: new Array(),
    
    move: function (e) {
        var oTooltip;
        
        if ((oTooltip = document.getElementById("wpv-tooltip")) != null) {
            e = e || window.event;
            var oCoords = DraggableObj.mouseCoords(e);
            
            oTooltip.style.left = oCoords.x + 10 + "px";
            oTooltip.style.top = oCoords.y + 10 + "px";
        }
    },
    
    show: function(e, oText) {
        e = e || window.event;

        var oTooltip;
        var oCoords = DraggableObj.mouseCoords(e);
        var oTarget = WpvTooltip.getTarget(e);
        
        if ((oTooltip = document.getElementById("wpv-tooltip")) == null) {
            oTooltip = document.createElement("div")
            oTooltip.setAttribute("id", "wpv-tooltip")

            document.body.appendChild(oTooltip)
        }
        oTooltip.innerHTML = oText;
        WpvTooltip.move(e);
        
        if (oTooltip.style.display == "inline")
            oTooltip.style.display = "none";
        
        oTarget.onmouseout = WpvTooltip.hide;
        oTarget.onmousemove = WpvTooltip.move;
        
        WpvTooltip.timeOutId = setTimeout(
            function() {
                var oTooltip;
                if ((oTooltip = document.getElementById("wpv-tooltip")) != null) {
                    oTooltip.style.display = "inline";
                }
            }, 1000
        );
    },

    hide: function(e) {
        e = e || window.event;

        if (WpvTooltip.timeOutId > 0) {
            clearTimeout(WpvTooltip.timeOutId);
            WpvTooltip.timeOutId = 0;
        }
        WpvTooltip.getTarget(e).style.cursor = "default";

        setTimeout(
            function() {
                var oTooltip;
                if ((oTooltip = document.getElementById("wpv-tooltip")) != null) {
                    oTooltip.style.display = "none";
                }
            }, 500
        );
    },
    
    load: function(e, sUri, sId) {
        e = e || window.event;

        var oTooltip;
        var oCoords = DraggableObj.mouseCoords(e);
        var oTarget = WpvTooltip.getTarget(e);
        
        if ((oTooltip = document.getElementById("wpv-tooltip")) == null) {
            oTooltip = document.createElement("div")
            oTooltip.setAttribute("id", "wpv-tooltip")
            document.body.appendChild(oTooltip)
        }
        WpvTooltip.move(e);
        
        if (oTooltip.style.display == "inline")
            oTooltip.style.display = "none";
        
        oTarget.onmouseout = WpvTooltip.hide;
        oTarget.onmousemove = WpvTooltip.move;
        
        WpvTooltip.timeOutId = setTimeout(
            function() {
                var oTooltip;
                if ((oTooltip = document.getElementById("wpv-tooltip")) != null) {
                    var oParameters = new XmlRequestParameters();
                    
                    oParameters.add("wpv-tooltip", sId);
                    if (WpvTooltip.cache[oParameters.getParameters()] == null) {
                        WpvTooltip.xmlRequest.cancelRequest();
                        WpvTooltip.xmlRequest.requestUri = sUri;
                        WpvTooltip.xmlRequest.onRequestSent = WpvTooltip.doRequestSent;
                        WpvTooltip.xmlRequest.onSuccess = WpvTooltip.doRequestSuccess;
                        WpvTooltip.xmlRequest.onFailure = WpvTooltip.doRequestFailed;
                        WpvTooltip.xmlRequest.onTimeOut = WpvTooltip.doRequestFailed;
                        WpvTooltip.xmlRequest.onCanceled = WpvTooltip.doRequestFailed;
                        
                        WpvTooltip.xmlRequest.sendPostRequest(oParameters);
                    }
                    else {
                        oTooltip.innerHTML = WpvTooltip.cache[oParameters.getParameters()];
                    }
                    oTooltip.style.display = "inline";
                }
            }, 1000
        );
    },
    
    doRequestSent: function() {
        var oTooltip;
        if ((oTooltip = document.getElementById("wpv-tooltip")) != null) {
            oTooltip.innerHTML = "Loading...";
        }
    },
    
    doRequestSuccess: function(oRequest, iStatus, sStatusText) {
        var oTooltip;
        if ((oTooltip = document.getElementById("wpv-tooltip")) != null) {
            oTooltip.innerHTML = oRequest.getResponseText();
            WpvTooltip.cache[oRequest.parameters] = oRequest.getResponseText();
        }
    },
    
    doRequestFailed: function() {
        var oTooltip;
        if ((oTooltip = document.getElementById("wpv-tooltip")) != null) {
            oTooltip.innerHTML = "Failed to load the tooltip";
        }
    },
    
    getTarget: function(e) {
        var oTarget;

        if (e.target) 
            oTarget = e.target;
        else if (e.srcElement) 
            oTarget = e.srcElement;
            
        if (oTarget.nodeType == 3) // defeat Safari bug
            return oTarget.parentNode;
            
        return oTarget;
    }
}

var WpvPopup = {
    xmlRequest: new XmlRequest(),
    cache: new Array(),
    className: null,
    offsetLeft: 0,
    offsetTop: 0,
    canMove: true,

    getPopup: function() {
        var oPopup;
        
        if ((oPopup = document.getElementById("wpv-popup")) == null) {
            oPopup = document.createElement("div");
            oPopup.setAttribute("id", "wpv-popup");

            if (WpvPopup.className != null)
                oPopup.className = WpvPopup.className;

            document.body.appendChild(oPopup);
        }
        return oPopup;
    },
    
    updateText: function(sText) {
        var oPopup;
        
        if ((oPopup = document.getElementById("wpv-popup")) != null) {
            oPopup.innerHTML = sText;
            oPopup.style.display = "inline";
        }
    },
    
    show: function(sText) {
        var oPopup;
        
        oPopup = WpvPopup.getPopup();
        oPopup.innerHTML = sText;
        oPopup.style.left = WpvPopup.offsetLeft + "px";
        oPopup.style.top = WpvPopup.offsetTop + "px";
        oPopup.style.display = "inline";
        if (WpvPopup.canMove)
            DraggableObj.makeDraggable(oPopup);
    },

    hide: function() {
        var oPopup;

        if ((oPopup = document.getElementById("wpv-popup")) != null) {
            oPopup.style.display = "none";
        }
    },
    
    load: function(sUri, sId) {
        var oPopup;
        var oParameters = new XmlRequestParameters();
        
        if ((oPopup = document.getElementById("wpv-popup")) == null) {
            oPopup = document.createElement("div")
            oPopup.setAttribute("id", "wpv-popup")

            document.body.appendChild(oPopup)
        }
        
        oParameters.add("wpv-popup", sId);
        if (WpvPopup.cache[oParameters.getParameters()] == null) {
            WpvPopup.xmlRequest.cancelRequest();
            WpvPopup.xmlRequest.requestUri = sUri;
            WpvPopup.xmlRequest.onRequestSent = WpvPopup.doRequestSent;
            WpvPopup.xmlRequest.onSuccess = WpvPopup.doRequestSuccess;
            WpvPopup.xmlRequest.onFailure = WpvPopup.doRequestFailed;
            WpvPopup.xmlRequest.onTimeOut = WpvPopup.doRequestFailed;
            WpvPopup.xmlRequest.onCanceled = WpvPopup.doRequestFailed;
            
            WpvPopup.xmlRequest.sendPostRequest(oParameters);
        }
        else {
            oPopup.innerHTML = WpvTooltip.cache[oParameters.getParameters()];
        }
        oPopup.style.left = WpvPopup.offsetLeft + "px";
        oPopup.style.top = WpvPopup.offsetTop + "px";
        oPopup.style.display = "inline";
    },
    
    loadAdmin: function(sUri, sAction, oParameters) {
        var oPopup;
                
        if ((oPopup = document.getElementById("wpv-popup")) == null) {
            oPopup = document.createElement("div")
            oPopup.setAttribute("id", "wpv-popup")

            document.body.appendChild(oPopup)
        }
        
        if (oParameters == null)
            oParameters = new XmlRequestParameters();
        oParameters.add("action", sAction);
        oParameters.add("cookie", document.cookie);
        
        if (WpvPopup.cache[oParameters.getParameters()] == null) {
            WpvPopup.xmlRequest.cancelRequest();
            WpvPopup.xmlRequest.requestUri = sUri + "/wp-admin/admin-ajax.php";
            WpvPopup.xmlRequest.onRequestSent = WpvPopup.doRequestSent;
            WpvPopup.xmlRequest.onSuccess = WpvPopup.doRequestSuccess;
            WpvPopup.xmlRequest.onFailure = WpvPopup.doRequestFailed;
            WpvPopup.xmlRequest.onTimeOut = WpvPopup.doRequestFailed;
            WpvPopup.xmlRequest.onCanceled = WpvPopup.doRequestFailed;
            
            WpvPopup.xmlRequest.sendPostRequest(oParameters);
        }
        else {
            oPopup.innerHTML = WpvPopup.cache[oParameters.getParameters()];
        }
        oPopup.style.left = WpvPopup.offsetLeft + "px";
        oPopup.style.top = WpvPopup.offsetTop + "px";
        oPopup.style.display = "inline";
    },

    doRequestSent: function() {
        var oPopup;
        if ((oPopup = document.getElementById("wpv-popup")) != null) {
            oPopup.innerHTML = "<div style='padding: 10px 10px 10px 10px; border: 1px solid #000000;'>Loading...</div>";
        }
    },
    
    doRequestSuccess: function(oRequest, iStatus, sStatusText) {
        var oPopup;
        if ((oPopup = document.getElementById("wpv-popup")) != null) {
            if (WpvPopup.canMove)
                DraggableObj.makeDraggable(oPopup);
            oPopup.innerHTML = oRequest.getResponseText();
            WpvPopup.cache[oRequest.parameters] = oRequest.getResponseText();
        }
    },
    
    doRequestFailed: function() {
        var oPopup;
        if ((oPopup = document.getElementById("wpv-popup")) != null) {
            var sFailedMessage = "<div style='margin: 20px 20px 20px 20px; '>"; 
            sFailedMessage += "Failed to load data.";
            sFailedMessage += "<div style='margin: 20px 20px 20px 20px;'><a href='javascript:WpvPopup.hide()'>Close</a></div>";
            sFailedMessage += "</div>";
            oPopup.innerHTML = sFailedMessage;
        }
    }
}

var WindowSize = {

    // Adapted from Lightbox JS code by Lokesh Dhakar.
    // http://www.huddletogether.com/projects/lightbox/
    getValues: function() {  
        var xScroll, yScroll;
        var windowWidth, windowHeight;
        
        if (window.innerHeight && window.scrollMaxY) {
            xScroll = window.innerWidth + window.scrollMaxX;
            yScroll = window.innerHeight + window.scrollMaxY;
        } 
        else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac
            xScroll = document.body.scrollWidth;
            yScroll = document.body.scrollHeight;
        } 
        else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari
            xScroll = document.body.offsetWidth;
            yScroll = document.body.offsetHeight;
        }
        
        if (self.innerHeight) {  // all except Explorer
            if (document.documentElement.clientWidth) {
                windowWidth = document.documentElement.clientWidth; 
            } 
            else {
                windowWidth = self.innerWidth;
            }
            windowHeight = self.innerHeight;
        } 
        else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode
            windowWidth = document.documentElement.clientWidth;
            windowHeight = document.documentElement.clientHeight;
        } 
        else if (document.body) { // other Explorers
            windowWidth = document.body.clientWidth;
            windowHeight = document.body.clientHeight;
        }
        
        // for small pages with total height less then height of the viewport
        if (yScroll < windowHeight) {
            pageHeight = windowHeight;
        } 
        else { 
            pageHeight = yScroll;
        }

        // for small pages with total width less then width of the viewport
        if (xScroll < windowWidth) {
            pageWidth = xScroll;
        } 
        else {
            pageWidth = windowWidth;
        }

        return new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
    },
    
    getPageWidth: function() {
        return WindowSize.getValues()[0];
    },
    
    getPageHeight: function() {
        return WindowSize.getValues()[1];
    },
    
    getWindowWidth: function() {
        return WindowSize.getValues()[2];
    },
    
    getWindowHeight: function() {
        return WindowSize.getValues()[3];
    }
}

var ScrollXY = {
    getX: function() {
        if ( typeof( window.pageXOffset ) == 'number' ) {
            //Netscape compliant
            return window.pageXOffset;
        }
        else if ( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
            //DOM compliant
            return document.body.scrollLeft;
        }
        else if ( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
            //IE6 standards compliant mode
            return document.documentElement.scrollLeft;
        }
        else {
            return 0;
        }
    },
    
    getY: function() {
        if ( typeof( window.pageYOffset ) == 'number' ) {
            //Netscape compliant
            return window.pageYOffset;
        }
        else if ( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
            //DOM compliant
            return document.body.scrollTop;
        }
        else if ( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
            //IE6 standards compliant mode
            return document.documentElement.scrollTop;
        }
        else {
            return 0;
        }
    }
}

var WpvObjectOffset = {
    getOffsetLeft: function(obj, iLeft) {
        if (iLeft == null) {
            return WpvObjectOffset.getOffsetLeft(obj, obj.offsetLeft);
        }
        else if (obj.offsetParent != null) {
            return WpvObjectOffset.getOffsetLeft(obj.offsetParent, obj.offsetLeft + iLeft);
        }
        else {
            return iLeft;
        }
    },

    getOffsetTop: function(obj, iTop) {
        if (iTop == null) {
            return WpvObjectOffset.getOffsetTop(obj, obj.offsetTop);
        }
        else if (obj.offsetParent != null) {
            return WpvObjectOffset.getOffsetTop(obj.offsetParent, obj.offsetTop + iTop);
        }
        else {
            return iTop;
        }
    }
}

var WpvGrayOut = {
    scrollX: 0,
    scrollY: 0,
    
    show: function() {
        var oGrayArea;;

        if ((oGrayArea = document.getElementById("wpv-gray-out")) == null) {
            oGrayArea = document.createElement("div");
            oGrayArea.setAttribute("id", "wpv-gray-out");
            oGrayArea.style.position = "absolute";

            document.body.appendChild(oGrayArea);
        }

        if (oGrayArea != null) {
            var iWidth;
            var iHeight;
            var oDropdownArray = document.getElementsByTagName("SELECT");
            
            // Hack to deal with IE6 bug where drop down comes through overlaying div element.
            for (var i = 0; i < oDropdownArray.length; i++) {
                oDropdownArray[i].style.display = "none";
            }

            iWidth = WindowSize.getPageWidth();
            iHeight = WindowSize.getPageHeight();

            oGrayArea.style.width = iWidth + "px";
            oGrayArea.style.height = iHeight + "px";
            oGrayArea.style.left = ScrollXY.getX();
            oGrayArea.style.top = "0px";
            oGrayArea.style.display = "block"; 

            window.onmousewheel = document.onmousewheel = function() {return false;};
        }
    },

    hide: function() {
        var oGrayArea = document.getElementById("wpv-gray-out");
        var oDropdownArray = document.getElementsByTagName("SELECT");
        
        // Hack to deal with IE6 bug where drop down comes through overlaying div element.
        for (var i = 0; i < oDropdownArray.length; i++) {
            oDropdownArray[i].style.display = "inline";
        }
        oGrayArea.style.display = "none";
        window.onmousewheel = document.onmousewheel = null;
        window.onclick = null;
        window.onmousedown = null;
    },
    
    resize: function() {
        var oGrayArea = document.getElementById("wpv-gray-out");

        if (oGrayArea != null) {
            oGrayArea.style.width = WindowSize.getPageWidth() + "px";
            oGrayArea.style.height = WindowSize.getPageHeight() + "px";
        }
    },
    
    scroll: function() {
        var oGrayArea = document.getElementById("wpv-gray-out");

        if (oGrayArea != null) {
            oGrayArea.style.left = ScrollXY.getX() + "px";
        }
    }
}

var WpvDialog = {
    xmlRequest: new XmlRequest(),
    intervalId: 0,
    dialogId: "wpv-modal-dialog",
    
    openDialog: function(sUri, sMethod, oParameters, sDialogId) {      
        var oDialog;
        
        if (sDialogId != null)
            WpvDialog.dialogId = sDialogId;
            
        if ((oDialog = document.getElementById(WpvDialog.dialogId)) == null) {
            oDialog = document.createElement("div");
            oDialog.setAttribute("id", WpvDialog.dialogId);
            oDialog.style.position = "absolute";

            document.body.appendChild(oDialog);
        }

        oDialog.style.visibility = "hidden";
        
        window.onresize = WpvGrayOut.resize;
        window.onscroll = WpvGrayOut.scroll;

        this.xmlRequest.requestUri = sUri;
        this.xmlRequest.onRequestSent = this.doRequestSent.bind(this);
        this.xmlRequest.onSuccess = this.doRequestSuccess.bind(this);
        this.xmlRequest.onFailure = this.doRequestFailed.bind(this);
        this.xmlRequest.onTimeOut = this.doRequestFailed.bind(this);
        this.xmlRequest.onCanceled = this.doRequestFailed.bind(this);
        
        if (sMethod.toUpperCase() == "GET")
            this.xmlRequest.sendGetRequest(oParameters);
        else if (sMethod.toUpperCase() == "POST")
            this.xmlRequest.sendPostRequest(oParameters);
    },
    
    openAdminDialog: function(frm) {
        var oDialog;
        
        if ((oDialog = document.getElementById(WpvDialog.dialogId)) == null) {
            oDialog = document.createElement("div");
            oDialog.setAttribute("id", WpvDialog.dialogId);
            oDialog.style.position = "absolute";

            document.body.appendChild(oDialog);
        }

        oDialog.style.visibility = "hidden";

        window.onresize = WpvGrayOut.resize;
        window.onscroll = WpvGrayOut.scroll;

        frm.cookie.value = document.cookie;
        this.xmlRequest.requestUri = frm.requestUri.value; 
        this.xmlRequest.onRequestSent = this.doRequestSent.bind(this);
        this.xmlRequest.onSuccess = this.doRequestSuccess.bind(this);
        this.xmlRequest.onFailure = this.doRequestFailed.bind(this);
        this.xmlRequest.onTimeOut = this.doRequestFailed.bind(this);
        this.xmlRequest.onCanceled = this.doRequestFailed.bind(this);
        this.xmlRequest.sendPostRequestFromForm(frm);
    },
    
    doRequestSent: function(oRequest) {
        WpvGrayOut.show();
        WpvGrayOut.resize(WindowSize.getWindowWidth(), WindowSize.getWindowHeight());
        this.showMessage("Please Wait...");
    },
    
    doRequestSuccess: function(oRequest, iStatus, sStatusText) {
        var oDialog = document.getElementById(WpvDialog.dialogId);
        var oMessage;
        
        if ((oMessage = document.getElementById("wpv-modal-dialog-message")) != null)
            oMessage.style.visibility = "hidden";
            
        oDialog.innerHTML = oRequest.getResponseText();
        this.adjustLocation(oDialog);
        oDialog.style.visibility = "visible";
        DraggableObj.makeDraggable(oDialog);
        if (this.onLoad != null) {
            this.onLoad();
        }
    },
    
    doRequestFailed: function(oRequest, iStatus, sStatusText) {
        var oDialog = document.getElementById(WpvDialog.dialogId);
        var oMessage, sFailedMessage;
        
        if ((oMessage = document.getElementById("wpv-modal-dialog-message")) != null)
            oMessage.style.visibility = "hidden";

        sFailedMessage = "<div style='margin: 20px 20px 20px 20px; text-align: center;'>"; 
        sFailedMessage += iStatus + ": " + sStatusText; 
        sFailedMessage += "</div>";
        sFailedMessage += "<div style='margin: 20px 20px 20px 20px'><a href='javascript:WpvDialog.closeDialog()'>Click to close</a></div>";
        
        oDialog.innerHTML = sFailedMessage;
        this.adjustLocation(oDialog);
        oDialog.style.visibility = "visible";
    },
    
    adjustLocation: function(obj) {
        if (obj != null) {
            var iWidth = WindowSize.getWindowWidth();
            var iHeight = WindowSize.getWindowHeight();
            var iObjWidth = obj.offsetWidth;
            var iObjHeight = obj.offsetHeight;
            var iObjLeft = (iWidth - iObjWidth) / 2  + ScrollXY.getX();
            var iObjTop;

            WpvGrayOut.resize();
            iObjTop = 10 + ScrollXY.getY();
            obj.style.top = iObjTop + "px";

            if (iObjLeft + iObjWidth > iWidth - 10)
                obj.style.left = iWidth - iObjWidth + "px";
            else
                obj.style.left = (iObjLeft < 0 ? 0 : iObjLeft) + "px";
        }
    },

    closeDialog: function() {
        var oDialog = document.getElementById(WpvDialog.dialogId);

        if (WpvDialog.intervalId > 0)
            clearInterval(WpvDialog.intervalId);
        WpvGrayOut.hide();
        oDialog.style.visibility = "hidden";
        oDialog.innerHTML = "";
        window.onresize = null;
        window.scroll = null;
        document.onmousemove = null;
        document.onmouseup = null;
    },
    
    showMessage: function(sMessage) {
        var oMessage;
        
        if ((oMessage = document.getElementById("wpv-modal-dialog-message")) == null) {
            oMessage = document.createElement("div");
            oMessage.setAttribute("id", "wpv-modal-dialog-message");
            oMessage.style.position = "absolute";
            oMessage.style.zIndex = 1002;
            oMessage.style.visibility = "hidden";
            oMessage.style.backgroundColor = "#ffffff";
            oMessage.style.borderColor = "#000000";
            oMessage.style.padding = "10px 20px 10px 20px";

            document.body.appendChild(oMessage);
        }
        oMessage.innerHTML = sMessage;
        this.adjustLocation(oMessage);
        oMessage.style.visibility = "visible"
    }
}
