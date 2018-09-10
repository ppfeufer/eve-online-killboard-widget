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

/**
 * WP Filesystem API
 */
require_once(\ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
require_once(\ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');

class CacheHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
    /**
     * The base directoy of our cache
     *
     * @var string
     */
    private $cacheDirectoryBase;

    /**
     * Constructor
     */
    protected function __construct() {
        parent::__construct();

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
     * @param string $transientName
     * @return mixed
     */
    public function getTransientCache($transientName) {
        $data = \get_transient($transientName);

        return $data;
    }

    /**
     * Setting the transient cache
     *
     * @param string $transientName cache name
     * @param mixed $data the data that is needed to be cached
     * @param type $time cache time in hours (default: 2)
     */
    public function setTransientCache($transientName, $data, $time = 2) {
        \set_transient($transientName, $data, $time * \HOUR_IN_SECONDS);
    }
}
