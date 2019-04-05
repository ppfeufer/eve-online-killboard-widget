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
 * Killboard Widget
 */

namespace WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper;

use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton;

\defined('ABSPATH') or die();

class KillboardHelper extends AbstractSingleton {
    /**
     * link to zkillboard
     *
     * @var string
     */
    private $zkbLink = null;

    /**
     * Link to zkillboard API
     * @var string
     */
    private $zkbApiLink = null;

    /**
     * entityID
     *
     * @var int
     */
    private $entityID = null;

    /**
     * eveApi
     *
     * @var \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper\EveApiHelper
     */
    private $eveApi = null;

    /**
     * cacheHelper
     *
     * @var CacheHelper
     */
    private $cacheHelper = null;

    /**
     * remoteHelper
     *
     * @var RemoteHelper
     */
    private $remoteHelper = null;

    /**
     * constructor
     *
     * no external instanciation allowed
     */
    protected function __construct() {
        parent::__construct();

        $this->zkbApiLink = 'https://zkillboard.com/api/';
        $this->zkbLink = 'https://zkillboard.com/';
        $this->eveApi = \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper\EveApiHelper::getInstance();
        $this->cacheHelper = CacheHelper::getInstance();
        $this->remoteHelper = RemoteHelper::getInstance();
    }

    /**
     * Getting the kill list from zKillboard
     *
     * @param array $widgetSettings
     * @return array
     */
    public function getKillList(array $widgetSettings) {
        if(isset($widgetSettings['eve-online-killboard-widget-entity-id'])) {
            $this->entityID = $widgetSettings['eve-online-killboard-widget-entity-id'];
        }

        /**
         * In case we don't have the entity ID set by the widget's settings ...
         */
        if(\is_null($this->entityID)) {
            $entityIdTransientName = 'EVE Online :: ID ' . $widgetSettings['eve-online-killboard-widget-entity-type'] . '/' . $widgetSettings['eve-online-killboard-widget-entity-name'];

            $this->entityID = $this->cacheHelper->getKillboardCache($entityIdTransientName);

            if($this->entityID === false || empty($this->entityID)) {
                $this->entityID = $this->eveApi->getEveIdByName($widgetSettings['eve-online-killboard-widget-entity-name'], $widgetSettings['eve-online-killboard-widget-entity-type']);

                $this->cacheHelper->setKillboardCache([
                    $entityIdTransientName,
                    \maybe_serialize($this->entityID),
                    \strtotime('+10 years')
                ]);
            }
        }

        $widgetSettingsCacheKey = 'Killboard Data :: ' . $widgetSettings['eve-online-killboard-widget-number-of-kills']  . '/'. $widgetSettings['eve-online-killboard-widget-entity-name'] . '/' . $widgetSettings['eve-online-killboard-widget-entity-id'] . '/Kills Only/';

        if((int) $widgetSettings['eve-online-killboard-widget-show-losses'] === 1) {
            $widgetSettingsCacheKey = 'Killboard Data :: ' . $widgetSettings['eve-online-killboard-widget-number-of-kills']  . '/'. $widgetSettings['eve-online-killboard-widget-entity-name'] . '/' . $widgetSettings['eve-online-killboard-widget-entity-id'] . '/';
        }

        $data = $this->cacheHelper->getKillboardCache($widgetSettingsCacheKey);

        if($data === false || empty($data)) {
            $data = $this->getZkillboardData($widgetSettings);

            /**
             * setting the transient caches
             */
            $this->cacheHelper->setKillboardCache([
                $widgetSettingsCacheKey,
                \maybe_serialize($data),
                \strtotime('+5 Minutes')
            ]);
        }

        return $data;
    }

