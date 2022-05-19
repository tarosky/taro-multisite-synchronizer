<?php

namespace Tarosky\TaroMultisiteSynchronizer\Utility;


use Tarosky\TaroMultisiteSynchronizer\Pattern\Singleton;

/**
 * Short hand for input
 *
 * @package TaroMultiSite\Utility
 */
class Input extends Singleton {

	/**
	 * Get $_GET variables
	 *
	 * @param string $name
	 *
	 * @return null|mixed
	 */
	public function get( $name ) {
		return isset( $_GET[ $name ] ) ? $_GET[ $name ] : null;
	}

	/**
	 * Get $_POST variables
	 *
	 * @param string $name
	 *
	 * @return null|mixed
	 */
	public function post( $name ) {
		return isset( $_POST[ $name ] ) ? $_POST[ $name ] : null;
	}

	/**
	 * Get $_REQUEST variables
	 *
	 * @param string $name
	 *
	 * @return null|mixed
	 */
	public function request( $name ) {
		return isset( $_REQUEST[ $name ] ) ? $_REQUEST[ $name ] : null;
	}

	/**
	 * Verify nonce
	 *
	 * @param string $action
	 * @param string $key Default _wpnonce
	 *
	 * @return bool
	 */
	public function verify_nonce( $action, $key = '_wpnonce' ) {
		return wp_verify_nonce( $this->request( $key ), $action );
	}

}
