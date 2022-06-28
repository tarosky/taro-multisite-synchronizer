<?php

namespace Tarosky\TaroMultisiteSynchronizer\Ui;

use Tarosky\TaroMultisiteSynchronizer\Models\BlogComments;


/**
 * Class CommentTable
 *
 * @package taroms
 * @property-read \wpdb $db
 * @property-read BlogComments $blog_comments
 */
class CommentTable extends \WP_List_Table {

	protected $nonce_key = 'multisite_comment_edit';

	protected $nonce = '';

	protected $per_page = 100;

	protected $status = '0';

	/**
	 * Constructor
	 *
	 * @param array $args
	 */
	public function __construct( $args = array(), $status = '0' ) {
		$this->nonce = wp_create_nonce( $this->nonce_key );
		$args        = wp_parse_args( $args, array(
			'plural'   => 'comments',
			'singular' => 'comment',
			'ajax'     => true,
			'screen'   => null,
		) );
		switch ( $status ) {
			case '1':
			case 'spam':
			case 'trash':
				$this->status = $status;
				break;
			default:
				$this->status = '0';
				break;
		}
		parent::__construct( $args );
	}

	public function prepare_items() {

		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$wheres = array(
			$this->db->prepare( 'comment_approved = %s', $this->status ),
		);

		if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
			$eq       = strval( $_GET['s'] );
			$like     = '%' . $eq . '%';
			$s_query  = <<<SQL
				(  comment_author_IP = %s
				OR comment_content LIKE %s
				OR comment_author_url LIKE %s
				OR comment_author_email LIKE %s )
SQL;
			$wheres[] = $this->db->prepare( $s_query, $eq, $like, $like, $like );
		}

		$where_clause = 'WHERE ' . implode( ' AND ', $wheres );

		$offset = ( max( 1, $this->get_pagenum() ) - 1 ) * $this->per_page;

		$query = <<<SQL
			SELECT SQL_CALC_FOUND_ROWS * FROM {$this->blog_comments->table}
			{$where_clause}
			ORDER BY comment_date DESC
			LIMIT {$offset}, {$this->per_page}
SQL;

		$this->items = $this->db->get_results( $query );

