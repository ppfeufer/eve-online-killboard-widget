<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs;

\defined('ABSPATH') or die();

class AjaxApi {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->initActions();
	} // END public function __construct()

	/**
	 * Initialize the actions
	 */
	private function initActions() {
		\add_action('wp_ajax_nopriv_get-eve-killboard-widget-data', array($this, 'ajaxGetKillboardData'));
		\add_action('wp_ajax_get-eve-killboard-widget-data', array($this, 'ajaxGetKillboardData'));
	} // END private function initActions()

	/**
	 * Getting the market data for a fitting
	 */
	public function ajaxGetKillboardData() {
		$nonce = \filter_input(\INPUT_POST, 'nonce');
		if(!\wp_verify_nonce($nonce, 'ajax-nonce-eve-online-killboard-widget')) {
			die('Busted!');
		} // END if(!\wp_verify_nonce($nonce, 'ajax-nonce-eve-online-killboard-widget'))

		$killList = \WordPress\Plugin\EveOnlineKillboardWidget\Helper\KillboardHelper::getInstance()->getKillList([
			'eve-online-killboard-widget-entity-type' => \esc_html(\filter_input(\INPUT_POST, 'type')),
			'eve-online-killboard-widget-entity-name' => \esc_html(\filter_input(\INPUT_POST, 'name')),
			'eve-online-killboard-widget-number-of-kills' => \filter_input(\INPUT_POST, 'count'),
			'eve-online-killboard-widget-show-losses' => \filter_input(\INPUT_POST, 'showLosses')
		]);

		$widgetHtml = null;
		if(!empty($killList) && is_array($killList)) {
			$widgetHtml = \WordPress\Plugin\EveOnlineKillboardWidget\Helper\KillboardHelper::getInstance()->getWidgetHtml($killList);
		} // END if(!empty($killList) && is_array($killList))

		echo \json_encode(['html' => $widgetHtml]);

		// always exit this API function
		exit;
	} // END public function ajaxGetKillboardData()
} // END class AjaxApi
