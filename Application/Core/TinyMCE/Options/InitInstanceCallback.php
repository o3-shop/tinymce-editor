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

use O3\TinyMCE\Application\Core\TinyMCE\PluginList;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\PluginInterface;
use O3\TinyMCE\Application\Core\TinyMCE\Utils;

class InitInstanceCallback extends AbstractOption
{
    protected string $key = 'init_instance_callback';

    public function get(): string
    {
        // https://github.com/tinymce/tinymce/issues/2271

        $js = <<<JS
            function (editor) {
                editor.on('PostProcess', function (e) {
                    e.content = e.content.replace(
                        /(&lt;!--mce:protected\s)(.*?)(--&gt;)/gm, 
                        function(text, p1, p2, p3){
                            if (unescape) {
                                return unescape(p2);
                            } else {
                                return decodeURIComponent(p2);
                            }
                        }
                    );
                });
            }
        JS;

        return (oxNew(Utils::class))->minifyJS($js);
    }
}
