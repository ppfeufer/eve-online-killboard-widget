<?php
/**
 * Killboard Widget
 */

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class KillboardHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
	private $zkbLink = null;
	private $zkbApiLink = null;
	private $entityID = null;

	/**
	 * constructor
	 *
	 * no external instanciation allowed
	 */
	protected function __construct() {
		parent::__construct();

		$this->zkbApiLink = 'https://zkillboard.com/api/';
		$this->zkbLink = 'https://zkillboard.com/';
	} // END protected function __construct()

	/**
	 * Getting the kill list from zKillboard
	 *
	 * @param array $widgetSettings
	 * @return array
	 */
	public function getKillList(array $widgetSettings) {
		$this->entityID = EveApiHelper::getInstance()->getEveIdFromName($widgetSettings['eve-online-killboard-widget-entity-name'], $widgetSettings['eve-online-killboard-widget-entity-type']);

		$transientName = \sanitize_title('eve_online_killboard-' . \md5(\json_encode($widgetSettings)) . '.lastkills_kills-only');
		if((int) $widgetSettings['eve-online-killboard-widget-show-losses'] === 1) {
			$transientName = \sanitize_title('eve_online_killboard-' . \md5(\json_encode($widgetSettings)) . '.lastkills');
		} // END if($showLosses === true)

		$data = \get_transient($transientName);

		if($data === false) {
			$zkbUrl = $this->zkbApiLink . 'kills/' . $widgetSettings['eve-online-killboard-widget-entity-type'] . 'ID/' . $this->entityID. '/limit/' . $widgetSettings['eve-online-killboard-widget-number-of-kills'] . '/npc/0/';
			if((int) $widgetSettings['eve-online-killboard-widget-show-losses'] === 1) {
				$zkbUrl = $this->zkbApiLink . $widgetSettings['eve-online-killboard-widget-entity-type'] . 'ID/' . $this->entityID . '/limit/' . $widgetSettings['eve-online-killboard-widget-number-of-kills'] . '/npc/0/';
			} // END if($showLosses === true)

			$data = \json_decode(RemoteHelper::getInstance()->getRemoteData($zkbUrl));

			/**
			 * setting the transient caches
			 */
			\set_transient($transientName, $data, 300);
		} // END if($data === false)

		return $data;
	} // END public function getKillList($killCount)

	/**
	 * Getting the HTML for our widget
	 *
	 * @param array $killList
	 * @return string
	 */
	public function getWidgetHtml(array $killList) {
		$widgetHtml = null;

		foreach($killList as $killMail) {
			$countAttackers = \count($killMail->attackers);
			$stringInvolved = ($countAttackers - 1 === 0) ? '' : ' (+' . ($countAttackers - 1) . ')';

			$victimEntityIdsArray = [
				'characterID' => (isset($killMail->victim->character_id)) ? (int) $killMail->victim->character_id : null,
				'corporationID' => (isset($killMail->victim->corporation_id)) ? (int) $killMail->victim->corporation_id : null,
				'allianceID' => (isset($killMail->victim->alliance_id)) ? (int) $killMail->victim->alliance_id : null
			];

			// Check if we have a kill or a loss mail
			$killType = ' kill-list-kill-mail';
			if(\in_array((int) $this->entityID, $victimEntityIdsArray)) {
				$killType = ' kill-list-loss-mail';
			} // END if(\in_array((int) $this->entityID, $victimEntityIdsArray))

			$systemInformation = $this->getSystemInformation($killMail->solar_system_id);
			$widgetHtml .= '<div class="row killboard-entry' . $killType . '">'
						. '	<div class="col-xs-4 col-sm-12 col-md-12 col-lg-5">'
						. '		<figure>'
						. '			<a href="' . $this->getKillboardLink($killMail->killmail_id) . '" rel="external" target="_blank">'
						.				$this->getVictimImage($killMail->victim)
						. '			</a>'
						. '			<div class="eve-online-killboard-widget-pilot-information">'
						. '				<span class="victimShipImage">'
						.					$this->getVictimShipImage($killMail->victim, 32)
						. '				</span>'
						. '				<span class="victimCorpImage">'
						.					$this->getVictimCorpImage($killMail->victim, 32)
						. '				</span>'
						. '				<span class="victimAllianceImage">'
						.					$this->getVictimAllianceImage($killMail->victim, 32)
						. '				</span>'
						. '			</div>'
						. '		</figure>'
						. '	</div>'
						. '	<div class="col-xs-8 col-sm-12 col-md-12 col-lg-7">'
						. '		<ul>'
						. '			<li>' . $this->getVictimType($killMail->victim) . ': ' . $this->getVictimName($killMail->victim) . '</li>'
						. '			<li>Loss: ' . $this->getVictimShip($killMail->victim) . '</li>'
						. '			<li>ISK lost: ' . $this->getIskLoss($killMail->zkb) . '</li>'
						. '			<li>System: ' . $systemInformation->name . ' (' . \round($systemInformation->security_status, 2) . ')</li>'
						. '			<li>Killed by: ' . $this->getFinalBlow($killMail->attackers) . $stringInvolved . '</li>'
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
				. '			<li>Loss:</li>'
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

		if(isset($victimData->character_id)) {
			$imageUrl = ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('character', EveApiHelper::getInstance()->getImageServerUrl() . 'Character/' . $victimData->character_id . '_' . $size. '.jpg');
			$victimImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-character-id-' . $victimData->character_id . '" alt="' . \esc_html($this->getVictimName($victimData)) . '">';
		}

		if(!isset($victimData->character_id)) {
			$victimImage = '<img src="' . ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('render', EveApiHelper::getInstance()->getImageServerUrl() . 'Render/' . $victimData->ship_type_id . '_' . $size . '.png') . '" class="eve-structure-image eve-online-id-' . $victimData->ship_type_id . '">';
		}

		return $victimImage;
	} // END public function getVictimImage(\stdClass $victimData, $size = 256)

	public function getVictimCorpImage(\stdClass $victimData, $size = 256) {
		$victimCorporationImage = null;

		if($victimData->corporation_id) {
			$corpData = EveApiHelper::getInstance()->getCorporationData($victimData->corporation_id);
			$imageUrl = ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('corporation', EveApiHelper::getInstance()->getImageServerUrl() . 'Corporation/' . $victimData->corporation_id . '_' . $size. '.png');
			$victimCorporationImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-corporation-id-' . $victimData->corporation_id . '" alt="' . \esc_html($corpData['data']->corporation_name) . '" data-title="' . \esc_html($corpData['data']->corporation_name) . '" data-toggle="eve-killboard-tooltip">';
		}


		return $victimCorporationImage;
	} // END public function getVictimImage(\stdClass $victimData, $size = 256)

	public function getVictimShipImage(\stdClass $victimData, $size = 256) {
		$victimShipImage = null;

		switch($victimData->ship_type_id) {
			case 0:
				$victimShipImage = null;
				break;

			default:
				$victimShipImage = EveApiHelper::getInstance()->getShipImageById($victimData->ship_type_id, false, $size);
				break;
		} // END switch($victimData->shipTypeID)

		return $victimShipImage;
	} // END public function getVictimImage(\stdClass $victimData, $size = 256)

	public function getVictimAllianceImage(\stdClass $victimData, $size = 128) {
		$victimAllianceImage = null;

		if(isset($victimData->alliance_id)) {
			$allianceData = EveApiHelper::getInstance()->getAllianceData($victimData->alliance_id);
			$imageUrl = ImageHelper::getInstance()->getLocalCacheImageUriForRemoteImage('alliance', EveApiHelper::getInstance()->getImageServerUrl() . 'Alliance/' . $victimData->alliance_id . '_' . $size. '.png');
			$victimAllianceImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-alliance-id-' . $victimData->alliance_id . '" alt="' . \esc_html($allianceData['data']->alliance_name) . '" data-title="' . \esc_html($allianceData['data']->alliance_name) . '" data-toggle="eve-killboard-tooltip">';
		}

		return $victimAllianceImage;
	} // END public function getVictimImage(\stdClass $victimData, $size = 256)

	/**
	 * Getting the victims type
	 *
	 * @param \stdClass $victimData
	 * @return string
	 */
	public function getVictimType(\stdClass $victimData) {
		$victimType = \__('Pilot', 'yulai-federation');

		if(!isset($victimData->character_id)) {
			$victimType = \__('Corp', 'yulai-federation');
		} // END if(isset($victimData->character_id))

		return $victimType;
	} // END public function getVictimType(\stdClass $victimData)

	/**
	 * Getting the victims name
	 *
	 * @param \stdClass $victimData
	 * @return string
	 */
	public function getVictimName(\stdClass $victimData) {
		$victimType = $this->getVictimType($victimData);
		$victimName = null;

		switch($victimType) {
			case 'Pilot':
				$pilotData = EveApiHelper::getInstance()->getCharacterData($victimData->character_id);
				$victimName = $pilotData['data']->name;
				break;

			case 'Corp':
				$corpData = EveApiHelper::getInstance()->getCorporationData($victimData->corporation_id);
				$victimName = $corpData['data']->corporation_name;
				break;

			default:
				$victimName = null;
				break;
		}

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
			if($attacker->final_blow === 1) {
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
		$ship = EveApiHelper::getInstance()->getShipData($victimData->ship_type_id);

		return $ship['data']->name;
	} // END public function getVictimShip(\stdClass $victimData)

	/**
	 * Getting the sytem name from ID
	 *
	 * @param type $systemID
	 * @return string
	 */
	public function getSystemInformation($systemID) {
		$system = EveApiHelper::getInstance()->getSystemData($systemID);

		return $system['data'];
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
