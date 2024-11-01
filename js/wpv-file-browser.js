function assignTags() {
    var frm = document.getElementById("file-browser-form");
    
    frm.proc.value = "browse-tag-assign";
    frm.submit();

    WpvDialog.showMessage("Saving...");
}

function unassignTags() {
    var frm = document.getElementById("file-browser-form");
    
    frm.proc.value = "browse-tag-unassign";
    frm.submit();

    WpvDialog.showMessage("Saving...");
}

function submitDelete() {
    if (confirm("Are you sure you want to permanently delete the selected file(s)?")) {
        var frm = document.getElementById("file-browser-form");
    
        frm.proc.value = "browse-file-delete";
        frm.submit();
    }
}

function submitEdit() {
    var frm = document.getElementById("file-browser-form");
    
    frm.proc.value = "browse-file-edit";
    frm.submit();

    WpvDialog.showMessage("Saving...");
}

function openDialog(sAction) {
    var frm = document.getElementById("file-browser-form");

    frm.action.value = sAction;
    WpvDialog.openAdminDialog(frm);
}

function closeDialog() {
    WpvDialog.closeDialog();
}
