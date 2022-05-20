<?php

namespace Tarosky\TaroMultisiteSynchronizer\Hooks;

use Tarosky\TaroMultisiteSynchronizer\Pattern\Singleton;

/**
 * Save blog updated.
 */
class BlogUpdated extends Singleton {

	/**
	 * {@inheritdoc}
	 */
	protected function init() {
		add_action( 'wpmu_blog_updated', [ $this, 'update_date_hook' ] );
	}

	/**
	 * Get post type to compare.
	 *
	 * @return string
	 */
	protected function default_post_type() {
		return apply_filters( 'taroms_post_type_as_update', [ 'post' ] );
	}

	/**
	 * Get latest blog post.
	 *
	 * @param string[] $post_types Post types.
	 * @return string
	 */
	public function get_latest( $post_types = [] ) {
		if ( empty( $post_types ) ) {
			$post_types = $this->default_post_type();
		}
		$query = new \WP_Query( [
			'post_type'      => $post_types,
			'post_status'    => 'publish',
			'orderby'        => 'date',
			'order'          => 'DESC',
			'posts_per_page' => 1,
		] );
		return $query->have_posts() ? $query->posts[0]->post_date_gmt : '';
	}

	/**
	 * Update date by post meta.
	 *
	 * @parma int $site_id
	 * @return void
	 */
	public function update_date_hook( $site_id ) {
		$this->update();
	}

	/**
	 * Update post types.
	 *
	 * @param string[] $post_types Post types.
	 * @param int|null $blog_id    Blog ID. Default, current blog.
	 * @return void
	 */
	public function update( $post_types = [], $blog_id = null ) {
		$should_change_blog = false;
		if ( is_null( $blog_id ) ) {
			$blog_id = get_current_blog_id();
		} elseif ( $blog_id !== get_current_blog_id() ) {
			$should_change_blog = true;
		} else {
			$blog_id = get_current_blog_id();
		}
		if ( $should_change_blog ) {
			switch_to_blog( $blog_id );
		}
		$latest = $this->get_latest( $post_types );
		if ( $latest ) {
			update_blog_details( $blog_id, [
				'last_updated' => $latest,
			] );
		}
		if ( $should_change_blog ) {
			restore_current_blog();
		}
	}
}
