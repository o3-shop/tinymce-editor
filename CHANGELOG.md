# Changelog for O3-Shop TinyMCE Editor plugin

All notable changes to this project will be documented in this file.
The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## unreleased

## [v1.1.0] - 2026-05-17

### Fixed
- Editor-inserted images saved with bare relative paths (`out/pictures/...`)
  now use root-relative paths (`/out/pictures/...`), so they render on
  storefront URLs at any depth instead of returning 404 on sub-paths.
  See o3-shop/o3-shop#151.

### Added
- New `RemoveScriptHost` option, registered in `addUrlHandling()` so
  the absolute URLs produced by `relative_urls: false` are reduced to
  root-relative on save.

### Changed
- `RelativeUrls` now returns `'false'`; combined with `RemoveScriptHost`
  this produces root-relative URLs in the persisted HTML.
- `out/scripts/urlConverter.js` — dropped a stray `console.log` and
  rewrote the (currently unwired) converter to always produce
  root-relative paths, consistent with the new default flow.

## [v1.0.0] - 2023-04-11

### Added
- Refactor plugin
- update TinyMCE to 6.4.1

[v1.0.0]: https://gitlab.o3-shop.com/o3/tinymce-editor/releases/tag/v1.0.0