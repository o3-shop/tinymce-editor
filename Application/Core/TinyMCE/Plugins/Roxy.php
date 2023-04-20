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

namespace O3\TinyMCE\Application\Core\TinyMCE\Plugins;

use OxidEsales\Eshop\Core\Exception\FileException;
use OxidEsales\Eshop\Core\Registry;

class Roxy extends AbstractPlugin
{
    public function getPluginName(): string
    {
        return 'roxy';
    }

    /**
     * @return string|null
     * @throws FileException
     */
    public function getScriptPath(): ?string
    {
        return Registry::getConfig()->getActiveView()->getViewConfig()->getModuleUrl(
            'o3-tinymce-editor',
            'out/plugins/roxy/plugin.js'
        );
    }

    public function requireRegistration(): bool
    {
        return false;
    }

    public function requireScript(): bool
    {
        return (bool) Registry::getConfig()->getConfigParam("blTinyMCE_filemanager");
    }
}
