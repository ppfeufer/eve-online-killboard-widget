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
     * @var \WordPress\EsiClient\Repository\KillmailsRepository
     */
    protected $esiKillmails = null;

    /**
     * esiCharacter
     *
     * @var \WordPress\EsiClient\Repository\CharacterRepository
     */
    protected $esiCharacter = null;

    /**
     * esiCorporation
     *
     * @var \WordPress\EsiClient\Repository\CorporationRepository
     */
    protected $esiCorporation = null;

    /**
     * esiAlliance
     *
     * @var \WordPress\EsiClient\Repository\AllianceRepository
     */
    protected $esiAlliance = null;

    /**
     * esiUniverse
     *
     * @var \WordPress\EsiClient\Repository\UniverseRepository
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

        $this->esiKillmails = new \WordPress\EsiClient\Repository\KillmailsRepository;
        $this->esiCharacter = new \WordPress\EsiClient\Repository\CharacterRepository;
        $this->esiCorporation = new \WordPress\EsiClient\Repository\CorporationRepository;
        $this->esiAlliance = new \WordPress\EsiClient\Repository\AllianceRepository;
        $this->esiUniverse = new \WordPress\EsiClient\Repository\UniverseRepository;

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
        $cacheKey = 'characters/' . $characterID;
        $characterData = $this->cacheHelper->getEsiCache($cacheKey);

        if($characterData === false || empty($characterData)) {
            $characterData = $this->esiCharacter->charactersCharacterId($characterID);

            $this->cacheHelper->setEsiCache([
                $cacheKey,
                \maybe_serialize($characterData),
                \strtotime('+1 day')]
            );
        }

        return $characterData;
    }

    public function getCorporationDataByCorporationId($corporationID) {
        $cacheKey = 'corporations/' . $corporationID;
        $corporationData = $this->cacheHelper->getEsiCache($cacheKey);

        if($corporationData === false || empty($corporationData)) {
            $corporationData = $this->esiCorporation->corporationsCorporationId($corporationID);

            $this->cacheHelper->setEsiCache([
                $cacheKey,
                \maybe_serialize($corporationData),
                \strtotime('+1 week')
            ]);
        }

        return $corporationData;
    }

    public function getAllianceDataByAllianceId($allianceID) {
        $cacheKey = 'alliances/' . $allianceID;
        $allianceData = $this->cacheHelper->getEsiCache($cacheKey);

        if($allianceData === false || empty($allianceData)) {
            $allianceData = $this->esiAlliance->alliancesAllianceId($allianceID);

            $this->cacheHelper->setEsiCache([
                $cacheKey,
                \maybe_serialize($allianceData),
                \strtotime('+1 week')
            ]);
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
        $cacheKey = 'universe/types/' . $shipID;
        $shipData = $this->cacheHelper->getEsiCache($cacheKey);

        if($shipData === false || empty($shipData)) {
            $shipData = $this->esiUniverse->universeTypesTypeId($shipID);

            $this->cacheHelper->setEsiCache([
                $cacheKey,
                \maybe_serialize($shipData),
                \strtotime('+1 week')
            ]);
        }

        return $shipData;
    }

    /**
     * Getting all the needed system information from the ESI
     *
     * @param int $systemID
     * @return array
     */
    public function getSystemDataBySystemId($systemID) {
        $cacheKey = 'universe/systems/' . $systemID;
        $systemData = $this->cacheHelper->getEsiCache($cacheKey);

        if($systemData === false || empty($systemData)) {
            $systemData = $this->esiUniverse->universeSystemsSystemId($systemID);

            $this->cacheHelper->setEsiCache([
                $cacheKey,
                \maybe_serialize($systemData),
                \strtotime('+10 years')
            ]);
        }

        return $systemData;
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
        /* @var $ship \WordPress\EsiClient\Model\Universe\UniverseTypesTypeId */
        $ship = $this->getShipDataByShipId($shipTypeID);

        $imagePath = $this->imageserverUrl . $this->imageserverEndpoints['ship'] . $shipTypeID . '_' . $size. '.png';

        if($imageOnly === true) {
            return $imagePath;
        }

        $html = '<img src="' . $imagePath . '" class="eve-character-image eve-ship-id-' . $shipTypeID . '" alt="' . \esc_html($ship->getName()) . '" data-title="' . \esc_html($ship->getName()) . '" data-toggle="eve-killboard-tooltip">';

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

        /* @var $esiData \WordPress\EsiClient\Model\Universe\UniverseIds */
        $esiData = $this->esiUniverse->universeIds([(string) \esc_html($name)]);

        switch($type) {
            case 'alliance':
                foreach($esiData->getAlliances() as $alliance) {
                    /* @var $alliance \WordPress\EsiClient\Model\Universe\UniverseIds\Alliances */
                    if($alliance->getName() === (string) \esc_html($name)) {
                        $returnData = $alliance->getId();
                    }
                }
                break;

            case 'corporation':
                foreach($esiData->getCorporations() as $corporation) {
                    /* @var $corporation \WordPress\EsiClient\Model\Universe\UniverseIds\Corporations */
                    if($corporation->getName() === (string) \esc_html($name)) {
                        $returnData = $corporation->getId();
                    }
                }
                break;

            case 'character':
                foreach($esiData->getCharacters() as $character) {
                    /* @var $character \WordPress\EsiClient\Model\Universe\UniverseIds\Characters */
                    if($character->getName() === (string) \esc_html($name)) {
                        $returnData = $character->getId();
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
