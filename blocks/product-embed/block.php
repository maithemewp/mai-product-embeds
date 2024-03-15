<?php

// Prevent direct file access.
defined( 'ABSPATH' ) || die;

class Mai_Product_Embed_Block {
	/**
	 * Construct the class.
	 */
	function __construct() {
		$this->hooks();
	}

	/**
	 * Add hooks.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	function hooks() {
		add_action( 'acf/init', [ $this, 'register_block' ] );
		add_action( 'acf/init', [ $this, 'register_field_group' ] );
	}

	/**
	 * Registers block.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	function register_block() {
		register_block_type( __DIR__ . '/block.json',
			[
				'render_callback' => [ $this, 'render_block' ],
			]
		);
	}

	/**
	 * Callback function to render the block.
	 *
	 * @since TBD
	 *
	 * @param array    $attributes The block attributes.
	 * @param string   $content    The block content.
	 * @param bool     $is_preview Whether or not the block is being rendered for editing preview.
	 * @param int      $post_id    The current post being edited or viewed.
	 * @param WP_Block $block      The block instance (since WP 5.5).
	 *
	 * @return void
	 */
	function render_block( $attributes, $content, $is_preview, $post_id, $block ) {
		$id = get_field( 'id' );

		if ( ! $id ) {
			return;
		}

		// If Mai Theme v2.
		if ( function_exists( 'mai_get_processed_content' ) ) {
			echo mai_get_processed_content( get_post_field( 'post_content', $id ) );
		} else {
			echo do_blocks( $post->post_content );
		}
	}

	/**
	 * Registers field group.
	 *
	 * @since TBD
	 *
	 * @return void
	 */
	function register_field_group() {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			[
				'key'    => 'mai_product_embed_field_group',
				'title'  => __( 'Mai List', 'mai-product-embeds' ),
				'fields' => [
					[
						'key'           => 'mai_product_embed_id',
						'label'         => __( 'Embed', 'mai-product-embeds' ),
						'name'          => 'id',
						'type'          => 'post_object',
						'post_type'     => [ 'mai_embed' ],
						'taxonomy'      => '',
						'allow_null'    => 1,
						'return_format' => 'id',
						'ui'            => 1,
					],
				],
				'location' => [
					[
						[
							'param'    => 'block',
							'operator' => '==',
							'value'    => 'acf/mai-product-embed',
						],
					],
				],
			]
		);
	}
}
