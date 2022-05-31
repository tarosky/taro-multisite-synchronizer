<?php

namespace Tarosky\TaroMultisiteSynchronizer\Models;


use Tarosky\TaroMultisiteSynchronizer\Pattern\Model;

/**
 * Post Status syncing
 *
 * @package TaroMultiSite\Models
 */
class PostStatus extends Model {

	protected $table_name = 'blog_status';

	protected $version = '1.0';

	protected $version_key = 'blog_status_version';

	/**
	 * {@inheritdoc}
	 */
	protected function init() {
		parent::init();
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_action( 'delete_post', array( $this, 'delete_post' ) );
	}


	/**
	 * Create common table
	 *
	 * @return string
	 */
	protected function create_sql() {
		$query = <<<SQL
			CREATE TABLE {$this->table} (
				blog_id BIGINT NOT NULL,
				post_id BIGINT NOT NULL,
				post_status VARCHAR(256) NOT NULL DEFAULT 'publish',
				post_date DATETIME NOT NULL,
				updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				PRIMARY KEY (blog_id, post_id),
				INDEX post_idx (blog_id, post_status, post_date)
			) ENGINE = InnoDb DEFAULT CHARSET utf8
SQL;

		return $query;
	}

	/**
	 * Sync post status on updated
	 *
	 * @param int $post_id
	 * @param \WP_Post $post
	 */
	public function save_post( $post_id, \WP_Post $post ) {
		if ( 'post' == $post->post_type ) {
			// We should save post.
			$blog_id = get_current_blog_id();
			$this->sync( $blog_id, $post );
		}
	}

	/**
	 * Sync data
	 *
	 * @param int $blog_id
	 * @param \WP_Post $post
	 *
	 * @return false|int
	 */
	public function sync( $blog_id, $post ) {
		$query = <<<SQL
			INSERT INTO {$this->table} (blog_id, post_id, post_status, post_date )
				VALUES (%d, %d, %s, %s)
			ON DUPLICATE KEY
				UPDATE post_status=%s, post_date=%s, updated=%s;
SQL;

		return $this->db->query( $this->db->prepare( $query, $blog_id, $post->ID, $post->post_status, $post->post_date,
			$post->post_status, $post->post_date, $post->post_modified ) );

	}

	/**
	 * Delete post
	 *
	 * @param int $post_id
	 */
	public function delete_post( $post_id ) {
		$blog_id = get_current_blog_id();
		if ( $blog_id ) {
			$this->db->delete( $this->table, array(
				'blog_id' => $blog_id,
				'post_id' => $post_id,
			), array( '%d', '%d' ) );
		}
	}

	/**
	 * Get recent post retrieve query
	 *
	 * @param array $args
	 *
	 * @return false|null|string
	 */
	public function get_recent_query( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'posts_per_page' => get_option( 'posts_per_page', 10 ),
			'paged'          => 1,
			'post_status'    => 'publish',
			'exclude_parent' => true,
			'group_by'       => true,
			'blog_ids'       => array(),
		) );
		// Build where
		$wheres = array(
			$this->db->prepare( 'p.post_status = %s', $args['post_status'] ),
			"b.public > 1",
			"(b.archived + b.mature + b.spam + b.deleted) = 0",
		);
		if ( $args['exclude_parent'] ) {
			$wheres[] = "p.blog_id > 1";
		}
		if ( $args['blog_ids'] ) {
			$wheres[] = sprintf( '( p.blog_id IN (%s) )', implode( ', ', array_map( 'intval', $args['blog_ids'] ) ) );
		}
		$where_clause = "WHERE " . implode( " AND ", $wheres );
		$group_by     = $args['group_by'] ? "GROUP BY posts.blog_id" : '';
		$query        = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS
			 posts.blog_id, MAX( posts.post_id ) AS post_id, posts.post_status, MAX( posts.post_date ) AS post_date, posts.updated FROM (
				SELECT
				  p.*
			    FROM {$this->table} AS p
				LEFT JOIN {$this->db->blogs} AS b
				ON p.blog_id = b.blog_id
				{$where_clause}
				ORDER BY p.post_date DESC
			) AS posts
			{$group_by}
			ORDER BY post_date DESC
			LIMIT %d, %d
SQL;
		return $this->db->prepare( $query, $args['posts_per_page'] * ( max( $args['paged'], 1 ) - 1 ), $args['posts_per_page'] );
	}

	/**
	 * Get latest query.
	 *
	 * @param array $args
	 *
	 * @return \stdClass[]
	 */
	public function get_recent( $args = array() ) {
		$query  = $this->get_recent_query( $args );
		$result = $this->db->get_results( $query );

		return array_map( function( $row ) {
			$row->blog_id = (int) $row->blog_id;
			$row->post_id = (int) $row->post_id;
			return $row;
		}, $result );
	}

	/**
	 * Get proper table object
	 *
	 * @param int $blog_id
	 * @param int $post_id
	 *
	 * @return mixed
	 */
	public function get_post( $blog_id, $post_id ) {
		$table_name = $this->db->base_prefix . ( intval( $blog_id ) > 1 ? ( intval( $blog_id ) ) . '_' : '' ) . 'posts';
		$blog_id    = max( 1, intval( $blog_id ) );
		$query      = <<<SQL
			SELECT *, {$blog_id} AS post_parent, 'raw' AS filter FROM {$table_name} WHERE ID = %d
SQL;

		return $this->db->get_row( $this->db->prepare( $query, $post_id ) );
	}

}
