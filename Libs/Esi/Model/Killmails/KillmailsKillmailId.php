<?php

/*
 * Copyright (C) 2018 ppfeufer
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

namespace WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails;

class KillmailsKillmailId {
    /**
     * attackers
     *
     * @var array
     */
    protected $attackers = null;

    /**
     * killmailId
     *
     * @var int
     */
    protected $killmailId = null;

    /**
     * killmailTime
     *
     * @var \DateTime
     */
    protected $killmailTime = null;

    /**
     * moonId
     *
     * @var int
     */
    protected $moonId = null;

    /**
     * solarSystemId
     *
     * @var int
     */
    protected $solarSystemId = null;

    /**
     * victim
     *
     * @var \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailsKillmailIdVictim
     */
    protected $victim = null;

    /**
     * warId
     *
     * @var int
     */
    protected $warId = null;

    /**
     * getAttackers
     *
     * @return array
     */
    public function getAttackers() {
        return $this->attackers;
    }

    /**
     * setAttackers
     *
     * @param array $attackers
     */
    public function setAttackers(array $attackers) {
        $mapper = new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Mapper\JsonMapper;

        $this->attackers = $mapper->mapArray($attackers, [], '\\WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailsKillmailIdAttacker');
    }

    /**
     * getKillmailId
     *
     * @return int
     */
    public function getKillmailId() {
        return $this->killmailId;
    }

    /**
     * setKillmailId
     *
     * @param int $killmailId
     */
    public function setKillmailId($killmailId) {
        $this->killmailId = $killmailId;
    }

    /**
     * getKillmailTime
     *
     * @return \DateTime
     */
    public function getKillmailTime() {
        return $this->killmailTime;
    }

    /**
     * setKillmailTime
     *
     * @param \DateTime $killmailTime
     */
    public function setKillmailTime(\DateTime $killmailTime) {
        $this->killmailTime = $killmailTime;
    }

    /**
     * getMoonId
     *
     * @return int
     */
    public function getMoonId() {
        return $this->moonId;
    }

    /**
     * setMoonId
     *
     * @param int $moonId
     */
    public function setMoonId($moonId) {
        $this->moonId = $moonId;
    }

    /**
     * getSolarSystemId
     *
     * @return int
     */
    public function getSolarSystemId() {
        return $this->solarSystemId;
    }

    /**
     * setSolarSystemId
     *
     * @param int $solarSystemId
     */
    public function setSolarSystemId($solarSystemId) {
        $this->solarSystemId = $solarSystemId;
    }

    /**
     * getVictim
     *
     * @return \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailsKillmailIdVictim
     */
    public function getVictim() {
        return $this->victim;
    }

    /**
     * setVictim
     *
     * @param \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailsKillmailIdVictim $victim
     */
    public function setVictim(\WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Killmails\KillmailsKillmailIdVictim $victim) {
        $this->victim = $victim;
    }

    /**
     * getWarId
     *
     * @return int
     */
    public function getWarId() {
        return $this->warId;
    }

    /**
     * setWarId
     *
     * @param int $warId
     */
    public function setWarId($warId) {
        $this->warId = $warId;
    }
}
