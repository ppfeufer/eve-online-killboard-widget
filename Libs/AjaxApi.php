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

namespace WordPress\Plugins\EveOnlineKillboardWidget\Libs;

use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper\KillboardHelper;

\defined('ABSPATH') or die();

class AjaxApi {
    /**
     * Constructor
     */
    public function __construct() {
        $this->initActions();
    }

    /**
     * Initialize the actions
     */
    private function initActions() {
        \add_action('wp_ajax_nopriv_get-eve-killboard-widget-data', [$this, 'ajaxGetKillboardData']);
        \add_action('wp_ajax_get-eve-killboard-widget-data', [$this, 'ajaxGetKillboardData']);
    }

    /**
     * Getting the market data for a fitting
     */
    public function ajaxGetKillboardData() {
        $killList = KillboardHelper::getInstance()->getKillList([
            'eve-online-killboard-widget-entity-type' => \esc_html(\filter_input(\INPUT_POST, 'type')),
            'eve-online-killboard-widget-entity-name' => \esc_html(\filter_input(\INPUT_POST, 'name')),
            'eve-online-killboard-widget-entity-id' => \esc_html(\filter_input(\INPUT_POST, 'id')),
            'eve-online-killboard-widget-number-of-kills' => \filter_input(\INPUT_POST, 'count'),
            'eve-online-killboard-widget-show-losses' => \filter_input(\INPUT_POST, 'showLosses')
        ]);

        $widgetHtml = null;
        if(!empty($killList) && \is_array($killList)) {
            $widgetHtml = KillboardHelper::getInstance()->getWidgetHtml($killList);
        }

        \wp_send_json(['html' => $widgetHtml]);

        // always exit this API function
        exit;
    }
}
