<?php
/**
 * Killboard Widget
 */

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs;

class KillboardWidget extends \WP_Widget {
	/**
	 * Root ID for all widgets of this type.
	 *
	 * @since 2.8.0
	 * @access public
	 * @var mixed|string
	 */
	public $id_base;

	/**
	 * Name for this widget type.
	 *
	 * @since 2.8.0
	 * @access public
	 * @var string
	 */
	public $name;

	public function __construct() {
		$widget_options = array(
			'classname' => 'eve-online-killboard-widget',
			'description' => \__('Displaying the latest kills (and maybe losses if you are tough enough) in your sidebar.', 'eve-online-killboard-widget')
		);

		$control_options = array();

		parent::__construct('eve_online_killboard_widget', __('EVE Online Killboard Widget', 'eve-online-killboard-widget'), $widget_options, $control_options);
	} // END public function __construct($id_base, $name, $widget_options = array(), $control_options = array())

	/**
	 * The widgets settings form
	 *
	 * @param type $instance
	 */
	public function form($instance) {
		/**
		 * Standardwerte
		 *
		 * @var array
		 */
		$instance = \wp_parse_args((array) $instance, array(
			'eve-online-killboard-widget-title' => '',
			'eve-online-killboard-widget-number-of-kills' => 5,
			'eve-online-killboard-widget-entity-type' => '',
			'eve-online-killboard-widget-entity-name' => '',
			'eve-online-killboard-widget-show-losses' => false
		));

		$showLosses = $instance['eve-online-killboard-widget-show-losses'] ? 'checked="checked"' : '';

		// Titel
		echo '<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . \__('Title', 'eve-online-killboard-widget') . '</strong></p>';
		echo '<p><input id="' . $this->get_field_id('eve-online-killboard-widget-title') . '" name="' . $this->get_field_name('eve-online-killboard-widget-title') . '" type="text" value="' . $instance['eve-online-killboard-widget-title'] . '"></p>';
		echo '<p style="clear:both;"></p>';

		// Entity tyoe (Corporation / Alliance)
		echo '<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . \__('Corp or Alliance?', 'eve-online-killboard-widget') . '</strong></p>';
		echo '<p><input id="' . $this->get_field_id('eve-online-killboard-widget-entity-type') . '" name="' . $this->get_field_name('eve-online-killboard-widget-entity-type') . '" type="text" value="' . $instance['eve-online-killboard-widget-entity-type'] . '"></p>';
		echo '<p style="clear:both;"></p>';

		// Entity name
		echo '<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . \__('Name', 'eve-online-killboard-widget') . '</strong></p>';
		echo '<p><input id="' . $this->get_field_id('eve-online-killboard-widget-entity-name') . '" name="' . $this->get_field_name('eve-online-killboard-widget-entity-name') . '" type="text" value="' . $instance['eve-online-killboard-widget-entity-name'] . '"></p>';
		echo '<p style="clear:both;"></p>';

		// Number of kills
		echo '<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . \__('Number of kills to show', 'eve-online-killboard-widget') . '</strong></p>';
		echo '<p><input id="' . $this->get_field_id('eve-online-killboard-widget-number-of-kills') . '" name="' . $this->get_field_name('eve-online-killboard-widget-number-of-kills') . '" type="text" value="' . $instance['eve-online-killboard-widget-number-of-kills'] . '"></p>';
		echo '<p style="clear:both;"></p>';

		// Show losses
		echo '<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . \__('Losses', 'eve-online-killboard-widget') . '</strong></p>';
		echo '<p><label><input class="checkbox" type="checkbox" ' . $showLosses . ' id="' . $this->get_field_id('eve-online-killboard-widget-show-losses') . '" name="' . $this->get_field_name('eve-online-killboard-widget-show-losses') . '"> <span>' . \__('Show losses as well?', 'eve-online-killboard-widget') . '</span></label></p>';
		echo '<p style="clear:both;"></p>';
	} // END public function form($instance)

	/**
	 * Update Widget Setting
	 *
	 * @param type $new_instance
	 * @param type $old_instance
	 */
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;

		/**
		 * Set defaults
		 *
		 * @var array
		 */
		$new_instance = \wp_parse_args((array) $new_instance, array(
			'eve-online-killboard-widget-title' => '',
			'eve-online-killboard-widget-number-of-kills' => 5,
			'eve-online-killboard-widget-entity-type' => '',
			'eve-online-killboard-widget-entity-name' => '',
			'eve-online-killboard-widget-show-losses' => false
		));

