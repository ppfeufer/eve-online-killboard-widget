<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class PluginHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
	/**
	 * Getting the Plugin Path
	 *
	 * @param string $file
	 * @return string
	 */
	public function getPluginPath($file = '') {
		return \trailingslashit(\plugin_dir_path(\dirname(\dirname(__FILE__)))) . $file;
	} // END public function getPluginPath($file = '')

	/**
	 * Getting the Plugin URI
	 *
	 * @param string $file
	 * @return string
	 */
	public function getPluginUri($file = '') {
		return \plugins_url($file, \dirname(\dirname(__FILE__)));
	} // END public function getPluginUri()
} // END class PluginHelper
