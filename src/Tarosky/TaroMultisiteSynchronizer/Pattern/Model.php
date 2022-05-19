<?php

namespace Tarosky\TaroMultisiteSynchronizer\Pattern;


/**
 * Class Model
 *
 * @package TaroMultiSite\Pattern
 * @property-read \wpdb $db
 * @property-read string $table
 * @property-read string $current_version
 */
abstract class Model extends Singleton {

	/**
	 * If false, table will be created for every blog.
	 *
	 * @var bool
	 */
	protected $only_parent = true;

	/**
	 * @var string
	 */
	protected $version = '1.0';

	/**
	 * Version
	 *
	 * @var string
	 */
	protected $version_key = '';

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $table_name = 'users';

	/**
	 * {@inheritdoc}
	 */
	protected function init() {
		// If this is not ajax request, try to install table.
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			add_action( 'admin_init', array( $this, 'install' ) );
		}
	}

	/**
	 * Override this function
	 *
	 * @return string
	 */
	protected function create_sql() {
		return '';
	}

	/**
	 * Install table
	 */
	public function install() {
		if ( ! $this->needs_update() ) {
			return;
		}
		$sql = $this->create_sql();
		if ( ! $sql ) {
			return;
		}
		// Create table.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $this->create_sql() );
		update_option( $this->version_key, $this->version );
		add_action( 'admin_notices', function (){
			printf(
				'<div class="updated">%s</div>',
				sprintf(
					esc_html__( 'Database %s is updated.', 'taroms' ),
					esc_html( $this->table )
				)
			);
		} );
	}

	/**
	 * Check if table should be installed
	 *
	 * @return bool
	 */
	protected function needs_update() {
		return current_user_can( 'manage_options' ) && version_compare( $this->version, $this->current_version, '>' )
			   && (
				   ( $this->only_parent && is_main_site() )
				   ||
				   ! $this->only_parent
			   );

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
			case 'table':
				return ( $this->only_parent ? $this->db->base_prefix : $this->db->prefix ) . $this->table_name;
				break;
			case 'db':
				global $wpdb;

				return $wpdb;
				break;
			case 'current_version':
				return get_option( $this->version_key, 0 );
				break;
			default:
				return null;
				break;
		}
	}
}
