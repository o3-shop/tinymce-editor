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

use O3\TinyMCE\Application\Core\TinyMCE\Toolbar\Align;
use O3\TinyMCE\Application\Core\TinyMCE\Toolbar\Blockquote;
use O3\TinyMCE\Application\Core\TinyMCE\Toolbar\Color;
use O3\TinyMCE\Application\Core\TinyMCE\Toolbar\Font;
use O3\TinyMCE\Application\Core\TinyMCE\Toolbar\Blocks;
use O3\TinyMCE\Application\Core\TinyMCE\Toolbar\Indent;
use O3\TinyMCE\Application\Core\TinyMCE\Toolbar\Lists;
use O3\TinyMCE\Application\Core\TinyMCE\Toolbar\RemoveFormat;
use O3\TinyMCE\Application\Core\TinyMCE\Toolbar\ToolbarInterface;

class ToolbarList
{
    /**
     * @return array<int, array<string, ToolbarInterface>>
     */
    public function get(): array
    {
        return [
            [
                'blocks'        => oxNew(Blocks::class),
                'font'          => oxNew(Font::class),
                'color'         => oxNew(Color::class),
                'align'         => oxNew(Align::class),
                //'subscript'     => oxNew(Subscript::class),
                //'superscript'   => oxNew(Superscript::class),
            ],
            [
                //'undo'          => oxNew(Undo::class),
                //'copypaste'     => oxNew(CopyPaste::class),
                'lists'         => oxNew(Lists::class),
                'indent'        => oxNew(Indent::class),
                'blockquote'    => oxNew(Blockquote::class),
                'removeformat'  => oxNew(RemoveFormat::class),
            ],
        ];
    }
}
