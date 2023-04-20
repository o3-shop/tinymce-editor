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

use O3\TinyMCE\Application\Core\Setup\Events;

$sMetadataVersion = '2.1';
$aModule          = [
    'id' => 'o3-tinymce-editor',
    'title' => 'TinyMCE Editor',
    'description' => 'TinyMCE 6 integration for O3-Shop',
    'thumbnail' => 'logo.png',
    'version' => '1.0.0',
    'author' => 'O3-Shop, Marat Bedoev',
    'url' => 'https://www.o3-shop.com/',
    'extend' => [
        OxidEsales\Eshop\Core\ViewConfig::class => O3\TinyMCE\Application\Core\ViewConfig::class,
    ],
    'controllers' => [
        'tinyfilemanager'   => O3\TinyMCE\Application\Controller\Admin\TinyFileManager::class,
    ],
    'templates' => [
        'TinyFilemanager.tpl'   => 'o3-shop/tinymce-editor/Application/views/admin/filemanager.tpl',
        'EditorSwitch.tpl'      => 'o3-shop/tinymce-editor/Application/views/admin/editorswitch.tpl',
    ],
    'blocks' => [
        [
            'template' => 'bottomnaviitem.tpl',
            'block' => 'admin_bottomnaviitem',
            'file' => 'Application/views/blocks/admin/bottomnaviitem_admin_bottomnaviitem.tpl',
        ],
    ],
    'settings' => [
        /* enabling tinyMCE for these classes */
        [
            'group' => 'tinyMceMain',
            'name' => 'aTinyMCE_classes',
            'type' => 'arr',
            'value' => [
                "article_main",
                "category_text",
                "content_main",
                "newsletter_main",
                "news_text",
            ],
            'position' => 0,
        ],
        [
            'group' => 'tinyMceMain',
            'name' => 'blTinyMCE_filemanager',
            'type' => 'bool',
            'value' => true,
            'position' => 2,
        ]
    ],
    'events'       => [
        'onActivate'   => Events::class.'::onActivate',
        'onDeactivate' => Events::class.'::onDeactivate'
    ],
];
