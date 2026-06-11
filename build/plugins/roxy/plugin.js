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
        // These options are injected into the init config by the module's PHP
        // (Options/FilemanagerUrl.php). In TinyMCE 7 custom options must be
        // registered before they can be read with editor.options.get().
        editor.options.register('filemanager_url', { processor: 'string', default: '' });
        editor.options.register('filemanager_access_key', { processor: 'string', default: '' });

        editor.options.set('file_picker_callback', function (callback, value, meta) {
            let url = editor.options.get('filemanager_url')
                + '&type=' + meta.filetype
                + '&value=' + value
                + '&selected=' + value;

            const language = editor.options.get('language');
            if (language) {
                url += '&langCode=' + language;
            }

            const accessKey = editor.options.get('filemanager_access_key');
            if (accessKey) {
                url += '&akey=' + accessKey;
            }

            const instanceApi = editor.windowManager.openUrl({
                title: 'Filemanager',
                url: url,
                width: window.innerWidth,
                height: window.innerHeight - 40,
                onMessage: function (dialogApi, details) {
                    callback(details.content);
                    instanceApi.close();
                }
            });
        });
    });
}());