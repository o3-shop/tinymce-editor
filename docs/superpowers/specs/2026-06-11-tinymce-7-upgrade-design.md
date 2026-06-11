# Design — Upgrade bundled TinyMCE 6.4.1 → 7.9.3 (CVE fix)

- **Issue:** o3-shop/o3-shop#194
- **Date:** 2026-06-11
- **Repo:** `o3-shop/tinymce-editor` (the module that vendors TinyMCE)
- **Status:** Approved design, ready for implementation plan

## Problem

The module vendors TinyMCE **6.4.1** in `out/tinymce/`. A container scan flagged three
HIGH-severity stored/reflected XSS CVEs in the admin WYSIWYG editor:

| CVE | Vector | Fixed in |
|---|---|---|
| CVE-2026-47759 | XSS via `data-mce-`-prefixed `src`/`href`/`style` attributes | 7.9.3, 8.5.1 |
| CVE-2026-47761 | XSS via media plugin `data-mce-object` injection | 7.9.3, 8.5.1 |
| CVE-2026-47762 | XSS via `mce:protected` comments | 7.9.3, 8.5.1 |

There is no fixed 6.x release. The patched line is 7.9.3 / 8.5.1, i.e. a **major-version
upgrade** of the vendored editor.

## Decision

Upgrade to **TinyMCE 7.9.3** (latest 7.x; confirmed on npm, license `GPL-2.0-or-later`).
7.9.3 fixes all three CVEs with the smallest breaking-change surface relative to 8.5.1
(which additionally enables `sandbox_iframes` / `convert_unsafe_embeds` by default —
affecting the `media` plugin — and changes the license-key format). The CVE remediation
does not justify the extra QA surface of an 8.x jump.

## Goal & non-goals

