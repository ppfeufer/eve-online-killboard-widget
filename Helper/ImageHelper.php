<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Helper;

\defined('ABSPATH') or die();

class ImageHelper {
	/**
	 * instance
	 *
	 * static variable to keep the current (and only!) instance of this class
	 *
	 * @var Singleton
	 */
	protected static $instance = null;

	public static function getInstance() {
		if(null === self::$instance) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * clone
	 *
	 * no cloning allowed
	 */
	protected function __clone() {
		;
	}

	/**
	 * constructor
	 *
	 * no external instanciation allowed
	 */
	protected function __construct() {
		;
	}

	/**
	 * Getting the cached URL for a remote image
	 *
	 * @param string $cacheType The subdirectory in the image cache filesystem
	 * @param string $remoteImageUrl The URL for the remote image
	 * @return string The cached Image URL
	 */
	public function getLocalCacheImageUriForRemoteImage($cacheType = null, $remoteImageUrl = null) {
		$returnValue = $remoteImageUrl;

		// Check if we should use image cache
		$explodedImageUrl = \explode('/', $remoteImageUrl);
		$imageFilename = \end($explodedImageUrl);
		$cachedImage = CacheHelper::getInstance()->getImageCacheUri() . $cacheType . '/' . $imageFilename;

		// if we don't have the image cached already
		if(CacheHelper::getInstance()->checkCachedImage($cacheType, $imageFilename) === false) {
			/**
			 * Check if the content dir is writable and cache the image.
			 * Otherwise set the remote image as return value.
			 */
			if(\is_dir(CacheHelper::getInstance()->getImageCacheDir() . $cacheType) && \is_writable(CacheHelper::getInstance()->getImageCacheDir() . $cacheType)) {
				if(CacheHelper::getInstance()->cacheRemoteImageFile($cacheType, $remoteImageUrl) === true) {
					$returnValue = $cachedImage;
				}
			} // END if(\is_dir(CacheHelper::getImageCacheDir() . $cacheType) && \is_writable(CacheHelper::getImageCacheDir() . $cacheType))
		} else {
			$returnValue = $cachedImage;
		} // END if(CacheHelper::checkCachedImage($cacheType, $imageName) === false)

		return $returnValue;
	} // END public static function getLocalCacheImageUri($cacheType = null, $remoteImageUrl = null)
} // END class ImageHelper
