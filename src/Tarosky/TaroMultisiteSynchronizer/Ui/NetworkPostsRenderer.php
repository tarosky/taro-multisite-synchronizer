<?php

namespace Tarosky\TaroMultisiteSynchronizer\Ui;


use Tarosky\TaroMultisiteSynchronizer\Pattern\RendererPattern;

/**
 * Render network posts.
 */
class NetworkPostsRenderer extends RendererPattern {

	protected $is_dynamic_block = true;

	/**
	 * {@inheritdoc}
	 */
	protected function block_name() {
		return 'network-posts';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_editor_script() {
		return 'taroms-block-network-posts';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function block_label() {
		return __( 'Network Posts', 'taroms' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function enqueue_block_editor_assets() {
		wp_localize_script( $this->get_editor_script(), 'TaroMsBlockNetworkPostsVars', $this->get_all_attributes() );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_block_description() {
		return __( 'Display network posts.', 'taroms' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_block_attributes() {
		return [
			'posts_per_page' => [
				'type'        => 'integer',
				'default'     => 10,
				'description' => __( 'Number of posts', 'taroms' ),
			],
			'exclude_parent' => [
				'type'        => 'boolean',
				'default'     => true,
				'description' => __( 'Exclude main site', 'taroms' ),
			],
			'group_by'       => [
				'type'        => 'boolean',
				'default'     => true,
				'description' => __( 'Group by blog', 'taroms' ),
			],
			'blog_ids'       => [
				'type'        => 'string',
				'default'     => '',
				'description' => __( 'Blog ids to include', 'taroms' ),
			],
		];
	}

	/**
	 * Render block callback..
	 *
	 * @param array  $attributes Attributes.
	 * @param string $content    contents.
	 *
	 * @return string
	 */
	public function render_callback( $attributes = [], $content = '' ) {
		$attributes['blog_ids'] = array_values( array_filter( array_map( function( $id ) {
			$id = trim( $id );
			return ( is_numeric( $id ) && $id > 0 ) ? $id : false;
		}, explode( ',', $attributes['blog_ids'] ) ) ) );
		// Add filter for network posts list.
		$attributes = apply_filters( 'taroms_network_posts_list_block_arguments', $attributes, $content );
		return taroms_network_posts_list( $attributes ) ?: __( 'No posts found.', 'taroms' );
	}
}
