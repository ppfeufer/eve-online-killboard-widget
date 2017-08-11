<?php
/**
 * Killboard Widget
 */

namespace WordPress\Plugin\EveOnlineKillboardWidget\Helper;

\defined('ABSPATH') or die();

class KillboardHelper {
	private $zkbLink = null;
	private $zkbApiLink = null;

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
	 * @return \WordPress\Plugin\EveOnlineKillboardWidget\Helper\KillboardHelper
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
	 * constructor
	 *
	 * no external instanciation allowed
	 */
	protected function __construct() {
		$this->zkbApiLink = 'https://zkillboard.com/api/';
		$this->zkbLink = 'https://zkillboard.com/';
	} // END protected function __construct()

	public function getKillList($entityType, $entityName, $killCount = 5, $showLosses = 0) {
		$this->entityID = EveApiHelper::getInstance()->getEveIdFromName($entityName);
		$this->entityType = $entityType;

		$transientName = \sanitize_title('eve_online_killboard-' . $entityType . '-' . $entityName . '.lastkills_kills-only');
		if((int) $showLosses === 1) {
			$transientName = \sanitize_title('eve_online_killboard-' . $entityType . '-' . $entityName . '.lastkills');
		} // END if($showLosses === true)

		$data = \get_transient($transientName);

		if($data === false) {
			$zkbUrl = $this->zkbApiLink . 'kills/' . $this->entityType . 'ID/' . $this->entityID . '/limit/' . $killCount . '/';
			if((int) $showLosses === 1) {
				$zkbUrl = $this->zkbApiLink . $this->entityType . 'ID/' . $this->entityID . '/limit/' . $killCount . '/';
			} // END if($showLosses === true)

			$data = \json_decode(PluginHelper::getInstance()->getRemoteData($zkbUrl));

			/**
			 * setting the transient caches
			 */
			\set_transient($transientName, $data, 300);
		} // END if($data === false)

		return $data;
	} // END public function getKillList($killCount)

	public function getWidgetHtml(array $killList) {
		$widgetHtml = null;

		foreach($killList as $killmail) {
			$countAttackers = \count($killmail->attackers);
			$stringInvolved = ($countAttackers - 1 === 0) ? '' : ' (+' . ($countAttackers - 1) . ')';

			$killType = ' kill-list-kill-mail';
			if($killmail->victim->corporationID === (int) $this->entityID || $killmail->victim->allianceID === (int) $this->entityID) {
				$killType = ' kill-list-loss-mail';
			} // END if($killmail->victim->corporationID === $this->entityID || $killmail->victim->allianceID === $this->entityID)

			$widgetHtml .= '<div class="row killboard-entry' . $killType . '">'
						. '	<div class="col-xs-4 col-sm-12 col-md-12 col-lg-5">'
						. '		<figure>'
						. '			<a href="' . $this->getKillboardLink($killmail->killID) . '" rel="external" target="_blank">'
						.				$this->getVictimImage($killmail->victim)
						. '			</a>'
						. '		</figure>'
						. '	</div>'
						. '	<div class="col-xs-8 col-sm-12 col-md-12 col-lg-7">'
						. '		<ul>'
						. '			<li>' . $this->getVictimType($killmail->victim) . ': ' . $this->getVictimName($killmail->victim) . '</li>'
						. '			<li>' . $this->getVictimShipType($killmail->victim) . ': ' . $this->getVictimShip($killmail->victim) . '</li>'
						. '			<li>ISK lost: ' . $this->getIskLoss($killmail->zkb) . '</li>'
						. '			<li>System: ' . $this->getSystem($killmail->solarSystemID) . '</li>'
						. '			<li>Killed by: ' . $this->getFinalBlow($killmail->attackers) . $stringInvolved . '</li>'
						. '		</ul>'
						. '	</div>'
						. '</div>';
		} // END foreach($array as $killmail)

		return $widgetHtml;
	} // END public function getWidgetHtml(array $killList)

	/**
	 * Getting dummy HTML for the killboard widget, which will be
	 * replaced with the real killboard information after the ajax call
	 *
	 * @return string
	 */
	public function getDummyHtml() {
		return '<div class="row killboard-entry">'
				. '	<div class="col-xs-4 col-sm-12 col-md-12 col-lg-5">'
				. '		<figure>'
				.			$this->getDummyImage()
				. '		</figure>'
				. '	</div>'
				. '	<div class="col-xs-8 col-sm-12 col-md-12 col-lg-7">'
				. '		<ul>'
				. '			<li>Pilot:</li>'
				. '			<li>Ship:</li>'
				. '			<li>ISK lost:</li>'
				. '			<li>System:</li>'
				. '			<li>Killed by:</li>'
				. '		</ul>'
				. '	</div>'
				. '</div>';
	} // END public function getDummyHtml()

