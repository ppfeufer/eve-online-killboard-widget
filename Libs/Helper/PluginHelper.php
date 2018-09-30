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

namespace WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class PluginHelper extends \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
    /**
     * Getting the Plugin Path
     *
     * @param string $file
     * @return string
     */
    public function getPluginPath($file = '') {
        return \WP_PLUGIN_DIR . '/' . $this->getPluginDirName() . '/' . $file;
    }

    /**
     * Getting the Plugin URI
     *
     * @param string $file
     * @return string
     */
    public function getPluginUri($file = '') {
        return \WP_PLUGIN_URL . '/' . $this->getPluginDirName() . '/' . $file;
    }

    /**
     * Get the plugins directory base name
     *
     * @return string
     */
    public function getPluginDirName() {
        return \dirname(\dirname(\dirname(\plugin_basename(__FILE__))));
    }
}
