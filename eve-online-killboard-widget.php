<?php
/**
 * Plugin Name: EVE Online Killboard Widget for WordPress
 * Plugin URI: https://github.com/ppfeufer/eve-online-killboard-widget
 * Git URI: https://github.com/ppfeufer/eve-online-killboard-widget
 * Description: A widget to display your latest kills and/or losses on your WordPress website.
 * Version: 0.5
 * Author: Rounon Dax
 * Author URI: http://yulaifederation.net
 * Text Domain: eve-online-killboard-widget
 * Domain Path: /l10n
 */

namespace WordPress\Plugin\EveOnlineKillboardWidget;
const WP_GITHUB_FORCE_UPDATE = true;

// Include the autoloader so we can dynamically include the rest of the classes.
require_once(\trailingslashit(\dirname(__FILE__)) . 'inc/autoloader.php');

class EveOnlineKillboardWidget {
	private $textDomain = null;
	private $localizationDirectory = null;
	private $pluginDir = null;
	private $pluginUri = null;

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
		$this->pluginDir =  \plugin_dir_path(__FILE__);
		$this->pluginUri = \trailingslashit(\plugins_url('/', __FILE__));
		$this->localizationDirectory = $this->pluginDir . '/l10n/';

		$this->loadTextDomain();
	} // END public function __construct()

	/**
	 * Initialize the plugin
	 */
	public function init() {
		// Loading CSS
		$cssLoader = new ResourceLoader\CssLoader;
		$cssLoader->init();

		// Loading JavaScript
		$javascriptLoader = new ResourceLoader\JavascriptLoader;
		$javascriptLoader->init();


		\add_action('widgets_init', \create_function('', 'return register_widget("WordPress\Plugin\EveOnlineKillboardWidget\Libs\KillboardWidget");'));

		new Libs\AjaxApi;
		/**
		 * start backend only libs
		 */
		if(\is_admin()) {
			/**
			 * Check Github for updates
			 */
			$githubConfig = array(
				'slug' => \plugin_basename(__FILE__),
				'proper_folder_name' => 'eve-online-killboard-widget',
				'api_url' => 'https://api.github.com/repos/ppfeufer/eve-online-killboard-widget',
				'raw_url' => 'https://raw.github.com/ppfeufer/eve-online-killboard-widget/master',
				'github_url' => 'https://github.com/ppfeufer/eve-online-killboard-widget',
				'zip_url' => 'https://github.com/ppfeufer/eve-online-killboard-widget/archive/master.zip',
				'sslverify' => true,
				'requires' => '4.7',
				'tested' => '4.8',
				'readme' => 'README.md',
				'access_token' => '',
			);

			new Libs\GithubUpdater($githubConfig);
		} // END if(\is_admin())
	} // END public function init()

	/**
	 * Setting up our text domain for translations
	 */
	public function loadTextDomain() {
		if(\function_exists('\load_plugin_textdomain')) {
			\load_plugin_textdomain($this->getTextDomain(), false, $this->getLocalizationDirectory());
		} // END if(function_exists('\load_plugin_textdomain'))
	} // END public function addTextDomain()

	/**
	 * Getting the Plugin's Textdomain for translations
	 *
	 * @return string Plugin Textdomain
	 */
	public function getTextDomain() {
		return $this->textDomain;
	} // END public function getTextDomain()

	/**
	 * Getting the Plugin's Localization Directory for translations
	 *
	 * @return string Plugin Localization Directory
	 */
	public function getLocalizationDirectory() {
		return $this->localizationDirectory;
	} // END public function getLocalizationDirectory()
} // END class EveOnlineFittingManager

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
} // END function initializePlugin()

// Start the show
\add_action('plugins_loaded', 'WordPress\Plugin\EveOnlineKillboardWidget\initializePlugin');
