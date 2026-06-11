# TinyMCE 6.4.1 → 7.9.3 Upgrade Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace the vendored TinyMCE 6.4.1 with 7.9.3 in the `o3-shop/tinymce-editor` module, fixing CVE-2026-47759/47761/47762, while keeping the admin editor's behaviour unchanged.

**Architecture:** The module vendors the editor under `out/tinymce/` (committed to git) and builds its `tinymce.init({...})` config from PHP option/plugin classes. The upgrade swaps the bundle, adds the now-required `license_key: 'gpl'` option, ports the `roxy` file-picker plugin off the removed `editor.settings` API onto `editor.options`, and deletes the already-dead `fullpage`/`legacyoutput` plugin classes.

**Tech Stack:** PHP 7.4+ (O3-Shop module), vanilla JS TinyMCE plugins, npm (for fetching the bundle), Smarty templates.

**Working repo/branch:** `/Users/nick/o3/tinymce-editor` on branch `194-tinymce-7-upgrade` (already created; the design doc is already committed there).

**Spec:** `docs/superpowers/specs/2026-06-11-tinymce-7-upgrade-design.md`

---

## Testing reality (read first)

This module has **no automated test suite** (no PHPUnit/Jest/Playwright). The realistic
verification toolchain per task is:

- **PHP syntax:** `php -l <file>` (always available).
- **JS syntax:** `node --check <file>`.
- **Structural assertions:** `grep` checks that a symbol is gone / present.
- **Static analysis (optional, needs shop context):** `phpstan.neon` and `.php-cs-fixer.php`
  exist but require the module to live inside a shop checkout at
  `source/modules/o3-shop/tinymce-editor` with vendor autoload. Run if that environment is
  available; otherwise rely on `php -l`.
- **Integration gate:** the manual admin QA checklist in **Task 9** — this is the real
  "does it work" proof and must be run before the task is considered done.

Commit after each task. Do not push (await instruction).

---

## File structure

| File | Responsibility | Action |
|---|---|---|
| `build/update.sh` | Reproducible fetch of a pinned TinyMCE from npm into `out/tinymce/` | Rewrite |
| `out/tinymce/**` | Vendored editor bundle | Replace (6.4.1 → 7.9.3) |
| `Application/Core/TinyMCE/Options/LicenseKey.php` | Emit `license_key: 'gpl'` | Create |
| `Application/Core/TinyMCE/Configuration.php` | Option assembly | Modify (register LicenseKey) |
| `Application/Core/TinyMCE/PluginList.php` | Active plugin list | Modify (drop 2 entries) |
| `Application/Core/TinyMCE/Plugins/FullPage.php` | Dead plugin | Delete |
| `Application/Core/TinyMCE/Plugins/Legacyoutput.php` | Dead plugin | Delete |
| `build/plugins/roxy/plugin.js` | File-picker plugin (source) | Rewrite to `editor.options` |
| `out/plugins/roxy/plugin.js` | File-picker plugin (runtime copy) | Rewrite (identical) |
| `metadata.php` | Module metadata | Modify (version 2.0.0, description) |
| `composer.json` | Package metadata | Modify (description) |
| `CHANGELOG.md` | Changelog | Modify (v2.0.0 entry) |
| `README.md` | Docs | Modify (version refs) |
| `.tinymce-upgrade-notes.md` | Scratch research notes | Delete at end |

---

## Task 1: Rewrite `build/update.sh` as a reproducible npm fetch

**Files:**
- Modify: `build/update.sh` (currently a dead TinyMCE-4 cloud URL)

- [ ] **Step 1: Replace the file contents**

Replace the entire contents of `build/update.sh` with:

```bash
#!/usr/bin/env bash
#
# Fetch a pinned TinyMCE release from npm and stage it into out/tinymce/.
# The bundle is committed to the repo; re-run this script to bump versions.
#
# Usage: ./build/update.sh [version]   (default below)
#
set -euo pipefail

VERSION="${1:-7.9.3}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
MODULE_DIR="$(dirname "$SCRIPT_DIR")"
OUT="$MODULE_DIR/out/tinymce"
TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT

echo "Downloading tinymce@$VERSION ..."
( cd "$TMP" && npm pack "tinymce@$VERSION" >/dev/null )
tar -xzf "$TMP"/tinymce-*.tgz -C "$TMP"
SRC="$TMP/package"

# Replace bundle assets. Preserve the existing langs/ packs — the npm package
# does not ship language files, and ours are forward-compatible with 7.x.
for item in tinymce.js tinymce.min.js tinymce.d.ts package.json bower.json \
            composer.json CHANGELOG.md README.md license.txt \
            plugins skins themes models icons; do
    rm -rf "${OUT:?}/$item"
    if [ -e "$SRC/$item" ]; then
        cp -R "$SRC/$item" "$OUT/$item"
    fi
done

echo "TinyMCE staged into $OUT"
grep -o '"version"[^,]*' "$OUT/package.json" | head -1
```

