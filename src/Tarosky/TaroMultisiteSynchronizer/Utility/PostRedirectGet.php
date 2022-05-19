<?php

namespace Tarosky\TaroMultisiteSynchronizer\Utility;


use Tarosky\TaroMultisiteSynchronizer\Pattern\Singleton;

/**
 * Post redirect get helper
 *
 * @package TaroMultiSite\Utility
 */
class PostRedirectGet extends Singleton {

	/**
	 * Session key name
	 */
	const SESSION_KEY = 'ts_message_session';

	/**
	 * Enable session
	 */
	public function enable_session() {
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * Add message
	 *
	 * @param string $message
	 * @param bool $error
	 */
	public function add_message( $message, $error = false ) {
		if ( session_id() ) {
			if ( ! isset( $_SESSION[ self::SESSION_KEY ] ) ) {
				$_SESSION[ self::SESSION_KEY ] = array(
					'error'   => array(),
					'updated' => array(),
				);
			}
			$key                = $error ? 'error' : 'updated';
			$_SESSION[ $key ][] = $message;
		}
	}

	/**
	 * Show message
	 */
	public function flush_message() {
		if ( session_id() ) {
			foreach ( array( 'error', 'updated' ) as $key ) {
				if ( isset( $_SESSION[ $key ] ) && ! empty( $_SESSION[ $key ] ) ) {
					printf( '<div class="%s"><p>%s</p></div>', $key, implode( '<br />', $_SESSION[ $key ] ) );
					$_SESSION[ $key ] = array();
				}
			}
		}
	}

}
