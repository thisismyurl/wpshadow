<?php
/**
 * FAQ custom post type and block.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\FAQ;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the FAQ post type, taxonomy, meta, and block.
 */
class FAQ_Post_Type {
	/**
	 * Bootstrap hooks.
	 * Called from init hook, so we call methods directly instead of registering more hooks.
	 */
	public static function init(): void {
		self::register_post_type();
		self::register_taxonomy();
		self::register_meta();
		self::register_block();
	}

	/**
	 * Register the FAQ custom post type.
	 */
	public static function register_post_type(): void {
		$labels = [
			'name'               => __( 'FAQs', 'wpshadow' ),
			'singular_name'      => __( 'FAQ', 'wpshadow' ),
			'menu_name'          => __( 'FAQs', 'wpshadow' ),
			'add_new'            => __( 'Add New', 'wpshadow' ),
			'add_new_item'       => __( 'Add New FAQ', 'wpshadow' ),
			'edit_item'          => __( 'Edit FAQ', 'wpshadow' ),
			'new_item'           => __( 'New FAQ', 'wpshadow' ),
			'view_item'          => __( 'View FAQ', 'wpshadow' ),
			'search_items'       => __( 'Search FAQs', 'wpshadow' ),
			'not_found'          => __( 'No FAQs found', 'wpshadow' ),
			'not_found_in_trash' => __( 'No FAQs found in trash', 'wpshadow' ),
		];

		$args = [
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => [ 'slug' => 'faq' ],
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => 24,
			'menu_icon'          => 'dashicons-editor-help',
			'show_in_rest'       => true,
			'supports'           => [ 'title', 'editor', 'excerpt', 'revisions', 'custom-fields' ],
		];

		register_post_type( 'wpshadow_faq', $args );
	}

	/**
	 * Register FAQ taxonomy for topics.
	 */
	public static function register_taxonomy(): void {
		$labels = [
			'name'          => __( 'FAQ Topics', 'wpshadow' ),
			'singular_name' => __( 'FAQ Topic', 'wpshadow' ),
			'search_items'  => __( 'Search FAQ Topics', 'wpshadow' ),
			'all_items'     => __( 'All FAQ Topics', 'wpshadow' ),
			'edit_item'     => __( 'Edit FAQ Topic', 'wpshadow' ),
			'update_item'   => __( 'Update FAQ Topic', 'wpshadow' ),
			'add_new_item'  => __( 'Add New FAQ Topic', 'wpshadow' ),
			'new_item_name' => __( 'New FAQ Topic Name', 'wpshadow' ),
			'menu_name'     => __( 'FAQ Topics', 'wpshadow' ),
		];

		register_taxonomy(
			'faq_topic',
			[ 'wpshadow_faq' ],
			[
				'hierarchical'      => false,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'show_in_rest'      => true,
				'rewrite'           => [ 'slug' => 'faq-topic' ],
			]
		);
	}

	/**
	 * Register meta fields for FAQs.
	 */
	public static function register_meta(): void {
		$meta = [
			'wpshadow_faq_tooltip'        => 'string',
			'wpshadow_faq_related_links'  => 'string',
			'wpshadow_faq_order'          => 'number',
		];

		foreach ( $meta as $key => $type ) {
			register_post_meta(
				'wpshadow_faq',
				$key,
				[
					'single'            => true,
					'type'              => $type,
					'show_in_rest'      => true,
					'sanitize_callback' => 'sanitize_text_field',
					'auth_callback'     => function () {
						return current_user_can( 'edit_posts' );
					},
				]
			);
		}
	}

