<?php
/**
 * EVE API Helper
 *
 * Getting some stuff from CCP's EVE API
 */

namespace WordPress\Plugin\EveOnlineKillboardWidget\Helper;

\defined('ABSPATH') or die();

class EveApiHelper {
	/**
	 * API URL
	 *
	 * @var string
	 */
	private $apiUrl = 'https://api.eveonline.com/';

	/**
	 * API Endpoints
	 *
	 * @var array
	 */
	private $apiEndpoints = null;

	/**
	 * Image Server URL
	 *
	 * @var string
	 */
	private $imageserverUrl = 'https://image.eveonline.com/';

	/**
	 * Image Server Endpoints
	 *
	 * @var array
	 */
	private $imageserverEndpoints = null;

	/**
	 * instance
	 *
	 * static variable to keep the current (and only!) instance of this class
	 *
	 * @var Singleton
	 */
	protected static $instance = null;

	/**
	 * Getting the instance
	 *
	 * @return \WordPress\Plugin\EveOnlineKillboardWidget\Helper\EveApiHelper
	 */
	public static function getInstance() {
		if(null === self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * clone
	 *
	 * no cloning allowed
	 */
	protected function __clone() {
		;
	}

	/**
	 * The Constructor
	 */
	protected function __construct() {
		/**
		 * Assigning API Endpoints
		 */
		$this->apiEndpoints = array(
			'eve.characterName' => 'eve/CharacterName.xml.aspx',
			'eve.owner' => 'eve/OwnerID.xml.aspx',
			'eve.typeName' => 'eve/TypeName.xml.aspx', // Returns the names associated with a sequence of typeIDs. ( http://eveonline-third-party-documentation.readthedocs.io/en/latest/xmlapi/eve/eve_typename.html )
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

	public function getImageServerEndpoint($group) {
		return $this->getImageServerUrl() . $this->imageserverEndpoints[$group];
	} // END public function getImageServerEndpoint($group)

	public function getEveApiUrl() {
		return $this->apiUrl;
	} // END public function getEveApiUrl()

	public function getEveApiEndpoint($section) {
		return $this->getEveApiUrl() . $this->apiEndpoints[$section];
	} // END public function getEveApiEndpoint($section)

	public function getCharacterImageByName($name, $imageOnly = true, $size = 128) {
		$entitieID = $this->getEveIdFromName($name);

		if($entitieID == 0 || $entitieID === false) {
			return false;
		} // END if($entitieID == 0)

		$imagePath = ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('character', $this->imageserverUrl . $this->imageserverEndpoints['character'] . $entitieID . '_' . $size. '.jpg');

		if($imageOnly === true) {
			return $imagePath;
		} // END if($imageOnly === true)

		$html = '<img src="' . $imagePath . '" class="eve-character-image eve-character-id-' . $entitieID . '" alt="' . $name . '">';

		return $html;
	} // END public function getCharacterImageByName($name, $imageOnly = true, $size = 128)

	public function getCharacterImageById($characterID, $imageOnly = true, $size = 128) {
		$imagePath = ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('character', $this->imageserverUrl . $this->imageserverEndpoints['character'] . $characterID . '_' . $size. '.jpg');

		if($imageOnly === true) {
			return $imagePath;
		} // END if($imageOnly === true)

		$html = '<img src="' . $imagePath . '" class="eve-character-image eve-character-id-' . $characterID . '">';

		return $html;
	} // END public function getCharacterImageByName($name, $imageOnly = true, $size = 128)

	public function getTypeName($typeID) {
		$transientName = \sanitize_title('get_eve.typeName_' . $typeID);
		$data = $this->checkApiCache($transientName);
		$typeName = null;

		if($data === false) {
			$endpoint = 'eve.typeName';
			$data = PluginHelper::getInstance()->getRemoteData($this->apiUrl . $this->apiEndpoints[$endpoint], array('ids' => $typeID));

			/**
			 * setting the transient caches
			 */
			$this->setApiCache($transientName, $data);
		} // END if($data === false)

		if($this->isXml($data)) {
			$xml = new \SimpleXMLElement($data);

			if(!empty($xml->result->rowset)) {
				foreach($xml->result->rowset->row as $row) {
					$typeName[] = (string) $row->attributes()->typeName;
				} // END foreach($xml->result->rowset->row as $row)
			} // END if(!empty($xml->result->rowset))
		} // END if($this->isXml($data))

		return $typeName;
	} // END public function getTypeName($typeID)

	public function getSystemNameFromId($systemID) {
		$transientName = \sanitize_title('get_eve.systemName_' . $systemID);
		$data = $this->checkApiCache($transientName);
		$systemName = null;

		if($data === false) {
			$endpoint = 'eve.characterName';
			$data = PluginHelper::getInstance()->getRemoteData($this->apiUrl . $this->apiEndpoints[$endpoint], array('ids' => $systemID));

			/**
			 * setting the transient caches
			 */
			$this->setApiCache($transientName, $data);
		} // END if($data === false)

		if($this->isXml($data)) {
			$xml = new \SimpleXMLElement($data);

			if(!empty($xml->result->rowset)) {
				foreach($xml->result->rowset->row as $row) {
					$systemName[] = (string) $row->attributes()->name;
				} // END foreach($xml->result->rowset->row as $row)
			} // END if(!empty($xml->result->rowset))
		} // END if($this->isXml($data))

		return $systemName;
	} // END public function getSystemNameFromId($systemID)

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
