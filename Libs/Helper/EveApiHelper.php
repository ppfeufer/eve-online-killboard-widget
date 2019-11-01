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

use \WordPress\EsiClient\Model\Killmails\KillmailId\KillmailHash;
use \WordPress\EsiClient\Model\Universe\Groups\GroupId;
use \WordPress\EsiClient\Model\Universe\Ids\Alliances;
use \WordPress\EsiClient\Model\Universe\Ids\Characters;
use \WordPress\EsiClient\Model\Universe\Ids\Corporations;
use \WordPress\EsiClient\Model\Universe\Systems\SystemId;
use \WordPress\EsiClient\Model\Universe\Types\TypeId;
use \WordPress\EsiClient\Model\Universe\UniverseIds;
use \WordPress\EsiClient\Repository\AllianceRepository;
use \WordPress\EsiClient\Repository\CharacterRepository;
use \WordPress\EsiClient\Repository\CorporationRepository;
use \WordPress\EsiClient\Repository\KillmailsRepository;
use \WordPress\EsiClient\Repository\UniverseRepository;
use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton;

\defined('ABSPATH') or die();

class EveApiHelper extends AbstractSingleton {
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
     * @var KillmailsRepository
     */
    protected $esiKillmails = null;

    /**
     * esiCharacter
     *
     * @var CharacterRepository
     */
    protected $esiCharacter = null;

    /**
     * esiCorporation
     *
     * @var CorporationRepository
     */
    protected $esiCorporation = null;

    /**
     * esiAlliance
     *
     * @var AllianceRepository
     */
    protected $esiAlliance = null;

    /**
     * esiUniverse
     *
     * @var UniverseRepository
     */
    protected $esiUniverse = null;

