<?php

namespace Tarosky\TaroMultisiteSynchronizer\Ui;


use Tarosky\TaroMultisiteSynchronizer\Pattern\RendererPattern;

/**
 * Render blogs.
 */
class BlogsRenderer extends RendererPattern {

	protected $is_dynamic_block = true;

	/**
	 * {@inheritdoc}
	 */
	protected function block_name() {
		return 'blogs';
	}

	/**
	 * Get items.
	 *
	 * @param array $args Arguments.
	 * @return \WP_Site[]
	 */
	public function get_items( $args = [] ) {
		$query = new \WP_Site_Query( $args );
		return $query->get_sites();
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_editor_script() {
		return 'taroms-block-blogs';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function block_label() {
		return __( 'Blog List', 'taroms' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function enqueue_block_editor_assets() {
		wp_localize_script( $this->get_editor_script(), 'TaroMsBlockBlogsVars', $this->get_all_attributes() );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_block_description() {
		return __( 'Display recently updated blogs.', 'taroms' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_block_attributes() {
		return [
			'number'       => [
				'type'        => 'integer',
				'default'     => 10,
				'description' => __( 'Count of blogs.', 'taroms' ),
			],
			'orderby'      => [
				'type'        => 'string',
				'default'     => 'last_updated',
				'description' => __( 'Order by', 'taroms' ),
			],
			'order'        => [
				'type'        => 'string',
				'default'     => 'DESC',
				'description' => __( 'Order', 'taroms' ),
			],
			'exclude_self' => [
				'type'        => 'boolean',
				'default'     => true,
				'description' => __( 'Exclude Self', 'taroms' ),
			],
		];
	}

	/**
	 * Render block conents.
	 *
	 * @param array  $attributes Attributes.
	 * @param string $content    contents.
	 *
	 * @return string
	 */
	public function render_callback( $attributes = [], $content = '' ) {
		if ( ! empty( $attributes['exclude_self'] ) ) {
			// Exclude current blog.
			$attributes['site__not_in'] = get_current_blog_id();
		}
		unset( $attributes['exclude_self'] );
		$attributes = apply_filters( 'taroms_blog_list_block_arguments', $attributes, $content );
		return taroms_blog_list( $attributes ) ?: __( 'No Blog found.', 'taroms' );
	}


}
