<?php

namespace Tarosky\TaroMultisiteSynchronizer\Models;


use Tarosky\TaroMultisiteSynchronizer\Pattern\Model;

/**
 * Post Status syncing
 *
 * @package taroms
 */
class BlogComments extends Model {

	protected $table_name = 'blog_comments';

	protected $version = '1.0';

	protected $version_key = 'blog_comments_version';


	/**
	 * {@inheritdoc}
	 */
	protected function init() {
		parent::init();
		add_action( 'wp_insert_comment', array( $this, 'insert_comment' ), 10, 2 );
		add_action( 'transition_comment_status', array( $this, 'transition_status' ), 10, 3 );
		add_action( 'edit_comment', array( $this, 'edit_comment' ) );
		add_action( 'delete_comment', array( $this, 'delete_comment' ) );
	}


	/**
	 * Sync all comments
	 *
	 * @param int $blog_id
	 */
	protected function sync_comment( $blog_id ) {
		$table = $this->db->base_prefix . ( $blog_id > 1 ? $blog_id . '_' : '' ) . 'comments';
		$query = <<<SQL
			SELECT * FROM {$table}
SQL;
		foreach ( $this->db->get_results( $query ) as $comment ) {
			$this->sync( $blog_id, $comment );
		}
	}

	/**
	 * Comment count
	 *
	 * @param string $status
	 *
	 * @return int
	 */
	public function count( $status ) {
		$query = <<<SQL
			SELECT COUNT(*) FROM {$this->table} WHERE comment_approved = %s
SQL;

		return (int) $this->db->get_var( $this->db->prepare( $query, $status ) );
	}


	/**
	 * Create common table
	 *
	 * @return string
	 */
	protected function create_sql() {
		$query = <<<SQL
			CREATE TABLE {$this->table} (
				blog_id BIGINT UNSIGNED NOT NULL,
				comment_ID BIGINT UNSIGNED NOT NULL,
				comment_post_ID BIGINT UNSIGNED NOT NULL,
				comment_author TINYTEXT NOT NULL,
				comment_author_email VARCHAR(100) NOT NULL,
				comment_author_url VARCHAR(200) NOT NULL,
				comment_author_IP VARCHAR(100) NOT NULL,
				comment_date DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				comment_date_gmt DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
				comment_content TEXT NOT NULL,
				comment_karma INT NOT NULL DEFAULT 1,
				comment_approved VARCHAR(20) NOT NULL,
				comment_agent VARCHAR(255) NOT NULL,
				comment_type VARCHAR(20) NOT NULL,
				comment_parent BIGINT UNSIGNED NOT NULL,
				user_id BIGINT UNSIGNED NOT NULL,
				PRIMARY KEY (blog_id, comment_ID),
				INDEX type_idx (comment_type, comment_approved, comment_date)
			) ENGINE = InnoDb DEFAULT CHARSET utf8
SQL;

		return $query;
	}

	/**
	 * Sync post status on updated
	 *
	 * @param int $comment_id
	 * @param \stdClass|\WP_Comment $comment
	 */
	public function insert_comment( $comment_id, $comment ) {
		$this->sync( get_current_blog_id(), $comment );
	}

	/**
	 * Sync comment
	 *
	 * @param int $comment_id
	 */
	public function edit_comment( $comment_id ) {
		$comment = get_comment( $comment_id );
		if ( $comment ) {
			$this->sync( get_current_blog_id(), $comment );
		}
	}

	/**
	 * Sync status when transition occurs.
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param \stdClass|\WP_Comment $comment
	 */
	public function transition_status( $new_status, $old_status, $comment ) {
		if ( 'delete' !== $new_status ) {
			$this->sync( get_current_blog_id(), $comment );
		}
	}

	/**
	 * Sync data
	 *
	 * @param int $blog_id
	 * @param \stdClass $comment
	 *
	 * @return false|int
	 */
	public function sync( $blog_id, $comment ) {
		$query  = <<<SQL
			SELECT comment_ID FROM {$this->table}
			WHERE blog_id = %d AND comment_ID = %d
SQL;
		$params = array(
			'comment_ID'           => '%d',
			'comment_post_ID'      => '%d',
			'comment_author'       => '%s',
			'comment_author_email' => '%s',
			'comment_author_url'   => '%s',
			'comment_author_IP'    => '%s',
			'comment_date'         => '%s',
			'comment_date_gmt'     => '%s',
			'comment_content'      => '%s',
			'comment_karma'        => '%s',
			'comment_approved'     => '%s',
			'comment_agent'        => '%s',
			'comment_type'         => '%s',
			'comment_parent'       => '%d',
			'user_id'              => '%d',
		);
		$data   = array( 'blog_id' => $blog_id );
		$where  = array( '%d' );
		foreach ( $params as $param => $place_holder ) {
			$data[ $param ] = $comment->{$param};
			$where[]        = $place_holder;
		}
		if ( $this->db->get_var( $this->db->prepare( $query, $blog_id, $comment->comment_ID ) ) ) {
			// Record exists. Update
			unset( $data['blog_id'] );
			unset( $data['comment_ID'] );
			array_shift( $where );
			array_shift( $where );

			return $this->db->update( $this->table, $data, array(
				'blog_id'    => $blog_id,
				'comment_ID' => $comment->comment_ID,
			), $where, array( '%d', '%d' ) );
		} else {
			// Record doesn't exist. Insert
			return $this->db->insert( $this->table, $data, $where );
		}
	}

	/**
	 * Delete comment
	 *
	 * @param int $comment_id
	 */
	public function delete_comment( $comment_id ) {
		$blog_id = get_current_blog_id();
		if ( $blog_id ) {
			$this->db->delete( $this->table, array(
				'blog_id'    => $blog_id,
				'comment_ID' => $comment_id,
			), array( '%d', '%d' ) );
		}
	}

	/**
	 * Get comment object
	 *
	 * @param int $blog_id
	 * @param int $comment_id
	 *
	 * @return \stdClass|null
	 */
	public function get_comment( $blog_id, $comment_id ) {
		$query = <<<SQL
			SELECT * FROM {$this->table}
			WHERE blog_id = %d AND comment_ID = %d
SQL;

		return $this->db->get_row( $this->db->prepare( $query, $blog_id, $comment_id ) );
	}


}
