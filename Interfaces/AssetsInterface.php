<?php
namespace WordPress\Plugin\EveOnlineKillboardWidget\Interfaces;

\defined('ABSPATH') or die();

/**
 * Defines a common set of functions that any class responsible for loading
 * stylesheets, JavaScript, or other assets should implement.
 */
interface AssetsInterface {
	public function init();

	public function enqueue();
} // END interface AssetsInterface
