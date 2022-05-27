<?php

namespace Tarosky\TaroMultisiteSynchronizer\Pattern;

/**
 * Rendering function.
 */
abstract class RendererPattern extends Singleton {

	/**
	 * @var string Shortcode name.
	 */
	protected $short_code = '';

	/**
	 * @var bool Is dynamic block?
	 */
	protected $is_dynamic_block = false;

	/**
	 * Slug name.
	 *
	 * @return string
	 */
	protected function block_name() {
		return '';
	}

	/**
	 * Construct.
	 */
	protected function init() {
		// Register block.
		add_action( 'init', [ $this, 'register_block' ] );
		// Editor assets.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
		// Add shortcode.
		if ( $this->short_code ) {
			add_shortcode( $this->short_code, [ $this, 'do_shortcode' ] );
		}
	}

	/**
	 * Register block type.
	 *
	 * @return void
	 */
	public function register_block() {
		$block_name = $this->block_name();
		if ( ! $block_name ) {
			return;
		}
		$attr = $this->get_all_attributes();
		register_block_type( $attr['name'], $this->get_all_attributes() );
	}

	/**
	 * Get all attributes for block.
	 *
	 * @return array
	 */
	protected function get_all_attributes() {
		$attributes = [];
		foreach ( [
			'name'            => $block_name = 'taroms/' . ltrim( $this->block_name(), '/' ),
			'label'           => $this->block_label(),
			'attributes'      => $this->get_block_attributes(),
			'script'          => $this->get_script(),
			'editor_script'   => $this->get_editor_script(),
			'style'           => $this->get_style(),
			'editor_style'    => $this->get_editor_style(),
			'render_callback' => $this->is_dynamic_block ? [ $this, 'render_callback' ] : null,
			'description'     => $this->get_block_description(),
		] as $attr => $handle ) {
			if ( $handle ) {
				$attributes[ $attr ] = $handle;
			}
		}
		return $attributes;
	}

	/**
	 * Block name.
	 *
	 * @return string
	 */
	protected function block_label() {
		return '';
	}

	/**
	 * Attributes of blocks.
	 *
	 * @return array
	 */
	protected function get_block_attributes() {
		return [];
	}

	/**
	 * Editor style handler.
	 *
	 * @return string
	 */
	protected function get_editor_style() {
		return '';
	}

	/**
	 * Style handler.
	 *
	 * @return string
	 */
	protected function get_style() {
		return '';
	}

	/**
	 * Editor script handler.
	 *
	 * @return string
	 */
	protected function get_editor_script() {
		return '';
	}

	/**
	 * Script handler.
	 *
	 * @return string
	 */
	protected function get_script() {
		return '';
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		// Do wp_localize script.
	}

	/**
	 * Render shortcode.
	 *
	 * @param array  $attrs   Attributes.
	 * @param string $content Content.
	 *
	 * @return string
	 */
	public function do_shortcode( $attrs = [], $content = '' ) {
		return '';
	}

	/**
	 * Render callback.
	 *
	 * @param array  $attributes block attributes.
	 * @param string $content    block ocontent.
	 *
	 * @return string
	 */
	public function render_callback( $attributes = [], $content ) {
		return '<p>' . esc_html__( 'Override this function.', 'taroms' ) . '</p>';
	}

	/**
	 * Block description.
	 *
	 * @return string
	 */
	protected function get_block_description() {
		return '';
	}
}
