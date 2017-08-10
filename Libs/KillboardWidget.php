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
			'eve-online-killboard-widget-show-losses' => false,
			'eve-online-killboard-widget-static-cache' => false
		));

		$showLosses = $instance['eve-online-killboard-widget-show-losses'] ? 'checked="checked"' : '';
		$staticCache = $instance['eve-online-killboard-widget-static-cache'] ? 'checked="checked"' : '';

		$typeArray = [
			'corporation' => \__('Corporation', 'eve-online-killboard-widget'),
			'alliance' => \__('Alliance', 'eve-online-killboard-widget')
		];

		// Titel
		echo '<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . \__('Title', 'eve-online-killboard-widget') . '</strong></p>';
		echo '<p><input id="' . $this->get_field_id('eve-online-killboard-widget-title') . '" name="' . $this->get_field_name('eve-online-killboard-widget-title') . '" type="text" value="' . $instance['eve-online-killboard-widget-title'] . '"></p>';
		echo '<p style="clear:both;"></p>';

		// Entity type (Corporation / Alliance)
		echo '<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . \__('Corp or Alliance?', 'eve-online-killboard-widget') . '</strong></p>';
		echo '<p><select id="' . $this->get_field_id('eve-online-killboard-widget-entity-type') . '" name="' . $this->get_field_name('eve-online-killboard-widget-entity-type') . '">';

		foreach($typeArray as $value => $output) {
			$selected = '';
			if($instance['eve-online-killboard-widget-entity-type'] === $value) {
				$selected = ' selected';
			} // END if($instance['eve-online-killboard-widget-entity-type'] === $value)

			echo '<option value="' . $value . '"' . $selected . '>' . $output . '</option>';
		} // END foreach($typeArray as $value => $output)

		echo '</select></p>';
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

		// Cache patch
		echo '<p style="border-bottom: 1px solid #DFDFDF;"><strong>' . \__('Static cache workaround', 'eve-online-killboard-widget') . '</strong></p>';
		echo '<p><label><input class="checkbox" type="checkbox" ' . $staticCache . ' id="' . $this->get_field_id('eve-online-killboard-widget-static-cache') . '" name="' . $this->get_field_name('eve-online-killboard-widget-static-cache') . '"> <span>' . \__('FC, I am using a static cache, halp!!', 'eve-online-killboard-widget') . '</span></label></p>';
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
			'eve-online-killboard-widget-show-losses' => false,
			'eve-online-killboard-widget-static-cache' => false
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
		$instance['eve-online-killboard-widget-static-cache'] = $new_instance['eve-online-killboard-widget-static-cache'] ? 1 : 0;

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


		$widgetHtml = null;

		switch($instance['eve-online-killboard-widget-static-cache']) {
			case 0:
				$widgetHtml = $this->getWidgetData($instance);
				break;

			case 1:
				$widgetHtml = '<div class="killboard-widget-kill-list">';
				$widgetHtml .= '<p>' . \__('Loading killboard data, please wait ...') . '</p>';
				$widgetHtml .= '<p><span class="loaderImage"></span></p>';
				$widgetHtml .= '</div>';
				$widgetHtml .= $args['after_widget'];
				$widgetHtml .= '<script type="text/javascript">'
					. 'var entityType = "' . $instance['eve-online-killboard-widget-entity-type'] . '";' . "\n"
					. 'var entityName = "' . $instance['eve-online-killboard-widget-entity-name'] . '";' . "\n"
					. 'var killCount = ' . $instance['eve-online-killboard-widget-number-of-kills'] . ';' . "\n"
					. 'var showLosses = ' . $instance['eve-online-killboard-widget-show-losses'] . ';' . "\n"
					. '</script>';
				break;
		}



		echo $widgetHtml;
	} // END public function widget($args, $instance)

	private function getWidgetData($instance) {
		$widgetHtml = null;
		$killList = \WordPress\Plugin\EveOnlineKillboardWidget\Helper\KillboardHelper::getInstance()->getKillList($instance['eve-online-killboard-widget-entity-type'], $instance['eve-online-killboard-widget-entity-name'], $instance['eve-online-killboard-widget-number-of-kills'], $instance['eve-online-killboard-widget-show-losses']);

		if(!empty($killList) && is_array($killList)) {
			$widgetHtml = \WordPress\Plugin\EveOnlineKillboardWidget\Helper\KillboardHelper::getInstance()->getWidgetHtml($killList);
		} // END if(!empty($killList) && is_array($killList))

		return $widgetHtml;
	} // END private function getWidgetData($instance)
} // END class KillboardWidget extends \WP_Widgets