    /**
     * Getting Zkillboard data
     *
     * @param array $widgetSettings
     * @return array
     */
    public function getZkillboardData(array $widgetSettings) {
        global $wp_version;

        $returnValue = null;

        $this->remoteHelper->setUserAgent('Killboard Widget for WordPress » https://github.com/ppfeufer/eve-online-killboard-widget // WordPress/' . $wp_version . '; ' . \home_url());

        $zkbUrl = $this->zkbApiLink . 'kills/' . $widgetSettings['eve-online-killboard-widget-entity-type'] . 'ID/' . $this->entityID. '/npc/0/';

        if((int) $widgetSettings['eve-online-killboard-widget-show-losses'] === 1) {
            $zkbUrl = $this->zkbApiLink . $widgetSettings['eve-online-killboard-widget-entity-type'] . 'ID/' . $this->entityID . '/npc/0/';
        }

        $zkbData = $this->remoteHelper->getRemoteData($zkbUrl);

        if(!\is_null($zkbData)) {
            $zkbDataKills = \array_slice(\json_decode($zkbData), 0, (int) $widgetSettings['eve-online-killboard-widget-number-of-kills'], true);

            $killmails = null;
            foreach($zkbDataKills as $kill) {
                $killmailDetail = $this->eveApi->getPublicKillmail($kill->killmail_id, $kill->zkb->hash);

                if(!\is_null($killmailDetail)) {
                    $killmails[$kill->killmail_id] = new \stdClass();
                    $killmails[$kill->killmail_id]->killmailID = $kill->killmail_id;
                    $killmails[$kill->killmail_id]->killmail = $killmailDetail;
                    $killmails[$kill->killmail_id]->zkbData = $kill->zkb;
                }
            }

            $returnValue = $killmails;
        }

        return $returnValue;
    }

    /**
     * Getting the HTML for our widget
     *
     * @param array $killList
     * @return string
     */
    public function getWidgetHtml(array $killList) {
        $widgetHtml = null;

        foreach($killList as $killMailData) {
            /* @var $killmail->killmail \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId */
            $countAttackers = \count($killMailData->killmail->getAttackers());
            $stringInvolved = ($countAttackers - 1 === 0) ? '' : ' <small>(+' . ($countAttackers - 1) . ')</small>';

            $victimEntityIdsArray = [
                'characterID' => (!\is_null($killMailData->killmail->getVictim()->getCharacterId())) ? (int) $killMailData->killmail->getVictim()->getCharacterId() : null,
                'corporationID' => (!\is_null($killMailData->killmail->getVictim()->getCorporationId())) ? (int) $killMailData->killmail->getVictim()->getCorporationId() : null,
                'allianceID' => (!\is_null($killMailData->killmail->getVictim()->getAllianceId())) ? (int) $killMailData->killmail->getVictim()->getAllianceId() : null
            ];

            // Check if we have a kill or a loss mail
            $killType = ' kill-list-kill-mail';
            if(\in_array((int) $this->entityID, $victimEntityIdsArray)) {
                $killType = ' kill-list-loss-mail';
            }

            /* @var $systemInformation \WordPress\EsiClient\Model\Universe\UniverseSystemsSystemId */
            $systemInformation = $this->getSystemInformation($killMailData->killmail->getSolarSystemId());
            $widgetHtml .= '<div class="row killboard-entry' . $killType . '">'
                        . '    <div class="col-xs-4 col-sm-12 col-md-12 col-lg-5">'
                        . '        <figure>'
                        . '            <a href="' . $this->getKillboardLink($killMailData->killmail->getKillmailId()) . '" rel="external" target="_blank">'
                        .                  $this->getVictimImage($killMailData->killmail->getVictim())
                        . '            </a>'
                        . '            <div class="eve-online-killboard-widget-pilot-information">'
                        . '                <span class="victimShipImage">'
                        .                      $this->getVictimShipImage($killMailData->killmail->getVictim(), 32)
                        . '                </span>'
                        . '                <span class="victimCorpImage">'
                        .                      $this->getVictimCorpImage($killMailData->killmail->getVictim(), 32)
                        . '                </span>'
                        . '                <span class="victimAllianceImage">'
                        .                      $this->getVictimAllianceImage($killMailData->killmail->getVictim(), 32)
                        . '                </span>'
                        . '            </div>'
                        . '        </figure>'
                        . '    </div>'
                        . '    <div class="col-xs-8 col-sm-12 col-md-12 col-lg-7">'
                        . '        <ul>'
                        . '            <li>' . $this->getVictimType($killMailData->killmail->getVictim()) . ': ' . $this->getVictimName($killMailData->killmail->getVictim()) . '</li>'
                        . '            <li>' . \__('EVE Time', 'eve-online-killboard-widget') . ': ' . $killMailData->killmail->getKillmailTime()->format('d.m.Y') . ' ' . $killMailData->killmail->getKillmailTime()->format('H:i:s') . '</li>'
                        . '            <li>' . \__('Loss', 'eve-online-killboard-widget') . ': ' . $this->getVictimShip($killMailData->killmail->getVictim()) . ' <small>(' . $this->getVictimShipType($killMailData->killmail->getVictim()) . ')</small></li>'
                        . '            <li>' . \__('ISK lost', 'eve-online-killboard-widget') . ': ' . $this->getIskLoss($killMailData->zkbData) . '</li>'
                        . '            <li>' . \__('System', 'eve-online-killboard-widget') . ': ' . $systemInformation->getName() . ' <small>(' . \round($systemInformation->getSecurityStatus(), 2) . ')</small></li>'
                        . '            <li>' . \__('Killed by', 'eve-online-killboard-widget') . ': ' . $this->getFinalBlow($killMailData->killmail->getAttackers()) . $stringInvolved . '</li>'
                        .              $this->getBadges($killMailData)
                        . '        </ul>'
                        . '    </div>'
                        . '</div>';
        }

        return $widgetHtml;
    }

