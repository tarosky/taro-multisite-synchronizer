<?php
/**
 * List template for blogs.
 *
 * @package taroms
 * @var array $args Template arguments.
 */

/**
 * @var WP_Site[] $blogs List of blogs.
 */
$blogs = $args['blogs'];

/**
 * @var string[] $dirs List of directories to avoid theme switching.
 */
$dirs = $args['dirs'];

/**
 * @var string Class names. Default, 'taroms-blogs'
 */
$classes = $args['class'];

?>
<div class="<?php echo esc_attr( $classes ); ?>">
	<?php
	// Make loop for all blogs.
	foreach ( $blogs as $blog ) {
		switch_to_blog( $blog->blog_id );
		// Load template-parts/taroms/loop-site.php
		taroms_get_template_part( 'loop', 'site', [
			'args'  => $args,
			'blog'  => $blog,
			'class' => $classes,
		], $dirs );
		restore_current_blog();
	}
	?>
</div>
