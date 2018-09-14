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
namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class DatabaseHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
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
    }

    /**
     * Update the plugin database
     *
     * @param string $newVersion New database version
     */
    public function updateDatabase($newVersion) {
        $this->createKillboardCacheTable();

        /**
         * Update database version
         */
        \update_option($this->getDatabaseFieldName(), $newVersion);
    }

    /**
     * Creating the pilot table
     */
    private function createKillboardCacheTable() {
        $charsetCollate = $this->wpdb->get_charset_collate();
        $tableName = $this->wpdb->base_prefix . 'killboardWidgetCache';

        $sql = "CREATE TABLE $tableName (
            api_route varchar(255),
            value text,
            valid_until varchar(255),
            PRIMARY KEY api_route (api_route)
        ) $charsetCollate;";

        require_once(\ABSPATH . 'wp-admin/includes/upgrade.php');

        \dbDelta($sql);
    }

    public function getDatabaseCache($route) {
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
    public function setDatabaseCache($route, $value, $validUntil, $returnData = false) {
        $this->wpdb->query($this->wpdb->prepare(
            'REPLACE INTO ' . $this->wpdb->base_prefix . 'killboardWidgetCache' . ' (api_route, value, valid_until) VALUES (%s, %s, %s)', [
                $route,
                \maybe_serialize($value),
                $validUntil
            ]
        ));

        if($returnData === true) {
            return $this->getDatabaseCache($route);
        }
    }
}
