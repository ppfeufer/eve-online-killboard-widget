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

namespace WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class ImageHelper extends \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
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
            }
        } else {
            $returnValue = $cachedImage;
        }

        return $returnValue;
    }

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
        }

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
        }

        \imagedestroy($image);

        // return destination file
        return $returnValue;
    }
}
