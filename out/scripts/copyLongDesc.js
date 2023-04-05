function copyLongDescFromTinyMCE(sIdent) {
    var editor = tinymce.get("editor_"+sIdent);
    var content = (editor && !editor.isHidden()) ? editor.getContent() : document.getElementById("editor_"+sIdent).value;
    document.getElementsByName("editval[" + sIdent + "]").item(0).value = content.replace(/\[{([^\]]*?)}\]/g, function(m) { return m.replace(/&gt;/g, ">").replace(/&lt;/g, "<").replace(/&amp;/g, "&") });
    return true;
}

var origCopyLongDesc = copyLongDesc;
copyLongDesc = function(sIdent) {
    if ( copyLongDescFromTinyMCE( sIdent ) ) return;
    console.log("tinymce disabled, copy content from regular textarea");
    origCopyLongDesc( sIdent );
}