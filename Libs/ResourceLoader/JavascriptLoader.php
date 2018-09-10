<?php

/**
 * Copyright (C) 2017 Rounon Dax
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\ResourceLoader;

\defined('ABSPATH') or die();

/**
 * JavaScript Loader
 */
class JavascriptLoader implements \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Interfaces\AssetsInterface {
    /**
     * Initialize the loader
     */
    public function init() {
        \add_action('wp_enqueue_scripts', [$this, 'enqueue'], 99);
    }

    /**
     * Load the JavaScript
     */
    public function enqueue() {
        /**
         * Only in Frontend
         */
        if(!\is_admin()) {
            \wp_enqueue_script('bootstrap-js', \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('bootstrap/js/bootstrap.min.js'), ['jquery'], '', true);
            \wp_enqueue_script('bootstrap-toolkit-js', \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('bootstrap/bootstrap-toolkit/bootstrap-toolkit.min.js'), ['jquery', 'bootstrap-js'], '', true);
            \wp_enqueue_script('eve-online-killboard-widget-js', \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('js/eve-online-killboard-widget.min.js'), ['jquery'], '', true);
            \wp_localize_script('eve-online-killboard-widget-js', 'killboardWidgetL10n', $this->getJavaScriptTranslations());
        }
    }

    /**
     * Getting the translation array to translate strings in JavaScript
     *
     * @return array
     */
    private function getJavaScriptTranslations() {
        return [
            'ajax' => [
                'url' => \admin_url('admin-ajax.php'),
                'loaderImage' => \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('images/loader-sprite.gif')
            ]
        ];
    }
}
