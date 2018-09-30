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

namespace WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Repository;

\defined('ABSPATH') or die();

class UniverseRepository extends \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Swagger {
    /**
     * Used ESI enpoints in this class
     *
     * @var array ESI enpoints
     */
    protected $esiEndpoints = [
        'universe_ids' => 'universe/ids/?datasource=tranquility',
        'universe_types_typeID' => 'universe/types/{type_id}/?datasource=tranquility',
        'universe_systems_systemID' => 'universe/systems/{system_id}/?datasource=tranquility',
    ];

    /**
     * Find type data by type ID
     *
     * @param int $typeID
     * @return object
     */
    public function universeTypesTypeId($typeID) {
        $this->setEsiMethod('get');
        $this->setEsiRoute($this->esiEndpoints['universe_types_typeID']);
        $this->setEsiRouteParameter([
            '/{type_id}/' => $typeID
        ]);
        $this->setEsiVersion('v3');

        $typeData = $this->callEsi();

        if(!\is_null($typeData)) {
            $jsonMapper = new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Mapper\JsonMapper;
            $returnValue = $jsonMapper->map(\json_decode($typeData), new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Universe\UniverseTypesTypeId);
        }

        return $returnValue;
    }

    /**
     * Find system data by system ID
     *
     * @param int $systemID
     * @return object
     */
    public function universeSystemsSystemId($systemID) {
        $this->setEsiMethod('get');
        $this->setEsiRoute($this->esiEndpoints['universe_systems_systemID']);
        $this->setEsiRouteParameter([
            '/{system_id}/' => $systemID
        ]);
        $this->setEsiVersion('v4');

        $systemData = $this->callEsi();

        if(!\is_null($systemData)) {
            $jsonMapper = new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Mapper\JsonMapper;
            $returnData = $jsonMapper->map(\json_decode($systemData), new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Universe\UniverseSystemsSystemId);
        }

        return $returnData;
    }

    /**
     * Get the ID of a name in EVE
     *
     * @param array $names
     * @return object
     */
    public function universeIds(array $names) {
        $this->setEsiMethod('post');
        $this->setEsiPostParameter($names);
        $this->setEsiRoute($this->esiEndpoints['universe_ids']);
        $this->setEsiVersion('v1');

        $nameData = $this->callEsi();

        if(!\is_null($nameData)) {
            $jsonMapper = new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Mapper\JsonMapper;
            $returnData = $jsonMapper->map(\json_decode($nameData), new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Universe\UniverseIds);
        }

        return $returnData;
    }
}
