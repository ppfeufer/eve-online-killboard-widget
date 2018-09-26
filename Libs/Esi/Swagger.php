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
    protected $esiUrl = 'https://esi.evetech.net/';

    /**
     * ESI Version
     *
     * @var string
     */
    protected $esiVersion = 'latest/';

    /**
     * ESI method
     *
     * @var string
     */
    protected $esiMethod = 'get';

    /**
     * ESI route
     *
     * @var string
     */
    protected $esiRoute = null;

    /**
     * ESI route parameter
     *
     * @var array
     */
    protected $esiRouteParameter = [];

    /**
     * ESI POST parameter
     *
     * @var array
     */
    protected $esiPostParameter = [];

    /**
     * Remote Helper
     *
     * @var \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\RemoteHelper
     */
    protected $remoteHelper = null;

    /**
     * Constructor
     */
    public function __construct() {
        $this->remoteHelper = \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\RemoteHelper::getInstance();
    }

    /**
     * Call ESI
     *
     * @return stdClass Object
     */
    public function callEsi() {
        $esiUrl = \trailingslashit($this->getEsiUrl() . $this->getEsiVersion());
        $esiRoute = $this->getEsiRoute();

        if(\count($this->getEsiRouteParameter()) > 0) {
            $esiRoute = \preg_replace(\array_keys($this->getEsiRouteParameter()), \array_values($this->getEsiRouteParameter()), $this->getEsiRoute());
        }

        $callUrl = $esiUrl . $esiRoute;

        switch($this->getEsiMethod()) {
            case 'get':
                $data = $this->remoteHelper->getRemoteData($callUrl);
                break;

            case 'post':
                $data = $this->remoteHelper->getRemoteData($callUrl, $this->getEsiMethod(), $this->getEsiPostParameter());
                break;
        }

        return $data;
    }

    /**
     * getEsiMethod
     *
     * @return string
     */
    public function getEsiMethod() {
        return $this->esiMethod;
    }

    /**
     * setEsiMethod
     *
     * @param string $esiMethod
     */
    public function setEsiMethod($esiMethod) {
        $this->esiMethod = $esiMethod;
    }

    /**
     * getEsiPostParameter
     *
     * @return array
     */
    public function getEsiPostParameter() {
        return $this->esiPostParameter;
    }

    /**
     * setEsiPostParameter
     *
     * @param array $esiPostParameter
     */
    public function setEsiPostParameter(array $esiPostParameter) {
        $this->esiPostParameter = $esiPostParameter;
    }

    /**
     * getEsiRoute
     *
     * @return string
     */
    public function getEsiRoute() {
        return $this->esiRoute;
    }

    /**
     * setEsiRoute
     *
     * @param string $esiRoute
     */
    public function setEsiRoute($esiRoute) {
        $this->esiRoute = $esiRoute;
    }

    /**
     * getEsiRouteParameter
     *
     * @return array
     */
    public function getEsiRouteParameter() {
        return $this->esiRouteParameter;
    }

    /**
     * setEsiRouteParameter
     *
     * @param array $esiRouteParameter
     */
    public function setEsiRouteParameter(array $esiRouteParameter) {
        $this->esiRouteParameter = $esiRouteParameter;
    }

    /**
     * getEsiUrl
     *
     * @return string
     */
    public function getEsiUrl() {
        return $this->esiUrl;
    }

    /**
     * getEsiVersion
     *
     * @return string
     */
    public function getEsiVersion() {
        return $this->esiVersion;
    }

    /**
     * setEsiVersion
     *
     * @param string $esiVersion
     */
    public function setEsiVersion($esiVersion) {
        $this->esiVersion = $esiVersion;
    }
}
