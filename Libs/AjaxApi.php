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

		$entityType = \filter_input(\INPUT_POST, 'type');
		$entityName  = \filter_input(\INPUT_POST, 'name');
		$numberOfKills = \filter_input(\INPUT_POST, 'count');
		$showLosses = \filter_input(\INPUT_POST, 'showLosses');

		$killList = \WordPress\Plugin\EveOnlineKillboardWidget\Helper\KillboardHelper::getInstance()->getKillList($entityType, $entityName, $numberOfKills, $showLosses);

		$widgetHtml = null;
		if(!empty($killList) && is_array($killList)) {
			$widgetHtml = \WordPress\Plugin\EveOnlineKillboardWidget\Helper\KillboardHelper::getInstance()->getWidgetHtml($killList);
		} // END if(!empty($killList) && is_array($killList))

		echo \json_encode(['html' => $widgetHtml]);

		// always exit this API function
		exit;
	} // END public function ajaxGetKillboardData()
} // END class AjaxApi
