<?php

namespace Tarosky\TaroMultisiteSynchronizer\Pattern;


use Tarosky\TaroMultisiteSynchronizer\Utility\PostRedirectGet;

/**
 * Admin parts
 *
 * @package taroms
 * @property-read PostRedirectGet $prg
 */
class AdminParts extends Application {

	/**
	 * @var bool
	 */
	private static $initialized = false;

	/**
	 * {@inheritdoc}
	 */
	protected function init() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_action( 'admin_init', array( $this, 'register_ajax' ) );
		} else {
			if ( ! self::$initialized ) {
				// Session
				$prg = $this->prg;
				add_action( 'admin_init', function () use ( $prg ) {
					$prg->enable_session();
				}, 1 );
				// Flush
				add_action( 'all_admin_notices', function () use ( $prg ) {
					$prg->flush_message();
				} );
				self::$initialized = true;
			}
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}
	}


	/**
	 * Called on admin_menu
	 */
	public function admin_menu() {
		// Override this function
	}

	/**
	 * Called on admin_init hook
	 */
	public function admin_init() {
		// Override this function
	}

	/**
	 * Register Ajax
	 *
	 * Called on admin_init hook
	 */
	public function register_ajax() {
		// Register Ajax action here
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
			case 'prg':
				return PostRedirectGet::get_instance();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}


}
