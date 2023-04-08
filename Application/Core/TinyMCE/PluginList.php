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

namespace O3\TinyMCE\Application\Core\TinyMCE;

use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Anchor;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Autolink;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\AutoResize;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Charmap;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Code;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\FullPage;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\FullScreen;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Image;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Legacyoutput;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Link;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Lists;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Media;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Nonbreaking;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Pagebreak;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\PluginInterface;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Preview;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Quickbars;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Roxy;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\SearchReplace;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Table;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\Visualblocks;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\WordCount;

class PluginList
{
    /**
     * @return array<string, PluginInterface>
     */
    public function get(): array
    {
        return [
            'anchor'        => oxNew(Anchor::class),
            'autolink'      => oxNew(Autolink::class),
            'autoresize'    => oxNew(AutoResize::class),
            'charmap'       => oxNew(Charmap::class),
            'code'          => oxNew(Code::class),
            'fullpage'      => oxNew(FullPage::class),
            'image'         => oxNew(Image::class),
            'legacyoutput'  => oxNew(Legacyoutput::class),
            'link'          => oxNew(Link::class),
            'lists'         => oxNew(Lists::class),
            'media'         => oxNew(Media::class),
            'nonbreaking'   => oxNew(Nonbreaking::class),
            'pagebreak'     => oxNew(Pagebreak::class),
            'preview'       => oxNew(Preview::class),
            'quickbars'     => oxNew(Quickbars::class),
            'searchreplace' => oxNew(SearchReplace::class),
            'table'         => oxNew(Table::class),
            'visualblocks'  => oxNew(Visualblocks::class),
            'wordcount'     => oxNew(WordCount::class),
            'fullscreen'    => oxNew(FullScreen::class),
            'roxy'          => oxNew(Roxy::class),
        ];
    }
}