		$this->set_pagination_args( array(
			'total_items' => $this->db->get_var( 'SELECT FOUND_ROWS()' ),
			'per_page'    => $this->per_page,
		) );
	}

	/**
	 * Column Name
	 *
	 * @return array
	 */
	public function get_columns() {
		return [
			'cb'       => '<input type="checkbox" />',
			'author'   => '作成者',
			'comment'  => 'コメント',
			'response' => '投稿先',
		];
	}

	/**
	 * Not sortable
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array();
	}


	/**
	 * Render checkbox
	 *
	 * @param \stdClass $comment
	 *
	 * @return string
	 */
	public function column_cb( $comment ) {
		return sprintf( '<input type="checkbox" id="comment-%1$d" class="comment-id-container" name="comment[]" value="%1$d" data-blog-id="%2$d"/>', $comment->comment_ID, $comment->blog_id );
	}

	/**
	 * Comment author
	 *
	 * @param \stdClass $comment
	 *
	 * @return string
	 */
	public function column_author( $comment ) {
		switch ( $comment->comment_type ) {
			case 'pingback':
			case 'trackback':
				$html = <<<'HTML'
					<span class="dashicons dashicons-share"></span> <a target="_blank" href="%s">%s</a><br />
					@ <a href="%s">%s</a>
HTML;

				return sprintf( $html,
					esc_url( $comment->comment_author_url ), esc_html( $comment->comment_author ),
				esc_url( admin_url( "edit-comments.php?page=ms-comment&s={$comment->comment_author_IP}&status={$this->status}" ) ), $comment->comment_author_IP );

				break;
			default:
				$html = <<<'HTML'
				<strong>%s %s</strong>
				%s<br />
				@ <a href="%s">%s</a><br />
				%s

HTML;

				return sprintf( $html,
					get_avatar( $comment->comment_author_email, 32 ), esc_html( $comment->comment_author ),
					$comment->user_id ? '<small class="registered">WPユーザー</small>' : '',
					esc_url( admin_url( "edit-comments.php?page=ms-comment&s={$comment->comment_author_IP}&status={$this->status}" ) ), $comment->comment_author_IP,
					$comment->comment_author_email ? sprintf( '<a href="mailto:%1$s">%1$s</a><br />', $comment->comment_author_email ) : ''
				);
				break;
		}
	}

	/**
	 * Print comment content
	 *
	 * @param \stdClass $comment
	 *
	 * @return string
	 */
	public function column_comment( $comment ) {
		switch_to_blog( $comment->blog_id );
		$comment_text = apply_filters( 'get_comment_text', $comment->comment_content, $comment, array() );
		// Status
		switch ( $comment->comment_approved ) {
			case '1':
				$label = '<span class="comment-label label-' . $comment->comment_approved . '">承認済み%s</span>';
				break;
			case 'spam':
				$label = '<span class="comment-label label-' . $comment->comment_approved . '">スパム%s</span>';
				break;
			case 'trash':
				$label = '<span class="comment-label label-' . $comment->comment_approved . '">削除済み%s</span>';
				break;
			default:
				$label = '<span class="comment-label label-0">承認待ち%s</span>';
				break;
		}
		// Comment type
		switch ( $comment->comment_type ) {
			case 'pingback':
			case 'trackback':
				$type = sprintf( '<span class="dashicons dashicons-share"></span> %s', sprintf( $label, $comment->comment_type ) );
				break;
			default:
				$type = '<span class="dashicons dashicons-format-status"></span> ' . sprintf( $label, 'コメント' );
				break;
		}
		// Feature
		$labels = array();
		if ( mb_strlen( $comment_text, 'utf-8' ) > 400 ) {
			$labels[] = '<span class="feature"><span class="dashicons dashicons-info"></span> 長文</span>';
		}
		if ( preg_match( '/https?:\/\//u', $comment_text ) ) {
			$labels[] = '<span class="feature"><span class="dashicons dashicons-shield"></span> 含URL</span>';

		}
		$html  = <<<HTML
			<small class="comment-type">%s @ <em>%s</em> %s</small>
			<div class="comment-body">%s</div>
HTML;
		$html  = sprintf( $html,
			$type,
			mysql2date( 'Y年n月j日（D） H:i', $comment->comment_date ),
			implode( ' ', $labels ),
			apply_filters( 'comment_text', $comment_text, $comment, array() )
		);
		$links = array(
			'view' => '<a href="#" data-label="縮小表示">拡大表示</a>',
		);

		switch ( $comment->comment_approved ) {
			case 'spam':
			case 'trash':
				$links['fix'] = sprintf( '<a href="%s">修正する</a>', admin_url( 'edit-comments.php?comment_status=' . $comment->comment_approved ) );
				switch_to_blog( 1 );
				break;
			default:
				$edit_link = admin_url( 'comment.php?action=editcomment&c=' . $comment->comment_ID );
				switch_to_blog( 1 );
				$links = array_merge( $links, array(
					'approve'   => sprintf( '<a href="%s">承認する</a>', admin_url( 'admin-ajax.php?action=taro_edit_comment&b=' . $comment->blog_id . '&c=' . $comment->comment_ID . '&status=1&_wpnonce=' . $this->nonce ) ),
					'unapprove' => sprintf( '<a href="%s">承認待ちにする</a>', admin_url( 'admin-ajax.php?action=taro_edit_comment&b=' . $comment->blog_id . '&c=' . $comment->comment_ID . '&status=0&_wpnonce=' . $this->nonce ) ),
					'edit'      => sprintf( '<a href="%s">編集</a>', $edit_link ),
					'spam'      => sprintf( '<a href="%s">スパム</a>', admin_url( 'admin-ajax.php?action=taro_edit_comment&b=' . $comment->blog_id . '&c=' . $comment->comment_ID . '&status=spam&_wpnonce=' . $this->nonce ) ),
					'trash'     => sprintf( '<a href="%s">ゴミ箱</a>', admin_url( 'admin-ajax.php?action=taro_edit_comment&b=' . $comment->blog_id . '&c=' . $comment->comment_ID . '&status=trash&_wpnonce=' . $this->nonce ) ),
				) );
				if ( '1' === $comment->comment_approved ) {
					unset( $links['approve'] );
				} else {
					unset( $links['unapprove'] );
				}
				break;
		}
		$html .= $this->row_actions( $links );

		return $html;
	}

	/**
	 * Show comment is response to.
	 *
	 * @param \stdClass $comment
	 *
	 * @return string
	 */
	public function column_response( $comment ) {
		switch_to_blog( $comment->blog_id );
		$post   = get_post( $comment->comment_post_ID );
		$html   = <<<HTML
		<small><span class="dashicons dashicons-admin-home"></span> <a href="%s">%s</a></small>
		<hr />
		<a href="%s" target="_blank">%s</a><br />
		<span class="post-com-count"><span>%d</span></span>
		<a href="%s">コメント一覧</a>

HTML;
		$return = sprintf( $html,
			admin_url( 'edit-comments.php' ), esc_html( get_bloginfo( 'name' ) ),
			get_permalink( $post ), get_the_title( $post ),
			$post->comment_count,
		admin_url( 'edit-comments.php?p=' . $post->ID ) );
		switch_to_blog( 1 );

		return $return;
	}

	/**
	 * アクションを登録
	 *
	 * @return array
	 */
	protected function get_bulk_actions() {
		$actions = array(
			'approve'   => '承認する',
			'unapprove' => '承認待ちにする',
			'spam'      => 'スパムにする',
			'trash'     => 'ゴミ箱に入れる',
		);
		if ( in_array( $this->status, [ 'spam', 'trash' ], true ) ) {
			return array();
		}
		switch ( $this->status ) {
			case '0':
				unset( $actions['unapprove'] );
				break;
			case '1':
				unset( $actions['approve'] );
				break;
		}

		return $actions;
	}


	/**
	 * Getter
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
			case 'db':
				global $wpdb;

				return $wpdb;
				break;
			case 'blog_comments':
				return BlogComments::get_instance();
				break;
			default:
				return parent::__get( $name );
				break;
		}
	}

}
