<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Helper;

\defined('ABSPATH') or die();

/**
 * WP Filesystem API
 */
require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php');
require_once(ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php');

class CacheHelper {
	private static $instance = null;
	private $cacheDirectoryBase;

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->cacheDirectoryBase = $this->getPluginCacheDir();

		$this->checkOrCreateCacheDirectories();
	} // END private function __construct()

	/**
	 * Getting the instance
	 *
	 * @return \WordPress\Plugin\EveOnlineKillboardWidget\Helper\CacheHelper
	 */
	public static function getInstance() {
		if(\is_null(self::$instance)) {
			self::$instance = new self();
		} // END if(\is_null(self::$instance))

		return self::$instance;
	} // END public static function getInstance()

	/**
	 * Check if cache directories exist, otherwise try to create them
	 */
	public function checkOrCreateCacheDirectories() {
		$this->createCacheDirectory();
		$this->createCacheDirectory('images');
		$this->createCacheDirectory('images/ship');
		$this->createCacheDirectory('images/character');
	} // END public function checkOrCreateCacheDirectories()

	/**
	 * Getting the absolute path for the cache directory
	 *
	 * @return string absolute path for the cache directory
	 */
	public function getPluginCacheDir() {
		return \trailingslashit(\WP_CONTENT_DIR) . 'cache/plugins/eve-online-killboard-widget/';
	} // END public static function getThemeCacheDir()

	/**
	 * Getting the URI for the cache directory
	 *
	 * @return string URI for the cache directory
	 */
	public function getPluginCacheUri() {
		return \trailingslashit(\WP_CONTENT_URL) . 'cache/plugins/eve-online-killboard-widget/';
	} // END public function getThemeCacheUri()

	/**
	 * Getting the local image cache directory
	 *
	 * @return string Local image cache directory
	 */
	public function getImageCacheDir() {
		return \trailingslashit($this->getPluginCacheDir() . 'images/');
	} // END public function getImageCacheDir()

	/**
	 * Getting the local image cache URI
	 *
	 * @return string Local image cache URI
	 */
	public function getImageCacheUri() {
		return \trailingslashit($this->getPluginCacheUri() . 'images/');
	} // END public static function getImageCacheUri()

	/**
	 * creating our needed cache directories under:
	 *		/wp-content/cache/plugin/«plugin-name»/
	 *
	 * @param string $directory The Directory to create
	 */
	public function createCacheDirectory($directory = '') {
		$wpFileSystem =  new \WP_Filesystem_Direct(null);

		if($wpFileSystem->is_writable($wpFileSystem->wp_content_dir())) {
			/**
			 * Fix for Windows Server since they are to stupid to create nested dirs
			 */
			if(\strtoupper(\substr(\PHP_OS, 0, 3)) === 'WIN') {
				if(!$wpFileSystem->is_dir(\trailingslashit($this->getPluginCacheDir()))) {
					$subdirs = \explode('/', \str_replace(\trailingslashit(\WP_CONTENT_DIR), '', $this->getPluginCacheDir()));

					$createDir = '';
					foreach($subdirs as $dir) {
						$createDir .= '/' . $dir;

						if(!$wpFileSystem->is_dir(\trailingslashit(\WP_CONTENT_DIR) . $createDir) && !empty($dir)) {
							$wpFileSystem->mkdir(\trailingslashit(\WP_CONTENT_DIR) . $createDir, 0755);

							if(!$wpFileSystem->is_file(\trailingslashit($this->getPluginCacheDir()) . $directory . '/index.php')) {
								$wpFileSystem->put_contents(
									\trailingslashit($this->getPluginCacheDir()) . $directory . '/index.php',
									'',
									FS_CHMOD_FILE // predefined mode settings for WP files
								);
							}
						} // END if(!$wpFileSystem->is_dir(\trailingslashit(\WP_CONTENT_DIR) . $createDir) && !empty($dir))
					} // END foreach($subdirs as $dir)
				} // END if(!$wpFileSystem->is_dir(\trailingslashit($this->getPluginCacheDir())))
			} // END if(strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')

			if(!$wpFileSystem->is_dir(\trailingslashit($this->getPluginCacheDir()) . $directory)) {
				$wpFileSystem->mkdir(\trailingslashit($this->getPluginCacheDir()) . $directory, 0755);

				if(!$wpFileSystem->is_file(\trailingslashit($this->getPluginCacheDir()) . $directory . '/index.php')) {
					$wpFileSystem->put_contents(
						\trailingslashit($this->getPluginCacheDir()) . $directory . '/index.php',
						'',
						FS_CHMOD_FILE // predefined mode settings for WP files
					);
				}
			} // END if(!$wpFileSystem->is_dir(\trailingslashit($this->getThemeCacheDir()) . $directory))
		} // END if($wpFileSystem->is_writable($wpFileSystem->wp_content_dir()))
	} // END public function createCacheDirectories()

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
			} // END if(\time() - \filemtime($cacheDir . $imageName) > 2 * 3600)
		} else {
			$returnValue = false;
		} // END if(\file_exists($cacheDir . $imageName))

		return $returnValue;
	} // END static function checkCachedImage($cacheType = null, $imageName = null)

	/**
	 * Cachng a remote image locally
	 *
	 * @param string $cacheType The subdirectory in the image cache filesystem
	 * @param string $remoteImageUrl The URL for the remote image
	 */
	public function cacheRemoteImageFile($cacheType = '', $remoteImageUrl = null) {
		$cacheDir = \trailingslashit($this->getImageCacheDir() . $cacheType);
		$explodedImageUrl = \explode('/', $remoteImageUrl);
		$imageFilename = \end($explodedImageUrl);
		$explodedImageFilename = \explode('.', $imageFilename);
		$extension = \end($explodedImageFilename);

		// make sure its an image
		if($extension === 'gif' || $extension === 'jpg' || $extension === 'jpeg' || $extension === 'png') {
			// get the remote image
			$imageToFetch = PluginHelper::getInstance()->getRemoteData($remoteImageUrl);

			$wpFileSystem = new \WP_Filesystem_Direct(null);

			return $wpFileSystem->put_contents($cacheDir . $imageFilename, $imageToFetch, 0755);
		} // END if($extension === 'gif' || $extension === 'jpg' || $extension === 'jpeg' || $extension === 'png')
	} // END public function cacheRemoteImageFile($cacheType = null, $remoteImageUrl = null)

	/**
	 * Getting transient cache information / data
	 *
	 * @param string $transientName
	 * @return mixed
	 */
	public function checkTransientCache($transientName) {
		$data = \get_transient($transientName);

		return $data;
	} // END public function checkApiCache($transientName)

	/**
	 * Setting the transient cahe
	 *
	 * @param string $transientName cache name
	 * @param mixed $data the data that is needed to be cached
	 * @param type $time cache time in hours (default: 2)
	 */
	public function setTransientCache($transientName, $data, $time = 2) {
		\set_transient($transientName, $data, $time * \HOUR_IN_SECONDS);
	} // END public function setApiCache($transientName, $data)
} // END class CacheHelper
