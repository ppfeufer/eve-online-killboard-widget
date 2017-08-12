<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Helper;

\defined('ABSPATH') or die();

class PluginHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Singleton\AbstractSingleton {
	/**
	 * Getting the Plugin Path
	 *
	 * @param string $file
	 * @return string
	 */
	public function getPluginPath($file = '') {
		return \trailingslashit(\plugin_dir_path(dirname(__FILE__))) . $file;
	} // END public function getPluginPath($file = '')

	/**
	 * Getting the Plugin URI
	 *
	 * @param string $file
	 * @return string
	 */
	public function getPluginUri($file = '') {
		return \plugins_url($file, dirname(__FILE__));
	} // END public function getPluginUri()

	/**
	 * Getting data from a remote source
	 *
	 * @param string $url
	 * @param array $parameter
	 * @return mixed
	 */
	public function getRemoteData($url, array $parameter = array()) {
		$params = '';

		if(\count($parameter) > 0) {
			$params = '?' . \http_build_query($parameter);
		} // END if(\count($parameter > 0))

		$remoteUrl = $url . $params;

		$get = \wp_remote_get($remoteUrl);
		$data = \wp_remote_retrieve_body($get);

		return $data;
	} // END private function getRemoteData($url, array $parameter)
} // END class PluginHelper
