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

use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper\DatabaseHelper;
use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton;

\defined('ABSPATH') or die();

/**
 * WP Filesystem API
 */
require_once(\ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
require_once(\ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');

class CacheHelper extends AbstractSingleton {
    /**
     * databaseHelper
     *
     * @var DatabaseHelper
     */
    private $databaseHelper;

    /**
     * Constructor
     */
    protected function __construct() {
        parent::__construct();

        $this->databaseHelper = DatabaseHelper::getInstance();
    }

    /**
     * Getting transient cache information / data
     *
     * @param string $route
     * @return mixed
     */
    public function getKillboardCache(string $route) {
        return $this->databaseHelper->getCachedKillboardDataFromDb($route);
    }

    /**
     * Setting the transient cache
     *
     * @param array $data
     * @return mixed
     */
    public function setKillboardCache(array $data) {
        $this->databaseHelper->writeKillboardCacheDataToDb($data);
    }

    public function getEsiCache(string $esiRoute) {
        return $this->databaseHelper->getCachedEsiDataFromDb($esiRoute);
    }

    public function setEsiCache(array $data) {
        $this->databaseHelper->writeEsiCacheDataToDb($data);
    }
}
