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

class KillmailsApi extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Esi\Swagger {
    /**
     * Used ESI enpoints in this class
     *
     * @var array ESI enpoints
     */
    protected $esiEndpoints = [
        'killmails_killmailid_killmailhash' => 'killmails/{killmail_id}/{killmail_hash}/?datasource=tranquility'
    ];

    public function getPublicKillmail($killmailID, $killmailHash) {
        $this->setEsiMethod('get');
        $this->setEsiRoute($this->esiEndpoints['killmails_killmailid_killmailhash']);
        $this->setEsiRouteParameter([
            '/{killmail_id}/' => $killmailID,
            '/{killmail_hash}/' => $killmailHash
        ]);
        $this->setEsiVersion('v1');

        $allianceData = $this->callEsi();

        return $allianceData;
    }
}
