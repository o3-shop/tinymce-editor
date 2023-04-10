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

/*global tinymce:true */

(function () {
    'use strict';
    var PM = tinymce.util.Tools.resolve('tinymce.PluginManager');

    PM.add('oxfullscreen', function (editor) {
        editor.ui.registry.addToggleButton('fullscreen', {
            tooltip: 'Fullscreen',
            icon: 'fullscreen',
            shortcut: 'Meta+Alt+F',
            active: false,
            onAction: (api) => {
                const topframeset = top.document.getElementsByTagName("frameset");
                topframeset[0].setAttribute("cols", (topframeset[0].getAttribute("cols") === "200,*" ? "1px,*" : "200,*"));
                topframeset[1].setAttribute("rows", (topframeset[1].getAttribute("rows") === "54,*" ? "1px,*" : "54,*"));
                const parentframeset = parent.document.getElementsByTagName("frameset");
                parentframeset[0].setAttribute("rows", (parentframeset[0].getAttribute("rows") === "40%,*" ? "1px,*" : "40%,*"));
                api.setActive(!api.isActive());
            }
        });

        return {
            getMetadata: () => {
                return {
                    name: "TinyMCE Fullscreen Editing Plugin for O3-Shop",
                    url: "https://github.com/vanilla-thunder/oxid-module-tinymce"
                };
            }
        };
    });
}());