<?php
/**
 * Knowledge Base custom post type.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\KB;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the KB post type, taxonomies, and meta fields.
 */
class KB_Post_Type {
	/**
	 * Bootstrap hooks.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ) );
		add_action( 'init', array( __CLASS__, 'register_meta' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_fields' ) );
	}

	/**
	 * Register the KB custom post type.
	 */
	public static function register_post_type(): void {
		$labels = array(
			'name'                  => __( 'Knowledge Base', 'wpshadow' ),
			'singular_name'         => __( 'KB Article', 'wpshadow' ),
			'menu_name'             => __( 'Knowledge Base', 'wpshadow' ),
			'add_new'               => __( 'Add Article', 'wpshadow' ),
			'add_new_item'          => __( 'Add New KB Article', 'wpshadow' ),
			'edit_item'             => __( 'Edit KB Article', 'wpshadow' ),
			'new_item'              => __( 'New KB Article', 'wpshadow' ),
			'view_item'             => __( 'View KB Article', 'wpshadow' ),
			'view_items'            => __( 'View KB Articles', 'wpshadow' ),
			'search_items'          => __( 'Search KB Articles', 'wpshadow' ),
			'not_found'             => __( 'No KB articles found', 'wpshadow' ),
			'not_found_in_trash'    => __( 'No KB articles found in trash', 'wpshadow' ),
			'all_items'             => __( 'All KB Articles', 'wpshadow' ),
			'archives'              => __( 'KB Archives', 'wpshadow' ),
			'attributes'            => __( 'KB Article Attributes', 'wpshadow' ),
			'insert_into_item'      => __( 'Insert into KB article', 'wpshadow' ),
			'uploaded_to_this_item' => __( 'Uploaded to this KB article', 'wpshadow' ),
		);

		$args = array(
			'labels'                => $labels,
			'description'           => __( 'Knowledge Base articles and documentation', 'wpshadow' ),
			'public'                => true,
			'publicly_queryable'    => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'query_var'             => true,
			'rewrite'               => array(
				'slug'       => 'kb',
				'with_front' => false,
			),
			'capability_type'       => 'post',
			'has_archive'           => 'kb',
			'hierarchical'          => false,
			'menu_position'         => 23,
			'menu_icon'             => 'dashicons-book-alt',
			'show_in_rest'          => true,
			'rest_base'             => 'kb',
			'rest_controller_class' => 'WP_REST_Posts_Controller',
			'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'author' ),
			'taxonomies'            => array( 'post_tag' ),
		);

		register_post_type( 'kb', $args );
	}

	/**
	 * Register KB taxonomies.
	 */
	public static function register_taxonomies(): void {
		// KB Category taxonomy
		$category_labels = array(
			'name'              => __( 'KB Categories', 'wpshadow' ),
			'singular_name'     => __( 'KB Category', 'wpshadow' ),
			'search_items'      => __( 'Search KB Categories', 'wpshadow' ),
			'all_items'         => __( 'All KB Categories', 'wpshadow' ),
			'parent_item'       => __( 'Parent KB Category', 'wpshadow' ),
			'parent_item_colon' => __( 'Parent KB Category:', 'wpshadow' ),
			'edit_item'         => __( 'Edit KB Category', 'wpshadow' ),
			'update_item'       => __( 'Update KB Category', 'wpshadow' ),
			'add_new_item'      => __( 'Add New KB Category', 'wpshadow' ),
			'new_item_name'     => __( 'New KB Category Name', 'wpshadow' ),
			'menu_name'         => __( 'Categories', 'wpshadow' ),
		);

		register_taxonomy(
			'kb_category',
			array( 'kb' ),
			array(
				'hierarchical'      => true,
				'labels'            => $category_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => 'kb/category',
					'with_front' => false,
				),
			)
		);

		// Difficulty level taxonomy
		$difficulty_labels = array(
			'name'          => __( 'Difficulty Levels', 'wpshadow' ),
			'singular_name' => __( 'Difficulty Level', 'wpshadow' ),
			'search_items'  => __( 'Search Difficulty Levels', 'wpshadow' ),
			'all_items'     => __( 'All Difficulty Levels', 'wpshadow' ),
			'edit_item'     => __( 'Edit Difficulty Level', 'wpshadow' ),
			'update_item'   => __( 'Update Difficulty Level', 'wpshadow' ),
			'add_new_item'  => __( 'Add New Difficulty Level', 'wpshadow' ),
			'new_item_name' => __( 'New Difficulty Level Name', 'wpshadow' ),
			'menu_name'     => __( 'Difficulty', 'wpshadow' ),
		);

