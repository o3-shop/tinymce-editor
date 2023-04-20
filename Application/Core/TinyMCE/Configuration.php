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

use O3\TinyMCE\Application\Core\TinyMCE\Options\BaseUrl;
use O3\TinyMCE\Application\Core\TinyMCE\Options\CacheSuffix;
use O3\TinyMCE\Application\Core\TinyMCE\Options\ContentCss;
use O3\TinyMCE\Application\Core\TinyMCE\Options\ContextMenu;
use O3\TinyMCE\Application\Core\TinyMCE\Options\DocumentBaseUrl;
use O3\TinyMCE\Application\Core\TinyMCE\Options\EntityEncoding;
use O3\TinyMCE\Application\Core\TinyMCE\Options\ExternalPlugins;
use O3\TinyMCE\Application\Core\TinyMCE\Options\FilemanagerUrl;
use O3\TinyMCE\Application\Core\TinyMCE\Options\ImageAdvtab;
use O3\TinyMCE\Application\Core\TinyMCE\Options\InitInstanceCallback;
use O3\TinyMCE\Application\Core\TinyMCE\Options\Language;
use O3\TinyMCE\Application\Core\TinyMCE\Options\MaxHeight;
use O3\TinyMCE\Application\Core\TinyMCE\Options\MaxWidth;
use O3\TinyMCE\Application\Core\TinyMCE\Options\Menubar;
use O3\TinyMCE\Application\Core\TinyMCE\Options\MinHeight;
use O3\TinyMCE\Application\Core\TinyMCE\Options\OptionInterface;
use O3\TinyMCE\Application\Core\TinyMCE\Options\Plugins;
use O3\TinyMCE\Application\Core\TinyMCE\Options\Promotion;
use O3\TinyMCE\Application\Core\TinyMCE\Options\Protect;
use O3\TinyMCE\Application\Core\TinyMCE\Options\QuickbarsInsertToolbar;
use O3\TinyMCE\Application\Core\TinyMCE\Options\RelativeUrls;
use O3\TinyMCE\Application\Core\TinyMCE\Options\Resize;
use O3\TinyMCE\Application\Core\TinyMCE\Options\Selector;
use O3\TinyMCE\Application\Core\TinyMCE\Options\Setup;
use O3\TinyMCE\Application\Core\TinyMCE\Options\Skin;
use O3\TinyMCE\Application\Core\TinyMCE\Options\Toolbar;
use O3\TinyMCE\Application\Core\TinyMCE\Options\ToolbarMode;
use O3\TinyMCE\Application\Core\TinyMCE\Options\ToolbarSticky;

class Configuration
{
    protected Loader $loader;
    /** @var array<String, String> */
    protected array $options = [];

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    public function build(): void
    {
        $this->addIntegrateOptions();
        $this->addGuiOptions();
        $this->addContentAppearance();
        $this->addContentFiltering();
        $this->addLocalizationOptions();
        $this->addUrlHandling();
        $this->addPlugins();
        $this->addToolbar();
        $this->addCustomOptions();
    }

    protected function addOption(OptionInterface $optionInstance): void
    {
        if (!$optionInstance->requireRegistration()) {
            return;
        }

        $option = $optionInstance->get();

        if ($optionInstance->isQuoted()) {
            $option = (oxNew(Utils::class))->quote($option);
        }

        $this->options[$optionInstance->getKey()] = $option;
    }

    /**
     * @param string $optionKey
     * @return void
     */
    protected function removeOption(string $optionKey): void
    {
        if (isset($this->options[$optionKey])) {
            unset($this->options[$optionKey]);
        }
    }

    public function getConfig(): string
    {
        $sConfig = '';

        /**
         * @var string $param
         * @var string $value
         */
        foreach ($this->options as $param => $value) {
            $sConfig .= "$param: $value, ";
        }

        return $sConfig;
    }

    /**
     * @return void
     */
    protected function addIntegrateOptions(): void
    {
        $this->addOption(oxNew(Setup::class, $this->loader));
        $this->addOption(oxNew(BaseUrl::class, $this->loader));
        $this->addOption(oxNew(CacheSuffix::class, $this->loader));
        $this->addOption(oxNew(Selector::class, $this->loader));
        $this->addOption(oxNew(InitInstanceCallback::class, $this->loader));
    }

    protected function addGuiOptions(): void
    {
        $this->addOption(oxNew(Skin::class, $this->loader));
        $this->addOption(oxNew(Promotion::class, $this->loader));
        $this->addOption(oxNew(ContextMenu::class, $this->loader));
        $this->addOption(oxNew(MinHeight::class, $this->loader));
        $this->addOption(oxNew(MaxHeight::class, $this->loader));
        $this->addOption(oxNew(MaxWidth::class, $this->loader));
        $this->addOption(oxNew(Menubar::class, $this->loader));
        $this->addOption(oxNew(ToolbarSticky::class, $this->loader));
        $this->addOption(oxNew(ToolbarMode::class, $this->loader));
        $this->addOption(oxNew(Resize::class, $this->loader));
    }

    protected function addContentAppearance(): void
    {
        $this->addOption(oxNew(ContentCss::class, $this->loader));
    }

    protected function addContentFiltering(): void
    {
        $this->addOption(oxNew(EntityEncoding::class, $this->loader));
        $this->addOption(oxNew(Protect::class, $this->loader));
    }

    protected function addLocalizationOptions(): void
    {
        $this->addOption(oxNew(Language::class, $this->loader));
    }

    protected function addUrlHandling(): void
    {
        $this->addOption(oxNew(DocumentBaseUrl::class, $this->loader));
        $this->addOption(oxNew(RelativeUrls::class, $this->loader));
    }

    protected function addPlugins(): void
    {
        $this->addOption(oxNew(ImageAdvtab::class, $this->loader));
        $this->addOption(oxNew(Plugins::class, $this->loader));
        $this->addOption(oxNew(ExternalPlugins::class, $this->loader));
        $this->addOption(oxNew(FilemanagerUrl::class, $this->loader));
        $this->addOption(oxNew(QuickbarsInsertToolbar::class, $this->loader));
    }

    protected function addToolbar(): void
    {
        $this->addOption(oxNew(Toolbar::class, $this->loader));
    }

    protected function addCustomOptions(): void
    {
    }
}
