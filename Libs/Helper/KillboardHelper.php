<?php

/**
 * Copyright (C) 2017 Rounon Dax
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

/**
 * Killboard Widget
 */

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class KillboardHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
    private $zkbLink = null;
    private $zkbApiLink = null;
    private $entityID = null;
    private $eveApi = null;

    /**
     * constructor
     *
     * no external instanciation allowed
     */
    protected function __construct() {
        parent::__construct();

        $this->eveApi = EveApiHelper::getInstance();
        $this->zkbApiLink = 'https://zkillboard.com/api/';
        $this->zkbLink = 'https://zkillboard.com/';
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
            $this->entityID = \get_transient('eve_online_killboard_widget_entity_id_' . \sanitize_title($widgetSettings['eve-online-killboard-widget-entity-name']));

            if($this->entityID === false || empty($this->entityID)) {
                $this->entityID = $this->eveApi->getEveIdFromName($widgetSettings['eve-online-killboard-widget-entity-name'], $widgetSettings['eve-online-killboard-widget-entity-type']);

                \set_transient('eve_online_killboard_widget_entity_id_' . \sanitize_title($widgetSettings['eve-online-killboard-widget-entity-name']), $this->entityID, 1800);
            }
        }

        $transientName = \sanitize_title('eve_online_killboard-' . \md5(\json_encode($widgetSettings)) . '.lastkills_kills-only');

        if((int) $widgetSettings['eve-online-killboard-widget-show-losses'] === 1) {
            $transientName = \sanitize_title('eve_online_killboard-' . \md5(\json_encode($widgetSettings)) . '.lastkills');
        }

        $data = \get_transient($transientName);

        if($data === false || empty($data)) {
            $zkbUrl = $this->zkbApiLink . 'kills/' . $widgetSettings['eve-online-killboard-widget-entity-type'] . 'ID/' . $this->entityID. '/npc/0/';

            if((int) $widgetSettings['eve-online-killboard-widget-show-losses'] === 1) {
                $zkbUrl = $this->zkbApiLink . $widgetSettings['eve-online-killboard-widget-entity-type'] . 'ID/' . $this->entityID . '/npc/0/';
            }

            $zkbData = RemoteHelper::getInstance()->getRemoteData($zkbUrl);

            $data = \array_slice(\json_decode($zkbData), 0, (int) $widgetSettings['eve-online-killboard-widget-number-of-kills'], true);

            /**
             * setting the transient caches
             */
            \set_transient($transientName, $data, 300);
        }

        return $data;
    }

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
            }

            $systemInformation = $this->getSystemInformation($killMail->solar_system_id);
            $widgetHtml .= '<div class="row killboard-entry' . $killType . '">'
                        . '    <div class="col-xs-4 col-sm-12 col-md-12 col-lg-5">'
                        . '        <figure>'
                        . '            <a href="' . $this->getKillboardLink($killMail->killmail_id) . '" rel="external" target="_blank">'
                        .                $this->getVictimImage($killMail->victim)
                        . '            </a>'
                        . '            <div class="eve-online-killboard-widget-pilot-information">'
                        . '                <span class="victimShipImage">'
                        .                    $this->getVictimShipImage($killMail->victim, 32)
                        . '                </span>'
                        . '                <span class="victimCorpImage">'
                        .                    $this->getVictimCorpImage($killMail->victim, 32)
                        . '                </span>'
                        . '                <span class="victimAllianceImage">'
                        .                    $this->getVictimAllianceImage($killMail->victim, 32)
                        . '                </span>'
                        . '            </div>'
                        . '        </figure>'
                        . '    </div>'
                        . '    <div class="col-xs-8 col-sm-12 col-md-12 col-lg-7">'
                        . '        <ul>'
                        . '            <li>' . $this->getVictimType($killMail->victim) . ': ' . $this->getVictimName($killMail->victim) . '</li>'
                        . '            <li>Loss: ' . $this->getVictimShip($killMail->victim) . '</li>'
                        . '            <li>ISK lost: ' . $this->getIskLoss($killMail->zkb) . '</li>'
                        . '            <li>System: ' . $systemInformation->name . ' (' . \round($systemInformation->security_status, 2) . ')</li>'
                        . '            <li>Killed by: ' . $this->getFinalBlow($killMail->attackers) . $stringInvolved . '</li>'
                        . '        </ul>'
                        . '    </div>'
                        . '</div>';
        }

        return $widgetHtml;
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
     * @param \stdClass $victimData
     * @param int $size
     * @return string
     */
    public function getVictimImage(\stdClass $victimData, $size = 256) {
        $victimImage = null;

        if(isset($victimData->character_id)) {
            $imageUrl = $this->eveApi->getImageServerUrl() . $this->eveApi->getImageServerEndpont('character') . $victimData->character_id . '_' . $size. '.jpg';
            $victimImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-character-id-' . $victimData->character_id . '" alt="' . \esc_html($this->getVictimName($victimData)) . '">';
        }

        if(!isset($victimData->character_id)) {
            $victimImage = '<img src="' . $this->eveApi->getImageServerUrl() . $this->eveApi->getImageServerEndpont('render') . $victimData->ship_type_id . '_' . $size . '.png' . '" class="eve-structure-image eve-online-id-' . $victimData->ship_type_id . '">';
        }

        return $victimImage;
    }

    public function getVictimCorpImage(\stdClass $victimData, $size = 256) {
        $victimCorporationImage = null;

        if($victimData->corporation_id) {
            $corpData = $this->eveApi->getCorporationData($victimData->corporation_id);
            $imageUrl = $this->eveApi->getImageServerUrl() . $this->eveApi->getImageServerEndpont('corporation') . $victimData->corporation_id . '_' . $size. '.png';
            $victimCorporationImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-corporation-id-' . $victimData->corporation_id . '" alt="' . \esc_html($corpData['data']->name) . '" data-title="' . \esc_html($corpData['data']->name) . '" data-toggle="eve-killboard-tooltip">';
        }


        return $victimCorporationImage;
    }

    public function getVictimShipImage(\stdClass $victimData, $size = 256) {
        $victimShipImage = null;

        switch($victimData->ship_type_id) {
            case 0:
                $victimShipImage = null;
                break;

            default:
                $victimShipImage = $this->eveApi->getShipImageById($victimData->ship_type_id, false, $size);
                break;
        }

        return $victimShipImage;
    }

    public function getVictimAllianceImage(\stdClass $victimData, $size = 128) {
        $victimAllianceImage = null;

        if(isset($victimData->alliance_id)) {
            $allianceData = $this->eveApi->getAllianceData($victimData->alliance_id);
            $imageUrl = $this->eveApi->getImageServerUrl() . $this->eveApi->getImageServerEndpont('alliance') . $victimData->alliance_id . '_' . $size. '.png';
            $victimAllianceImage = '<img src="' . $imageUrl . '" class="eve-character-image eve-alliance-id-' . $victimData->alliance_id . '" alt="' . \esc_html($allianceData['data']->name) . '" data-title="' . \esc_html($allianceData['data']->name) . '" data-toggle="eve-killboard-tooltip">';
        }

        return $victimAllianceImage;
    }

    /**
     * Getting the victims type
     *
     * @param \stdClass $victimData
     * @return string
     */
    public function getVictimType(\stdClass $victimData) {
        $victimType = \__('Pilot', 'eve-online-killboard-widget');

        if(!isset($victimData->character_id)) {
            $victimType = \__('Corp', 'eve-online-killboard-widget');
        }

        return $victimType;
    }

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
                $pilotData = $this->eveApi->getCharacterData($victimData->character_id);
                $victimName = $pilotData['data']->name;
                break;

            case 'Corp':
                $corpData = $this->eveApi->getCorporationData($victimData->corporation_id);
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

        foreach($attackerData as $attacker) {
            if($attacker->final_blow === true) {
                $finalBlowPilotData = $this->eveApi->getCharacterData($attacker->character_id);
                $finalBlow = $finalBlowPilotData['data']->name;
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
     * @param \stdClass $victimData
     * @return string
     */
    public function getVictimShip(\stdClass $victimData) {
        $ship = $this->eveApi->getShipData($victimData->ship_type_id);

        return $ship['data']->name;
    }

    /**
     * Getting the sytem name from ID
     *
     * @param type $systemID
     * @return string
     */
    public function getSystemInformation($systemID) {
        $system = $this->eveApi->getSystemData($systemID);

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
