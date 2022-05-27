<?php
/*
Plugin Name: Taro Multisite Synchronizer
Plugin URI: https://wordpresss.org/taro-multisite-synchronizer
Description: Synchronize Multisite.
Author: Tarosky inc.
Author URI: http://tarosky.co.jp
Version: nightly
Text Domain: taroms
*/

defined( 'ABSPATH' ) || die( 'Do not load directory' );

/**
 * Load plugin files.
 */
add_action( 'plugins_loaded', function() {
	// Load text domain.
	load_plugin_textdomain( 'taroms', false, basename( __DIR__ ) . '/languages' );
	// Load composer.
	require_once  __DIR__ . '/vendor/autoload.php';
	// Load functions.
	require_once __DIR__ . '/functions.php';
	// Initialize models.
	\Tarosky\TaroMultisiteSynchronizer\Models\PostStatus::initialize();
	\Tarosky\TaroMultisiteSynchronizer\Models\BlogComments::initialize();
	if ( is_main_site() ) {
		\Tarosky\TaroMultisiteSynchronizer\Hooks\CommentScreen::initialize();
	}
	// Blog updated.
	\Tarosky\TaroMultisiteSynchronizer\Hooks\BlogUpdated::initialize();
	// Blocks.
	\Tarosky\TaroMultisiteSynchronizer\Ui\BlogsRenderer::initialize();

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::add_command( 'taroms', \Tarosky\TaroMultisiteSynchronizer\Utility\Commands::class );
	}
} );

/**
 * Register post types.
 */
add_action( 'init', function() {
	$path = __DIR__ . '/wp-dependencies.json';
	if ( ! file_exists( $path ) ) {
		return;
	}
	$json = json_decode( file_get_contents( $path ), true );
	if ( ! $json ) {
		return;
	}
	$root = plugin_dir_url( __FILE__ );
	foreach ( $json as $asset ) {
		if ( empty( $asset ) ) {
			continue;
		}
		$url = $root . $asset['path'];
		switch ( $asset['ext'] ) {
			case 'js':
				wp_register_script( $asset['handle'], $url, $asset['deps'], $asset['hash'], $asset['footer'] );
				break;
			case 'css':
				wp_register_style( $asset['handle'], $url, $asset['deps'], $asset['hash'], $asset['media'] );
				break;
		}
	}
} );
