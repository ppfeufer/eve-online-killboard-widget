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

class RemoteHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
    protected $userAgent = null;

    /**
     * Getting data from a remote source
     *
     * @param string $url
     * @param array $parameter
     * @return mixed
     */
    public function getRemoteData($url, $method = 'get', $parameter = []) {
        $returnValue = null;
        $params = '';

        switch($method) {
            case 'get':
                if(\count($parameter) > 0) {
                    $params = '?' . \http_build_query($parameter);
                }

                $remoteData = \wp_remote_get($url . $params, [
                    'timeout' => 60,
                    'user-agent' => $this->getUserAgent(),
                    'headers' => [
                        'Accept-Encoding' => 'gzip',
                        'User-Agent' => $this->getUserAgent()
                    ]
                ]);
                break;

            case 'post':
                $remoteData = \wp_remote_post($url, [
                    'user-agent' => $this->getUserAgent(),
                    'headers' => [
                        'Content-Type' => 'application/json; charset=utf-8'
                    ],
                    'body' => \json_encode($parameter),
                    'method' => 'POST'
                ]);
                break;
        }

        if(\wp_remote_retrieve_response_code($remoteData) === 200) {
            $returnValue = \wp_remote_retrieve_body($remoteData);
        }

        return $returnValue;
    }

    /**
     * Getting the userAgant
     *
     * @return string
     */
    public function getUserAgent() {
        return $this->userAgent;
    }

    /**
     * Setting the userAgent
     *
     * @param string $userAgent
     */
    public function setUserAgent($userAgent) {
        $this->userAgent = $userAgent;
    }
}
