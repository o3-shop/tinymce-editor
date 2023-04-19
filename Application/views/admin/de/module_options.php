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

$sLangName = 'Deutsch';
$aLang = [
   'charset'                                => 'UTF-8',
   'TINYMCE_TOGGLE'                         => 'Editor zeigen/verstecken',
   'TINYMCE_PLAINCMS'                       => '<b class="errorbox">Der Editor wurde für diese Seite deaktiviert, weil sie keine HTML Formatierung enthalten darf </b>',
   'SHOP_MODULE_GROUP_tinyMceMain'          => '<style>.groupExp dl dd li {margin-left: 300px;} .groupExp dd h3 {border-bottom: none; margin-bottom: 0;} .groupExp dt .txtfield {margin-top: 33px;}</style>Moduleinstellungen',
   'SHOP_MODULE_blTinyMCE_filemanager'      => 'Dateimanager aktivieren',
   'HELP_SHOP_MODULE_blTinyMCE_filemanager' => 'Ist diese Option aktiv, können Bilder hochgeladen werden. Der Speicherort ist: out/pictures/wysiwigpro/',
   'SHOP_MODULE_aTinyMCE_classes'           => '<h3>TinyMCE für folgende Backend-Seiten laden:</h3><ul><li>article_main (Artikelbeschreibung)</li><li>content_main (CMS Seiten)</li><li>category_text (Kategorienbeschreibung)</li><li>newsletter_main (Newsletter)</li><li>news_text (Nachrichten-Text)</li></ul>',
   'HELP_SHOP_MODULE_aTinyMCE_classes'      => 'für die Benutzung von TinyMCE in eigenen Admin Views muss hier die entsprechende Controllerklasse eingetragen werden, dann wird für jedes Textarea je ein Editor erzeugt',
   'SHOP_MODULE_GROUP_tinyMceSettings'      => 'TinyMCE Einstellungen &amp; Plugins',
];
