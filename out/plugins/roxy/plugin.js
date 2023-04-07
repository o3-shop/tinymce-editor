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

(function () {
    'use strict';
    const PluginManager = tinymce.util.Tools.resolve('tinymce.PluginManager');

    PluginManager.add('roxy', function (editor) {
        editor.options.set('file_picker_callback', function ($callback, $value, $meta) {
            var url = editor.options.get('filemanager_url')
                + "&type=" + $meta.filetype
                + '&value=' + $value
                + '&selected=' + $value;

            if (editor.options.isSet('language')) {
                url += '&langCode=' + editor.options.get('language');
            }
            if (editor.options.isSet('filemanager_access_key')) {
                url += '&akey=' + editor.options.get('filemanager_access_key');
            }

            const instanceApi = editor.windowManager.openUrl({
                title: 'Filemanager',
                url: url,
                width: window.innerWidth,
                height: window.innerHeight - 40,
                onMessage: function(dialogApi, details) {
                    $callback(details.content);
                    instanceApi.close();
                }
            });
        });

        return {
            getMetadata: () => {
                return {
                    name: "Roxy Filemanager Plugin for O3-Shop",
                    url: "https://gitlab.o3-shop.com/o3/tinymce-editor/"
                };
            }
        };
    });
}());