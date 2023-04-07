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

class ExternalPlugins extends AbstractOption
{
    protected string $key = 'external_plugins';

    public function get(): string
    {
        $pluginList = oxNew(PluginList::class);

        $list = implode(', ', array_filter(
            array_map(
                function (PluginInterface $plugin) {
                    return $plugin->getScriptPath() ? implode(
                        ':',
                        [
                            (oxNew(Utils::class))->quote($plugin->getPluginName()),
                            (oxNew(Utils::class))->quote($plugin->getScriptPath())
                        ]
                    ) : null;
                },
                $pluginList->get()
            ))
        );

        return '{ '.$list.' }';
    }
}