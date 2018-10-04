<?php

/**
 * Copyright (C) 2017 Rounon Dax
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace WordPress\Plugins\EveOnlineKillboardWidget\Libs\ResourceLoader;

use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper\PluginHelper;
use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Interfaces\AssetsInterface;

\defined('ABSPATH') or die();

/**
 * JavaScript Loader
 */
class JavascriptLoader implements AssetsInterface {
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
            \wp_enqueue_script('bootstrap-js', PluginHelper::getInstance()->getPluginUri('bootstrap/js/bootstrap.min.js'), ['jquery'], '', true);
            \wp_enqueue_script('bootstrap-toolkit-js', PluginHelper::getInstance()->getPluginUri('bootstrap/bootstrap-toolkit/bootstrap-toolkit.min.js'), ['jquery', 'bootstrap-js'], '', true);
            \wp_enqueue_script('eve-online-killboard-widget-js', PluginHelper::getInstance()->getPluginUri('js/eve-online-killboard-widget.min.js'), ['jquery'], '', true);
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
                'loaderImage' => PluginHelper::getInstance()->getPluginUri('images/loader-sprite.gif')
            ]
        ];
    }
}
