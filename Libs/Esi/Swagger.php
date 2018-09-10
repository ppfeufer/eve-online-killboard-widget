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
namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Esi;

\defined('ABSPATH') or die();

class Swagger {
    /**
     * ESI URL
     *
     * @var string
     */
    public $esiUrl = 'https://esi.evetech.net/';

    /**
     * ESI Version
     *
     * @var string
     */
    public $esiVersion = 'latest/';

    /**
     * ESI route
     *
     * @var string
     */
    public $esiRoute = null;

    /**
     * Remote Helper
     *
     * @var \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\RemoteHelper
     */
    public $remoteHelper = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->remoteHelper = \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\RemoteHelper::getInstance();
    }

    /**
     * Call ESI
     *
     * @param string $method
     * @param array $parameter
     * @return stdClass Object
     */
    public function callEsi($method = 'get', $parameter = []) {
        $esiUrl = \trailingslashit($this->esiUrl . $this->esiVersion);
        $callUrl = $esiUrl . $this->esiRoute;

        switch($method) {
            case 'get':
                $data = $this->remoteHelper->getRemoteData($callUrl);
                break;

            case 'post':
                $data = $this->remoteHelper->getRemoteData($callUrl, $parameter, $method);
                break;
        } // switch($method)

        return $data;
    }
}
