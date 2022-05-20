<?php

namespace Tarosky\TaroMultisiteSynchronizer\Utility;

use Tarosky\TaroMultisiteSynchronizer\Hooks\BlogUpdated;

/**
 * Utility commands for Taro Multisite Synchronizer
 */
class Commands extends \WP_CLI_Command {

	/**
	 * Update all commands.
	 *
	 * @return void
	 */
	public function update_network_updated() {
		$paged    = 0;
		$updated  = 0;
		$hax_next = true;
		\WP_CLI::line( 'Updating sites last_updated fields.' );
		while ( $hax_next ) {
			$query = new \WP_Site_Query( [
				'offset' => $paged * 100,
			] );
			$sites = $query->get_sites();
			if ( empty( $sites ) ) {
				$has_next = false;
				break;
			}

			// Sites found.
			$paged++;
			foreach ( $sites as $site ) {
				/** @var \WP_Site $site */
				BlogUpdated::get_instance()->update( [], $site->blog_id );
				echo '.';
				$updated++;
			}
		}
		\WP_CLI::line( '' );
		\WP_CLI::success( sprintf( '%d sites are updated.', $updated ) );
	}

}