- [ ] **Step 2: Make it executable and syntax-check**

Run:
```bash
cd /Users/nick/o3/tinymce-editor
chmod +x build/update.sh
bash -n build/update.sh && echo "syntax OK"
```
Expected: `syntax OK`

- [ ] **Step 3: Commit**

```bash
cd /Users/nick/o3/tinymce-editor
git add build/update.sh
git commit -m "build(#194): make update.sh fetch a pinned TinyMCE from npm

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Task 2: Swap the vendored bundle to 7.9.3

**Files:**
- Replace: `out/tinymce/**` (keep `out/tinymce/langs/`)

- [ ] **Step 1: Run the fetch script**

Run:
```bash
cd /Users/nick/o3/tinymce-editor
./build/update.sh 7.9.3
```
Expected: ends with `"version": "7.9.3"`.

- [ ] **Step 2: Verify the version landed**

Run:
```bash
cd /Users/nick/o3/tinymce-editor
grep -m1 '"version"' out/tinymce/package.json
node -e "process.stdout.write(require('./out/tinymce/package.json').version)"; echo
```
Expected: `7.9.3` both times.

- [ ] **Step 3: Verify every active plugin still has JS (minus the two being removed)**

Run:
```bash
cd /Users/nick/o3/tinymce-editor
for p in anchor autolink autoresize charmap code image link lists media \
         nonbreaking pagebreak preview quickbars searchreplace table \
         visualblocks wordcount fullscreen; do
    test -f "out/tinymce/plugins/$p/plugin.min.js" \
      || test -f "out/tinymce/plugins/$p/plugin.js" \
      && echo "OK   $p" || echo "MISS $p"
done
test -d out/tinymce/skins/ui/oxide && echo "OK   skin oxide" || echo "MISS skin oxide"
test -d out/tinymce/langs && echo "OK   langs preserved" || echo "MISS langs"
```
Expected: every line `OK`. If any plugin is `MISS`, it was renamed/removed in 7.x —
stop and reconcile with `PluginList` before continuing (none of the above is expected to
be missing in 7.9.3).

- [ ] **Step 4: Commit (large vendored diff)**

```bash
cd /Users/nick/o3/tinymce-editor
git add out/tinymce
git commit -m "fix(#194): upgrade vendored TinyMCE 6.4.1 -> 7.9.3

Fixes CVE-2026-47759, CVE-2026-47761, CVE-2026-47762 (HIGH XSS).

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Task 3: Add the `license_key: 'gpl'` option

TinyMCE 7 requires a license key for self-hosted use; `'gpl'` declares GPL mode and
silences the license warning.

**Files:**
- Create: `Application/Core/TinyMCE/Options/LicenseKey.php`
- Modify: `Application/Core/TinyMCE/Configuration.php`

- [ ] **Step 1: Create the option class**

Create `Application/Core/TinyMCE/Options/LicenseKey.php` (mirrors the `Promotion`/`Skin`
pattern — note `isQuoted() => true` so it renders as `license_key: 'gpl'`):

```php
<?php

/**
 * This file is part of O3-Shop TinyMCE editor module.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with O3-Shop.  If not, see <http://www.gnu.org/licenses/>
 *
 * @copyright  Copyright (c) 2022 Marat Bedoev, bestlife AG
 * @copyright  Copyright (c) 2023 O3-Shop (https://www.o3-shop.com)
 * @license    https://www.gnu.org/licenses/gpl-3.0  GNU General Public License 3 (GPLv3)
 */

declare(strict_types=1);

namespace O3\TinyMCE\Application\Core\TinyMCE\Options;

class LicenseKey extends AbstractOption
{
    protected string $key = 'license_key';

    public function get(): string
    {
        return 'gpl';
    }

