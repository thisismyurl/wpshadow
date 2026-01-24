<?php
/**
 * Plugin Name: WPShadow KB CPT
 * Description: Registers a Knowledge Base custom post type with REST support, taxonomies, and meta fields.
 * Version: 1.0.0
 * Author: WPShadow
 * License: GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Register CPT, taxonomies, and meta.
add_action( 'init', function () {
	register_post_type( 'kb', [
		'labels' => [
			'name'               => __( 'Knowledge Base', 'wpshadow' ),
			'singular_name'      => __( 'KB Article', 'wpshadow' ),
			'menu_name'          => __( 'Knowledge Base', 'wpshadow' ),
			'add_new'            => __( 'Add Article', 'wpshadow' ),
			'add_new_item'       => __( 'Add New KB Article', 'wpshadow' ),
			'edit_item'          => __( 'Edit KB Article', 'wpshadow' ),
			'new_item'           => __( 'New KB Article', 'wpshadow' ),
			'view_item'          => __( 'View KB Article', 'wpshadow' ),
			'view_items'         => __( 'View KB Articles', 'wpshadow' ),
			'search_items'       => __( 'Search KB Articles', 'wpshadow' ),
			'not_found'          => __( 'No KB articles found', 'wpshadow' ),
			'not_found_in_trash' => __( 'No KB articles found in trash', 'wpshadow' ),
		],
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'query_var'           => true,
		'rewrite'             => [ 'slug' => 'kb', 'with_front' => false ],
		'capability_type'     => 'post',
		'has_archive'         => 'kb',
		hierarchical          => false,
		'menu_position'       => 23,
		'menu_icon'           => 'dashicons-book-alt',
		'show_in_rest'        => true,
		'rest_base'           => 'kb',
		'rest_controller_class' => 'WP_REST_Posts_Controller',
		'supports'            => [ 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'author' ],
		taxonomies            => [ 'post_tag' ],
	] );

	register_taxonomy( 'kb_category', [ 'kb' ], [
		'hierarchical'      => true,
		'labels'            => [
			'name'          => __( 'KB Categories', 'wpshadow' ),
			'singular_name' => __( 'KB Category', 'wpshadow' ),
		],
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,
		'rewrite'           => [ 'slug' => 'kb/category', 'with_front' => false ],
	] );

	register_taxonomy( 'kb_difficulty', [ 'kb' ], [
		'hierarchical'      => false,
		'labels'            => [
			'name'          => __( 'Difficulty Levels', 'wpshadow' ),
			'singular_name' => __( 'Difficulty Level', 'wpshadow' ),
		],
		'show_ui'           => true,
		s_show_admin_column' => true,
		'query_var'         => true,
		'show_in_rest'      => true,
		'rewrite'           => [ 'slug' => 'kb/difficulty', 'with_front' => false ],
	] );

	register_post_meta( 'kb', 'read_time', [
		'type'         => 'integer',
		'description'  => 'Estimated read time in minutes',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'kb', 'principles', [
		'type'         => 'array',
		'description'  => 'Core principles this article aligns with',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ],
		],
	] );

	register_post_meta( 'kb', 'related_articles', [
		'type'         => 'array',
		'description'  => 'Related KB article slugs',
		'single'       => true,
		'show_in_rest' => [
			'schema' => [ 'type' => 'array', 'items' => [ 'type' => 'string' ] ],
		],
	] );

	register_post_meta( 'kb', 'course_link', [
		'type'         => 'string',
		'description'  => 'Related Academy course URL',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'kb', 'course_name', [
		'type'         => 'string',
		'description'  => 'Related Academy course name',
		'single'       => true,
		'show_in_rest' => true,
	] );

	register_post_meta( 'kb', 'article_status', [
		'type'         => 'string',
		'description'  => 'Article completion status',
		'single'       => true,
		'show_in_rest' => true,
		'default'      => 'draft',
	] );

	register_post_meta( 'kb', 'kb_last_updated', [
		'type'         => 'string',
		'description'  => 'Last content update date',
		'single'       => true,
		'show_in_rest' => true,
	] );
} );

// REST extras: GitHub edit URL and bundled metadata.
add_action( 'rest_api_init', function () {
	register_rest_field( 'kb', 'github_edit_url', [
		'get_callback' => function ( $post ) {
			$slug = $post['slug'] ?? '';
			$cats = wp_get_post_terms( $post['id'], 'kb_category' );
			$cat  = ! empty( $cats ) ? $cats[0]->slug : 'general';
			return sprintf(
				'https://github.com/thisismyurl/wpshadow/blob/main/kb-articles/%s/%s.md',
				$cat,
				$slug
			);
		},
		'schema' => [ 'description' => 'GitHub edit URL for this article', 'type' => 'string' ],
	] );

	register_rest_field( 'kb', 'kb_metadata', [
		'get_callback' => function ( $post ) {
			return [
				'read_time'        => get_post_meta( $post['id'], 'read_time', true ),
				'principles'       => get_post_meta( $post['id'], 'principles', true ),
				'related_articles' => get_post_meta( $post['id'], 'related_articles', true ),
				'course_link'      => get_post_meta( $post['id'], 'course_link', true ),
				'course_name'      => get_post_meta( $post['id'], 'course_name', true ),
				'article_status'   => get_post_meta( $post['id'], 'article_status', true ),
				'kb_last_updated'  => get_post_meta( $post['id'], 'kb_last_updated', true ),
			];
		},
		'schema' => [ 'description' => 'KB article metadata', 'type' => 'object' ],
	] );
} );

// On activation, flush permalinks so /kb/ works.
register_activation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );
