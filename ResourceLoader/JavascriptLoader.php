<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\ResourceLoader;

\defined('ABSPATH') or die();

/**
 * JavaScript Loader
 */
class JavascriptLoader implements \WordPress\Plugin\EveOnlineKillboardWidget\Interfaces\AssetsInterface {
	/**
	 * Initialize the loader
	 */
	public function init() {
		\add_action('wp_enqueue_scripts', array($this, 'enqueue'), 99);
	} // END public function init()

	/**
	 * Load the JavaScript
	 */
	public function enqueue() {
		/**
		 * Only in Frontend
		 */
		if(!\is_admin()) {
			\wp_enqueue_script('bootstrap-js', \WordPress\Plugin\EveOnlineKillboardWidget\Helper\PluginHelper::getInstance()->getPluginUri('bootstrap/js/bootstrap.min.js'), array('jquery'), '', true);
			\wp_enqueue_script('bootstrap-toolkit-js', \WordPress\Plugin\EveOnlineKillboardWidget\Helper\PluginHelper::getInstance()->getPluginUri('bootstrap/bootstrap-toolkit/bootstrap-toolkit.min.js'), array('jquery', 'bootstrap-js'), '', true);
			\wp_enqueue_script('eve-online-killboard-widget-js', \WordPress\Plugin\EveOnlineKillboardWidget\Helper\PluginHelper::getInstance()->getPluginUri('js/eve-online-killboard-widget.min.js'), array('jquery'), '', true);
			\wp_localize_script('eve-online-killboard-widget-js', 'killboardWidgetL10n', $this->getJavaScriptTranslations());
		} // END if(!\is_admin())
	} // END public function enqueue()

	/**
	 * Getting teh translation array to translate strings in JavaScript
	 *
	 * @return array
	 */
	private function getJavaScriptTranslations() {
		return array(
			'ajax' => array(
				'url' => \admin_url('admin-ajax.php'),
				'loaderImage' => \WordPress\Plugin\EveOnlineKillboardWidget\Helper\PluginHelper::getInstance()->getPluginUri('images/loader-sprite.gif'),
				'eveKillboardWidget' => array(
					'nonce' => \wp_create_nonce('ajax-nonce-eve-online-killboard-widget')
				)
			)
		);
	} // END private function getJavaScriptTranslations()
} // END class JavascriptLoader implements \WordPress\Plugin\EveOnlineKillboardWidget\Interfaces\AssetsInterface
