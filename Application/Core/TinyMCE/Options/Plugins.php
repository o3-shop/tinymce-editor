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
 * @copyright  Copyright (c) 2022 OXID Marat Bedoev, bestlife AG
 * @copyright  Copyright (c) 2023 O3-Shop (https://www.o3-shop.com)
 * @license    https://www.gnu.org/licenses/gpl-3.0  GNU General Public License 3 (GPLv3)
 */

declare(strict_types=1);

namespace O3\TinyMCE\Application\Core\TinyMCE\Options;

use O3\TinyMCE\Application\Core\TinyMCE\PluginList;
use O3\TinyMCE\Application\Core\TinyMCE\Plugins\PluginInterface;

class Plugins extends AbstractOption
{
    protected string $key = 'plugins';

    public function get(): string
    {
        $pluginList = oxNew(PluginList::class);

        return implode(' ', array_filter(
                array_map(
                function (PluginInterface $plugin) {
                    return $plugin->requireRegistration() ?
                        $plugin->getPluginName() :
                        null
                    ;
                },
                $pluginList->get()
            )
        ));

        $aPlugins = array(
            //'advlist' => '', // '' = plugin has no buttons
            'anchor'        => 'anchor',
            'autolink'      => '',
            'autoresize'    => '',
            'charmap'       => 'charmap',
            'code'          => 'code',
            'hr'            => 'hr',
            'image'         => 'image',
            // 'imagetools' => '', // das hier klingt sehr kompliziert
            'link'          => 'link unlink',
            'lists'         => '',
            'media'         => 'media',
            'nonbreaking'   => 'nonbreaking',
            'pagebreak'     => 'pagebreak',
            'paste'         => 'pastetext',
            'preview'       => 'preview',
            'quickbars'     => '',//'quicklink quickimage quicktable',
            'searchreplace' => 'searchreplace',
            'table'         => 'table',
            'visualblocks'  => 'visualblocks',
            'wordcount'     => '',
            'oxfullscreen'  => 'fullscreen', //custom fullscreen plugin
            //'oxwidget'       => 'widget'
            //'oxgetseourl'    => 'yolo' //custom seo url plugin // wip
        );

        // plugins for newsletter emails
        if ( $this->getActiveClassName() === "newsletter_main" ) {
            $aPlugins["legacyoutput"] = "";
            $aPlugins["fullpage"]     = "fullpage";
        }

        return 350;
    }

    public function mustQuote(): bool
    {
        return true;
    }
}