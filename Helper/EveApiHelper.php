<?php
/**
 * EVE API Helper
 *
 * Getting some stuff from CCP's EVE API
 */

namespace WordPress\Plugin\EveOnlineKillboardWidget\Helper;

\defined('ABSPATH') or die();

class EveApiHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Singleton\AbstractSingleton {
	/**
	 * API URL
	 *
	 * @var string
	 */
	private $apiUrl = null;

	/**
	 * ESI URL
	 *
	 * @var string
	 */
	private $esiUrl = null;

	/**
	 * API Endpoints
	 *
	 * @var array
	 */
	private $apiEndpoints = null;

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
		$this->apiUrl = 'https://api.eveonline.com/';
		$this->imageserverUrl = 'https://image.eveonline.com/';

		/**
		 * Assigning API Endpoints
		 */
		$this->apiEndpoints = array(
			'eve.characterName' => 'eve/CharacterName.xml.aspx',
			'eve.owner' => 'eve/OwnerID.xml.aspx',
			'eve.typeName' => 'eve/TypeName.xml.aspx', // Returns the names associated with a sequence of typeIDs. ( http://eveonline-third-party-documentation.readthedocs.io/en/latest/xmlapi/eve/eve_typename.html )
		);

		/**
		 * Assigning API Endpoints
		 */
		$this->esiEndpoints = array(
			'corporation-information' => 'corporations/', // getting corporation information by ID - https://esi.tech.ccp.is/latest/corporations/98000030/
			'alliance-information' => 'alliances/', // getting alliance information by ID - https://esi.tech.ccp.is/latest/alliances/99000102/
			'character-information' => 'characters/', // getting character information by ID - https://esi.tech.ccp.is/latest/characters/90607580/
			'type-information' => 'universe/types/', // getting types information by ID - https://esi.tech.ccp.is/latest/universe/types/670/
			'system-information' => 'universe/systems/', // getting system information by ID - https://esi.tech.ccp.is/latest/universe/systems/30000003/
		);

