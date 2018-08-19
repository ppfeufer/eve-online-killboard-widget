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
     * ESI Endpoints
     *
     * @var array
     */
    private $esiEndpoints = null;

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
     * The Constructor
     */
    protected function __construct() {
        parent::__construct();

        $this->esiUrl = 'https://esi.evetech.net/latest/';
        $this->imageserverUrl = 'https://imageserver.eveonline.com/';

        /**
         * Assigning ESI Endpoints
         *
         * @see https://esi.evetech.net/latest/
         */
        $this->esiEndpoints = [
            'alliance-information' => 'alliances/', // getting alliance information by ID - https://esi.evetech.net/latest/alliances/99000102/
            'character-information' => 'characters/', // getting character information by ID - https://esi.evetech.net/latest/characters/90607580/
            'corporation-information' => 'corporations/', // getting corporation information by ID - https://esi.evetech.net/latest/corporations/98000030/
            'search' => 'search/', // Search for entities that match a given sub-string. - https://esi.evetech.net/latest/search/?search=Yulai%20Federation&strict=true&categories=alliance
            'system-information' => 'universe/systems/', // getting system information by ID - https://esi.evetech.net/latest/universe/systems/30000003/
            'type-information' => 'universe/types/', // getting types information by ID - https://esi.evetech.net/latest/universe/types/670/
        ];

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
        $characterData = $this->getEsiData($this->esiEndpoints['character-information'] . $characterID . '/');

        return [
            'data' => $characterData
        ];
    }

    public function getCorporationData($corporationID) {
        $corporationData = $this->getEsiData($this->esiEndpoints['corporation-information'] . $corporationID . '/');

        return [
            'data' => $corporationData
        ];
    }

    public function getAllianceData($allianceID) {
        $allianceData = $this->getEsiData($this->esiEndpoints['alliance-information'] . $allianceID . '/', 3600);

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
        $shipData = $this->getEsiData($this->esiEndpoints['type-information'] . $shipID . '/', 3600);

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
        $systemData = $this->getEsiData($this->esiEndpoints['system-information'] . $systemID . '/', 3600);

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

        $imagePath = ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('ship', $this->imageserverUrl . $this->imageserverEndpoints['ship'] . $shipTypeID . '_' . $size. '.png');

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

        $data = $this->getEsiData($this->esiEndpoints['search'] . '?search=' . \urlencode(\wp_specialchars_decode($name, \ENT_QUOTES)) . '&strict=true&categories=' . $type, 3600);

        if(!isset($data->error) && !empty($data)) {
            /**
             * -= FIX =-
             * CCPs strict mode is not really strict, so we have to check manually ....
             * Please CCP, get your shit sorted ...
             */
            foreach($data->{$type} as $entityID) {
                switch($type) {
                    case 'character':
                        $characterSheet = $this->getCharacterData($entityID);

                        if($this->isValidEsiData($characterSheet) === true && \strtolower($characterSheet['data']->name) === \strtolower($name)) {
                            $returnData = $entityID;
                            break;
                        }
                        break;

                    case 'corporation':
                        $corporationSheet = $this->getCorporationData($entityID);

                        if($this->isValidEsiData($corporationSheet) === true && \strtolower($corporationSheet['data']->name) === \strtolower($name)) {
                            $returnData = $entityID;
                            break;
                        }
                        break;

                    case 'alliance':
                        $allianceSheet = $this->getAllianceData($entityID);

                        if($this->isValidEsiData($allianceSheet) === true && \strtolower($allianceSheet['data']->name) === \strtolower($name)) {
                            $returnData = $entityID;
                            break;
                        }
                        break;
                }
            }
        }

        return $returnData;
    }

    /**
     * Getting data from the ESI
     *
     * @param string $route
     * @param int $cacheTime Caching time in hours (Default: 120)
     * @return object
     */
    private function getEsiData($route, $cacheTime = 120) {
        $returnValue = null;
        $transientName = \sanitize_title('eve-esi-data_' . $route);
        $data = CacheHelper::getInstance()->getTransientCache($transientName);
//        echo '<pre>' . print_r($data, true) . '</pre>';
        $data = '';

        if($data === false || empty($data)) {
            $data = RemoteHelper::getInstance()->getRemoteData($this->esiUrl . $route);

            /**
             * setting the transient caches
             */
            if(!isset($data->error) && !empty($data)) {
                CacheHelper::getInstance()->setTransientCache($transientName, $data, $cacheTime);
            }
        }

        if(!empty($data) && !isset($data->error)) {
            $returnValue = $data;
        }

        return $returnValue;
    }

    /**
     * Check if we have valid ESI data or not
     *
     * @param array $esiData
     * @return boolean
     */
    public function isValidEsiData($esiData) {
        $returnValue = false;

        if(!\is_null($esiData) && isset($esiData['data']) && !\is_null($esiData['data']) && !isset($esiData['data']->error)) {
            $returnValue = true;
        }

        return $returnValue;
    }
}