    /**
     * Get killboard badges
     *
     * @param object $killMail
     * @return string
     */
    private function getBadges($killMail) {
        $returnValue = null;
        $badgeSoloKill = false;

        /* @var $killmail->killmail \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId */
        if($killMail->zkbData->solo || \count($killMail->killmail->getAttackers()) === 1) {
            $badgeSoloKill = '<span class="eve-online-killboard-widget-solokill"><small>' . \__('SOLO', 'eve-online-killboard-widget') . '</small></span>';
        }

        if($badgeSoloKill) {
            $returnValue = '<li class="eve-online-killboard-widget-badges">';
            $returnValue .= $badgeSoloKill;
            $returnValue .= '</li>';
        }

        return $returnValue;
    }

    /**
     * Getting dummy HTML for the killboard widget, which will be
     * replaced with the real killboard information after the ajax call
     *
     * @return string
     */
    public function getDummyHtml() {
        return '<div class="row killboard-entry">'
                . '    <div class="col-xs-4 col-sm-12 col-md-12 col-lg-5">'
                . '        <figure>'
                .            $this->getDummyImage()
                . '        </figure>'
                . '    </div>'
                . '    <div class="col-xs-8 col-sm-12 col-md-12 col-lg-7">'
                . '        <ul>'
                . '            <li>' . \__('Pilot', 'eve-online-killboard-widget') . ':</li>'
                . '            <li>' . \__('EVE Time', 'eve-online-killboard-widget') . ':</li>'
                . '            <li>' . \__('Loss', 'eve-online-killboard-widget') . ':</li>'
                . '            <li>' . \__('ISK lost', 'eve-online-killboard-widget') . ':</li>'
                . '            <li>' . \__('System', 'eve-online-killboard-widget') . ':</li>'
                . '            <li>' . \__('Killed by', 'eve-online-killboard-widget') . ':</li>'
                . '        </ul>'
                . '    </div>'
                . '</div>';
    }

    /**
     * Getting the dummy image
     *
     * @param bool $linkOnly
     * @return string
     */
    public function getDummyImage(bool $linkOnly = false) {
        $dummyImage = PluginHelper::getInstance()->getPluginUri('images/dummy.jpg');
        $returnValue = $dummyImage;

        if($linkOnly === false) {
            $returnValue = '<img src="' . $dummyImage . '" class="eve-character-image">';
        }

        return $returnValue;
    }

    /**
     * Getting the link to teh killmail on ZKB
     *
     * @param int $killID
     * @return string
     */
    public function getKillboardLink(int $killID) {
        return $this->zkbLink . 'kill/' . $killID . '/';
    }

