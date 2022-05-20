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

// Load pugin files.
add_action( 'plugins_loaded', function() {
	// Load text domain.
	load_plugin_textdomain( 'taroms', false, basename( __DIR__ ) . '/languages' );
	// Load composer.
	require_once  __DIR__ . '/vendor/autoload.php';
	// Initialize models.
	\Tarosky\TaroMultisiteSynchronizer\Models\PostStatus::initialize();
	\Tarosky\TaroMultisiteSynchronizer\Models\BlogComments::initialize();
	if ( is_main_site() ) {
		\Tarosky\TaroMultisiteSynchronizer\Hooks\CommentScreen::initialize();
	}
	// Blog updated.
	\Tarosky\TaroMultisiteSynchronizer\Hooks\BlogUpdated::initialize();

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::add_command( 'taroms', \Tarosky\TaroMultisiteSynchronizer\Utility\Commands::class );
	}
} );
