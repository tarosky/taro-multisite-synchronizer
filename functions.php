<?php
/**
 * Utility functions
 */

/**
 * Get root directory.
 *
 * @return string
 */
function taroms_root_dir() {
	return __DIR__;
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
function taroms_get_template_part( $name, $suffix = '', $args = [], $dirs = [] ) {
	if ( empty( $dirs ) ) {
		$dirs = [
			get_template_directory(),
		];
		if ( get_template_directory() !== get_stylesheet_directory() ) {
			array_unshift( $dirs, get_stylesheet_directory() );
		}
	}
	$dirs[] = taroms_root_dir();
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
			$path = $dir . '/' . ltrim( $file, '/' ) . '.php';
			if ( file_exists( $path ) ) {
				$found = $path;
				break 2;
			}
		}
	}
	if ( ! $found ) {
		return;
	}
	load_template( $found, false, $args );
}

/**
 * Render blog list.
 *
 * @param array  $args  Arguments.
 * @param string $class Class names.
 * @return string
 */
function taroms_blog_list( $args = [], $class = 'taroms-blogs' ) {
	$blogs = taroms_get_blogs( $args );
	if ( empty( $blogs ) ) {
		return '';
	}
	$out = [];
	$out[] = sprintf( '<div class="%s">', esc_attr( $class ) );
	// Keep directory.
	$dirs = [ get_template_directory() ];
	if ( get_template_directory() !== get_stylesheet_directory() ) {
		array_unshift( $dirs, get_stylesheet_directory() );
	}
	ob_start();
	foreach ( $blogs as $blog ) {
		switch_to_blog( $blog->blog_id );
		taroms_get_template_part( 'loop', 'site', [
			'args'  => $args,
			'blog'  => $blog,
			'class' => $class,
		], $dirs );
		restore_current_blog();
	}
	$out[] = ob_get_contents();
	ob_end_clean();
	$out[] = '</div>';
	return implode( "\n", $out );
}

/**
 * Get all blogs.
 *
 * @param $args
 * @return WP_Site[]
 */
function taroms_get_blogs( $args = [] ) {
	$args = array_merge( [
		'public'  =>  1,
		'number'  => 10,
		'orderby' => 'last_updated',
		'order'   => 'DESC',
	], $args );
	$query = new WP_Site_Query( $args );
	return $query->get_sites();
}

/**
 * Get network posts.
 *
 * @param array $args Arguments.
 * @return stdClass[]
 */
function taroms_get_network_posts( $args = [] ) {
	return \Tarosky\TaroMultisiteSynchronizer\Models\PostStatus::get_instance()->get_recent( $args );
}

/**
 * Display
 *
 * @param array  $args  Setting.
 * @param string $class Class name.
 *
 * @return string
 */
function taroms_network_posts_list( $args = [], $class = 'taroms-network-post' ) {
	$posts = taroms_get_network_posts( $args );
	if ( empty( $posts ) ) {
		return '';
	}
	// Keep directory.
	$dirs = [ get_template_directory() ];
	if ( get_template_directory() !== get_stylesheet_directory() ) {
		array_unshift( $dirs, get_stylesheet_directory() );
	}
	$out = [];
	$out[] = sprintf( '<div class="%s">', esc_attr( $class ) );
	ob_start();
	foreach ( $posts as $network_post ) {
		switch_to_blog( $network_post->blog_id );
		$post = get_post( $network_post->post_id );
		taroms_get_template_part( 'loop', 'post', [
			'args'  => $args,
			'class' => $class,
			'post'  => $post,
		], $dirs );
		restore_current_blog();
	}
	wp_reset_postdata();
	$out[] = ob_get_contents();
	ob_end_clean();
	$out[] = '</div>';
	return implode( "\n", $out );
}