    /**
     * Getting victims image
     *
     * @param \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData
     * @param int $size
     * @return string
     */
    public function getVictimImage(\WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData, $size = 256) {
        $victimImage = null;

        if(!\is_null($victimData->getCharacterId())) {
            $imageUrl = $this->eveApi->getImageServerUrl() . $this->eveApi->getImageServerEndpont('character') . $victimData->getCharacterId() . '_' . $size. '.jpg';
            $victimImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-character-id-' . $victimData->getCharacterId() . '" alt="' . \esc_html($this->getVictimName($victimData)) . '">';
        }

        if(\is_null($victimData->getCharacterId())) {
            $victimImage = '<img src="' . $this->eveApi->getImageServerUrl() . $this->eveApi->getImageServerEndpont('render') . $victimData->getShipTypeId() . '_' . $size . '.png' . '" class="eve-structure-image eve-online-id-' . $victimData->getShipTypeId() . '">';
        }

        return $victimImage;
    }

    /**
     * Getting victms corporation logo
     *
     * @param \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData
     * @param int $size
     * @return string
     */
    public function getVictimCorpImage(\WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData, $size = 256) {
        $victimCorporationImage = null;

        if($victimData->getCorporationId()) {
            /* @var $corpData \WordPress\EsiClient\Model\Corporation\CorporationsCorporationId */
            $corpData = $this->eveApi->getCorporationDataByCorporationId($victimData->getCorporationId());
            $imageUrl = $this->eveApi->getImageServerUrl() . $this->eveApi->getImageServerEndpont('corporation') . $victimData->getCorporationId() . '_' . $size. '.png';
            $victimCorporationImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-corporation-id-' . $victimData->getCorporationId() . '" alt="' . \esc_html($corpData->getName()) . '" data-title="' . \esc_html($corpData->getName()) . '" data-toggle="eve-killboard-tooltip">';
        }


        return $victimCorporationImage;
    }

    /**
     * Getting victims ship image
     *
     * @param \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData
     * @param int $size
     * @return type
     */
    public function getVictimShipImage(\WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData, $size = 256) {
        $victimShipImage = null;

        switch($victimData->getShipTypeId()) {
            case 0:
                $victimShipImage = null;
                break;

            default:
                $victimShipImage = $this->eveApi->getShipImageByShipId($victimData->getShipTypeId(), false, $size);
                break;
        }

        return $victimShipImage;
    }

    /**
     * Getting victims alliance logo
     *
     * @param \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData
     * @param type $size
     * @return string
     */
    public function getVictimAllianceImage(\WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData, $size = 128) {
        $victimAllianceImage = null;

        if(!\is_null($victimData->getAllianceId())) {
            /* @var $allianceData \WordPress\EsiClient\Model\Alliance\AlliancesAllianceId */
            $allianceData = $this->eveApi->getAllianceDataByAllianceId($victimData->getAllianceId());
            $imageUrl = $this->eveApi->getImageServerUrl() . $this->eveApi->getImageServerEndpont('alliance') . $victimData->getAllianceId() . '_' . $size. '.png';
            $victimAllianceImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-alliance-id-' . $victimData->getAllianceId() . '" alt="' . \esc_html($allianceData->getName()) . '" data-title="' . \esc_html($allianceData->getName()) . '" data-toggle="eve-killboard-tooltip">';
        }

        return $victimAllianceImage;
    }

    /**
     * Getting the victims type
     *
     * @param \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData
     * @return string
     */
    public function getVictimType(\WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData) {
        $victimType = \__('Pilot', 'eve-online-killboard-widget');

        if(\is_null($victimData->getCharacterId())) {
            $victimType = \__('Corp', 'eve-online-killboard-widget');
        }

        return $victimType;
    }

