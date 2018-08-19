<?php

namespace WordPress\Plugin\EveOnlineKillboardWidget\Libs\ResourceLoader;

\defined('ABSPATH') or die();

/**
 * JavaScript Loader
 */
class JavascriptLoader implements \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Interfaces\AssetsInterface {
    /**
     * Initialize the loader
     */
    public function init() {
        \add_action('wp_enqueue_scripts', [$this, 'enqueue'], 99);
    }

    /**
     * Load the JavaScript
     */
    public function enqueue() {
        /**
         * Only in Frontend
         */
        if(!\is_admin()) {
            \wp_enqueue_script('bootstrap-js', \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('bootstrap/js/bootstrap.min.js'), ['jquery'], '', true);
            \wp_enqueue_script('bootstrap-toolkit-js', \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('bootstrap/bootstrap-toolkit/bootstrap-toolkit.min.js'), ['jquery', 'bootstrap-js'], '', true);
            \wp_enqueue_script('eve-online-killboard-widget-js', \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('js/eve-online-killboard-widget.min.js'), ['jquery'], '', true);
            \wp_localize_script('eve-online-killboard-widget-js', 'killboardWidgetL10n', $this->getJavaScriptTranslations());
        }
    }

    /**
     * Getting the translation array to translate strings in JavaScript
     *
     * @return array
     */
    private function getJavaScriptTranslations() {
        return [
            'ajax' => [
                'url' => \admin_url('admin-ajax.php'),
                'loaderImage' => \WordPress\Plugin\EveOnlineKillboardWidget\Libs\Helper\PluginHelper::getInstance()->getPluginUri('images/loader-sprite.gif')
            ]
        ];
    }
}