	/**
	 * Getting the dummy image
	 *
	 * @param boolean $linkOnly
	 * @return string
	 */
	public function getDummyImage($linkOnly = false) {
		$dummyImage = PluginHelper::getInstance()->getPluginUri('images/dummy.jpg');
		$returnValue = $dummyImage;

		if($linkOnly === false) {
			$returnValue = '<img src="' . $dummyImage . '" class="eve-character-image">';
		} // END if($linkOnly === false)

		return $returnValue;
	} // END public function getDummyImage($linkOnly = false)

	/**
	 * Getting the link to teh killmail on ZKB
	 *
	 * @param int $killID
	 * @return string
	 */
	public function getKillboardLink($killID) {
		return $this->zkbLink . 'kill/' . $killID . '/';
	} // END public function getKillboardLink($killID)

	/**
	 * Getting victims image
	 *
	 * @param \stdClass $victimData
	 * @param int $size
	 * @return string
	 */
	public function getVictimImage(\stdClass $victimData, $size = 256) {
		$victimImage = null;

		switch($victimData->characterID) {
			case 0:
				$victimImage = '<img src="' . ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('render', EveApiHelper::getInstance()->getImageServerUrl() . 'Render/' . $victimData->shipTypeID . '_' . $size . '.png') . '" class="eve-structure-image eve-online-id-' . $victimData->shipTypeID . '">';
				break;

			default:
				$victimImage = EveApiHelper::getInstance()->getCharacterImageById($victimData->characterID, false, $size);
				break;
		} // END switch($victimData->characterID)

		return $victimImage;
	} // END public function getVictimImage(\stdClass $victimData, $size = 256)

	/**
	 * Getting the victims type
	 *
	 * @param \stdClass $victimData
	 * @return string
	 */
	public function getVictimType(\stdClass $victimData) {
		$victimType = \__('Pilot', 'yulai-federation');

		if($victimData->characterID === 0) {
			$victimType = \__('Corp', 'yulai-federation');
		} // END if($victimData->characterID === 0)

		return $victimType;
	} // END public function getVictimType(\stdClass $victimData)

	/**
	 * Getting the victims name
	 *
	 * @param \stdClass $victimData
	 * @return string
	 */
	public function getVictimName(\stdClass $victimData) {
		$victimName = $victimData->characterName;

		if(empty($victimName)) {
			$victimName = $victimData->corporationName;
		} // END if(empty($victimName))

		return $victimName;
	} // END public function getVictimName(\stdClass $victimData)

	/**
	 * Get the final blow
	 *
	 * @param array $attackerData
	 * @return string
	 */
	public function getFinalBlow(array $attackerData) {
		$finalBlow = null;

		foreach($attackerData as $attacker) {
			if($attacker->finalBlow === 1) {
				$finalBlow = $attacker->characterName;
			} // END if($attacker->finalBlow === 1)
		} // END foreach($attackerData as $attacker)

		return $finalBlow;
	} // END public function getFinalBlow(array $attackerData)

	public function getIskLoss(\stdClass $zkbData) {
		return $this->sanitizeIskLoss($zkbData->totalValue);
	} // END public function getIskLoss(\stdClass $zkbData)

	/**
	 * getting the victims ship type
	 *
	 * @param \stdClass $victimData
	 * @return string
	 */
	public function getVictimShip(\stdClass $victimData) {
		$typeNames = EveApiHelper::getInstance()->getTypeName($victimData->shipTypeID);

		return $typeNames['0'];
	} // END public function getVictimShip(\stdClass $victimData)

	/**
	 * Determine if the victim lost a ship or a structure
	 *
	 * @param \stdClass $victimData
	 * @return string
	 */
	public function getVictimShipType(\stdClass $victimData) {
		$victimShipType = \__('Ship', 'yulai-federation');

		if($victimData->characterID === 0) {
			$victimShipType = \__('Structure', 'yulai-federation');
		} // END if($victimData->characterID === 0)

		return $victimShipType;
	} // END public function getVictimShipType(\stdClass $victimData)

	/**
	 * Getting the sytem name from ID
	 *
	 * @param type $systemID
	 * @return string
	 */
	public function getSystem($systemID) {
		$systemNames = EveApiHelper::getInstance()->getSystemNameFromId($systemID);

		return $systemNames['0'];
	} // END public function getSystem($systemID)

	private function sanitizeIskLoss($isk) {
		if($isk < 1000) {
			$isk = \number_format($isk, 0);
		} elseif(($isk/1000) < 1000) {
			$isk = \number_format(($isk/1000), 0) . 'K';
		} elseif(($isk/1000/1000) < 1000) {
			$isk = \number_format(($isk/1000/1000), 0) . 'M';
		} else {
			$isk = \number_format(($isk/1000/1000/1000), 0, '.', ',') . 'B';
		} // END if($isk < 1000)

		return $isk;
	} // END private function sanitizeIskLoss($isk)
} // END class ZkbKillboardHelper
