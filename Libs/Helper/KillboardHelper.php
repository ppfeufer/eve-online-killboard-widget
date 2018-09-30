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

\defined('ABSPATH') or die();

class KillboardHelper extends \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
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
     * @var EveApiHelper
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
        $this->eveApi = EveApiHelper::getInstance();
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
            $entityIdTransientName = \sanitize_title('EVE Online :: ID ' . $widgetSettings['eve-online-killboard-widget-entity-type'] . '/' . $widgetSettings['eve-online-killboard-widget-entity-name']);
//            $this->entityID = $this->cacheHelper->getTransientCache('eve_online_killboard_widget_entity_id_' . \sanitize_title($widgetSettings['eve-online-killboard-widget-entity-name']));
            $this->entityID = $this->cacheHelper->getTransientCache($entityIdTransientName);

            if($this->entityID === false || empty($this->entityID)) {
                $this->entityID = $this->eveApi->getEveIdByName($widgetSettings['eve-online-killboard-widget-entity-name'], $widgetSettings['eve-online-killboard-widget-entity-type']);

                $this->cacheHelper->setTransientCache($entityIdTransientName, $this->entityID, \strtotime('+12 years'));
            }
        }

        $widgetSettingsTransientName = \sanitize_title('eve_online_killboard-' . $widgetSettings['eve-online-killboard-widget-entity-name'] . '-' . $widgetSettings['eve-online-killboard-widget-entity-id'] . '-' . \md5(\json_encode($widgetSettings)) . '.lastkills_kills-only.zkb_only');

        if((int) $widgetSettings['eve-online-killboard-widget-show-losses'] === 1) {
            $widgetSettingsTransientName = \sanitize_title('eve_online_killboard-' . $widgetSettings['eve-online-killboard-widget-entity-name'] . '-' . $widgetSettings['eve-online-killboard-widget-entity-id'] . '-' . \md5(\json_encode($widgetSettings)) . '.lastkills.zkb_only');
        }

        $data = $this->cacheHelper->getTransientCache($widgetSettingsTransientName);

        if($data === false || empty($data)) {
            $data = $this->getZkillboardData($widgetSettings);

            /**
             * setting the transient caches
             */
            $this->cacheHelper->setTransientCache($widgetSettingsTransientName, $data, \strtotime('+5 Minutes'));
        }

        return $data;
    }

    /**
     * Getting Zkillboard data
     *
     * @param array $widgetSettings
     * @return array
     */
    public function getZkillboardData($widgetSettings) {
        global $wp_version;

        $returnValue = null;

        $this->remoteHelper->setUserAgent('Killboard Widget for WordPress » https://github.com/ppfeufer/eve-online-killboard-widget // WordPress/' . $wp_version . '; ' . home_url());

        $zkbUrl = $this->zkbApiLink . 'kills/' . $widgetSettings['eve-online-killboard-widget-entity-type'] . 'ID/' . $this->entityID. '/npc/0/zkbOnly/';

        if((int) $widgetSettings['eve-online-killboard-widget-show-losses'] === 1) {
            $zkbUrl = $this->zkbApiLink . $widgetSettings['eve-online-killboard-widget-entity-type'] . 'ID/' . $this->entityID . '/npc/0/zkbOnly/';
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
            /* @var $killmail->killmail \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailsKillmailIdKillmailHash */
            $countAttackers = \count($killMailData->killmail->getAttackers());
            $stringInvolved = ($countAttackers - 1 === 0) ? '' : ' (+' . ($countAttackers - 1) . ')';

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
                        . '            <li>Loss: ' . $this->getVictimShip($killMailData->killmail->getVictim()) . '</li>'
                        . '            <li>ISK lost: ' . $this->getIskLoss($killMailData->zkbData) . '</li>'
                        . '            <li>System: ' . $systemInformation->name . ' (' . \round($systemInformation->security_status, 2) . ')</li>'
                        . '            <li>Killed by: ' . $this->getFinalBlow($killMailData->killmail->getAttackers()) . $stringInvolved . '</li>'
                        .              $this->getBadges($killMailData)
                        . '        </ul>'
                        . '    </div>'
                        . '</div>';
        }

        return $widgetHtml;
    }

    private function getBadges($killMail) {
        $returnValue = null;
        $badgeSoloKill = false;

        /* @var $killmail->killmail \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailsKillmailIdKillmailHash */
        if($killMail->zkbData->solo || \count($killMail->killmail->getAttackers()) === 1) {
            $badgeSoloKill = '<span class="eve-online-killboard-widget-solokill"><small>SOLO</small></span>';
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
                . '            <li>Pilot:</li>'
                . '            <li>Loss:</li>'
                . '            <li>ISK lost:</li>'
                . '            <li>System:</li>'
                . '            <li>Killed by:</li>'
                . '        </ul>'
                . '    </div>'
                . '</div>';
    }

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
        }

        return $returnValue;
    }

    /**
     * Getting the link to teh killmail on ZKB
     *
     * @param int $killID
     * @return string
     */
    public function getKillboardLink($killID) {
        return $this->zkbLink . 'kill/' . $killID . '/';
    }

    /**
     * Getting victims image
     *
     * @param \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData
     * @param int $size
     * @return string
     */
    public function getVictimImage(\WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData, $size = 256) {
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
     * @param \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData
     * @param int $size
     * @return string
     */
    public function getVictimCorpImage(\WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData, $size = 256) {
        $victimCorporationImage = null;

        if($victimData->getCorporationId()) {
            /* @var $corpData \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Corporation\CorporationsCorporationId */
            $corpData = $this->eveApi->getCorporationDataByCorporationId($victimData->getCorporationId());
            $imageUrl = $this->eveApi->getImageServerUrl() . $this->eveApi->getImageServerEndpont('corporation') . $victimData->getCorporationId() . '_' . $size. '.png';
            $victimCorporationImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-corporation-id-' . $victimData->getCorporationId() . '" alt="' . \esc_html($corpData->getName()) . '" data-title="' . \esc_html($corpData->getName()) . '" data-toggle="eve-killboard-tooltip">';
        }


        return $victimCorporationImage;
    }

    /**
     * Getting victims ship image
     *
     * @param \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData
     * @param int $size
     * @return type
     */
    public function getVictimShipImage(\WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData, $size = 256) {
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
     * @param \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData
     * @param type $size
     * @return string
     */
    public function getVictimAllianceImage(\WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData, $size = 128) {
        $victimAllianceImage = null;

        if(!\is_null($victimData->getAllianceId())) {
            /* @var $allianceData \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Alliance\AlliancesAllianceId */
            $allianceData = $this->eveApi->getAllianceDataByAllianceId($victimData->getAllianceId());
            $imageUrl = $this->eveApi->getImageServerUrl() . $this->eveApi->getImageServerEndpont('alliance') . $victimData->getAllianceId() . '_' . $size. '.png';
            $victimAllianceImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-alliance-id-' . $victimData->getAllianceId() . '" alt="' . \esc_html($allianceData->getName()) . '" data-title="' . \esc_html($allianceData->getName()) . '" data-toggle="eve-killboard-tooltip">';
        }

        return $victimAllianceImage;
    }

    /**
     * Getting the victims type
     *
     * @param \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData
     * @return string
     */
    public function getVictimType(\WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData) {
        $victimType = \__('Pilot', 'eve-online-killboard-widget');

        if(\is_null($victimData->getCharacterId())) {
            $victimType = \__('Corp', 'eve-online-killboard-widget');
        }

        return $victimType;
    }

    /**
     * Getting the victims name
     *
     * @param \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData
     * @return string
     */
    public function getVictimName(\WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData) {
        $victimType = $this->getVictimType($victimData);
        $victimName = null;

        switch($victimType) {
            case 'Pilot':
                /* @var $pilotData \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Character\CharactersCharacterId */
                $pilotData = $this->eveApi->getCharacterDataByCharacterId($victimData->getCharacterId());
                $victimName = $pilotData->getName();
                break;

            case 'Corp':
                $corpData = $this->eveApi->getCorporationDataByCorporationId($victimData->getCorporationId());
                $victimName = $corpData['data']->name;
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

        /* @var $attacker \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailAttacker */
        foreach($attackerData as $attacker) {
            if($attacker->getFinalBlow() === true) {
                /* @var $finalBlowPilotData \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Character\CharactersCharacterId */
                $finalBlowPilotData = $this->eveApi->getCharacterDataByCharacterId($attacker->getCharacterId());
                $finalBlow = $finalBlowPilotData->getName();
            }
        }

        return $finalBlow;
    }

    public function getIskLoss(\stdClass $zkbData) {
        return $this->sanitizeIskLoss($zkbData->totalValue);
    }

    /**
     * getting the victims ship type
     *
     * @param \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData
     * @return string
     */
    public function getVictimShip(\WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailVictim $victimData) {
        $ship = $this->eveApi->getShipDataByShipId($victimData->getShipTypeId());

        return $ship['data']->name;
    }

    /**
     * Getting the sytem name from ID
     *
     * @param type $systemID
     * @return string
     */
    public function getSystemInformation($systemID) {
        $system = $this->eveApi->getSystemDataBySystemId($systemID);

        return $system['data'];
    }

    private function sanitizeIskLoss($isk) {
        if($isk < 1000) {
            $isk = \number_format($isk, 0);
        } elseif(($isk/1000) < 1000) {
            $isk = \number_format(($isk/1000), 0) . 'K';
        } elseif(($isk/1000/1000) < 1000) {
            $isk = \number_format(($isk/1000/1000), 0) . 'M';
        } else {
            $isk = \number_format(($isk/1000/1000/1000), 0, '.', ',') . 'B';
        }

        return $isk;
    }
}