		register_taxonomy(
			'kb_difficulty',
			array( 'kb' ),
			array(
				'hierarchical'      => false,
				'labels'            => $difficulty_labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => 'kb/difficulty',
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Register meta fields for KB articles.
	 */
	public static function register_meta(): void {
		// Read time
		register_post_meta(
			'kb',
			'read_time',
			array(
				'type'         => 'integer',
				'description'  => __( 'Estimated read time in minutes', 'wpshadow' ),
				'single'       => true,
				'show_in_rest' => true,
			)
		);

		// Core principles mapping
		register_post_meta(
			'kb',
			'principles',
			array(
				'type'         => 'array',
				'description'  => __( 'Core principles this article aligns with', 'wpshadow' ),
				'single'       => true,
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
						),
					),
				),
			)
		);

		// Related articles
		register_post_meta(
			'kb',
			'related_articles',
			array(
				'type'         => 'array',
				'description'  => __( 'Related KB article slugs', 'wpshadow' ),
				'single'       => true,
				'show_in_rest' => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'string',
						),
					),
				),
			)
		);

		// Course link
		register_post_meta(
			'kb',
			'course_link',
			array(
				'type'         => 'string',
				'description'  => __( 'Related Academy course URL', 'wpshadow' ),
				'single'       => true,
				'show_in_rest' => true,
			)
		);

		// Course name
		register_post_meta(
			'kb',
			'course_name',
			array(
				'type'         => 'string',
				'description'  => __( 'Related Academy course name', 'wpshadow' ),
				'single'       => true,
				'show_in_rest' => true,
			)
		);

		// Article status (draft/needs-review/published)
		register_post_meta(
			'kb',
			'article_status',
			array(
				'type'         => 'string',
				'description'  => __( 'Article completion status', 'wpshadow' ),
				'single'       => true,
				'show_in_rest' => true,
				'default'      => 'draft',
			)
		);

		// Last updated date (separate from WordPress post_modified)
		register_post_meta(
			'kb',
			'kb_last_updated',
			array(
				'type'         => 'string',
				'description'  => __( 'Last content update date', 'wpshadow' ),
				'single'       => true,
				'show_in_rest' => true,
			)
		);
	}

	/**
	 * Register custom REST API fields.
	 */
	public static function register_rest_fields(): void {
		// Add GitHub edit URL to REST response
		register_rest_field(
			'kb',
			'github_edit_url',
			array(
				'get_callback' => function ( $post ) {
					$slug = $post['slug'];

					// Determine category from taxonomies
					$categories = wp_get_post_terms( $post['id'], 'kb_category' );
					$category   = ! empty( $categories ) ? $categories[0]->slug : 'general';

					return sprintf(
						'https://github.com/thisismyurl/wpshadow/blob/main/kb-articles/%s/%s.md',
						$category,
						$slug
					);
				},
				'schema'       => array(
					'description' => __( 'GitHub edit URL for this article', 'wpshadow' ),
					'type'        => 'string',
				),
			)
		);

		// Add full metadata object
		register_rest_field(
			'kb',
			'kb_metadata',
			array(
				'get_callback' => function ( $post ) {
					return array(
						'read_time'        => get_post_meta( $post['id'], 'read_time', true ),
						'principles'       => get_post_meta( $post['id'], 'principles', true ),
						'related_articles' => get_post_meta( $post['id'], 'related_articles', true ),
						'course_link'      => get_post_meta( $post['id'], 'course_link', true ),
						'course_name'      => get_post_meta( $post['id'], 'course_name', true ),
						'article_status'   => get_post_meta( $post['id'], 'article_status', true ),
						'kb_last_updated'  => get_post_meta( $post['id'], 'kb_last_updated', true ),
					);
				},
				'schema'       => array(
					'description' => __( 'KB article metadata', 'wpshadow' ),
					'type'        => 'object',
				),
			)
		);
	}
}

KB_Post_Type::init();
