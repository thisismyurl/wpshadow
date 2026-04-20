<?php
/**
 * Site content model registrations used by WP Shadow.
 *
 * Imports custom post types from the site plugin so content remains available
 * after consolidation into this plugin.
 *
 * @package WPShadow
 * @subpackage Content\Post_Types
 * @since 0.6096
 */

declare(strict_types=1);

namespace WPShadow\Content\Post_Types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register site-specific custom post types.
 */
class Site_Content_Models {

	/**
	 * Rewrite schema option key.
	 *
	 * @var string
	 */
	private const REWRITE_OPTION = 'wpshadow_site_content_models_rewrite_version';

	/**
	 * Rewrite schema version.
	 *
	 * @var string
	 */
	private const REWRITE_VERSION = '2';

	/**
	 * Option key used by the Post Types admin page for scoped feature settings.
	 *
	 * @var string
	 */
	private const FEATURE_SETTINGS_OPTION = 'wpshadow_post_type_feature_settings';

	/**
	 * Option key used by the Post Types admin page for CPT activation settings.
	 *
	 * @var string
	 */
	private const ACTIVATION_SETTINGS_OPTION = 'wpshadow_post_type_activation_settings';

	/**
	 * Ensure hooks are only added once.
	 *
	 * @var bool
	 */
	private static $bootstrapped = false;

	/**
	 * Wire registration hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		if ( self::$bootstrapped ) {
			return;
		}

		self::$bootstrapped = true;

		add_action( 'init', array( __CLASS__, 'register_post_types' ), 8 );
		add_action( 'init', array( __CLASS__, 'register_taxonomies' ), 9 );
		add_action( 'init', array( __CLASS__, 'maybe_flush_rewrite_rules' ), 99 );
		add_filter( 'register_post_type_args', array( __CLASS__, 'filter_post_type_args' ), 10, 2 );
		add_filter( 'register_taxonomy_args', array( __CLASS__, 'filter_taxonomy_args' ), 10, 2 );
	}

	/**
	 * Register all migrated custom post types.
	 *
	 * @return void
	 */
	public static function register_post_types(): void {
		$definitions = self::get_post_type_definitions();

		foreach ( $definitions as $post_type => $definition ) {
			if ( ! self::is_post_type_active( $post_type ) ) {
				continue;
			}

			self::register_post_type( $post_type, $definition );
		}
	}

	/**
	 * Register all migrated custom taxonomies.
	 *
	 * @return void
	 */
	public static function register_taxonomies(): void {
		$definitions = self::get_taxonomy_definitions();

		foreach ( $definitions as $taxonomy => $definition ) {
			$definition['object_types'] = self::filter_active_object_types( (array) $definition['object_types'] );

			if ( empty( $definition['object_types'] ) ) {
				continue;
			}

			self::register_taxonomy( $taxonomy, $definition );
		}
	}

	/**
	 * Check whether a managed post type is active.
	 *
	 * @param string $post_type Post type slug.
	 * @return bool
	 */
	public static function is_post_type_active( string $post_type ): bool {
		$settings = get_option( self::ACTIVATION_SETTINGS_OPTION, array() );

		if ( ! is_array( $settings ) || ! array_key_exists( $post_type, $settings ) ) {
			return false;
		}

		return ! empty( $settings[ $post_type ] );
	}

	/**
	 * Mark rewrite schema as stale so it is refreshed on next init.
	 *
	 * @return void
	 */
	public static function mark_rewrite_stale(): void {
		delete_option( self::REWRITE_OPTION );
	}

	/**
	 * Register a single post type from a definition map.
	 *
	 * @param string               $post_type Post type key.
	 * @param array<string, mixed> $definition Post type definition.
	 * @return void
	 */
	private static function register_post_type( string $post_type, array $definition ): void {
		register_post_type(
			$post_type,
			array(
				'labels'              => array(
					'name'          => $definition['name'],
					'singular_name' => $definition['singular_name'],
					'menu_name'     => $definition['menu_name'],
				),
				'public'              => true,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => $definition['show_in_menu'] ?? true,
				'show_in_rest'        => true,
				'rest_base'           => (string) $definition['rest_base'],
				'menu_position'       => (int) $definition['menu_position'],
				'menu_icon'           => (string) $definition['menu_icon'],
				'capability_type'     => (string) $definition['capability_type'],
				'map_meta_cap'        => true,
				'hierarchical'        => (bool) $definition['hierarchical'],
				'has_archive'         => (string) $definition['has_archive'],
				'query_var'           => (string) $definition['query_var'],
				'rewrite'             => array(
					'slug'       => (string) $definition['rewrite_slug'],
					'with_front' => false,
				),
				'supports'            => (array) $definition['supports'],
				'taxonomies'          => array(),
				'delete_with_user'    => false,
				'exclude_from_search' => false,
			)
		);
	}