    /**
     * Getting the victims name
     *
     * @param \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData
     * @return string
     */
    public function getVictimName(\WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData) {
        $victimType = $this->getVictimType($victimData);
        $victimName = null;

        switch($victimType) {
            case \__('Pilot', 'eve-online-killboard-widget'):
                /* @var $pilotData \WordPress\EsiClient\Model\Character\CharactersCharacterId */
                $pilotData = $this->eveApi->getCharacterDataByCharacterId($victimData->getCharacterId());
                $victimName = $pilotData->getName();
                break;

            case \__('Corp', 'eve-online-killboard-widget'):
                /* @var $corpData \WordPress\EsiClient\Model\Corporation\CorporationsCorporationId */
                $corpData = $this->eveApi->getCorporationDataByCorporationId($victimData->getCorporationId());
                $victimName = $corpData->getName();
                break;

            default:
                $victimName = null;
                break;
        }

        return $victimName;
    }

    /**
     * Get the final blow
     *
     * @param array $attackerData
     * @return string
     */
    public function getFinalBlow(array $attackerData) {
        $finalBlow = null;

        /* @var $attacker \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Attacker */
        foreach($attackerData as $attacker) {
            if($attacker->getFinalBlow() === true) {
                // is it a pilot
                if(!\is_null($attacker->getCharacterId())) {
                    /* @var $finalBlowPilotData \WordPress\EsiClient\Model\Character\CharactersCharacterId */
                    $finalBlowPilotData = $this->eveApi->getCharacterDataByCharacterId($attacker->getCharacterId());
                    $finalBlow = $finalBlowPilotData->getName();
                }

                // or maybe a structure?
                if(\is_null($finalBlow) && !\is_null($attacker->getShipTypeId())) {
                    /* @var $finalBlowItemData \WordPress\EsiClient\Model\Universe\UniverseTypesTypeId */
                    $finalBlowItemData = $this->eveApi->getShipDataByShipId($attacker->getShipTypeId());
                    $finalBlow = $finalBlowItemData->getName();
                }
            }
        }

        return $finalBlow;
    }

    public function getIskLoss(\stdClass $zkbData) {
        return $this->sanitizeIskLoss($zkbData->totalValue);
    }

    /**
     * getting the victims ship
     *
     * @param \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData
     * @return string
     */
    public function getVictimShip(\WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData) {
        $returnValue = null;

        /* @var $ship \WordPress\EsiClient\Model\Universe\UniverseTypesTypeId */
        $ship = $this->eveApi->getShipDataByShipId($victimData->getShipTypeId());

        if(!\is_null($ship)) {
            $returnValue = $ship->getName();
        }

        return $returnValue;
    }

    /**
     * getting the victims ship type
     *
     * @param \WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData
     * @return string
     */
    public function getVictimShipType(\WordPress\EsiClient\Model\Killmails\KillmailsKillmailId\Victim $victimData) {
        $returnValue = null;

        /* @var $shipTypeData \WordPress\EsiClient\Model\Universe\UniverseGroupsGroupId */
        $shipTypeData = $this->eveApi->getShipTypeFromShipId($victimData->getShipTypeId());

        if(!\is_nan($shipTypeData)) {
            $returnValue = $shipTypeData->getName();
        }

        return $returnValue;
    }

    /**
     * Getting the sytem name from ID
     *
     * @param type $systemID
     * @return string
     */
    public function getSystemInformation(int $systemID) {
        $returnValue = null;

        /* @var $system \WordPress\EsiClient\Model\Universe\UniverseSystemsSystemId */
        $system = $this->eveApi->getSystemDataBySystemId($systemID);

        if(!\is_null($system)) {
            $returnValue = $system;
        }

        return $returnValue;
    }

    /**
     * format the ISK loss value
     *
     * @param int $isk
     * @return string
     */
    private function sanitizeIskLoss($isk) {
        if($isk < 1000) {
            $isk = \number_format($isk, 2);
        } elseif(($isk/1000) < 1000) {
            $isk = \number_format(($isk/1000), 2) . 'K';
        } elseif(($isk/1000/1000) < 1000) {
            $isk = \number_format(($isk/1000/1000), 2) . 'M';
        } else {
            $isk = \number_format(($isk/1000/1000/1000), 2, '.', ',') . 'B';
        }

        return $isk;
    }
}