		/**
		 * Assigning Imagesever Endpoints
		 */
		$this->imageserverEndpoints = array(
			'alliance' => 'Alliance/',
			'corporation' => 'Corporation/',
			'character' => 'Character/',
			'item' => 'Type/',
			'inventory' => 'InventoryType/' // Ships and all the other stuff
		);
	} // END public function __construct()

	public function getImageServerUrl() {
		return $this->imageserverUrl;
	} // END public function getImageServerUrl()

	/**
	 * Get a pilots image by his ID
	 *
	 * @param int $characterID
	 * @param string $characterName
	 * @param boolean $imageOnly
	 * @param int $size
	 * @return string
	 */
	public function getCharacterImageById($characterID, $imageOnly = true, $size = 128) {
		$character = $this->getCharacterData($characterID);
		$imagePath = ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('character', $this->imageserverUrl . $this->imageserverEndpoints['character'] . $characterID . '_' . $size. '.jpg');

		if($imageOnly === true) {
			return $imagePath;
		} // END if($imageOnly === true)

		$html = '<img src="' . $imagePath . '" class="eve-character-image eve-character-id-' . $characterID . '" alt="' . \esc_html($character['data']->name) . '">';

		return $html;
	} // END public function getCharacterImageById($name, $imageOnly = true, $size = 128)

	public function getCharacterData($characterID) {
		$characterData = $this->getEsiData($this->esiEndpoints['character-information'] . $characterID);
		$characterPortraits = $this->getEsiData($this->esiEndpoints['character-information'] . $characterID . '/portrait/');

		return [
			'data' => $characterData,
			'portrait' => $characterPortraits
		];
	}

	public function getCorpratinData($corporationID) {
		$corporationData = $this->getEsiData($this->esiEndpoints['corporation-information'] . $corporationID);
		$corporationLogos = $this->getEsiData($this->esiEndpoints['corporation-information'] . $corporationID . '/icons/');

		return [
			'data' => $corporationData,
			'logo' => $corporationLogos
		];
	}

	public function getAllianceData($allianceID) {
		$allianceData = $this->getEsiData($this->esiEndpoints['alliance-information'] . $allianceID);
//		$allianceCorporations = $this->getEsiData($this->esiEndpoints['alliance-information'] . $allianceID . '/corporations/');
		$allianceLogos = $this->getEsiData($this->esiEndpoints['alliance-information'] . $allianceID . '/icons/');

		return [
			'data' => $allianceData,
//			'corporations' => $allianceCorporations,
			'logo' => $allianceLogos
		];
	}

	public function getShipData($shipID) {
		$shipData = $this->getEsiData($this->esiEndpoints['type-information'] . $shipID);

		return [
			'data' => $shipData
		];
	}

	public function getSystemData($systemID) {
		$systemData = $this->getEsiData($this->esiEndpoints['system-information'] . $systemID);

		return [
			'data' => $systemData
		];
	}

	/**
	 * Get a corporation logo by corp ID
	 *
	 * @param int $corporationID
	 * @param string $corporationName Corp name will be passed into the image tag
	 * @param boolean $imageOnly
	 * @param size $size
	 * @return string
	 */
	public function getCorporationImageById($corporationID, $imageOnly = true, $size = 128) {
		$corporation = $this->getCorpratinData($corporationID);
		$imagePath = ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('corporation', $this->imageserverUrl . $this->imageserverEndpoints['corporation'] . $corporationID . '_' . $size. '.png');

		if($imageOnly === true) {
			return $imagePath;
		} // END if($imageOnly === true)

		$html = '<img src="' . $imagePath . '" class="eve-character-image eve-corporation-id-' . $corporationID . '" alt="' . \esc_html($corporation['data']->corporation_name) . '" data-title="' . \esc_html($corporation['data']->corporation_name) . '" data-toggle="eve-killboard-tooltip">';

		return $html;
	} // END public function getCorporationImageById($corporationID, $imageOnly = true, $size = 128)

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
	 * Get the alliance logo by alliance ID
	 *
	 * @param int $allianceID
	 * @param string $allianceName
	 * @param boolean $imageOnly
	 * @param int $size
	 * @return string
	 */
	public function getAllianceImageById($allianceID, $allianceName = '', $imageOnly = true, $size = 128) {
		$alliance = $this->getAllianceData($allianceID);
		$imagePath = ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('alliance', $this->imageserverUrl . $this->imageserverEndpoints['alliance'] . $allianceID . '_' . $size. '.png');

		if($imageOnly === true) {
			return $imagePath;
		} // END if($imageOnly === true)

		$html = '<img src="' . $imagePath . '" class="eve-character-image eve-alliance-id-' . $allianceID . '" alt="' . \esc_html($alliance['data']->alliance_name) . '" data-title="' . \esc_html($alliance['data']->alliance_name) . '" data-toggle="eve-killboard-tooltip">';

		return $html;
	} // END public function getAllianceImageById($allianceID, $imageOnly = true, $size = 128)

	/**
	 * get the EVE ID by it's name
	 *
	 * @param type $name
	 * @return type
	 */
	public function getEveIdFromName($name) {
		$transientName = \sanitize_title('get_eve.owner_data_' . $name);
		$data = $this->checkApiCache($transientName);

		if($data === false) {
			$endpoint = 'eve.owner';
			$data = PluginHelper::getInstance()->getRemoteData($this->apiUrl . $this->apiEndpoints[$endpoint], array('names' => $name));

			/**
			 * setting the transient caches
			 */
			$this->setApiCache($transientName, $data);
		} // END if($data === false)

		if($this->isXml($data)) {
			$xml = new \SimpleXMLElement($data);

			if(!empty($xml->result->rowset)) {
				foreach($xml->result->rowset->row as $row) {
					if(\strtolower((string) $row->attributes()->ownerName) == strtolower($name)) {
						$ownerID = (string) $row->attributes()->ownerID;
					} // END if((string) $row->attributes()->name == $corpName)s
				} // END foreach($xml->result->rowset->row as $row)

				return $ownerID;
			} // END if(!empty($xml->result->rowset))
		} // END if($this->isXml($data))
	} // END public function getCorpIdFromName($name)

	private function getEsiData($route) {
		$returnValue = null;
		$transientName = \sanitize_title('eve-killboard-data_' . $route);
		$data = $this->checkApiCache($transientName);

		if($data === false) {
			$data = PluginHelper::getInstance()->getRemoteData($this->esiUrl . $route);

			/**
			 * setting the transient caches
			 */
			$this->setApiCache($transientName, $data);
		} // END if($data === false)

		if(!empty($data)) {
			$returnValue = \json_decode($data);
		} // END if(!empty($data))

		return $returnValue;
	} // END private function getEsiData($route)

	/**
	 * Getting transient cache information / data
	 *
	 * @param string $transientName
	 * @return mixed
	 */
	private function checkApiCache($transientName) {
		$data = \get_transient($transientName);

		return $data;
	} // END private function checkApiCache($transientName)

	/**
	 * Setting the transient cahe
	 *
	 * @param string $transientName
	 * @param mixed $data
	 */
	private function setApiCache($transientName, $data) {
		\set_transient($transientName, $data, 1 * \HOUR_IN_SECONDS);
	} // END private function setApiCache($transientName, $data)

	/**
	 * Check if a string is a valid XML
	 *
	 * @param string $string
	 * @return boolean
	 */
	private function isXml($string) {
		$returnValue = false;

		if(\substr($string, 0, 5) == "<?xml") {
			$returnValue = true;
		} // END if(substr($sovereigntyXml, 0, 5) == "<?xml")

		return $returnValue;
	} // END private function isXml($string)
} // END class EveApi
