<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\ResourceLoader;

\defined('ABSPATH') or die();

/**
 * CSS Loader
 */
class CssLoader implements \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Interfaces\AssetsInterface {
	/**
	 * Initialize the loader
	 */
	public function init() {
		\add_action('wp_enqueue_scripts', [$this, 'enqueue'], 99);
	} // END public function init()

	/**
	 * Load the styles
	 */
	public function enqueue() {
		/**
		 * Only in Frontend
		 */
		if(!\is_admin()) {
			\wp_enqueue_style('bootstrap', \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('bootstrap/css/bootstrap.min.css'));
			\wp_enqueue_style('eve-online-killboard-widget', \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('css/eve-online-killboard-widget.min.css'));
		} // END if(!\is_admin())
	} // END public function enqueue()
} // END class CssLoader implements \WordPress\Plugin\EveOnlineKillboardWidget\Interfaces\AssetsInterface
