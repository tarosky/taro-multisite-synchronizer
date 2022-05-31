<?php
/**
 * Loop template for network posts.
 *
 * @var array $args Loop arguments.
 */
$args = array_merge( [
	'class' => 'taroms-network-post',
	'post'  => null,
], $args );
// TODO: setup_postdata が動作しない？
?>
<div class="<?php echo esc_attr( $args['class'] ); ?>">
	<a href="<?php echo esc_url( get_the_permalink( $args['post'] ) ); ?>">
		<span class="taroms-post-title"><?php echo esc_html( get_the_title( $args['post'] ) ); ?></span>
		<span class="taroms-post-desc"><?php bloginfo( 'name' ); ?></span>
	</a>
</div>
