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
use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper\ImageHelper;
use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper\RemoteHelper;
use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton;

\defined('ABSPATH') or die();

/**
 * WP Filesystem API
 */
require_once(\ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
require_once(\ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');

class CacheHelper extends AbstractSingleton {
    /**
     * The base directoy of our cache
     *
     * @var string
     */
    private $cacheDirectoryBase;

    /**
     * databgaseHelper
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
        $this->cacheDirectoryBase = $this->getPluginCacheDir();

        $this->checkOrCreateCacheDirectories();
    }

    /**
     * Check if cache directories exist, otherwise try to create them
     */
    public function checkOrCreateCacheDirectories() {
        $this->createCacheDirectory();
        $this->createCacheDirectory('images');
        $this->createCacheDirectory('images/ship');
        $this->createCacheDirectory('images/character');
        $this->createCacheDirectory('images/corporation');
        $this->createCacheDirectory('images/alliance');
        $this->createCacheDirectory('images/render');
    }

    /**
     * Getting the absolute path for the cache directory
     *
     * @return string Absolute path for the cache directory
     */
    public function getPluginCacheDir() {
        return \trailingslashit(\WP_CONTENT_DIR) . 'cache/eve-online/';
    }

    /**
     * Getting the URI for the cache directory
     *
     * @return string URI for the cache directory
     */
    public function getPluginCacheUri() {
        return \trailingslashit(\WP_CONTENT_URL) . 'cache/eve-online/';
    }

    /**
     * Getting the local image cache directory
     *
     * @return string Local image cache directory
     */
    public function getImageCacheDir() {
        return \trailingslashit($this->getPluginCacheDir() . 'images/');
    }

    /**
     * Getting the local image cache URI
     *
     * @return string Local image cache URI
     */
    public function getImageCacheUri() {
        return \trailingslashit($this->getPluginCacheUri() . 'images/');
    }

    /**
     * creating our needed cache directories under:
     *      /wp-content/cache/plugin/«plugin-name»/
     *
     * @param string $directory The Directory to create
     */
    public function createCacheDirectory($directory = '') {
        $wpFileSystem =  new \WP_Filesystem_Direct(null);
        $dirToCreate = \trailingslashit($this->getPluginCacheDir() . $directory);

        \wp_mkdir_p($dirToCreate);

        if(!$wpFileSystem->is_file($dirToCreate . '/index.php')) {
            $wpFileSystem->put_contents(
                $dirToCreate . '/index.php',
                '',
                0644
            );
        }
    }

    /**
     * Chek if a remote image has been cached locally
     *
     * @param string $cacheType The subdirectory in the image cache filesystem
     * @param string $imageName The image file name
     * @return boolean true or false
     */
    public function checkCachedImage($cacheType = null, $imageName = null) {
        $cacheDir = \trailingslashit($this->getImageCacheDir() . $cacheType);

        if(\file_exists($cacheDir . $imageName)) {
            /**
             * Check if the file is older than 24 hrs
             * If so, time to renew it
             *
             * This is just in case our cronjob doesn't run for whetever reason
             */
            if(\time() - \filemtime($cacheDir . $imageName) > 24 * 3600) {
                \unlink($cacheDir . $imageName);

                $returnValue = false;
            } else {
                $returnValue = true;
            }
        } else {
            $returnValue = false;
        }

        return $returnValue;
    }

    /**
     * Cachng a remote image locally
     *
     * @param string $cacheType The subdirectory in the image cache filesystem
     * @param string $remoteImageUrl The URL for the remote image
     */
    public function cacheRemoteImageFile($cacheType = '', $remoteImageUrl = null) {
        $returnValue = false;
        $cacheDir = \trailingslashit($this->getImageCacheDir() . $cacheType);
        $explodedImageUrl = \explode('/', $remoteImageUrl);
        $imageFilename = \end($explodedImageUrl);
        $explodedImageFilename = \explode('.', $imageFilename);
        $extension = \end($explodedImageFilename);

        // make sure its an image
        if($extension === 'gif' || $extension === 'jpg' || $extension === 'jpeg' || $extension === 'png') {
            // get the remote image
            $imageToFetch = RemoteHelper::getInstance()->getRemoteData($remoteImageUrl);

            $wpFileSystem = new \WP_Filesystem_Direct(null);

            if($wpFileSystem->put_contents($cacheDir . $imageFilename, $imageToFetch, 0755)) {
                $returnValue = ImageHelper::getInstance()->compressImage($cacheDir . $imageFilename);
            }
        }

        return $returnValue;
    }

    /**
     * Getting transient cache information / data
     *
     * @param string $route
     * @return mixed
     */
    public function getKillboardCache($route) {
        $data = $this->databaseHelper->getCachedKillboardDataFromDb($route);

        return $data;
    }

    /**
     * Setting the transient cache
     *
     * @param string $route
     * @param string $value
     * @param int $validUntil
     * @param boolean $returnData
     * @return mixed
     */
    public function setKillboardCache($route, $value, $validUntil, $returnData = false) {
        $returnValue = $this->databaseHelper->writeKillboardCacheDataToDb($route, $value, $validUntil, $returnData);

        if($returnData === true) {
            return $returnValue;
        }
    }

    public function getEsiCache($esiRoute) {
        return $this->databaseHelper->getCachedEsiDataFromDb($esiRoute);
    }

    public function setEsiCache(array $data) {
        return $this->databaseHelper->writeEsiCacheDataToDb($data);
    }
}
