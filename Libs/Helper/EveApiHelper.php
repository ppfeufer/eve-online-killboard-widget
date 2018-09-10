<?php
/**
 * EVE API Helper
 *
 * Getting some stuff from CCP's EVE API
 */

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class EveApiHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
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
     * The Constructor
     */
    protected function __construct() {
        parent::__construct();

        $this->esiUrl = 'https://esi.evetech.net/latest/';
        $this->imageserverUrl = 'https://imageserver.eveonline.com/';
        $this->cacheHelper = CacheHelper::getInstance();

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

    public function getCharacterData($characterID) {
        $characterData = $this->cacheHelper->getTransientCache('eve_killboard_widget_character_data_' . $characterID);

        if($characterData === false || empty($characterData)) {
            $characterApi = new \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Esi\Api\CharacterApi;
            $characterData = $characterApi->findById($characterID);

            $this->cacheHelper->setTransientCache('eve_killboard_widget_character_data_' . $characterID, $characterData);
        }

        return [
            'data' => $characterData
        ];
    }

    public function getCorporationData($corporationID) {
        $corporationData = $this->cacheHelper->getTransientCache('eve_killboard_widget_corporation_data_' . $corporationID);

        if($corporationData === false || empty($corporationData)) {
            $corporationApi = new \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Esi\Api\CorporationApi;
            $corporationData = $corporationApi->findById($corporationID);

            $this->cacheHelper->setTransientCache('eve_killboard_widget_corporation_data_' . $corporationID, $corporationData);
        }

        return [
            'data' => $corporationData
        ];
    }

    public function getAllianceData($allianceID) {
        $allianceData = $this->cacheHelper->getTransientCache('eve_killboard_widget_alliance_data_' . $allianceID);

        if($allianceData === false || empty($allianceData)) {
            $allianceApi = new \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Esi\Api\AllianceApi;
            $allianceData = $allianceApi->findById($allianceID);

            $this->cacheHelper->setTransientCache('eve_killboard_widget_alliance_data_' . $allianceID, $allianceData, 999999);
        }

        return [
            'data' => $allianceData
        ];
    }

    /**
     * Getting all the needed ship information from the ESI
     *
     * @param int $shipID
     * @return array
     */
    public function getShipData($shipID) {
        $shipData = $this->cacheHelper->getTransientCache('eve_killboard_widget_ship_data_' . $shipID);

        if($shipData === false || empty($shipData)) {
            $universeApi = new \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Esi\Api\UniverseApi;
            $shipData = $universeApi->findTypeById($shipID);

            $this->cacheHelper->setTransientCache('eve_killboard_widget_ship_data_' . $shipID, $shipData, 999999);
        }

        return [
            'data' => $shipData
        ];
    }

    /**
     * Getting all the needed system information from the ESI
     *
     * @param int $systemID
     * @return array
     */
    public function getSystemData($systemID) {
        $systemData = $this->cacheHelper->getTransientCache('eve_killboard_widget_system_data_' . $systemID);

        if($systemData === false || empty($systemData)) {
            $universeApi = new \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Esi\Api\UniverseApi;
            $systemData = $universeApi->findSystemById($systemID);

            $this->cacheHelper->setTransientCache('eve_killboard_widget_system_data_' . $systemID, $systemData, 999999);
        }

        return [
            'data' => $systemData
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
    public function getShipImageById($shipTypeID, $imageOnly = true, $size = 128) {
        $ship = $this->getShipData($shipTypeID);

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
    public function getEveIdFromName($name, $type) {
        $returnData = null;

        $universeApi = new \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Esi\Api\UniverseApi;
        $esiData = $universeApi->getIdFromName([(string) \esc_html($name)]);

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
}