**Goal:** Replace the vendored editor with 7.9.3 so the CVEs are gone, while the editor's
behaviour in the O3-Shop admin (CMS pages, product long descriptions, newsletter, file
manager, fullscreen toggle, localisation, root-relative image URLs per #151) is
unchanged.

**Non-goals (separate follow-ups):**
- Bumping the product reference in `shop-metapackage-ce` / `o3-shop` and re-scanning the
  trowow image — done after this module release is tagged (issue #194 tasks 4 & 5).
- Modernising the roxy file manager UI / its bundled jquery-ui 1.10.4.
- Adding an automated test suite to the module (none exists today).

## Current architecture (as-is)

- **Vendored editor:** `out/tinymce/` — committed to git, not fetched at install time.
  Confirmed 6.4.1 via `out/tinymce/package.json`.
- **Custom JS plugins:** `build/plugins/{oxfullscreen,roxy}/plugin.js`, copied verbatim
  (no minify/transform) to `out/plugins/.../plugin.js`.
- **Runtime scripts:** `out/scripts/{init.js,copyLongDesc.js,urlConverter.js}`;
  `init.js` holds `tinymce.init({...})` with the PHP-injected config string.
- **PHP wrapper** builds the init config:
  - `Application/Core/TinyMCE/Loader.php` — loads URLs, injects config.
  - `Application/Core/TinyMCE/Configuration.php` — `build()` assembles options by calling
    `addOption(oxNew(XOption::class, $this->loader))` in grouped methods.
  - `Options/*` — one class per init option (`getKey()`, `get()`, `isQuoted()`,
    `requireRegistration()`).
  - `PluginList.php` — enumerates active plugins; `Plugins/*` — one class per plugin.
  - `Toolbar/*`, `ToolbarList.php` — toolbar buttons.
- **Build pipeline:** `build/3_build.js` copies dirs into `_module/`; `build/4_publish.js`
  git-commits. `build/update.sh` is **dead** (a TinyMCE-4-era tiny.cloud download URL) —
  the 6.4.1 bundle was placed manually.
- **No tests** in the module repo.

## Breaking changes 6.4.1 → 7.9.3 that affect this module

1. **`license_key` required.** Self-hosted GPL builds need `license_key: 'gpl'` in init or
   the editor shows a license warning / degrades. New option needed.
2. **`editor.settings` removed.** The `roxy` plugin reads/writes `editor.settings.*` —
   must port to the `editor.options` API.
3. **`fullpage` + `legacyoutput` plugins** were removed back in TinyMCE 6.0; their PHP
   classes are phantom references (no JS in the bundle, 404 for `newsletter_main`).
   Remove them.
4. **`lists` plugin required** for list commands in 7+ — already active in `PluginList`. OK.

## Changes (the work)

### C1 — Swap the vendored bundle
- Obtain 7.9.3 via `npm install tinymce@7.9.3` in a scratch dir.
- Replace `out/tinymce/` contents with the package tree: `tinymce.min.js`, `tinymce.js`,
  `plugins/`, `skins/`, `themes/`, `models/`, `icons/`, `langs/`, `license.txt`,
  `package.json`.
- **Preserve `langs/`** — the npm package does not ship language packs; keep the existing
  language files (forward-compatible with 7.x).
- Verify post-swap: `out/tinymce/package.json` shows `7.9.3`; `out/tinymce/plugins/`
  contains every plugin named in `PluginList` (minus the two being removed); the `oxide`
  skin still exists under `skins/ui/oxide`.

### C2 — Replace the dead build/update.sh
- Rewrite `build/update.sh` to fetch a pinned TinyMCE version from npm and stage it into
  `out/tinymce/` reproducibly (version as an argument/variable). This documents the bump
  procedure for the next CVE.

### C3 — New `license_key` option
- Add `Application/Core/TinyMCE/Options/LicenseKey.php` implementing `OptionInterface`:
  `getKey() => 'license_key'`, `get() => 'gpl'`, `isQuoted() => true`,
  `requireRegistration() => true`.
- Register it in `Configuration::addIntegrateOptions()`.

### C4 — Port the `roxy` plugin
- Edit `build/plugins/roxy/plugin.js` **and** `out/plugins/roxy/plugin.js` (kept identical;
  build only copies):
  - Register custom options inside the plugin:
    `editor.options.register('filemanager_url', {processor: 'string'})` and
    `editor.options.register('filemanager_access_key', {processor: 'string', default: ''})`.
  - Set the picker via `editor.options.set('file_picker_callback', fn)`.
  - Inside the callback, read values with `editor.options.get('filemanager_url')`,
    `editor.options.get('filemanager_access_key')`, `editor.options.get('language')`.
  - Keep URL building, `editor.windowManager.openUrl({...})`, and the `onMessage` →
    `callback(details.content)` flow byte-for-byte equivalent.
- The PHP side already injects `filemanager_url` (`Options/FilemanagerUrl.php`) into the
  init config; registering the option names lets `editor.options.get` resolve them.

### C5 — Remove dead plugins
- Delete `Application/Core/TinyMCE/Plugins/FullPage.php` and `Plugins/Legacyoutput.php`.
- Remove their `use` imports and `PluginList::get()` entries (`'fullpage'`,
  `'legacyoutput'`).
- No replacement; these were already non-functional.

### C6 — Audit `oxfullscreen`
- `build/plugins/oxfullscreen/plugin.js` uses `PluginManager.add` +
  `editor.ui.registry.addToggleButton('fullscreen', …)` — stable in 7.x. Expected: no
  code change. Verify the admin frameset cols/rows toggle still fires after the swap.

### C7 — Metadata / license / docs
- `metadata.php`: `version` `1.1.0` → **`2.0.0`**; `description` "TinyMCE 6 integration…"
  → "TinyMCE 7 integration…".
- `composer.json`: `description` "TinyMCE 6 Integration…" → "TinyMCE 7 Integration…".
  Keep module `license: GPL-3.0` (TinyMCE's GPL-2.0-or-later is compatible — GPL-2.0+ can
  be combined into a GPL-3.0 work).
- `CHANGELOG.md`: new `[v2.0.0] - 2026-06-11` entry: upgraded TinyMCE 6.4.1 → 7.9.3, fixes
  CVE-2026-47759/47761/47762; removed dead fullpage/legacyoutput; added `license_key: 'gpl'`;
  ported roxy to the options API; note the editor license is GPL-2.0-or-later.
- `README.md`: bump TinyMCE version references 6 → 7.

## License review (explicit confirmation)

- Module license: **GPL-3.0** (`composer.json`, file headers).
- TinyMCE 7.9.3 license: **GPL-2.0-or-later** (verified on npm).
- "or-later" means the editor may be used under GPL-3.0, so combining it into this
  GPL-3.0 module is compatible. No license-key purchase is required for self-hosted GPL
  use; `license_key: 'gpl'` declares that mode. Recorded in the changelog.

## Verification — manual QA checklist (no automated suite)

Build the bundle, install + activate the module in a running O3-Shop, open the admin, and
confirm:

1. **No regressions in console:** no TinyMCE license warning, no 404 for any plugin JS
   (especially confirms fullpage/legacyoutput removal is clean).
2. **CMS content page** (`content_main`): editor loads, edit + save round-trips HTML.
3. **Product long description** (`article_main`): insert an image via the file manager;
   saved markup uses a **root-relative** path (`/out/pictures/...`) — regression guard for
   #151.
4. **File manager (roxy):** the picker dialog opens via `openUrl`, a pick returns into the
   field (validates the `editor.options` port).
5. **Newsletter** (`newsletter_main`): editor loads with no errors now that fullpage /
   legacyoutput are gone.
6. **Fullscreen toggle (oxfullscreen):** toolbar button toggles the admin frameset.
7. **Localisation:** admin language is reflected in the editor UI.
8. **CVE smoke:** confirm version banner / `tinymce.majorVersion` is `7`.

After the module release is tagged, re-scan the trowow image to confirm the CVEs are gone
(tracked separately, issue #194 tasks 4–5).

## Risks

- The 7.9.3 `plugins/` set may differ in folder names from 6.4.1 for an edge plugin; C1's
  post-swap verification catches any plugin named in `PluginList` that lacks JS.
- `editor.options.set('file_picker_callback', …)` timing — register/set at plugin
  init, before any picker invocation; validated by QA step 4.
- `langs/` forward-compatibility — if a language pack errors on 7.x, fall back to the
  upstream 7.9.3 language pack for that locale.
