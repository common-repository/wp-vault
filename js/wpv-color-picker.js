var WpvColorPicker = {
    showColor: function(sColor) {
        document.getElementById('color-text').innerHTML = sColor;
        document.getElementById('color-display').style.backgroundColor = sColor;
    },
    
    pickColor: function(sColor, sTargetId) {
        if (document.getElementById(sTargetId + "-text") != null) {
            document.getElementById(sTargetId + "-text").value = sColor;
        }
        if (document.getElementById(sTargetId) != null) {
            document.getElementById(sTargetId).style.backgroundColor = sColor;
        }
        WpvColorPicker.hidePicker();
    },
    
    showPicker: function(e, sUri, sTargetId) {
        var oParameters = new XmlRequestParameters();
        var oCoords = DraggableObj.mouseCoords(e || window.event);
        
        oParameters.add("target_id", sTargetId);
        WpvPopup.offsetLeft = oCoords.x;
        WpvPopup.offsetTop = oCoords.y;
        WpvPopup.loadAdmin(sUri, 'wpv_color_picker', oParameters);
    },
    
    hidePicker: function() {
        WpvPopup.hide();
    }
}