	/**
	 * Register the FAQ block and assets.
	 */
	public static function register_block(): void {
		$script_handle = 'wpshadow-faq-block';
		
		// Module is in pro-modules/faq/, script is in pro-modules/faq/assets/faq-block.js
		$plugin_dir = plugin_dir_path( __FILE__ );
		$plugin_url = plugin_dir_url( __FILE__ );
		
		$script_path = $plugin_dir . 'assets/faq-block.js';
		$script_url  = $plugin_url . 'assets/faq-block.js';

		if ( file_exists( $script_path ) ) {
			wp_register_script(
				$script_handle,
				$script_url,
				[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-i18n', 'wp-editor', 'wp-data', 'wp-server-side-render' ],
				'1.0.0',
				true
			);
		}

		register_block_type(
			'wpshadow/faq-list',
			[
				'attributes'      => [
					'ids'         => [ 'type' => 'string', 'default' => '' ],
					'topic'       => [ 'type' => 'string', 'default' => '' ],
					'showExcerpt' => [ 'type' => 'boolean', 'default' => true ],
				],
				'editor_script'   => $script_handle,
				'render_callback' => [ 'WPShadow\\FAQ\\FAQ_Post_Type', 'render_block' ],
			]
		);
	}

	/**
	 * Render callback for the FAQ block.
	 *
	 * @param array $atts Block attributes.
	 * @return string
	 */
	public static function render_block( array $atts ): string {
		// Debug logging
		error_log('FAQ render_block called with: ' . print_r($atts, true));
		
		$ids_raw   = isset( $atts['ids'] ) ? $atts['ids'] : '';
		$topic     = isset( $atts['topic'] ) ? sanitize_title( (string) $atts['topic'] ) : '';
		$show_excerpt = isset( $atts['showExcerpt'] ) ? (bool) $atts['showExcerpt'] : true;

		$ids = array_filter( array_map( 'absint', explode( ',', (string) $ids_raw ) ) );
		
		// Debug logging
		error_log('FAQ IDs parsed: ' . print_r($ids, true));
		error_log('FAQ topic: ' . $topic);

		$args = [
			'post_type'      => 'wpshadow_faq',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order date',
			'order'          => 'ASC',
		];

		if ( ! empty( $ids ) ) {
			$args['post__in'] = $ids;
			$args['orderby']  = 'post__in';
			// When specific IDs are provided, don't filter by taxonomy
			// as it creates conflicts when terms exist in multiple taxonomies
		} elseif ( ! empty( $topic ) ) {
			// Only use taxonomy filter when no specific IDs provided
			$args['tax_query'] = [
				[
					'taxonomy' => 'faq_topic',
					'field'    => 'slug',
					'terms'    => $topic,
				],
			];
		}

		$q    = new \WP_Query( $args );
		$html = '';
		
		// Debug logging
		error_log('FAQ Query found: ' . $q->found_posts . ' posts');

		if ( $q->have_posts() ) {
			$html .= '<div class="wpshadow-faq-list" itemscope itemtype="https://schema.org/FAQPage">';

			while ( $q->have_posts() ) {
				$q->the_post();
				$tooltip = get_post_meta( get_the_ID(), 'wpshadow_faq_tooltip', true );
				$title   = get_the_title();
				$content = apply_filters( 'the_content', get_the_content() );
				$excerpt = has_excerpt() ? get_the_excerpt() : '';

				$html .= '<div class="wpshadow-faq" itemscope itemprop="mainEntity" itemtype="https://schema.org/Question">';
				$html .= '<div class="faq-question" itemprop="name">' . esc_html( $title ) . '</div>';
				$html .= '<div class="faq-answer" itemscope itemprop="acceptedAnswer" itemtype="https://schema.org/Answer">';
				if ( $show_excerpt && ! empty( $excerpt ) ) {
					$html .= '<div class="faq-excerpt">' . esc_html( $excerpt ) . '</div>';
				}
				$html .= '<div class="faq-content" itemprop="text">' . $content . '</div>';
				if ( ! empty( $tooltip ) ) {
					$html .= '<div class="faq-tooltip" aria-hidden="true">' . esc_html( $tooltip ) . '</div>';
				}
				$html .= '</div></div>';
			}

		
		// Debug logging
		error_log('FAQ final HTML length: ' . strlen($html));
			$html .= '</div>';
		}

		wp_reset_postdata();

		return $html;
	}
}

// Don't auto-initialize - let Module::init() handle it
