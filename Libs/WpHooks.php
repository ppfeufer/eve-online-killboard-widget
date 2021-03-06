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

use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper\UpdateHelper;
use \WordPress\Plugins\EveOnlineKillboardWidget\Libs\Helper\PluginHelper;

\defined('ABSPATH') or die();

class WpHooks {
    /**
     * Path to the plugin main file
     *
     * @var string
     */
    private $pluginFile = null;

    /**
     * Constructor
     *
     * @param array $parameter array with parameters
     */
    public function __construct() {
        $this->pluginFile = PluginHelper::getInstance()->getPluginPath('eve-online-killboard-widget.php');

        $this->init();
    }

    /**
     * Initialize all the needed hooks, filter, actions and so on
     */
    public function init() {
        $this->initHooks();
        $this->initActions();
        $this->initFilter();
    }

    /**
     * Initialize our hooks
     */
    public function initHooks() {
        \register_activation_hook($this->pluginFile, [UpdateHelper::getInstance(), 'checkDatabaseForUpdates']);
        \register_activation_hook($this->pluginFile, [UpdateHelper::getInstance(), 'checkEsiClientForUpdates']);

        \register_deactivation_hook($this->pluginFile, [$this, 'removeDatabaseVersionOnDeactivation']);
    }

    /**
     * Initialize our actions
     */
    public function initActions() {
        /**
         * in case of plugin update this need to be fired
         * since the activation doesn't fire on update
         * thx wordpress for removing update hooks ...
         */
        \add_action('plugins_loaded', [UpdateHelper::getInstance(), 'checkDatabaseForUpdates']);
        \add_action('plugins_loaded', [UpdateHelper::getInstance(), 'checkEsiClientForUpdates']);

        \add_action('widgets_init', [$this, 'registerWidget']);
    }

    /**
     * Initializing our filter
     */
    public function initFilter() {
        \add_filter('plugin_row_meta', [$this, 'addPluginRowMeta'], 10, 2);
    }

    /**
     * Ading some links to the plugin row meta data
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function addPluginRowMeta($links, $file) {
        if(\strpos($file, 'eve-online-killboard-widget.php') !== false) {
            $new_links = [
                'issue_tracker' => '<a href="https://github.com/ppfeufer/eve-online-killboard-widget/issues" target="_blank">GitHub Issue Tracker</a>',
                'support_discord' => '<a href="https://discord.gg/YymuCZa" target="_blank">Support Discord</a>'
            ];

            $links = \array_merge($links, $new_links);
        }

        return $links;
    }

    /**
     * Registering the widget
     */
    public function registerWidget() {
        \register_widget("\\WordPress\Plugins\EveOnlineKillboardWidget\Libs\KillboardWidget");
    }

    /**
     * Removing the DB version on plugin decativation
     * Issue: https://github.com/ppfeufer/eve-online-killboard-widget/issues/50
     */
    public function removeDatabaseVersionOnDeactivation() {
        \delete_option(UpdateHelper::getInstance()->getDatabaseFieldName());
    }
}
