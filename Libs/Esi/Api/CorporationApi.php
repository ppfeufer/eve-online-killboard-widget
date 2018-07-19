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
namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Esi\Api;

\defined('ABSPATH') or die();

class CorporationApi extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Esi\Swagger {
    /**
     * Used ESI enpoints in this class
     *
     * @var array ESI enpoints
     */
    protected $esiEndpoints = [
        'corporations_corporationId' => 'corporations/{corporation_id}/?datasource=tranquility',
        'corporations_icons' => 'corporations/{corporation_id}/icons/?datasource=tranquility'
    ];

    /**
     * Find corporation data by corporation ID
     *
     * @param int $corporationID
     * @return object
     */
    public function findById($corporationID) {
        $this->esiRoute = \preg_replace('/{corporation_id}/', $corporationID, $this->esiEndpoints['corporations_corporationId']);
        $this->esiVersion = 'v4';

        $corporationData = $this->callEsi();

        return $corporationData;
    }
}