	/**
	 * Register a single taxonomy from a definition map.
	 *
	 * @param string               $taxonomy Taxonomy key.
	 * @param array<string, mixed> $definition Taxonomy definition.
	 * @return void
	 */
	private static function register_taxonomy( string $taxonomy, array $definition ): void {
		register_taxonomy(
			$taxonomy,
			(array) $definition['object_types'],
			array(
				'labels'            => array(
					'name'          => (string) $definition['name'],
					'singular_name' => (string) $definition['singular_name'],
				),
				'hierarchical'      => (bool) $definition['hierarchical'],
				'public'            => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
				'rewrite'           => array(
					'slug'       => (string) $definition['rewrite_slug'],
					'with_front' => false,
				),
			)
		);
	}

	/**
	 * Return source-of-truth post type definitions.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_post_type_definitions(): array {
		return array(
			'case_study'       => array(
				'name'            => __( 'Case Studies', 'wpshadow' ),
				'singular_name'   => __( 'Case Study', 'wpshadow' ),
				'menu_name'       => __( 'Case Studies', 'wpshadow' ),
				'rest_base'       => 'case-studies',
				'menu_position'   => 21,
				'menu_icon'       => 'dashicons-chart-line',
				'capability_type' => 'post',
				'hierarchical'    => false,
				'has_archive'     => 'case-studies',
				'query_var'       => 'case_study',
				'rewrite_slug'    => 'case-studies',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'custom-fields', 'comments' ),
			),
			'portfolio_item'   => array(
				'name'            => __( 'Portfolio Items', 'wpshadow' ),
				'singular_name'   => __( 'Portfolio Item', 'wpshadow' ),
				'menu_name'       => __( 'Portfolio', 'wpshadow' ),
				'rest_base'       => 'portfolio-items',
				'menu_position'   => 22,
				'menu_icon'       => 'dashicons-portfolio',
				'capability_type' => 'post',
				'hierarchical'    => false,
				'has_archive'     => 'portfolio',
				'query_var'       => 'portfolio_item',
				'rewrite_slug'    => 'portfolio',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'author', 'page-attributes', 'custom-fields' ),
			),
			'testimonial'      => array(
				'name'            => __( 'Testimonials', 'wpshadow' ),
				'singular_name'   => __( 'Testimonial', 'wpshadow' ),
				'menu_name'       => __( 'Testimonials', 'wpshadow' ),
				'rest_base'       => 'testimonials',
				'menu_position'   => 23,
				'menu_icon'       => 'dashicons-format-quote',
				'capability_type' => 'post',
				'hierarchical'    => false,
				'has_archive'     => 'testimonials',
				'query_var'       => 'testimonial',
				'rewrite_slug'    => 'testimonials',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			),
			'service'          => array(
				'name'            => __( 'Services', 'wpshadow' ),
				'singular_name'   => __( 'Service', 'wpshadow' ),
				'menu_name'       => __( 'Services', 'wpshadow' ),
				'rest_base'       => 'services',
				'menu_position'   => 24,
				'menu_icon'       => 'dashicons-hammer',
				'capability_type' => 'page',
				'hierarchical'    => true,
				'has_archive'     => 'services',
				'query_var'       => 'service',
				'rewrite_slug'    => 'services',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes', 'custom-fields' ),
			),
			'training_program' => array(
				'name'            => __( 'Training Overviews', 'wpshadow' ),
				'singular_name'   => __( 'Training Overview', 'wpshadow' ),
				'menu_name'       => __( 'Training', 'wpshadow' ),
				'rest_base'       => 'training-programs',
				'menu_position'   => 25,
				'menu_icon'       => 'dashicons-welcome-learn-more',
				'capability_type' => 'page',
				'hierarchical'    => true,
				'has_archive'     => 'training',
				'query_var'       => 'training_program',
				'rewrite_slug'    => 'training',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'page-attributes', 'custom-fields' ),
			),
			'training_event'   => array(
				'name'            => __( 'Training Events', 'wpshadow' ),
				'singular_name'   => __( 'Training Event', 'wpshadow' ),
				'menu_name'       => __( 'Training Events', 'wpshadow' ),
				'rest_base'       => 'training-events',
				'menu_position'   => 26,
				'menu_icon'       => 'dashicons-calendar',
				'capability_type' => 'post',
				'hierarchical'    => false,
				'has_archive'     => 'training/events',
				'query_var'       => 'training_event',
				'rewrite_slug'    => 'training/events',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
				'show_in_menu'    => 'edit.php?post_type=training_program',
			),
			'download'         => array(
				'name'            => __( 'Downloads', 'wpshadow' ),
				'singular_name'   => __( 'Download', 'wpshadow' ),
				'menu_name'       => __( 'Downloads', 'wpshadow' ),
				'rest_base'       => 'downloads',
				'menu_position'   => 27,
				'menu_icon'       => 'dashicons-download',
				'capability_type' => 'post',
				'hierarchical'    => false,
				'has_archive'     => 'downloads',
				'query_var'       => 'download',
				'rewrite_slug'    => 'downloads',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			),
			'tool'             => array(
				'name'            => __( 'Tools', 'wpshadow' ),
				'singular_name'   => __( 'Tool', 'wpshadow' ),
				'menu_name'       => __( 'Tools', 'wpshadow' ),
				'rest_base'       => 'tools',
				'menu_position'   => 28,
				'menu_icon'       => 'dashicons-admin-tools',
				'capability_type' => 'post',
				'hierarchical'    => false,
				'has_archive'     => 'tools',
				'query_var'       => 'tool',
				'rewrite_slug'    => 'tools',
				'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields' ),
			),
			'faq'              => array(
				'name'            => __( 'FAQs', 'wpshadow' ),
				'singular_name'   => __( 'FAQ', 'wpshadow' ),
				'menu_name'       => __( 'FAQs', 'wpshadow' ),
				'rest_base'       => 'faqs',
				'menu_position'   => 29,
				'menu_icon'       => 'dashicons-editor-help',
				'capability_type' => 'post',
				'hierarchical'    => false,
				'has_archive'     => 'faqs',
				'query_var'       => 'faq',
				'rewrite_slug'    => 'faqs',
				'supports'        => array( 'title', 'editor', 'revisions', 'custom-fields' ),
			),
		);
	}

	/**
	 * Return source-of-truth taxonomy definitions.
	 *
	 * @return array<string,array<string,mixed>>
	 */
	public static function get_taxonomy_definitions(): array {
		return array(
			'case_study_industry'   => array(
				'name'          => __( 'Industries', 'wpshadow' ),
				'singular_name' => __( 'Industry', 'wpshadow' ),
				'object_types'  => array( 'case_study' ),
				'hierarchical'  => true,
				'rewrite_slug'  => 'industry',
			),
			'case_study_service'    => array(
				'name'          => __( 'Services', 'wpshadow' ),
				'singular_name' => __( 'Service', 'wpshadow' ),
				'object_types'  => array( 'case_study' ),
				'hierarchical'  => true,
				'rewrite_slug'  => 'case-study-service',
			),
			'portfolio_type'        => array(
				'name'          => __( 'Portfolio Types', 'wpshadow' ),
				'singular_name' => __( 'Portfolio Type', 'wpshadow' ),
				'object_types'  => array( 'portfolio_item' ),
				'hierarchical'  => true,
				'rewrite_slug'  => 'portfolio-type',
			),
			'portfolio_technology'  => array(
				'name'          => __( 'Technologies', 'wpshadow' ),
				'singular_name' => __( 'Technology', 'wpshadow' ),
				'object_types'  => array( 'portfolio_item' ),
				'hierarchical'  => false,
				'rewrite_slug'  => 'technology',
			),
			'testimonial_service'   => array(
				'name'          => __( 'Testimonial Services', 'wpshadow' ),
				'singular_name' => __( 'Testimonial Service', 'wpshadow' ),
				'object_types'  => array( 'testimonial' ),
				'hierarchical'  => true,
				'rewrite_slug'  => 'testimonial-service',
			),
			'location'              => array(
				'name'          => __( 'Locations', 'wpshadow' ),
				'singular_name' => __( 'Location', 'wpshadow' ),
				'object_types'  => array( 'case_study', 'portfolio_item', 'testimonial', 'service', 'training_program', 'training_event', 'download', 'tool' ),
				'hierarchical'  => true,
				'rewrite_slug'  => 'location',
			),
			'faq_topic'             => array(
				'name'          => __( 'FAQ Topics', 'wpshadow' ),
				'singular_name' => __( 'FAQ Topic', 'wpshadow' ),
				'object_types'  => array( 'faq', 'post', 'page', 'case_study', 'portfolio_item', 'testimonial', 'service', 'training_program', 'training_event', 'download', 'tool' ),
				'hierarchical'  => true,
				'rewrite_slug'  => 'faq-topic',
			),
		);
	}

