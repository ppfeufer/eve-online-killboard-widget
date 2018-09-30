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

class CharacterRepository extends \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Swagger {
    /**
     * Used ESI enpoints in this class
     *
     * @var array ESI enpoints
     */
    protected $esiEndpoints = [
        'characters_characterId' => 'characters/{character_id}/?datasource=tranquility'
    ];

    /**
     * Find character data by charater ID
     *
     * @param int $characterID
     * @return object
     */
    public function charactersCharacterId($characterID) {
        $returnValue = null;

        $this->setEsiMethod('get');
        $this->setEsiRoute($this->esiEndpoints['characters_characterId']);
        $this->setEsiRouteParameter([
            '/{character_id}/' => $characterID
        ]);
        $this->setEsiVersion('v4');

        $characterData = $this->callEsi();

        if(!\is_null($characterData)) {
            $jsonMapper = new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Mapper\JsonMapper;
            $returnValue = $jsonMapper->map(\json_decode($characterData), new \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Esi\Model\Character\CharactersCharacterId);
        }

        return $returnValue;
    }
}
