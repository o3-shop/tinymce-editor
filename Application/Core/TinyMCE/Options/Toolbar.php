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

use O3\TinyMCE\Application\Core\TinyMCE\Loader;
use O3\TinyMCE\Application\Core\TinyMCE\PluginList;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\PluginInterface;
use O3\TinyMCE\Application\Core\TinyMCE\ToolbarList;

class Toolbar extends AbstractOption
{
    protected string $key = 'toolbar';

    protected bool $forceSingleLineToolbar = true;

    public function __construct(Loader $loader)
    {
        parent::__construct($loader);
    }

    public function get(): string
    {
        $toolbarList = oxNew(ToolbarList::class);

        return $this->forceSingleLineToolbar ?
            $this->getSingleLineToolbar($toolbarList) :
            $this->getMultiLineToolbar($toolbarList);
    }

    /**
     * @param ToolbarList $toolbarList
     * @return string
     */
    protected function getSingleLineToolbar(ToolbarList $toolbarList): string
    {
        $all = [];

        foreach ($toolbarList->get() as $toolbar) {
            $all = array_merge($all, $toolbar);
        }

        $toolbarElements = implode(
            ' | ',
            array_filter(
                array_map(
                    function ($toolbarElement) {
                        return implode(
                            ' ',
                            $toolbarElement->getButtons()
                        );
                    },
                    $all
                )
            )
        );

        $pluginList = oxNew(PluginList::class);
        $pluginToolbarElements = implode(
            ' | ',
            array_filter(
                array_map(
                    function (PluginInterface $plugin) {
                        return count($plugin->getToolbarElements()) ? implode(
                            ' ',
                            $plugin->getToolbarElements()
                        ) : null;
                    },
                    $pluginList->get()
                )
            )
        );

        return $toolbarElements . ' | ' . $pluginToolbarElements;
    }

    /**
     * @param ToolbarList $toolbarList
     * @return string
     */
    protected function getMultiLineToolbar(ToolbarList $toolbarList): string
    {
        $list = [];

        foreach ($toolbarList->get() as $toolbar) {
            $list[] = implode(
                ' | ',
                array_filter(
                    array_map(
                        function ($toolbarElement) {
                            return implode(
                                ' ',
                                $toolbarElement->getButtons()
                            );
                        },
                        $toolbar
                    )
                )
            );
        }

        $pluginList = oxNew(PluginList::class);
        $list[] = implode(
            ' | ',
            array_filter(
                array_map(
                    function (PluginInterface $plugin) {
                        return count($plugin->getToolbarElements()) ? implode(
                            ' ',
                            $plugin->getToolbarElements()
                        ) : null;
                    },
                    $pluginList->get()
                )
            )
        );

        return '["'.implode('", "', $list).'"]';
    }

    public function isQuoted(): bool
    {
        return true;
    }
}
