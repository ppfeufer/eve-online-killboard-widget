<?php

/**
 * Plugin Name: EVE Online Killboard Widget for WordPress
 * Plugin URI: https://github.com/ppfeufer/eve-online-killboard-widget
 * Git URI: https://github.com/ppfeufer/eve-online-killboard-widget
 * Description: A widget to display your latest kills and/or losses on your WordPress website.
 * Version: 0.25.0
 * Author: Rounon Dax
 * Author URI: http://yulaifederation.net
 * Text Domain: eve-online-killboard-widget
 * Domain Path: /l10n
 */

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

namespace WordPress\Plugins\EveOnlineKillboardWidget;
const WP_GITHUB_FORCE_UPDATE = true;

// Include the autoloader so we can dynamically include the rest of the classes.
require_once(\trailingslashit(\dirname(__FILE__)) . 'inc/autoloader.php');

class EveOnlineKillboardWidget {
    private $textDomain = null;
    private $localizationDirectory = null;
    private $pluginDir = null;

    /**
     * Database version
     *
     * @var string
     */
    private $databaseVersion = null;

    /**
     * Plugin constructor
     *
     * @param boolean $init
     */
    public function __construct() {
        /**
         * Initializing Variables
         */
        $this->textDomain = 'eve-online-killboard-widget';
        $this->pluginDir = \plugin_dir_path(__FILE__);
        $this->localizationDirectory = $this->pluginDir . '/l10n/';
        $this->databaseVersion = '20180914';

        $this->loadTextDomain();
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Firing hooks
        new Libs\WpHooks([
            'newDatabaseVersion' => $this->databaseVersion
        ]);

        // Loading CSS
        $cssLoader = new Libs\ResourceLoader\CssLoader;
        $cssLoader->init();

        // Loading JavaScript
        $javascriptLoader = new Libs\ResourceLoader\JavascriptLoader;
        $javascriptLoader->init();

        new Libs\AjaxApi;

        // Initialize the widget
        \add_action('widgets_init', \create_function('', 'return register_widget("WordPress\Plugins\EveOnlineKillboardWidget\Libs\KillboardWidget");'));

        /**
         * start backend only libs
         */
        if(\is_admin()) {
            /**
             * Check Github for updates
             */
            $githubConfig = [
                'slug' => \plugin_basename(__FILE__),
                'proper_folder_name' => Libs\Helper\PluginHelper::getInstance()->getPluginDirName(),
                'api_url' => 'https://api.github.com/repos/ppfeufer/eve-online-killboard-widget',
                'raw_url' => 'https://raw.github.com/ppfeufer/eve-online-killboard-widget/master',
                'github_url' => 'https://github.com/ppfeufer/eve-online-killboard-widget',
                'zip_url' => 'https://github.com/ppfeufer/eve-online-killboard-widget/archive/master.zip',
                'sslverify' => true,
                'requires' => '4.7',
                'tested' => '4.8',
                'readme' => 'README.md',
                'access_token' => '',
            ];

            new Libs\GithubUpdater($githubConfig);
        }
    }

    /**
     * Setting up our text domain for translations
     */
    public function loadTextDomain() {
        if(\function_exists('\load_plugin_textdomain')) {
            \load_plugin_textdomain($this->getTextDomain(), false, $this->getLocalizationDirectory());
        }
    }

    /**
     * Getting the Plugin's Textdomain for translations
     *
     * @return string Plugin Textdomain
     */
    public function getTextDomain() {
        return $this->textDomain;
    }

    /**
     * Getting the Plugin's Localization Directory for translations
     *
     * @return string Plugin Localization Directory
     */
    public function getLocalizationDirectory() {
        return $this->localizationDirectory;
    }
}

/**
 * Start the show ....
 */
function initializePlugin() {
    $killboardWidget = new EveOnlineKillboardWidget;

    /**
     * Initialize the plugin
     *
     * @todo https://premium.wpmudev.org/blog/activate-deactivate-uninstall-hooks/
     */
    $killboardWidget->init();
}

// Start the show
initializePlugin();
