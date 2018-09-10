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
 * CSS Loader
 */
class CssLoader implements \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Interfaces\AssetsInterface {
    /**
     * Initialize the loader
     */
    public function init() {
        \add_action('wp_enqueue_scripts', [$this, 'enqueue'], 99);
    }

    /**
     * Load the styles
     */
    public function enqueue() {
        /**
         * Only in Frontend
         */
        if(!\is_admin()) {
            \wp_enqueue_style('bootstrap', \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('bootstrap/css/bootstrap.min.css'));
            \wp_enqueue_style('eve-online-killboard-widget', \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('css/eve-online-killboard-widget.min.css'));
        }
    }
}
