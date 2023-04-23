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

namespace O3\TinyMCE\Application\Core\Setup;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Console\CommandsProvider\ServicesCommandsProvider;
use OxidEsales\EshopCommunity\Internal\Framework\Console\Executor;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ShopConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration\Template;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration\TemplateBlock;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Exception\ModuleConfigurationNotFoundException;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\TemplateBlockModuleSettingHandlerBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Handler\TemplateBlockModuleSettingHandler;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;

class Actions
{
    /**
     * apply updated class extensions to yaml files
     *
     * @return void
     * @throws ModuleConfigurationNotFoundException
     */
    public function installApplyNewConfiguration()
    {
        /** @var ShopConfigurationDaoBridgeInterface $shopConfiguration */
        $shopConfiguration = ContainerFactory::getInstance()->getContainer()->get(ShopConfigurationDaoBridgeInterface::class);
        $beforeHash = md5(serialize($shopConfiguration->get()->getModuleConfiguration('o3-tinymce-editor')));

        $executor = $this->getCommandExecutor();

        $add = php_sapi_name() == 'cli' ? 'source/' : (isAdmin() ? '../' : '');

        $input = new ArrayInput([
            'command' => 'oe:module:install-configuration',
            'module-source-path'    => $add.'modules/o3-shop/tinymce-editor/'
        ]);
        $executor->execute($input);

        $changedConfiguration =
            md5(serialize($shopConfiguration->get()->getModuleConfiguration('o3-tinymce-editor'))) !== $beforeHash;

        if ($changedConfiguration) {
            /** @var ModuleConfigurationDaoBridgeInterface $mas */
            $mas = ContainerFactory::getInstance()->getContainer()->get(ModuleConfigurationDaoBridgeInterface::class);

            /** @var TemplateBlockModuleSettingHandler $tbsh */
            $tbsh = ContainerFactory::getInstance()->getContainer()->get(TemplateBlockModuleSettingHandlerBridgeInterface::class);
            $tbsh->handleOnModuleDeactivation($mas->get('o3-tinymce-editor'), Registry::getConfig()->getShopId());
            $tbsh->handleOnModuleActivation($mas->get('o3-tinymce-editor'), Registry::getConfig()->getShopId());
        }
    }

    /**
     * clear cache
     */
    public function clearCache(): void
    {
        try {
            $oUtils = Registry::getUtils();
            $oUtils->resetTemplateCache($this->getModuleTemplates());
            $oUtils->resetLanguageCache();
        } catch (ContainerExceptionInterface|NotFoundExceptionInterface|ModuleConfigurationNotFoundException $e) {
            Registry::getLogger()->error($e->getMessage(), [$this]);
            Registry::getUtilsView()->addErrorToDisplay($e->getMessage());
        }
    }

    /**
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ModuleConfigurationNotFoundException
     */
    protected function getModuleTemplates(): array
    {
        $container = $this->getDIContainer();
        $shopConfiguration = $container->get(ShopConfigurationDaoBridgeInterface::class)->get();
        $moduleConfiguration = $shopConfiguration->getModuleConfiguration('o3-tinymce-editor');

        return array_unique(
            array_merge(
                $this->getModuleTemplatesFromTemplates($moduleConfiguration),
                $this->getModuleTemplatesFromBlocks($moduleConfiguration)
            )
        );
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     *
     * @return array
     */
    protected function getModuleTemplatesFromTemplates(ModuleConfiguration $moduleConfiguration): array
    {
        /** @var $template Template */
        return array_map(
            function ($template) {
                return $template->getTemplateKey();
            },
            $moduleConfiguration->getTemplates()
        );
    }

    /**
     * @param ModuleConfiguration $moduleConfiguration
     *
     * @return array
     */
    protected function getModuleTemplatesFromBlocks(ModuleConfiguration $moduleConfiguration): array
    {
        /** @var $templateBlock TemplateBlock */
        return array_map(
            function ($templateBlock) {
                return basename($templateBlock->getShopTemplatePath());
            },
            $moduleConfiguration->getTemplateBlocks()
        );
    }

    /**
     * @return ContainerInterface|null
     */
    protected function getDIContainer(): ?ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }

    /**
     * @return Executor
     */
    protected function getCommandExecutor(): Executor
    {
        $servicesCommandsProvider = new ServicesCommandsProvider(ContainerFactory::getInstance()->getContainer());

        $application = new Application();
        $application->setAutoExit(false);

        return new Executor($application, $servicesCommandsProvider);
    }
}