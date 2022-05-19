<?php

namespace Tarosky\TaroMultisiteSynchronizer\Pattern;

use Tarosky\TaroMultisiteSynchronizer\Utility\Input;
use Tarosky\TaroMultisiteSynchronizer\Utility\Session;


/**
 * Application main class
 *
 * @package TaroMultiSite\Pattern
 * @property-read string $asset_url
 * @property-read string $template_dir
 * @property-read Input $input
 * @property-read Session $session
 * @property-read ModelAccessor $models
 */
abstract class Application extends Singleton {

	/**
	 * Detect if current blog is 1
	 *
	 * @deprecated
	 * @return bool
	 */
	public function is_main_blog() {
		return is_main_site();
	}

	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'asset_url':
				return plugin_dir_url( dirname( __DIR__ ) );
				break;
			case 'template_dir':
				return dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR . 'templates';
				break;
			case 'input':
				return Input::get_instance();
				break;
			case 'session':
				return Session::get_instance();
				break;
			case 'models':
				return ModelAccessor::get_instance();
				break;
			default:
				return null;
				break;
		}
	}

}
