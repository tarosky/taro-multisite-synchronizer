<?php

namespace Tarosky\TaroMultisiteSynchronizer\Utility;


/**
 * Utility for template inclusion.
 */
trait TemplatePart {

	/**
	 * Get root directory.
	 *
	 * @return string
	 */
	public function get_root_dir() {
		return dirname( __DIR__, 4 );
	}

	/**
	 * Get template part.
	 *
	 * @param string $name   Template file name.
	 * @param string $suffix File suffix.
	 * @param array  $args   Arguments.
	 *
	 * @return void
	 */
	public function get_template_part( $name, $suffix = '', $args = [] ) {
		$dirs = [
			get_template_directory(),
			$this->get_root_dir(),
		];
		if ( get_template_directory() !== get_stylesheet_directory() ) {
			array_unshift( $dirs, get_stylesheet_directory() );
		}
		$dirs = array_map( function( $dir ) {
			return trailingslashit( $dir ) . 'template-parts/taroms';
		}, $dirs );
		$files = [ $name ];
		if ( $suffix ) {
			array_unshift( $files, $name . '-' . $suffix );
		}
		$found = false;
		foreach ( $files as $file ) {
			foreach ( $dirs as $dir ) {
				$path = $dir . '/' . ltrim( $file, '/' );
				if ( file_exists( $path ) ) {
					$found = $path;
					break 2;
				}
			}
		}
		load_template( $found, false, $args );
	}
}