    public function isQuoted(): bool
    {
        return true;
    }
}
```

- [ ] **Step 2: Register it in the integrate-options group**

In `Application/Core/TinyMCE/Configuration.php`, add the import alongside the other
`Options\*` uses:

```php
use O3\TinyMCE\Application\Core\TinyMCE\Options\LicenseKey;
```

and add the registration line inside `addIntegrateOptions()` (first line of the body):

```php
    protected function addIntegrateOptions(): void
    {
        $this->addOption(oxNew(LicenseKey::class, $this->loader));
        $this->addOption(oxNew(Setup::class, $this->loader));
        $this->addOption(oxNew(BaseUrl::class, $this->loader));
        $this->addOption(oxNew(CacheSuffix::class, $this->loader));
        $this->addOption(oxNew(Selector::class, $this->loader));
        $this->addOption(oxNew(InitInstanceCallback::class, $this->loader));
    }
```

- [ ] **Step 3: Syntax-check both files**

Run:
```bash
cd /Users/nick/o3/tinymce-editor
php -l Application/Core/TinyMCE/Options/LicenseKey.php
php -l Application/Core/TinyMCE/Configuration.php
```
Expected: `No syntax errors detected` for both.

- [ ] **Step 4: Commit**

```bash
cd /Users/nick/o3/tinymce-editor
git add Application/Core/TinyMCE/Options/LicenseKey.php Application/Core/TinyMCE/Configuration.php
git commit -m "feat(#194): add license_key 'gpl' option required by TinyMCE 7

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Task 4: Remove the dead `fullpage` and `legacyoutput` plugins

Both were removed in TinyMCE 6.0; no JS exists in the bundle. Their PHP classes only ever
added phantom plugin names for `newsletter_main`, producing 404s.

**Files:**
- Delete: `Application/Core/TinyMCE/Plugins/FullPage.php`
- Delete: `Application/Core/TinyMCE/Plugins/Legacyoutput.php`
- Modify: `Application/Core/TinyMCE/PluginList.php`

- [ ] **Step 1: Delete the two plugin classes**

Run:
```bash
cd /Users/nick/o3/tinymce-editor
git rm Application/Core/TinyMCE/Plugins/FullPage.php Application/Core/TinyMCE/Plugins/Legacyoutput.php
```

- [ ] **Step 2: Remove them from `PluginList.php`**

In `Application/Core/TinyMCE/PluginList.php`, delete these two `use` imports:

```php
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\FullPage;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Legacyoutput;
```

and delete these two lines from the array returned by `get()`:

```php
            'fullpage'      => oxNew(FullPage::class),
            'legacyoutput'  => oxNew(Legacyoutput::class),
```

- [ ] **Step 3: Verify no remaining references anywhere**

Run:
```bash
cd /Users/nick/o3/tinymce-editor
grep -rn -i 'fullpage\|legacyoutput' Application/ && echo "FOUND refs (fix them)" || echo "clean"
php -l Application/Core/TinyMCE/PluginList.php
```
Expected: `clean`, then `No syntax errors detected`.

- [ ] **Step 4: Commit**

```bash
cd /Users/nick/o3/tinymce-editor
git add -A Application/Core/TinyMCE
git commit -m "fix(#194): remove dead fullpage/legacyoutput plugins (gone since TinyMCE 6)

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Task 5: Port the `roxy` plugin off the removed `editor.settings` API

`editor.settings` was removed in TinyMCE 7. The plugin must register its custom options and
read them via `editor.options`. The two copies (`build/` source and `out/` runtime) are kept
byte-identical — the build only copies `build/plugins/` → `out/plugins/`.

**Files:**
- Rewrite: `build/plugins/roxy/plugin.js`
- Rewrite: `out/plugins/roxy/plugin.js`

- [ ] **Step 1: Write the new plugin body**

Set the contents of **both** `build/plugins/roxy/plugin.js` and
`out/plugins/roxy/plugin.js` to exactly this (keep the existing GPL header comment block at
the top of each file unchanged; replace only the IIFE below it):

```js
(function () {
    'use strict';
    const PluginManager = tinymce.util.Tools.resolve('tinymce.PluginManager');

    PluginManager.add('roxy', function (editor) {
        // These options are injected into the init config by the module's PHP
        // (Options/FilemanagerUrl.php). In TinyMCE 7 custom options must be
        // registered before they can be read with editor.options.get().
        editor.options.register('filemanager_url', { processor: 'string', default: '' });
        editor.options.register('filemanager_access_key', { processor: 'string', default: '' });

        editor.options.set('file_picker_callback', function (callback, value, meta) {
            let url = editor.options.get('filemanager_url')
                + '&type=' + meta.filetype
                + '&value=' + value
                + '&selected=' + value;

            const language = editor.options.get('language');
            if (language) {
                url += '&langCode=' + language;
            }

            const accessKey = editor.options.get('filemanager_access_key');
            if (accessKey) {
                url += '&akey=' + accessKey;
            }

            const instanceApi = editor.windowManager.openUrl({
                title: 'Filemanager',
                url: url,
                width: window.innerWidth,
                height: window.innerHeight - 40,
                onMessage: function (dialogApi, details) {
                    callback(details.content);
                    instanceApi.close();
                }
            });
        });
    });
}());
```

- [ ] **Step 2: Confirm both copies are identical and syntactically valid**

Run:
```bash
cd /Users/nick/o3/tinymce-editor
node --check build/plugins/roxy/plugin.js && echo "build OK"
node --check out/plugins/roxy/plugin.js && echo "out OK"
diff build/plugins/roxy/plugin.js out/plugins/roxy/plugin.js && echo "identical"
grep -c 'editor.settings' build/plugins/roxy/plugin.js out/plugins/roxy/plugin.js
```
Expected: `build OK`, `out OK`, `identical`, and the grep prints `:0` for both files (no
`editor.settings` left).

- [ ] **Step 3: Commit**

```bash
cd /Users/nick/o3/tinymce-editor
git add build/plugins/roxy/plugin.js out/plugins/roxy/plugin.js
git commit -m "fix(#194): port roxy file-picker to editor.options API (TinyMCE 7)

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

