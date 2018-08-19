<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper;

\defined('ABSPATH') or die();

class RemoteHelper extends \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Singletons\AbstractSingleton {
    /**
     * Getting data from a remote source
     *
     * @param string $url
     * @param array $parameter
     * @return mixed
     */
    public function getRemoteData($url, $parameter = [], $method = 'get') {
        $returnValue = null;
        $params = '';

        switch($method) {
            case 'get':
                if(\count($parameter) > 0) {
                    $params = '?' . \http_build_query($parameter);
                }

                $remoteData = \wp_remote_get($url . $params);
                break;

            case 'post':
                $remoteData = \wp_remote_post($url, [
                    'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
                    'body' => \json_encode($parameter),
                    'method' => 'POST'
                ]);
                break;
        }

        if(\wp_remote_retrieve_response_code($remoteData) === 200) {
            $returnValue = \wp_remote_retrieve_body($remoteData);
        }

        return $returnValue;
    }
}
