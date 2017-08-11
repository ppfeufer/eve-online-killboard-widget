<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Helper;

\defined('ABSPATH') or die();

class PluginHelper {
	/**
	 * instance
	 *
	 * static variable to keep the current (and only!) instance of this class
	 *
	 * @var Singleton
	 */
	protected static $instance = null;

	/**
	 * Getting the instance
	 *
	 * @return \WordPress\Plugin\EveOnlineKillboardWidget\Helper\PluginHelper
	 */
	public static function getInstance() {
		if(null === self::$instance) {
			self::$instance = new self;
		} // END if(null === self::$instance)

		return self::$instance;
	} // END public static function getInstance()

	/**
	 * clone
	 *
	 * no cloning allowed
	 */
	protected function __clone() {
		;
	} // END protected function __clone()

	/**
	 * The Constructor
	 */
	protected function __construct() {
		;
	} // END protected function __construct()

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
