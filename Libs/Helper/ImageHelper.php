<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class ImageHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
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

	/**
	 * Compressing an image
	 *
	 * @param string $source the image source
	 * @param string $destination the path where to save the image
	 * @param int $quality Image quality in a range from 0 to 100 (default 75)
	 * @return string
	 */
	public function compressImage($source, $destination = null, $quality = 75) {
		$returnValue = false;

		/**
		 * In this case, we optimiza an already saved image ....
		 */
		if($destination === null) {
			$destination = $source;
		} // END if($destination === null)

		$info = \getimagesize($source);

		switch($info['mime']) {
			case 'image/jpeg':
				$image = \imagecreatefromjpeg($source);

				/**
				 * compressing the stuff
				 *
				 * ranges from 0 (worst quality, smaller file)
				 * to 100 (best quality, biggest file).
				 * The default is the default IJG quality value (about 75).
				 */
				$returnValue = \imagejpeg($image, $destination, $quality);
				break;

			case 'image/png':
				$image = \imagecreatefrompng($source);

				\imageAlphaBlending($image, true);
				\imageSaveAlpha($image, true);

				/**
				 * chang to png qulity
				 *
				 * Compression level: from 0 (no compression) to 9.
				 */
				$pngQuality = 9 - (($quality * 9 ) / 100 );

				$returnValue = \imagePng($image, $destination, $pngQuality);
				break;
		} // END switch($info['mime'])

		\imagedestroy($image);

		// return destination file
		return $returnValue;
	} // END public function compressImage($sourceUrl, $destinationUrl, $quality)
} // END class ImageHelper