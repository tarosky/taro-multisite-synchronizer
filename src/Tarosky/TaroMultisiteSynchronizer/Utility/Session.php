<?php

namespace Tarosky\TaroMultisiteSynchronizer\Utility;


use Tarosky\TaroMultisiteSynchronizer\Pattern\Singleton;

/**
 * Session utility
 *
 * @package TaroMultiSite\Utility
 */
class Session extends Singleton {

	/**
	 * Start session
	 *
	 * @return bool
	 */
	public function start() {
		if ( ! session_id() ) {
			return session_start();
		} else {
			return true;
		}
	}

	/**
	 * Set session value
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return bool If failed, return false
	 */
	public function set( $key, $value ) {
		if ( session_id() ) {
			$_SESSION[ $key ] = $value;

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get session param
	 *
	 * @param string $key
	 *
	 * @return null|mixed
	 */
	public function get( $key ) {
		return issset( $_SESSION[ $key ] ) ? $_SESSION[ $key ] : null;
	}

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function remove( $key ) {
		if ( isset( $_SESSION[ $key ] ) ) {
			unset( $_SESSION[ $key ] );

			return true;
		} else {
			return false;
		}
	}

}
