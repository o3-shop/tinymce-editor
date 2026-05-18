// Optional `url_converter` callback for TinyMCE. Not wired into the
// editor's init config today, but kept here so projects that DO opt in
// (or that override Configuration to register it) get the same
// root-relative URL strategy as the default `relative_urls: false` +
// `remove_script_host: true` flow.
//
// Smarty placeholders that look like `[{...}]` are passed through
// untouched so editor content with shop-side template fragments isn't
// mangled.
//
// Refs: o3-shop/o3-shop#151.
function urlconverter(url, node, on_save) {
    if (url.indexOf("[{") === 0) return url;
    var absolute = tinyMCE.activeEditor.documentBaseURI.toAbsolute(url);
    var a = document.createElement('a');
    a.href = absolute;
    return a.pathname + (a.search || '') + (a.hash || '');
}
