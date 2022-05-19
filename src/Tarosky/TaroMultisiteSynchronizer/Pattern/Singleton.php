<?php

namespace Tarosky\TaroMultisiteSynchronizer\Pattern;


/**
 * Singleton package
 *
 * @package TaroMultiSite\Pattern
 */
abstract class Singleton {

	/**
	 * @var array
	 */
	private static $instances = array();

	/**
	 * Constructor
	 *
	 * @param array $settings
	 */
	final protected function __construct( array $settings = array() ){
		$this->init();
	}

	/**
	 * Initializer.
	 *
	 * @return void
	 */
	protected function init() {
		// Do something in constructor.
	}

	/**
	 * Get instance.
	 *
	 * @param array $settings
	 * @return static
	 */
	final public static function get_instance( array $settings = array() ){
		$class_name = get_called_class();
		if ( ! isset( self::$instances[ $class_name ] ) ) {
			self::$instances[ $class_name ] = new $class_name( $settings );
		}
		return self::$instances[ $class_name ];
	}

	/**
	 * Initializer
	 *
	 * @param array $settings
	 *
	 * @return static
	 */
	final public static function initialize( array $settings = array() ){
		$class_name = get_called_class();
		if ( isset( self::$instances[ $class_name ] ) ) {
			trigger_error( sprintf( __( 'Do not call %s::init() again and again.', 'taroms' ), $class_name), E_USER_WARNING );
		}
		return self::get_instance( $settings );
	}

}
