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

/**
 * EVE API Helper
 *
 * Getting some stuff from CCP's EVE API
 */

namespace WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class EveApiHelper extends \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
    /**
     * ESI URL
     *
     * @var string
     */
    private $esiUrl = null;

    /**
     * Image Server URL
     *
     * @var string
     */
    private $imageserverUrl = null;

    /**
     * Image Server Endpoints
     *
     * @var array
     */
    private $imageserverEndpoints = null;

    /**
     * cacheHelper
     *
     * @var CacheHelper
     */
    protected $cacheHelper = null;

    /**
     * esiKillmails
     *
     * @var \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository\KillmailsRepository
     */
    protected $esiKillmails = null;

    /**
     * esiCharacter
     *
     * @var \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository\CharacterRepository
     */
    protected $esiCharacter = null;

    /**
     * esiCorporation
     *
     * @var \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository\CorporationRepository
     */
    protected $esiCorporation = null;

    /**
     * esiAlliance
     *
     * @var \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository\AllianceRepository
     */
    protected $esiAlliance = null;

    /**
     * esiUniverse
     *
     * @var \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository\UniverseRepository
     */
    protected $esiUniverse = null;

    /**
     * The Constructor
     */
    protected function __construct() {
        parent::__construct();

        $this->esiUrl = 'https://esi.evetech.net/latest/';
        $this->imageserverUrl = 'https://imageserver.eveonline.com/';
        $this->cacheHelper = CacheHelper::getInstance();

        $this->esiKillmails = new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository\KillmailsRepository;
        $this->esiCharacter = new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository\CharacterRepository;
        $this->esiCorporation = new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository\CorporationRepository;
        $this->esiAlliance = new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository\AllianceRepository;
        $this->esiUniverse = new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository\UniverseRepository;

        /**
         * Assigning Imagesever Endpoints
         */
        $this->imageserverEndpoints = [
            'alliance' => 'Alliance/',
            'corporation' => 'Corporation/',
            'character' => 'Character/',
            'item' => 'Type/',
            'ship' => 'Type/',
            'render' => 'Render/',
            'inventory' => 'InventoryType/' // Ships and all the other stuff
        ];
    }

    /**
     * Returning the url to CCP's image server
     *
     * @return string
     */
    public function getImageServerUrl() {
        return $this->imageserverUrl;
    }

    public function getImageServerEndpont($route) {
        return $this->imageserverEndpoints[$route];
    }

    public function getCharacterDataByCharacterId($characterID) {
        $characterData = $this->cacheHelper->getTransientCache('eve_killboard_widget_character_data_' . $characterID);

        if($characterData === false || empty($characterData)) {
            $characterData = $this->esiCharacter->charactersCharacterId($characterID);

            $this->cacheHelper->setTransientCache('eve_killboard_widget_character_data_' . $characterID, $characterData, \strtotime('+12 hours'));
        }

        return [
            'data' => (\gettype($characterData) === 'string') ? \json_decode($characterData) : $characterData
        ];
    }

    public function getCorporationDataByCorporationId($corporationID) {
        $corporationData = $this->cacheHelper->getTransientCache('eve_killboard_widget_corporation_data_' . $corporationID);

        if($corporationData === false || empty($corporationData)) {
            $corporationData = $this->esiCorporation->corporationsCorporationId($corporationID);

            $this->cacheHelper->setTransientCache('eve_killboard_widget_corporation_data_' . $corporationID, $corporationData, \strtotime('+12 hours'));
        }

        return [
            'data' => (\gettype($corporationData) === 'string') ? \json_decode($corporationData) : $corporationData
        ];
    }

    public function getAllianceDataByAllianceId($allianceID) {
        $transientName = \sanitize_title('ESI :: alliances/{alliance_id}/' . $allianceID);
        $allianceData = $this->cacheHelper->getTransientCache($transientName);

        if($allianceData === false || empty($allianceData)) {
            $allianceData = $this->esiAlliance->alliancesAllianceId($allianceID);

            $this->cacheHelper->setTransientCache($transientName, $allianceData, \strtotime('+1 hour'));
        }

        return $allianceData;
    }

    /**
     * Getting all the needed ship information from the ESI
     *
     * @param int $shipID
     * @return array
     */
    public function getShipDataByShipId($shipID) {
        $shipData = $this->cacheHelper->getTransientCache('eve_killboard_widget_ship_data_' . $shipID);

        if($shipData === false || empty($shipData)) {
            $shipData = $this->esiUniverse->universeTypesTypeId($shipID);

            $this->cacheHelper->setTransientCache('eve_killboard_widget_ship_data_' . $shipID, $shipData, \strtotime('+12 years'));
        }

        return [
            'data' => (\gettype($shipData) === 'string') ? \json_decode($shipData) : $shipData
        ];
    }

    /**
     * Getting all the needed system information from the ESI
     *
     * @param int $systemID
     * @return array
     */
    public function getSystemDataBySystemId($systemID) {
        $systemData = $this->cacheHelper->getTransientCache('eve_killboard_widget_system_data_' . $systemID);

        if($systemData === false || empty($systemData)) {
            $systemData = $this->esiUniverse->universeSystemsSystemId($systemID);

            $this->cacheHelper->setTransientCache('eve_killboard_widget_system_data_' . $systemID, $systemData, \strtotime('+12 years'));
        }

        return [
            'data' => (\gettype($systemData) === 'string') ? \json_decode($systemData) : $systemData
        ];
    }

    /**
     * Get the ship image by ship ID
     *
     * @param int $shipTypeID
     * @param string $shiptype
     * @param boolean $imageOnly
     * @param int $size
     * @return string
     */
    public function getShipImageByShipId($shipTypeID, $imageOnly = true, $size = 128) {
        $ship = $this->getShipDataByShipId($shipTypeID);

        $imagePath = $this->imageserverUrl . $this->imageserverEndpoints['ship'] . $shipTypeID . '_' . $size. '.png';

        if($imageOnly === true) {
            return $imagePath;
        }

        $html = '<img src="' . $imagePath . '" class="eve-character-image eve-ship-id-' . $shipTypeID . '" alt="' . \esc_html($ship['data']->name) . '" data-title="' . \esc_html($ship['data']->name) . '" data-toggle="eve-killboard-tooltip">';

        return $html;
    }

    /**
     * Get the EVE ID by it's name
     *
     * @param type $name
     * @param type $type
     * @return type
     */
    public function getEveIdByName($name, $type) {
        $returnData = null;

        $esiData = $this->esiUniverse->universeIds([(string) \esc_html($name)]);

        /**
         * make sure we have an object
         */
        if(\gettype($esiData) === 'string') {
            $esiData = \json_decode($esiData);
        }

        switch($type) {
            case 'alliance':
                foreach($esiData->alliances as $alliance) {
                    if($alliance->name === (string) \esc_html($name)) {
                        $returnData = $alliance->id;
                    }
                }
                break;

            case 'corporation':
                foreach($esiData->corporations as $corporation) {
                    if($corporation->name === (string) \esc_html($name)) {
                        $returnData = $corporation->id;
                    }
                }
                break;

            case 'character':
                foreach($esiData->characters as $character) {
                    if($character->name === (string) \esc_html($name)) {
                        $returnData = $character->id;
                    }
                }
                break;
        }

        return $returnData;
    }

    /**
     * Get public killmails
     *
     * @param int $killmailID
     * @param string $killmailHash
     * @param boolean $cache
     * @return json
     */
    public function getPublicKillmail($killmailID, $killmailHash) {
        $killmailData = $this->esiKillmails->killmailsKillmailIdKillmailHash($killmailID, $killmailHash);

        return $killmailData;
    }
}