		/**
		 * Sanitize the stuff rom our widget's form
		 *
		 * @var array
		 */
		$instance['eve-online-killboard-widget-title'] = (string) \esc_html($new_instance['eve-online-killboard-widget-title']);
		$instance['eve-online-killboard-widget-entity-type'] = (string) \esc_html($new_instance['eve-online-killboard-widget-entity-type']);
		$instance['eve-online-killboard-widget-entity-name'] = (string) \esc_html($new_instance['eve-online-killboard-widget-entity-name']);
		$instance['eve-online-killboard-widget-number-of-kills'] = (int) $new_instance['eve-online-killboard-widget-number-of-kills'];
		$instance['eve-online-killboard-widget-show-losses'] = $new_instance['eve-online-killboard-widget-show-losses'] ? 1 : 0;

		/**
		 * return new settings for saving them
		 */
		return $instance;
	} // END public function update($new_instance, $old_instance)

	/**
	 * Widget Output
	 *
	 * @param type $args
	 * @param type $instance
	 */
	public function widget($args, $instance) {
		echo $args['before_widget'];

		$title = (empty($instance['eve-online-killboard-widget-title'])) ? '' : \apply_filters('eve-online-killboard-widget-title', $instance['eve-online-killboard-widget-title']);

		if(!empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		} // END if(!empty($title))

		echo $this->getWidgetData($instance);
		echo $args['after_widget'];
	} // END public function widget($args, $instance)

	private function getWidgetData($instance) {
		$killList = YulaiFederation\Plugins\Helper\ZkbKillboardHelper::getInstance()->getKillList($instance['eve-online-killboard-widget-entity-type'], $instance['eve-online-killboard-widget-entity-name'], $instance['eve-online-killboard-widget-number-of-kills']);

		if(!empty($killList) && is_array($killList)) {
			$widgetHtml = null;

			foreach($killList as $killmail) {
				$countAttackers = \count($killmail->attackers);
				$stringInvolved = ($countAttackers - 1 === 0) ? '' : ' (+' . ($countAttackers - 1) . ')';

				$killType = ' kill-list-kill-mail';
				if($killmail->victim->corporationID === (int) $this->entityID || $killmail->victim->allianceID === (int) $this->entityID) {
					$killType = ' kill-list-loss-mail';
				} // END if($killmail->victim->corporationID === $this->entityID || $killmail->victim->allianceID === $this->entityID)

				$widgetHtml .= '<div class="row killboard-entry' . $killType . '">'
							. '	<div class="col-xs-4 col-sm-12 col-md-12 col-lg-5">'
							. '		<figure>'
							. '			<a href="' . YulaiFederation\Plugins\Helper\ZkbKillboardHelper::getInstance()->getKillboardLink($killmail->killID) . '" rel="external">'
							.				YulaiFederation\Plugins\Helper\ZkbKillboardHelper::getInstance()->getVictimImage($killmail->victim)
							. '			</a>'
							. '		</figure>'
							. '	</div>'
							. '	<div class="col-xs-8 col-sm-12 col-md-12 col-lg-7">'
							. '		<ul>'
							. '			<li>' . YulaiFederation\Plugins\Helper\ZkbKillboardHelper::getInstance()->getVictimType($killmail->victim) . ': ' . YulaiFederation\Plugins\Helper\ZkbKillboardHelper::getInstance()->getVictimName($killmail->victim) . '</li>'
							. '			<li>' . YulaiFederation\Plugins\Helper\ZkbKillboardHelper::getInstance()->getVictimShipType($killmail->victim) . ': ' . YulaiFederation\Plugins\Helper\ZkbKillboardHelper::getInstance()->getVictimShip($killmail->victim) . '</li>'
							. '			<li>ISK lost: ' . YulaiFederation\Plugins\Helper\ZkbKillboardHelper::getInstance()->getIskLoss($killmail->zkb) . '</li>'
							. '			<li>System: ' . YulaiFederation\Plugins\Helper\ZkbKillboardHelper::getInstance()->getSystem($killmail->solarSystemID) . '</li>'
							. '			<li>Killed by: ' . YulaiFederation\Plugins\Helper\ZkbKillboardHelper::getInstance()->getFinalBlow($killmail->attackers) . $stringInvolved . '</li>'
							. '		</ul>'
							. '	</div>'
							. '</div>';
			} // END foreach($array as $killmail)
		} // END if(!empty($killList) && is_array($killList))

		return $widgetHtml;
	} // END private function getWidgetData($instance)
} // END class KillboardWidget extends \WP_Widgets