> **Risk / fallback (note for the implementer):** if the editor console logs
> "file_picker_callback is not a registered option" when opening the picker, register it
> before the `set` call: `editor.options.register('file_picker_callback', { processor: 'function' });`
> Verified by Task 9 step 4.

---

## Task 6: Audit `oxfullscreen` (expected: no change)

`build/plugins/oxfullscreen/plugin.js` uses `PluginManager.add` and
`editor.ui.registry.addToggleButton` — both stable in TinyMCE 7. No edit is expected; this
task confirms that.

**Files:**
- Inspect: `build/plugins/oxfullscreen/plugin.js`, `out/plugins/oxfullscreen/plugin.js`

- [ ] **Step 1: Confirm it uses only stable APIs and has no `editor.settings`**

Run:
```bash
cd /Users/nick/o3/tinymce-editor
grep -n 'editor.settings\|editor\.options\|ui\.registry\|PluginManager' build/plugins/oxfullscreen/plugin.js
node --check build/plugins/oxfullscreen/plugin.js && echo "syntax OK"
```
Expected: references to `ui.registry` / `PluginManager` only, **no** `editor.settings`, and
`syntax OK`. If `editor.settings` appears, port it the same way as roxy and commit; otherwise
no change/commit for this task.

---

## Task 7: Bump metadata, composer description, README

**Files:**
- Modify: `metadata.php`
- Modify: `composer.json`
- Modify: `README.md`

- [ ] **Step 1: `metadata.php` — version and description**

In `metadata.php`, change:
```php
    'description' => 'TinyMCE 6 integration for O3-Shop',
```
to:
```php
    'description' => 'TinyMCE 7 integration for O3-Shop',
```
and change:
```php
    'version' => '1.1.0',
```
to:
```php
    'version' => '2.0.0',
```

- [ ] **Step 2: `composer.json` — description**

