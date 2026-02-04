<?php
/**
 * Custom Post Types Manager
 *
 * Manages custom post type registration, activation, and configuration.
 * Provides 10 common CPTs for WordPress websites with proper taxonomies.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since      1.6033.1530
 */

declare(strict_types=1);

namespace WPShadow\Content;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Types Manager Class
 *
 * Handles registration and management of WPShadow custom post types.
 *
 * @since 1.6033.1530
 */
class Post_Types_Manager extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'init' => array(
				array( 'register_active_post_types', 0 ),
				array( 'register_active_taxonomies', 1 ),
			),
		);
	}

	/**
	 * Initialize the post types manager (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Post_Types_Manager::subscribe() instead
	 * @since      1.6033.1530
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Get available post type definitions.
	 *
	 * @since  1.6033.1530
	 * @return array Post type definitions.
	 */
	public static function get_available_post_types() {
		return array(
			'wps_testimonial'   => array(
				'singular'    => __( 'Testimonial', 'wpshadow' ),
				'plural'      => __( 'Testimonials', 'wpshadow' ),
				'description' => __( 'Customer testimonials and reviews', 'wpshadow' ),
				'icon'        => 'dashicons-testimonial',
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'testimonials' ),
				'show_in_rest' => true,
				'menu_position' => 25,
				'taxonomies'  => array( 'wps_testimonial_category', 'wps_rating' ),
			),
			'wps_team_member'   => array(
				'singular'    => __( 'Team Member', 'wpshadow' ),
				'plural'      => __( 'Team Members', 'wpshadow' ),
				'description' => __( 'Staff and team member profiles', 'wpshadow' ),
				'icon'        => 'dashicons-groups',
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'team' ),
				'show_in_rest' => true,
				'menu_position' => 26,
				'taxonomies'  => array( 'wps_department', 'wps_team_role' ),
			),
			'wps_portfolio'     => array(
				'singular'    => __( 'Portfolio Item', 'wpshadow' ),
				'plural'      => __( 'Portfolio', 'wpshadow' ),
				'description' => __( 'Portfolio projects and work samples', 'wpshadow' ),
				'icon'        => 'dashicons-portfolio',
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'portfolio' ),
				'show_in_rest' => true,
				'menu_position' => 27,
				'taxonomies'  => array( 'wps_portfolio_category', 'wps_skill' ),
			),
			'wps_faq'           => array(
				'singular'    => __( 'FAQ', 'wpshadow' ),
				'plural'      => __( 'FAQs', 'wpshadow' ),
				'description' => __( 'Frequently asked questions', 'wpshadow' ),
				'icon'        => 'dashicons-editor-help',
				'supports'    => array( 'title', 'editor', 'revisions' ),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'faq' ),
				'show_in_rest' => true,
				'menu_position' => 28,
				'taxonomies'  => array( 'wps_faq_category' ),
			),
			'wps_case_study'    => array(
				'singular'    => __( 'Case Study', 'wpshadow' ),
				'plural'      => __( 'Case Studies', 'wpshadow' ),
				'description' => __( 'Detailed case studies and success stories', 'wpshadow' ),
				'icon'        => 'dashicons-analytics',
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'case-studies' ),
				'show_in_rest' => true,
				'menu_position' => 29,
				'taxonomies'  => array( 'wps_industry', 'wps_solution' ),
			),
			'wps_event'         => array(
				'singular'    => __( 'Event', 'wpshadow' ),
				'plural'      => __( 'Events', 'wpshadow' ),
				'description' => __( 'Events, seminars, and webinars', 'wpshadow' ),
				'icon'        => 'dashicons-calendar-alt',
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'events' ),
				'show_in_rest' => true,
				'menu_position' => 30,
				'taxonomies'  => array( 'wps_event_category', 'wps_event_type' ),
			),
			'wps_resource'      => array(
				'singular'    => __( 'Resource', 'wpshadow' ),
				'plural'      => __( 'Resources', 'wpshadow' ),
				'description' => __( 'Downloadable resources and materials', 'wpshadow' ),
				'icon'        => 'dashicons-download',
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'resources' ),
				'show_in_rest' => true,
				'menu_position' => 31,
				'taxonomies'  => array( 'wps_resource_type', 'wps_resource_category' ),
			),
			'wps_service'       => array(
				'singular'    => __( 'Service', 'wpshadow' ),
				'plural'      => __( 'Services', 'wpshadow' ),
				'description' => __( 'Business services and offerings', 'wpshadow' ),
				'icon'        => 'dashicons-admin-tools',
				'supports'    => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'services' ),
				'show_in_rest' => true,
				'menu_position' => 32,
				'taxonomies'  => array( 'wps_service_category' ),
			),
			'wps_location'      => array(
				'singular'    => __( 'Location', 'wpshadow' ),
				'plural'      => __( 'Locations', 'wpshadow' ),
				'description' => __( 'Business locations and branches', 'wpshadow' ),
				'icon'        => 'dashicons-location',
				'supports'    => array( 'title', 'editor', 'thumbnail' ),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'locations' ),
				'show_in_rest' => true,
				'menu_position' => 33,
				'taxonomies'  => array( 'wps_location_type' ),
			),
			'wps_documentation' => array(
				'singular'    => __( 'Documentation', 'wpshadow' ),
				'plural'      => __( 'Documentation', 'wpshadow' ),
				'description' => __( 'Knowledge base and documentation', 'wpshadow' ),
				'icon'        => 'dashicons-book',
				'supports'    => array( 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ),
				'public'      => true,
				'has_archive' => true,
				'rewrite'     => array( 'slug' => 'docs' ),
				'show_in_rest' => true,
				'menu_position' => 34,
				'taxonomies'  => array( 'wps_doc_category', 'wps_doc_version' ),
			),
		);
	}

	/**
	 * Get available taxonomy definitions.
	 *
	 * @since  1.6033.1530
	 * @return array Taxonomy definitions.
	 */
	public static function get_available_taxonomies() {
		return array(
			// Testimonial taxonomies
			'wps_testimonial_category' => array(
				'singular'     => __( 'Testimonial Category', 'wpshadow' ),
				'plural'       => __( 'Testimonial Categories', 'wpshadow' ),
				'post_types'   => array( 'wps_testimonial' ),
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'testimonial-category' ),
			),
			'wps_rating'               => array(
				'singular'     => __( 'Rating', 'wpshadow' ),
				'plural'       => __( 'Ratings', 'wpshadow' ),
				'post_types'   => array( 'wps_testimonial' ),
				'hierarchical' => false,
				'rewrite'      => array( 'slug' => 'rating' ),
			),

			// Team Member taxonomies
			'wps_department'           => array(
				'singular'     => __( 'Department', 'wpshadow' ),
				'plural'       => __( 'Departments', 'wpshadow' ),
				'post_types'   => array( 'wps_team_member' ),
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'department' ),
			),
			'wps_team_role'            => array(
				'singular'     => __( 'Team Role', 'wpshadow' ),
				'plural'       => __( 'Team Roles', 'wpshadow' ),
				'post_types'   => array( 'wps_team_member' ),
				'hierarchical' => false,
				'rewrite'      => array( 'slug' => 'team-role' ),
			),

			// Portfolio taxonomies
			'wps_portfolio_category'   => array(
				'singular'     => __( 'Portfolio Category', 'wpshadow' ),
				'plural'       => __( 'Portfolio Categories', 'wpshadow' ),
				'post_types'   => array( 'wps_portfolio' ),
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'portfolio-category' ),
			),
			'wps_skill'                => array(
				'singular'     => __( 'Skill', 'wpshadow' ),
				'plural'       => __( 'Skills', 'wpshadow' ),
				'post_types'   => array( 'wps_portfolio' ),
				'hierarchical' => false,
				'rewrite'      => array( 'slug' => 'skill' ),
			),

			// FAQ taxonomies
			'wps_faq_category'         => array(
				'singular'     => __( 'FAQ Category', 'wpshadow' ),
				'plural'       => __( 'FAQ Categories', 'wpshadow' ),
				'post_types'   => array( 'wps_faq' ),
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'faq-category' ),
			),

			// Case Study taxonomies
			'wps_industry'             => array(
				'singular'     => __( 'Industry', 'wpshadow' ),
				'plural'       => __( 'Industries', 'wpshadow' ),
				'post_types'   => array( 'wps_case_study' ),
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'industry' ),
			),
			'wps_solution'             => array(
				'singular'     => __( 'Solution', 'wpshadow' ),
				'plural'       => __( 'Solutions', 'wpshadow' ),
				'post_types'   => array( 'wps_case_study' ),
				'hierarchical' => false,
				'rewrite'      => array( 'slug' => 'solution' ),
			),

			// Event taxonomies
			'wps_event_category'       => array(
				'singular'     => __( 'Event Category', 'wpshadow' ),
				'plural'       => __( 'Event Categories', 'wpshadow' ),
				'post_types'   => array( 'wps_event' ),
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'event-category' ),
			),
			'wps_event_type'           => array(
				'singular'     => __( 'Event Type', 'wpshadow' ),
				'plural'       => __( 'Event Types', 'wpshadow' ),
				'post_types'   => array( 'wps_event' ),
				'hierarchical' => false,
				'rewrite'      => array( 'slug' => 'event-type' ),
			),

			// Resource taxonomies
			'wps_resource_type'        => array(
				'singular'     => __( 'Resource Type', 'wpshadow' ),
				'plural'       => __( 'Resource Types', 'wpshadow' ),
				'post_types'   => array( 'wps_resource' ),
				'hierarchical' => false,
				'rewrite'      => array( 'slug' => 'resource-type' ),
			),
			'wps_resource_category'    => array(
				'singular'     => __( 'Resource Category', 'wpshadow' ),
				'plural'       => __( 'Resource Categories', 'wpshadow' ),
				'post_types'   => array( 'wps_resource' ),
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'resource-category' ),
			),

			// Service taxonomies
			'wps_service_category'     => array(
				'singular'     => __( 'Service Category', 'wpshadow' ),
				'plural'       => __( 'Service Categories', 'wpshadow' ),
				'post_types'   => array( 'wps_service' ),
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'service-category' ),
			),

			// Location taxonomies
			'wps_location_type'        => array(
				'singular'     => __( 'Location Type', 'wpshadow' ),
				'plural'       => __( 'Location Types', 'wpshadow' ),
				'post_types'   => array( 'wps_location' ),
				'hierarchical' => false,
				'rewrite'      => array( 'slug' => 'location-type' ),
			),

			// Documentation taxonomies
			'wps_doc_category'         => array(
				'singular'     => __( 'Doc Category', 'wpshadow' ),
				'plural'       => __( 'Doc Categories', 'wpshadow' ),
				'post_types'   => array( 'wps_documentation' ),
				'hierarchical' => true,
				'rewrite'      => array( 'slug' => 'doc-category' ),
			),
			'wps_doc_version'          => array(
				'singular'     => __( 'Version', 'wpshadow' ),
				'plural'       => __( 'Versions', 'wpshadow' ),
				'post_types'   => array( 'wps_documentation' ),
				'hierarchical' => false,
				'rewrite'      => array( 'slug' => 'doc-version' ),
			),
		);
	}

	/**
	 * Register active post types.
	 *
	 * @since  1.6033.1530
	 * @return void
	 */
	public static function register_active_post_types() {
		$post_types = self::get_available_post_types();
		$active     = get_option( 'wpshadow_active_post_types', array() );

		foreach ( $active as $post_type_key ) {
			if ( ! isset( $post_types[ $post_type_key ] ) ) {
				continue;
			}

			$config = $post_types[ $post_type_key ];
			self::register_post_type( $post_type_key, $config );
		}
	}

	/**
	 * Register a single post type.
	 *
	 * @since  1.6033.1530
	 * @param  string $post_type Post type key.
	 * @param  array  $config    Post type configuration.
	 * @return void
	 */
	private static function register_post_type( $post_type, $config ) {
		$labels = array(
			'name'                  => $config['plural'],
			'singular_name'         => $config['singular'],
			'menu_name'             => $config['plural'],
			'name_admin_bar'        => $config['singular'],
			'add_new'               => sprintf( __( 'Add New %s', 'wpshadow' ), $config['singular'] ),
			'add_new_item'          => sprintf( __( 'Add New %s', 'wpshadow' ), $config['singular'] ),
			'new_item'              => sprintf( __( 'New %s', 'wpshadow' ), $config['singular'] ),
			'edit_item'             => sprintf( __( 'Edit %s', 'wpshadow' ), $config['singular'] ),
			'view_item'             => sprintf( __( 'View %s', 'wpshadow' ), $config['singular'] ),
			'all_items'             => sprintf( __( 'All %s', 'wpshadow' ), $config['plural'] ),
			'search_items'          => sprintf( __( 'Search %s', 'wpshadow' ), $config['plural'] ),
			'parent_item_colon'     => sprintf( __( 'Parent %s:', 'wpshadow' ), $config['plural'] ),
			'not_found'             => sprintf( __( 'No %s found.', 'wpshadow' ), strtolower( $config['plural'] ) ),
			'not_found_in_trash'    => sprintf( __( 'No %s found in Trash.', 'wpshadow' ), strtolower( $config['plural'] ) ),
			'archives'              => sprintf( __( '%s Archives', 'wpshadow' ), $config['singular'] ),
			'attributes'            => sprintf( __( '%s Attributes', 'wpshadow' ), $config['singular'] ),
			'insert_into_item'      => sprintf( __( 'Insert into %s', 'wpshadow' ), strtolower( $config['singular'] ) ),
			'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', 'wpshadow' ), strtolower( $config['singular'] ) ),
			'featured_image'        => __( 'Featured Image', 'wpshadow' ),
			'set_featured_image'    => __( 'Set featured image', 'wpshadow' ),
			'remove_featured_image' => __( 'Remove featured image', 'wpshadow' ),
			'use_featured_image'    => __( 'Use as featured image', 'wpshadow' ),
		);

		$args = array(
			'labels'             => $labels,
			'description'        => $config['description'],
			'public'             => $config['public'],
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => $config['rewrite'],
			'capability_type'    => 'post',
			'has_archive'        => $config['has_archive'],
			'hierarchical'       => false,
			'menu_position'      => $config['menu_position'],
			'menu_icon'          => $config['icon'],
			'supports'           => $config['supports'],
			'show_in_rest'       => $config['show_in_rest'],
		);

		register_post_type( $post_type, $args );
	}

	/**
	 * Register active taxonomies.
	 *
	 * @since  1.6033.1530
	 * @return void
	 */
	public static function register_active_taxonomies() {
		$taxonomies   = self::get_available_taxonomies();
		$active_cpts  = get_option( 'wpshadow_active_post_types', array() );
		$active_taxes = array();

		// Determine which taxonomies to activate based on active post types
		foreach ( $active_cpts as $post_type ) {
			$all_cpts = self::get_available_post_types();
			if ( isset( $all_cpts[ $post_type ]['taxonomies'] ) ) {
				$active_taxes = array_merge( $active_taxes, $all_cpts[ $post_type ]['taxonomies'] );
			}
		}

		$active_taxes = array_unique( $active_taxes );

		foreach ( $active_taxes as $taxonomy_key ) {
			if ( ! isset( $taxonomies[ $taxonomy_key ] ) ) {
				continue;
			}

			$config = $taxonomies[ $taxonomy_key ];
			self::register_taxonomy( $taxonomy_key, $config );
		}
	}

	/**
	 * Register a single taxonomy.
	 *
	 * @since  1.6033.1530
	 * @param  string $taxonomy Taxonomy key.
	 * @param  array  $config   Taxonomy configuration.
	 * @return void
	 */
	private static function register_taxonomy( $taxonomy, $config ) {
		$labels = array(
			'name'                       => $config['plural'],
			'singular_name'              => $config['singular'],
			'menu_name'                  => $config['plural'],
			'all_items'                  => sprintf( __( 'All %s', 'wpshadow' ), $config['plural'] ),
			'edit_item'                  => sprintf( __( 'Edit %s', 'wpshadow' ), $config['singular'] ),
			'view_item'                  => sprintf( __( 'View %s', 'wpshadow' ), $config['singular'] ),
			'update_item'                => sprintf( __( 'Update %s', 'wpshadow' ), $config['singular'] ),
			'add_new_item'               => sprintf( __( 'Add New %s', 'wpshadow' ), $config['singular'] ),
			'new_item_name'              => sprintf( __( 'New %s Name', 'wpshadow' ), $config['singular'] ),
			'parent_item'                => sprintf( __( 'Parent %s', 'wpshadow' ), $config['singular'] ),
			'parent_item_colon'          => sprintf( __( 'Parent %s:', 'wpshadow' ), $config['singular'] ),
			'search_items'               => sprintf( __( 'Search %s', 'wpshadow' ), $config['plural'] ),
			'popular_items'              => sprintf( __( 'Popular %s', 'wpshadow' ), $config['plural'] ),
			'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'wpshadow' ), strtolower( $config['plural'] ) ),
			'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'wpshadow' ), strtolower( $config['plural'] ) ),
			'choose_from_most_used'      => sprintf( __( 'Choose from most used %s', 'wpshadow' ), strtolower( $config['plural'] ) ),
			'not_found'                  => sprintf( __( 'No %s found.', 'wpshadow' ), strtolower( $config['plural'] ) ),
		);

		$args = array(
			'labels'            => $labels,
			'public'            => true,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_admin_column' => true,
			'query_var'         => true,
			'hierarchical'      => $config['hierarchical'],
			'rewrite'           => $config['rewrite'],
			'show_in_rest'      => true,
		);

		register_taxonomy( $taxonomy, $config['post_types'], $args );
	}

	/**
	 * Get settings for a specific post type.
	 *
	 * @since  1.6033.1530
	 * @param  string $post_type Post type key.
	 * @return array Post type settings.
	 */
	public static function get_post_type_settings( $post_type ) {
		$defaults = array(
			'enabled'      => false,
			'slug'         => '',
			'menu_icon'    => '',
			'has_archive'  => true,
			'show_in_rest' => true,
		);

		$settings = get_option( "wpshadow_post_type_{$post_type}", $defaults );
		return wp_parse_args( $settings, $defaults );
	}

	/**
	 * Save settings for a specific post type.
	 *
	 * @since  1.6033.1530
	 * @param  string $post_type Post type key.
	 * @param  array  $settings  Settings to save.
	 * @return bool Whether save was successful.
	 */
	public static function save_post_type_settings( $post_type, $settings ) {
		return update_option( "wpshadow_post_type_{$post_type}", $settings, false );
	}

	/**
	 * Activate a post type.
	 *
	 * @since  1.6033.1530
	 * @param  string $post_type Post type key.
	 * @return bool Whether activation was successful.
	 */
	public static function activate_post_type( $post_type ) {
		$active = get_option( 'wpshadow_active_post_types', array() );

		if ( ! in_array( $post_type, $active, true ) ) {
			$active[] = $post_type;
			update_option( 'wpshadow_active_post_types', $active, false );
			flush_rewrite_rules();
			return true;
		}

		return false;
	}

	/**
	 * Deactivate a post type.
	 *
	 * @since  1.6033.1530
	 * @param  string $post_type Post type key.
	 * @return bool Whether deactivation was successful.
	 */
	public static function deactivate_post_type( $post_type ) {
		$active = get_option( 'wpshadow_active_post_types', array() );
		$key    = array_search( $post_type, $active, true );

		if ( false !== $key ) {
			unset( $active[ $key ] );
			$active = array_values( $active );
			update_option( 'wpshadow_active_post_types', $active, false );
			flush_rewrite_rules();
			return true;
		}

		return false;
	}
}
