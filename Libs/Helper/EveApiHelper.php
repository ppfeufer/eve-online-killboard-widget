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

		$this->esiUrl = 'https://esi.tech.ccp.is/latest/';
		$this->imageserverUrl = 'https://image.eveonline.com/';

		/**
		 * Assigning ESI Endpoints
		 *
		 * @see https://esi.tech.ccp.is/latest/
		 */
		$this->esiEndpoints = [
			'alliance-information' => 'alliances/', // getting alliance information by ID - https://esi.tech.ccp.is/latest/alliances/99000102/
			'character-information' => 'characters/', // getting character information by ID - https://esi.tech.ccp.is/latest/characters/90607580/
			'corporation-information' => 'corporations/', // getting corporation information by ID - https://esi.tech.ccp.is/latest/corporations/98000030/
			'search' => 'search/', // Search for entities that match a given sub-string. - https://esi.tech.ccp.is/latest/search/?search=Yulai%20Federation&strict=true&categories=alliance
			'system-information' => 'universe/systems/', // getting system information by ID - https://esi.tech.ccp.is/latest/universe/systems/30000003/
			'type-information' => 'universe/types/', // getting types information by ID - https://esi.tech.ccp.is/latest/universe/types/670/
		];

		/**
		 * Assigning Imagesever Endpoints
		 */
		$this->imageserverEndpoints = [
			'alliance' => 'Alliance/',
			'corporation' => 'Corporation/',
			'character' => 'Character/',
			'item' => 'Type/',
			'inventory' => 'InventoryType/' // Ships and all the other stuff
		];
	} // END public function __construct()

	/**
	 * Returning the url to CCP's image server
	 *
	 * @return string
	 */
	public function getImageServerUrl() {
		return $this->imageserverUrl;
	} // END public function getImageServerUrl()

	/**
	 * Getting all the needed ship information from the ESI
	 *
	 * @param int $shipID
	 * @return array
	 */
	public function getShipData($shipID) {
		$shipData = $this->getEsiData($this->esiEndpoints['type-information'] . $shipID . '/');

		return [
			'data' => $shipData
		];
	} // END public function getShipData($shipID)

	/**
	 * Getting all the needed system information from the ESI
	 *
	 * @param int $systemID
	 * @return array
	 */
	public function getSystemData($systemID) {
		$systemData = $this->getEsiData($this->esiEndpoints['system-information'] . $systemID . '/');

		return [
			'data' => $systemData
		];
	} // END public function getSystemData($systemID)

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

		$imagePath = ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('ship', $this->imageserverUrl . $this->imageserverEndpoints['inventory'] . $shipTypeID . '_' . $size. '.png');

		if($imageOnly === true) {
			return $imagePath;
		} // END if($imageOnly === true)

		$html = '<img src="' . $imagePath . '" class="eve-character-image eve-ship-id-' . $shipTypeID . '" alt="' . \esc_html($ship['data']->name) . '" data-title="' . \esc_html($ship['data']->name) . '" data-toggle="eve-killboard-tooltip">';

		return $html;
	} // END public function getCorporationImageById($corporationID, $imageOnly = true, $size = 128)

	/**
	 * Get the EVE ID by it's name
	 *
	 * @param type $name
	 * @param type $type
	 * @return type
	 */
	public function getEveIdFromName($name, $type) {
		$returnData = null;

		$data = $this->getEsiData($this->esiEndpoints['search'] . '?search=' . \urlencode(\wp_specialchars_decode($name, \ENT_QUOTES)) . '&strict=true&categories=' . $type);

		if(isset($data->{$type}['0'])) {
			$returnData = $data->{$type}['0'];
		} // END if(isset($data->{$type}['0']))

		return $returnData;
	} // END public function getEveIdFromName($name, $type)

	/**
	 * Getting data from the ESI
	 *
	 * @param string $route
	 * @return object
	 */
	private function getEsiData($route) {
		$returnValue = null;
		$transientName = \sanitize_title('eve-online-killboard-widget-data_' . $route);
		$data = CacheHelper::getInstance()->getTransientCache($transientName);

		if($data === false) {
			$data = RemoteHelper::getInstance()->getRemoteData($this->esiUrl . $route);

			/**
			 * setting the transient caches
			 */
			CacheHelper::getInstance()->setTransientCache($transientName, $data, 2);
		} // END if($data === false)

		if(!empty($data)) {
			$returnValue = \json_decode($data);
		} // END if(!empty($data))

		return $returnValue;
	} // END private function getEsiData($route)
} // END class EveApi
