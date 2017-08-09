<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Helper;

\defined('ABSPATH') or die();

class PluginHelper {
	/**
	 * Getting the Plugin Path
	 *
	 * @param string $file
	 * @return string
	 */
	public static function getPluginPath($file = '') {
		return \trailingslashit(\plugin_dir_path(dirname(__FILE__))) . $file;
	} // END public static function getPluginPath($file = '')

	/**
	 * Getting the Plugin URI
	 *
	 * @param string $file
	 * @return string
	 */
	public static function getPluginUri($file = '') {
		return \plugins_url($file, dirname(__FILE__));
	} // END public function getThemeCacheUri()
} // END class PluginHelper