In `composer.json`, change:
```json
  "description": "TinyMCE 6 Integration for O3-Shop",
```
to:
```json
  "description": "TinyMCE 7 Integration for O3-Shop",
```
Leave `"license": ["GPL-3.0"]` unchanged (TinyMCE's GPL-2.0-or-later is compatible).

- [ ] **Step 3: `README.md` — version references**

In `README.md`, update any "TinyMCE 6" wording to "TinyMCE 7". Verify with:
```bash
cd /Users/nick/o3/tinymce-editor
grep -ni 'tinymce 6\|tinymce6' README.md && echo "still has v6 refs (fix)" || echo "clean"
```
Expected after edits: `clean`.

- [ ] **Step 4: Validate composer.json and php metadata**

Run:
```bash
cd /Users/nick/o3/tinymce-editor
php -l metadata.php
node -e "JSON.parse(require('fs').readFileSync('composer.json','utf8')); console.log('composer.json valid')"
```
Expected: `No syntax errors detected` and `composer.json valid`.

- [ ] **Step 5: Commit**

```bash
cd /Users/nick/o3/tinymce-editor
git add metadata.php composer.json README.md
git commit -m "chore(#194): bump module to 2.0.0, update TinyMCE 7 references

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Task 8: Changelog entry

**Files:**
- Modify: `CHANGELOG.md`

- [ ] **Step 1: Add the v2.0.0 entry**

In `CHANGELOG.md`, replace the `## unreleased` line with:

```markdown
## unreleased

## [v2.0.0] - 2026-06-11

### Security
- Upgraded the bundled TinyMCE editor from 6.4.1 to 7.9.3, fixing three HIGH
  XSS vulnerabilities: CVE-2026-47759 (data-mce- prefixed src/href/style),
  CVE-2026-47761 (media plugin data-mce-object injection), and
  CVE-2026-47762 (mce:protected comments). See o3-shop/o3-shop#194.

### Changed
- TinyMCE 7 requires a license key for self-hosted use; the editor now sends
  `license_key: 'gpl'`. The bundled editor is licensed GPL-2.0-or-later, which
  is compatible with this module's GPL-3.0 license.
- `roxy` file-picker plugin ported from the removed `editor.settings` API to
  `editor.options` (registers `filemanager_url` / `filemanager_access_key`).
- `build/update.sh` now fetches a pinned TinyMCE release from npm reproducibly.

### Removed
- Dropped the `fullpage` and `legacyoutput` plugin classes; both plugins were
  removed from TinyMCE in 6.0 and had no effect (they produced 404s on the
  newsletter editor).
```

- [ ] **Step 2: Commit**

```bash
cd /Users/nick/o3/tinymce-editor
git add CHANGELOG.md
git commit -m "docs(#194): changelog for v2.0.0 TinyMCE 7 upgrade

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

## Task 9: Integration verification — manual admin QA (the real gate)

No automated tests exist; this is the proof the upgrade works. The module must be running
inside an O3-Shop admin. Use the existing shop dev environment (e.g. symlink/clone this
branch into a shop's `source/modules/o3-shop/tinymce-editor`, then
`oe-console oe:module:install` + `oe:module:activate o3-tinymce-editor`, or use the
maintainer's standard module dev setup).

- [ ] **Step 1: Activate the module and open the admin with the browser devtools console open.**

- [ ] **Step 2: Console has no errors:** specifically **no** TinyMCE license warning and
  **no 404** for any plugin `.js` (confirms the fullpage/legacyoutput removal is clean and
  the bundle paths resolve).

- [ ] **Step 3: CMS content page (`content_main`):** editor loads; type + save; reopen and
  confirm the HTML round-tripped.

- [ ] **Step 4: Product long description (`article_main`) + file manager (roxy):** open the
  image dialog, pick an image via the file manager (the `openUrl` dialog opens and the pick
  returns into the field — this validates the `editor.options` port). Save and confirm the
  stored markup uses a **root-relative** path (`/out/pictures/...`) — regression guard for
  o3-shop/o3-shop#151.

- [ ] **Step 5: Newsletter editor (`newsletter_main`):** loads with no console errors now
  that fullpage/legacyoutput are gone.

- [ ] **Step 6: Fullscreen toggle (oxfullscreen):** the toolbar fullscreen button toggles
  the admin frameset.

- [ ] **Step 7: Localisation:** switch admin language; the editor UI reflects it.

- [ ] **Step 8: Version smoke check:** in the console, `tinymce.majorVersion` returns `"7"`.

- [ ] **Step 9: If all pass, remove the scratch notes.**

`.tinymce-upgrade-notes.md` is an untracked working-tree file, so just delete it:
```bash
cd /Users/nick/o3/tinymce-editor
rm -f .tinymce-upgrade-notes.md
```

If any step fails, switch to the superpowers:systematic-debugging skill before patching.

---

## Done criteria

- `out/tinymce/package.json` reports `7.9.3`.
- No references to `fullpage`, `legacyoutput`, or `editor.settings` remain in the module's
  PHP/JS.
- The editor loads in admin with no license warning and no 404s; CMS/product/newsletter
  editing, file manager, fullscreen, and localisation all work (Task 9).
- Module metadata is `2.0.0`; changelog documents the CVE fixes and license note.

## Follow-ups (separate, out of scope here — issue #194 tasks 4 & 5)

- Tag the `o3-shop/tinymce-editor` v2.0.0 release.
- Update the product reference (constraint) in `shop-metapackage-ce` / `o3-shop`.
- Re-scan the trowow image to confirm the CVEs are gone.
```
