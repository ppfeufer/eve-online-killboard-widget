<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper;

class RemoteHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
	/**
	 * Getting data from a remote source
	 *
	 * @param string $url
	 * @param array $parameter
	 * @return mixed
	 */
	public function getRemoteData($url, array $parameter = []) {
		$params = '';

		if(\count($parameter) > 0) {
			$params = '?' . \http_build_query($parameter);
		} // END if(\count($parameter > 0))

		$remoteUrl = $url . $params;

		$get = \wp_remote_get($remoteUrl, ['timeout' => 10]);
		$data = \wp_remote_retrieve_body($get);

		return $data;
	} // END private function getRemoteData($url, array $parameter)
} // END class RemoteHelper extends \WordPress\Plugin\EveOnlineTranquilityStatus\Singletons\AbstractSingleton