	/**
	 * Return taxonomy slugs attached to a specific post type.
	 *
	 * @param string $post_type Post type slug.
	 * @return array<int,string>
	 */
	public static function get_taxonomies_for_post_type( string $post_type ): array {
		$matches = array();

		foreach ( self::get_taxonomy_definitions() as $taxonomy_slug => $definition ) {
			$object_types = isset( $definition['object_types'] ) ? (array) $definition['object_types'] : array();

			if ( in_array( $post_type, $object_types, true ) ) {
				$matches[] = $taxonomy_slug;
			}
		}

		return $matches;
	}

	/**
	 * Remove inactive managed post types from taxonomy object type lists.
	 *
	 * @param array<int,string> $object_types Raw object types.
	 * @return array<int,string>
	 */
	private static function filter_active_object_types( array $object_types ): array {
		$definitions = self::get_post_type_definitions();
		$filtered    = array();

		foreach ( $object_types as $object_type ) {
			if ( isset( $definitions[ $object_type ] ) && ! self::is_post_type_active( $object_type ) ) {
				continue;
			}

			$filtered[] = $object_type;
		}

		return array_values( array_unique( $filtered ) );
	}

	/**
	 * Apply scoped feature overrides to post type args.
	 *
	 * @param array<string,mixed> $args      Post type args.
	 * @param string              $post_type Post type slug.
	 * @return array<string,mixed>
	 */
	public static function filter_post_type_args( array $args, string $post_type ): array {
		$settings = self::get_feature_settings_for_post_type( $post_type );

		if ( empty( $settings ) ) {
			return $args;
		}

		if ( ! empty( $settings['force_rest'] ) ) {
			$args['show_in_rest'] = true;
		}

		if ( ! empty( $settings['disable_comments_support'] ) ) {
			$supports         = isset( $args['supports'] ) ? (array) $args['supports'] : array();
			$args['supports'] = array_values( array_diff( $supports, array( 'comments', 'trackbacks' ) ) );
		}

		if ( ! empty( $settings['exclude_from_search'] ) ) {
			$args['exclude_from_search'] = true;
		}

		if ( ! empty( $settings['force_archive'] ) ) {
			$args['has_archive'] = true;
		}

		return $args;
	}

