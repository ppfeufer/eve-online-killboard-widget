<?php

/**
 * Debug Helper
 *
 * Getting some stuff from CCP's EVE API
 */

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class DebugHelper {
    public static function debug($debug) {
        echo '<pre>' . print_r($debug, true) . '</pre>';
    }
}
