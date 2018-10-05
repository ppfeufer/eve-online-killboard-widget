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

use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton;

\defined('ABSPATH') or die();

class DatabaseHelper extends AbstractSingleton {
    /**
     * Option field name for database version
     *
     * @var string
     */
    public $optionDatabaseFieldName = 'eve-online-killboard-widget-database-version';

    /**
     * WordPress Database Instance
     *
     * @var \WPDB
     */
    private $wpdb = null;

    /**
     * Constructor
     *
     * @global \WPDB $wpdb
     */
    protected function __construct() {
        parent::__construct();

        global $wpdb;

        $this->wpdb = $wpdb;
    }

    /**
     * Returning the database version field name
     *
     * @return string
     */
    public function getDatabaseFieldName() {
        return $this->optionDatabaseFieldName;
    }

    /**
     * Getting the current database version
     *
     * @return string
     */
    public function getCurrentDatabaseVersion() {
        return \get_option($this->getDatabaseFieldName());
    }

    /**
     * Check if the database needs to be updated
     *
     * @param string $newVersion New database version to check against
     */
    public function checkDatabase($newVersion) {
        $currentVersion = $this->getCurrentDatabaseVersion();

        if(!\is_null($newVersion)) {
            if(\version_compare($currentVersion, $newVersion) < 0) {
                $this->updateDatabase($newVersion);
            }
        }

        /**
         * Truncate the cache table after this version.
         *
         * We switched to a common ESI client with its own namespaces,
         * so we cannot use the older cached entries any longer.
         */
        if($currentVersion < 20181004) {
            $this->truncateKillboardCacheTable();
        }
    }

    /**
     * Update the plugin database
     *
     * @param string $newVersion New database version
     */
    public function updateDatabase($newVersion) {
        $this->createKillboardCacheTable();
        $this->createEsiCacheTable();

        /**
         * Update database version
         */
        \update_option($this->getDatabaseFieldName(), $newVersion);
    }

    private function truncateKillboardCacheTable() {
        $table = $this->wpdb->base_prefix . 'killboardWidgetCache';
        $sql = "TRUNCATE TABLE $table;";

        $this->wpdb->query($sql);
    }

    /**
     * Creating the pilot table
     */
    private function createKillboardCacheTable() {
        $charsetCollate = $this->wpdb->get_charset_collate();
        $tableName = $this->wpdb->base_prefix . 'killboardWidgetCache';

        $sql = "CREATE TABLE $tableName (
            api_route varchar(255),
            value longtext,
            valid_until varchar(255),
            PRIMARY KEY api_route (api_route)
        ) $charsetCollate;";

        require_once(\ABSPATH . 'wp-admin/includes/upgrade.php');

        \dbDelta($sql);
    }

    private function createEsiCacheTable() {
        $charsetCollate = $this->wpdb->get_charset_collate();
        $tableName = $this->wpdb->base_prefix . 'eveOnlineEsiCache';

        $sql = "CREATE TABLE $tableName (
            esi_route varchar(255),
            value longtext,
            valid_until varchar(255),
            PRIMARY KEY esi_route (esi_route)
        ) $charsetCollate;";

        require_once(\ABSPATH . 'wp-admin/includes/upgrade.php');

        \dbDelta($sql);
    }

    public function getCachedKillboardDataFromDb($route) {
        $returnValue = null;

        $cacheResult = $this->wpdb->get_results($this->wpdb->prepare(
            'SELECT * FROM ' . $this->wpdb->base_prefix . 'killboardWidgetCache' . ' WHERE api_route = %s AND valid_until > %s', [
                $route,
                time()
            ]
        ));

        if($cacheResult) {
            $returnValue = \maybe_unserialize($cacheResult['0']->value);
        }

        return $returnValue;
    }

    /**
     * Setting database based cache
     *
     * @param string $route
     * @param string $value
     * @param int $validUntil
     * @param boolean $returnData
     * @return object
     */
    public function writeKillboardCacheDataToDb($route, $value, $validUntil, $returnData = false) {
        $this->wpdb->query($this->wpdb->prepare(
            'REPLACE INTO ' . $this->wpdb->base_prefix . 'killboardWidgetCache' . ' (api_route, value, valid_until) VALUES (%s, %s, %s)', [
                $route,
                \maybe_serialize($value),
                $validUntil
            ]
        ));

        if($returnData === true) {
            return $this->getCachedKillboardDataFromDb($route);
        }
    }

    /**
     * Getting cached ESI data from DB
     *
     * @param string $route
     * @return Esi Object
     */
    public function getCachedEsiDataFromDb($route) {
        $returnValue = null;

        $cacheResult = $this->wpdb->get_results($this->wpdb->prepare(
            'SELECT * FROM ' . $this->wpdb->base_prefix . 'eveOnlineEsiCache' . ' WHERE esi_route = %s AND valid_until > %s', [
                $route,
                \time()
            ]
        ));

        if($cacheResult) {
            $returnValue = \maybe_unserialize($cacheResult['0']->value);
        }

        return $returnValue;
    }

    /**
     * Write ESI cache data into the DB
     *
     * @param array $data ([esi_route, value, valid_until])
     */
    public function writeEsiCacheDataToDb(array $data) {
        $this->wpdb->query($this->wpdb->prepare(
            'REPLACE INTO ' . $this->wpdb->base_prefix . 'eveOnlineEsiCache' . ' (esi_route, value, valid_until) VALUES (%s, %s, %s)', $data
        ));
    }
}
