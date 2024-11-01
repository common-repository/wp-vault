function submitData(sSubmitType) {
    var frm = document.getElementById("display-option-form");

    frm.proc.value = "display-option-" + sSubmitType;
    frm.submit();
}

