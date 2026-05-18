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
 * @copyright  Copyright (c) 2026 O3-Shop (https://www.o3-shop.com)
 * @license    https://www.gnu.org/licenses/gpl-3.0  GNU General Public License 3 (GPLv3)
 */

declare(strict_types=1);

namespace O3\TinyMCE\Application\Core\TinyMCE\Options;

/**
 * Pairs with `relative_urls: false`. When TinyMCE writes an absolute URL
 * back to the editor (e.g. via the file manager), this strips the
 * scheme + host so the persisted attribute is root-relative
 * ("/out/pictures/wysiwigpro/logo.png") instead of fully-qualified
 * ("https://shop.example.com/out/pictures/wysiwigpro/logo.png").
 *
 * Root-relative is what the storefront needs: it resolves correctly at
 * any URL depth (sub-path product pages, admin previews, etc.) and is
 * portable across environments (dev/ngrok/staging/prod) without DB
 * rewrites.
 *
 * `true` is already the TinyMCE default; we set it explicitly so future
 * TinyMCE default changes can't quietly flip our URL strategy.
 *
 * Refs: o3-shop/o3-shop#151.
 */
class RemoveScriptHost extends AbstractOption
{
    protected string $key = 'remove_script_host';

    public function get(): string
    {
        return 'true';
    }
}
