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
        return \WP_PLUGIN_DIR . '/' . $this->getPluginDirName() . '/' . $file;
    }

    /**
     * Getting the Plugin URI
     *
     * @param string $file
     * @return string
     */
    public function getPluginUri($file = '') {
        return \WP_PLUGIN_URL . '/' . $this->getPluginDirName() . '/' . $file;
    }

    /**
     * Get the plugins directory base name
     *
     * @return string
     */
    public function getPluginDirName() {
        return \dirname(\dirname(\dirname(\plugin_basename(__FILE__))));
    }
}
