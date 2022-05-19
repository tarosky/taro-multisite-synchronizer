<?php

namespace Tarosky\TaroMultisiteSynchronizer\Hooks;


use Tarosky\TaroMultisiteSynchronizer\Pattern\AdminParts;
use Tarosky\TaroMultisiteSynchronizer\Ui\CommentTable;

/**
 * Application.
 */
class CommentScreen extends AdminParts {

	private $capability = 'moderate_comments';

	/**
	 * Add menu
	 */
	public function admin_menu() {
		add_comments_page( '全ブログのコメント', '全ブログのコメント', $this->capability, 'ms-comment', array( $this, 'render' ) );
	}

	public function admin_init() {
		// Change menu name
		global $menu, $submenu;
		if ( isset( $menu[ 25 ][ 0 ] ) ) {
			$menu[ 25 ][ 0 ] = str_replace( 'NEWSの', '', $menu[ 25 ][ 0 ] );
		}
		if ( isset( $submenu[ 'edit-comments.php' ][ 0 ][ 0 ] ) ) {
			$submenu[ 'edit-comments.php' ][ 0 ][ 0 ] = 'このブログのコメント';
		}
		// Load script
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
	}

	public function register_ajax() {
		add_action( 'wp_ajax_taro_edit_comment', array( $this, 'ajax' ) );
	}


	public function admin_enqueue_scripts( $page ) {
		if ( 'comments_page_ms-comment' == $page ) {
			wp_enqueue_script( 'taro-comment-helper', $this->asset_url . 'assets/js/comment-helper.min.js', array( 'jquery-effects-highlight' ), '1.0', true );
			wp_enqueue_style( 'taro-comment-helper', $this->asset_url . 'assets/css/comment-helper.css', null, '1.0' );
		}
	}

	public function ajax() {
		try {
			if ( ! current_user_can( $this->capability ) || ! $this->input->verify_nonce( 'multisite_comment_edit' ) ) {
				throw new \Exception( 'あなたにはコメント編集権限がありません。', 403 );
			}
			$blog_id    = $this->input->get( 'b' );
			$comment_id = $this->input->get( 'c' );
			$comment    = $this->models->comments->get_comment( $blog_id, $comment_id );
			if ( ! $comment ) {
				throw new \Exception( '該当するコメントが存在しませんでした。', 404 );
			}
			$status = $this->input->get( 'status' );
			if ( $status == $comment->comment_approved ) {
				throw new \Exception( 'コメントのステータスはすでに変更済みです。', 500 );
			}
			if ( false === array_search( $status, array( '0', '1', 'spam', 'trash' ) ) ) {
				throw new \Exception( '無効なコメントステータスです。', 500 );
			}
			// OK.
			switch_to_blog( $blog_id );
			if ( ! wp_set_comment_status( $comment_id, $status ) ) {
				$real_comment = get_comment( $comment_id );
				$this->models->comments->sync( $blog_id, $real_comment );
				if ( $real_comment->comment_approved != $status ) {
					throw new \Exception( 'ステータス変更に失敗しました。あとでまた試してください。画面を再読み込みすると直るかもしれません。', 500 );
				}
			}
			restore_current_blog();
			switch ( $status ) {
				case '1':
					$label = '承認済み';
					break;
				case 'spam':
					$label = 'スパム';
					break;
				case 'trash':
					$label = 'ゴミ箱';
					break;
				default:
					$label = '承認待ち';
					break;
			}
			$link = sprintf( '<a href="%s">%s</a>', admin_url( 'edit-comments.php?page=ms-comment&status=' . $status ), $label );
			$html = <<<HTML
<td colspan="4" class="no-more-result"><p>このコメントは{$link}に移動しました。</p></td>
HTML;

			$json = array(
				'success' => true,
				'message' => $html,
			);
		} catch ( \Exception $e ) {
			$json = array(
				'success' => false,
				'message' => sprintf( '<p class="error">%s</p>', $e->getMessage() ),
			);
		}
		wp_send_json( $json );
	}

	public function render() {
		$status = isset( $_GET[ 'status' ] ) ? $this->input->get( 'status' ) : '0';
		?>
		<div class="wrap">
			<h2><i class="dashicons dashicons-format-chat"></i> コメント一括管理</h2>

			<ul class="subsubsub">
				<?php
				foreach (
					array(
						'0'     => '承認待ち',
						'1'     => '承認済み',
						'spam'  => 'スパム',
						'trash' => 'ゴミ箱',
					) as $approved => $link
				) {
					$approved = strval( $approved );
					$count    = $this->models->comments->count( $approved );
					echo '<li>';
					if ( $status == $approved ) {
						printf( '<strong>%s</strong>', $link );
					} else {
						printf( '<a href="%s">%s</a>', admin_url( 'edit-comments.php?page=ms-comment&status=' . $approved ), $link );
					}
					printf( '<span class="count">(<span>%s</span>)</span>', number_format_i18n( $count ) );
					if ( 'trash' != $approved ) {
						echo ' |';
					}
					echo '</li>';
				}

				?>
			</ul>
			<form action="<?php echo admin_url( 'edit-comments.php' ) ?>" method="get">
				<input type="hidden" name="page" value="ms-comment" />
				<input type="hidden" name="status" value="<?php echo esc_attr( $status ) ?>" />
				<?php
				$table = new CommentTable( array(), $status );
				$table->prepare_items();
				$table->search_box( '検索', 'search-comment' );
				$table->display();
				?>
			</form>
		</div>
		<?php
	}

}
