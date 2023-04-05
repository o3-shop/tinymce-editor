function urlconverter(url, node, on_save) {
    console.log(tinyMCE.activeEditor);
    if(url.indexOf("[{") === 0) return url;
    return (tinyMCE.activeEditor.settings.relative_urls) ?
        tinyMCE.activeEditor.documentBaseURI.toRelative(url) :
        tinyMCE.activeEditor.documentBaseURI.toAbsolute(url);
}