function submitUnlink(frm) {
    frm.proc.value = "file-link-unlink";
    frm.submit();
}

function submitSave(frm) {
    frm.proc.value = "file-link-update";
    frm.submit();

    WpvDialog.showMessage("Saving...");
}

function submitSaveSequence(frm) {
    frm.proc.value = "file-link-sequence";
    frm.submit();

    WpvDialog.showMessage("Saving...");
}

function openPost2FileEditDialog() {
    var frm = document.getElementById("post2file-form");
    
    frm.action.value = "wpv_post2file_edit";
    WpvDialog.openAdminDialog(frm);
}

function openPost2FileSequenceDialog() {
    var frm = document.getElementById("post2file-form");

    frm.action.value = "wpv_post2file_sequence";
    WpvDialog.openAdminDialog(frm);
}