    /**
     * The Constructor
     */
    protected function __construct() {
        parent::__construct();

        $this->esiUrl = 'https://esi.evetech.net/latest/';
        $this->imageserverUrl = 'https://images.evetech.net/';
        $this->cacheHelper = CacheHelper::getInstance();

        $this->esiKillmails = new KillmailsRepository;
        $this->esiCharacter = new CharacterRepository;
        $this->esiCorporation = new CorporationRepository;
        $this->esiAlliance = new AllianceRepository;
        $this->esiUniverse = new UniverseRepository;

        /**
         * Assigning Imagesever Endpoints
         */
        $this->imageserverEndpoints = [
            'alliance' => 'alliances/%d/logo',
            'corporation' => 'corporations/%d/logo',
            'character' => 'characters/%d/portrait',
            'typeIcon' => 'types/%d/icon',
            'typeRender' => 'types/%d/render'
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

        if(\is_null($characterData)) {
            $characterData = $this->esiCharacter->charactersCharacterId($characterID);

            if(\is_a($characterData, '\WordPress\EsiClient\Model\Character\CharacterId')) {
                $this->cacheHelper->setEsiCache([
                    $cacheKey,
                    \maybe_serialize($characterData),
                    \strtotime('+1 day')]
                );
            }
        }

        return $characterData;
    }

    public function getCorporationDataByCorporationId($corporationID) {
        $cacheKey = 'corporations/' . $corporationID;
        $corporationData = $this->cacheHelper->getEsiCache($cacheKey);

        if(\is_null($corporationData)) {
            $corporationData = $this->esiCorporation->corporationsCorporationId($corporationID);

            if(\is_a($corporationData, '\WordPress\EsiClient\Model\Corporations\CorporationId')) {
                $this->cacheHelper->setEsiCache([
                    $cacheKey,
                    \maybe_serialize($corporationData),
                    \strtotime('+1 week')
                ]);
            }
        }

        return $corporationData;
    }

    public function getAllianceDataByAllianceId($allianceID) {
        $cacheKey = 'alliances/' . $allianceID;
        $allianceData = $this->cacheHelper->getEsiCache($cacheKey);

        if(\is_null($allianceData)) {
            $allianceData = $this->esiAlliance->alliancesAllianceId($allianceID);

            if(\is_a($allianceData, '\WordPress\EsiClient\Model\Alliances\Alliance\AllianceId')) {
                $this->cacheHelper->setEsiCache([
                    $cacheKey,
                    \maybe_serialize($allianceData),
                    \strtotime('+1 week')
                ]);
            }
        }

        return $allianceData;
    }

    /**
     * Getting all the needed ship information from the ESI
     *
     * @param int $shipID
     * @return TypeId
     */
    public function getShipDataByShipId($shipID) {
        $cacheKey = 'universe/types/' . $shipID;
        $shipData = $this->cacheHelper->getEsiCache($cacheKey);

        if(\is_null($shipData)) {
            $shipData = $this->esiUniverse->universeTypesTypeId($shipID);

            if(\is_a($shipData, '\WordPress\EsiClient\Model\Universe\Types\TypeId')) {
                $this->cacheHelper->setEsiCache([
                    $cacheKey,
                    \maybe_serialize($shipData),
                    \strtotime('+1 week')
                ]);
            }
        }

        return $shipData;
    }

    /**
     * Getting all the ship type information from a ship ID
     *
     * @param int $shipID
     * @return GroupId
     */
    public function getShipTypeFromShipId($shipID) {
        $returnValue = null;

        $shipClassData = $this->getShipDataByShipId($shipID);

        if(!\is_null($shipClassData)) {
            $cacheKey = 'universe/groups/' . $shipClassData->getGroupId();
            $returnValue = $this->cacheHelper->getEsiCache($cacheKey);

            if(\is_null($returnValue)) {
                $returnValue = $this->esiUniverse->universeGroupsGroupId($shipClassData->getGroupId());

                if(\is_a($returnValue, '\WordPress\EsiClient\Model\Universe\Groups\GroupId')) {
                    $this->cacheHelper->setEsiCache([
                        $cacheKey,
                        \maybe_serialize($returnValue),
                        \strtotime('+1 week')
                    ]);
                }
            }
        }

        return $returnValue;
    }

    /**
     * Getting all the needed system information from the ESI
     *
     * @param int $systemID
     * @return SystemId
     */
    public function getSystemDataBySystemId($systemID) {
        $cacheKey = 'universe/systems/' . $systemID;
        $systemData = $this->cacheHelper->getEsiCache($cacheKey);

        if(\is_null($systemData)) {
            $systemData = $this->esiUniverse->universeSystemsSystemId($systemID);

            if(\is_a($systemData, '\WordPress\EsiClient\Model\Universe\Systems\SystemId')) {
                $this->cacheHelper->setEsiCache([
                    $cacheKey,
                    \maybe_serialize($systemData),
                    \strtotime('+10 years')
                ]);
            }
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
        /* @var $ship TypeId */
        $ship = $this->getShipDataByShipId($shipTypeID);
        $imagePath = \sprintf(
            $this->imageserverUrl . $this->imageserverEndpoints['typeRender'] . '?size=' . $size,
            $shipTypeID
        );

        if($imageOnly === true) {
            return $imagePath;
        }

        $html = '<img src="' . $imagePath . '" class="eve-character-image eve-ship-id-' . $shipTypeID . '" alt="' . \esc_html($ship->getName()) . '" data-title="' . \esc_html($ship->getName()) . '" data-toggle="eve-killboard-tooltip">';

        return $html;
    }

    /**
     * Get the EVE ID by its name
     *
     * @param string $name
     * @param string $type
     * @return object
     */
    public function getEveIdByName(string $name, string $type) {
        $returnData = null;

        /* @var $esiData UniverseIds */
        $esiData = $this->esiUniverse->universeIds([(string) \esc_html($name)]);

        if(\is_a($esiData, '\WordPress\EsiClient\Model\Universe\Ids')) {
            switch($type) {
                case 'alliance':
                    foreach($esiData->getAlliances() as $alliance) {
                        /* @var $alliance Alliances */
                        if(\strtolower($alliance->getName()) === \strtolower((string) \esc_html($name))) {
                            $returnData = $alliance->getId();
                        }
                    }
                    break;

                case 'corporation':
                    foreach($esiData->getCorporations() as $corporation) {
                        /* @var $corporation Corporations */
                        if(\strtolower($corporation->getName()) === \strtolower((string) \esc_html($name))) {
                            $returnData = $corporation->getId();
                        }
                    }
                    break;

                case 'character':
                    foreach($esiData->getCharacters() as $character) {
                        /* @var $character Characters */
                        if(\strtolower($character->getName()) === \strtolower((string) \esc_html($name))) {
                            $returnData = $character->getId();
                        }
                    }
                    break;
            }
        }

        return $returnData;
    }

    /**
     * Get public killmails
     *
     * @param int $killmailID
     * @param string $killmailHash
     * @param boolean $cache
     * @return KillmailHash
     */
    public function getPublicKillmail(int $killmailID, string $killmailHash) {
        $returnData = null;
        $killmailData = $this->esiKillmails->killmailsKillmailIdKillmailHash($killmailID, $killmailHash);

        if(\is_a($killmailData, '\WordPress\EsiClient\Model\Killmails\KillmailId\KillmailHash')) {
            $returnData = $killmailData;
        }

        return $returnData;
    }
}
