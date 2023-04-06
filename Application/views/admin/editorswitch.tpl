<li style="margin-left: 50px;">
    <button style="border: 1px solid #0089EE; color: #0089EE;padding: 3px 10px; margin-top: -10px; background: white;"
            onclick="tinymce.get().forEach(function(editor) { if(editor.isHidden()) { editor.show(); } else { editor.hide(); }});">
        <span>
            [{oxmultilang ident="TINYMCE_TOGGLE"}]
        </span>
    </button>
</li>