	/**
	 * Apply scoped feature overrides to taxonomy args.
	 *
	 * @param array<string,mixed> $args     Taxonomy args.
	 * @param string              $taxonomy Taxonomy slug.
	 * @return array<string,mixed>
	 */
	public static function filter_taxonomy_args( array $args, string $taxonomy ): array {
		if ( self::is_taxonomy_rest_enabled( $taxonomy ) ) {
			$args['show_in_rest'] = true;
		}

		return $args;
	}

	/**
	 * Return feature settings for one post type.
	 *
	 * @param string $post_type Post type slug.
	 * @return array<string,int>
	 */
	private static function get_feature_settings_for_post_type( string $post_type ): array {
		$all_settings = get_option( self::FEATURE_SETTINGS_OPTION, array() );

		if ( ! is_array( $all_settings ) || ! isset( $all_settings[ $post_type ] ) || ! is_array( $all_settings[ $post_type ] ) ) {
			return array();
		}

		return $all_settings[ $post_type ];
	}

	/**
	 * Check whether a taxonomy has REST enablement via any scoped CPT setting.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 * @return bool
	 */
	private static function is_taxonomy_rest_enabled( string $taxonomy ): bool {
		$all_settings = get_option( self::FEATURE_SETTINGS_OPTION, array() );

		if ( ! is_array( $all_settings ) ) {
			return false;
		}

		foreach ( $all_settings as $post_type => $settings ) {
			if ( ! is_string( $post_type ) || ! is_array( $settings ) || empty( $settings['taxonomy_rest'] ) ) {
				continue;
			}

			$taxonomies = self::get_taxonomies_for_post_type( $post_type );
			if ( in_array( $taxonomy, $taxonomies, true ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Flush rewrites once when structure changes.
	 *
	 * @return void
	 */
	public static function maybe_flush_rewrite_rules(): void {
		if ( get_option( self::REWRITE_OPTION ) === self::REWRITE_VERSION ) {
			return;
		}

		self::register_post_types();
		self::register_taxonomies();
		flush_rewrite_rules( false );
		update_option( self::REWRITE_OPTION, self::REWRITE_VERSION, false );
	}
}

Site_Content_Models::init();

if ( ! class_exists( 'WPShadow\\Content\\Site_Content_Models', false ) ) {
	class_alias( __NAMESPACE__ . '\\Site_Content_Models', 'WPShadow\\Content\\Site_Content_Models' );
}
