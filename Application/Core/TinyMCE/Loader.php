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

use OxidEsales\Eshop\Application\Model\Content;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Exception\FileException;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Registry;

class Loader
{
    protected Config $config;
    protected Language $language;

    public function __construct(Config $config, Language $language)
    {
        $this->config = $config;
        $this->language = $language;
    }

    /**
     * @return string
     * @throws FileException
     */
    public function getEditorCode(): string
    {
        if (!$this->isEnabledForCurrentController()) {
            return '';
        }

        if ($this->contentIsPlain()) {
            /** @var string $message */
            $message = $this->language->translateString('TINYMCE_PLAINCMS');
            return $message;
        }

        $configuration = oxNew(Configuration::class, $this);
        $configuration->build();

        $this->registerScripts($configuration);
        $this->registerIncludes();

        $smarty = Registry::getUtilsView()->getSmarty();
        return $smarty->fetch('EditorSwitch.tpl');
    }

    /**
     * @return bool
     */
    protected function isEnabledForCurrentController(): bool
    {
        /** @var string[] $aEnabledClasses */
        $aEnabledClasses = $this->getShopConfig()->getConfigParam("aTinyMCE_classes", []);

        return in_array($this->getShopConfig()->getActiveView()->getClassKey(), $aEnabledClasses);
    }

    /**
     * @return bool
     */
    protected function contentIsPlain(): bool
    {
        /** @var BaseModel|Content $oEditObject */
        $oEditObject = $this->getShopConfig()->getActiveView()->getViewDataElement("edit");
        return $oEditObject instanceof Content && $oEditObject->isPlain();
    }

    /**
     * @return Language
     */
    public function getLanguage(): Language
    {
        return $this->language;
    }

    /**
     * @return Config
     */
    public function getShopConfig(): Config
    {
        return $this->config;
    }

    /**
     * @param Configuration $configuration
     *
     * @return void
     */
    protected function registerScripts(Configuration $configuration): void
    {
        $sCopyLongDescFromTinyMCE = file_get_contents(__DIR__.'/../../../out/scripts/copyLongDesc.js');
        $sUrlConverter = file_get_contents(__DIR__.'/../../../out/scripts/urlConverter.js');
        $sInit = str_replace(
            "'CONFIG':'VALUES'",
            $configuration->getConfig(),
            (string) file_get_contents(__DIR__.'/../../../out/scripts/init.js')
        );
        $smarty = Registry::getUtilsView()->getSmarty();
        $sSufix = ($smarty->_tpl_vars["__oxid_include_dynamic"]) ? '_dynamic' : '';

        $aScript = (array) Registry::getConfig()->getGlobalParameter('scripts' . $sSufix);
        $aScript[] = $sCopyLongDescFromTinyMCE;
        $aScript[] = $sUrlConverter;
        $aScript[] = $sInit;

        Registry::getConfig()->setGlobalParameter('scripts' . $sSufix, $aScript);
    }

    /**
     * @return void
     * @throws FileException
     */
    protected function registerIncludes(): void
    {
        $smarty = Registry::getUtilsView()->getSmarty();
        $sSuffix = ($smarty->_tpl_vars["__oxid_include_dynamic"]) ? '_dynamic' : '';

        /** @var array<int, string[]> $aInclude */
        $aInclude = (array) Registry::getConfig()->getGlobalParameter('includes' . $sSuffix);

        $aInclude[3][] = Registry::getConfig()->getActiveView()->getViewConfig()->getModuleUrl(
            'o3-tinymce-editor',
            'out/tinymce/tinymce.min.js'
        );

        Registry::getConfig()->setGlobalParameter('includes' . $sSuffix, $aInclude);
    }
}
