<?php
/**
 * Custom Post Type Configuration Validation
 *
 * Validates custom post type registration and configuration.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CPT_Configuration Class
 *
 * Checks custom post type registration issues and configuration problems.
 *
 * @since 1.6093.1200
 */
class Diagnostic_CPT_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Post Type Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates custom post type registration and configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'custom-post-types';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_post_types;

		// Get all custom post types (exclude built-in)
		$built_in_types = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation' );
		$custom_types   = array();

		foreach ( $wp_post_types as $type => $type_obj ) {
			if ( ! in_array( $type, $built_in_types, true ) ) {
				$custom_types[ $type ] = $type_obj;
			}
		}

		// Pattern 1: Custom post types with missing labels
		$missing_labels = array();
		foreach ( $custom_types as $type => $type_obj ) {
			$required_labels = array( 'name', 'singular_name', 'add_new', 'add_new_item', 'edit_item', 'new_item', 'view_item', 'search_items', 'not_found', 'not_found_in_trash' );
			$missing         = array();

			foreach ( $required_labels as $label ) {
				if ( empty( $type_obj->labels->$label ) ) {
					$missing[] = $label;
				}
			}

			if ( ! empty( $missing ) ) {
				$missing_labels[ $type ] = $missing;
			}
		}

		if ( ! empty( $missing_labels ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post types missing important labels', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-configuration',
				'details'      => array(
					'issue'                  => 'missing_labels',
					'affected_post_types'    => $missing_labels,
					'message'                => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post types missing required labels', 'wpshadow' ),
						count( $missing_labels )
					),
					'why_labels_matter'      => array(
						'Used throughout WordPress admin',
						'Improve user experience',
						'Better accessibility',
						'Professional appearance',
					),
					'required_labels'        => array(
						'name'               => 'Plural name (e.g., "Books")',
						'singular_name'      => 'Singular name (e.g., "Book")',
						'add_new'            => 'Add new button text',
						'add_new_item'       => 'Add new item page title',
						'edit_item'          => 'Edit item page title',
						'new_item'           => 'New item label',
						'view_item'          => 'View item link text',
						'search_items'       => 'Search items button text',
						'not_found'          => 'No items found message',
						'not_found_in_trash' => 'No items in trash message',
					),
					'user_experience_impact' => __( 'Users see generic "Items" instead of specific post type names', 'wpshadow' ),
					'code_example'           => "register_post_type('book', array(
	'labels' => array(
		'name' => __('Books', 'textdomain'),
		'singular_name' => __('Book', 'textdomain'),
		'add_new' => __('Add New Book', 'textdomain'),
		'add_new_item' => __('Add New Book', 'textdomain'),
		'edit_item' => __('Edit Book', 'textdomain'),
		'new_item' => __('New Book', 'textdomain'),
		'view_item' => __('View Book', 'textdomain'),
		'search_items' => __('Search Books', 'textdomain'),
		'not_found' => __('No books found', 'textdomain'),
		'not_found_in_trash' => __('No books found in trash', 'textdomain'),
	),
));",
					'best_practice'          => __( 'Always define all label strings for better UX', 'wpshadow' ),
					'recommendation'         => __( 'Update post type registration to include all required labels', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Custom post types using reserved slugs
		$reserved_slugs    = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'action', 'author', 'order', 'theme', 'admin', 'wp-admin', 'login', 'register' );
		$problematic_slugs = array();

		foreach ( $custom_types as $type => $type_obj ) {
			if ( in_array( $type, $reserved_slugs, true ) ) {
				$problematic_slugs[] = $type;
			}
		}

		if ( ! empty( $problematic_slugs ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post types using reserved slugs', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-configuration',
				'details'      => array(
					'issue'                  => 'reserved_slugs',
					'problematic_slugs'      => $problematic_slugs,
					'message'                => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post types using reserved WordPress slugs', 'wpshadow' ),
						count( $problematic_slugs )
					),
					'why_this_is_critical'   => array(
						'Conflicts with WordPress core',
						'Breaks admin pages and menus',
						'Causes URL routing issues',
						'May break plugins',
					),
					'reserved_slugs_list'    => $reserved_slugs,
					'common_conflicts'       => array(
						'post'   => 'Conflicts with default post type',
						'page'   => 'Conflicts with default page type',
						'admin'  => 'Breaks admin area routing',
						'login'  => 'Interferes with login page',
						'author' => 'Conflicts with author archives',
					),
					'symptoms'               => array(
						'404 errors on post type archives',
						'Admin menu items not working',
						'Cannot access post editor',
						'Rewrite rules not working',
					),
					'how_to_fix'             => array(
						'1. Choose a unique slug',
						'2. Update register_post_type() call',
						'3. Migrate existing content (if any)',
						'4. Flush rewrite rules',
					),
					'data_migration_warning' => __( 'Changing slug requires migrating all existing post_type values in database', 'wpshadow' ),
					'recommendation'         => __( 'Use descriptive, unique slugs (e.g., "book", "product", "portfolio")', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Custom post types without REST API support
		$no_rest_api = array();
		foreach ( $custom_types as $type => $type_obj ) {
			if ( empty( $type_obj->show_in_rest ) ) {
				$no_rest_api[] = $type;
			}
		}

		if ( ! empty( $no_rest_api ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post types not exposed to REST API', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-configuration',
				'details'      => array(
					'issue'                       => 'no_rest_api_support',
					'affected_post_types'         => $no_rest_api,
					'message'                     => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post types not available in REST API', 'wpshadow' ),
						count( $no_rest_api )
					),
					'why_rest_api_matters'        => array(
						'Required for block editor (Gutenberg)',
						'Enables headless WordPress',
						'Allows mobile app integration',
						'Improves developer experience',
					),
					'features_requiring_rest_api' => array(
						'Block editor (Gutenberg)' => 'Cannot use modern editor without REST API',
						'Site Editor'              => 'Full Site Editing requires REST API',
						'Mobile apps'              => 'WordPress mobile apps use REST API',
						'Headless WordPress'       => 'Decoupled frontends require REST API',
						'Third-party integrations' => 'Many plugins and services use REST API',
					),
					'block_editor_impact'         => __( 'Post types without REST API support use classic editor only', 'wpshadow' ),
					'how_to_enable'               => "register_post_type('book', array(
	'show_in_rest' => true,
	'rest_base' => 'books', // Optional: custom REST base
	'rest_controller_class' => 'WP_REST_Posts_Controller', // Default
));",
					'security_considerations'     => array(
						'REST API respects same capabilities',
						'Private posts remain private',
						'Unpublished content not exposed',
					),
					'best_practice'               => __( 'Enable REST API for all modern custom post types', 'wpshadow' ),
					'recommendation'              => __( 'Add show_in_rest => true to post type registration', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Custom post types without proper capability mapping
		$capability_issues = array();
		foreach ( $custom_types as $type => $type_obj ) {
			if ( empty( $type_obj->capability_type ) || 'post' === $type_obj->capability_type ) {
				$capability_issues[] = $type;
			}
		}

		if ( ! empty( $capability_issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post types using default post capabilities', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-configuration',
				'details'      => array(
					'issue'                          => 'default_capabilities',
					'affected_post_types'            => $capability_issues,
					'message'                        => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post types using default "post" capabilities', 'wpshadow' ),
						count( $capability_issues )
					),
					'why_custom_capabilities_matter' => array(
						'Fine-grained permission control',
						'Separate access for different content types',
						'Better user role management',
						'More secure content management',
					),
					'capability_structure'           => array(
						'edit_posts'           => 'Can edit own items',
						'edit_others_posts'    => 'Can edit others items',
						'publish_posts'        => 'Can publish items',
						'read_private_posts'   => 'Can read private items',
						'delete_posts'         => 'Can delete own items',
						'edit_published_posts' => 'Can edit published items',
					),
					'example_use_case'               => __( 'Books managed by Book Editors, Products by Shop Managers - different roles', 'wpshadow' ),
					'how_to_implement'               => "register_post_type('book', array(
	'capability_type' => 'book',
	'capabilities' => array(
		'edit_post' => 'edit_book',
		'edit_posts' => 'edit_books',
		'edit_others_posts' => 'edit_others_books',
		'publish_posts' => 'publish_books',
		'read_post' => 'read_book',
		'read_private_posts' => 'read_private_books',
		'delete_post' => 'delete_book',
	),
	'map_meta_cap' => true,
));",
					'adding_capabilities_to_roles'   => array(
						'1. Register post type with custom capabilities',
						'2. Add capabilities to roles programmatically',
						'3. Use plugin like Members or User Role Editor',
						'4. Test access control',
					),
					'when_not_needed'                => __( 'If post type uses same permissions as regular posts, default is fine', 'wpshadow' ),
					'recommendation'                 => __( 'Use custom capabilities for specialized content types', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Custom post types with poorly configured permalinks
		$permalink_issues = array();
		foreach ( $custom_types as $type => $type_obj ) {
			if ( false === $type_obj->rewrite ) {
				continue; // Intentionally disabled
			}

			// Check if rewrite slug is same as post type slug (potential conflict)
			if ( is_array( $type_obj->rewrite ) ) {
				$rewrite_slug = isset( $type_obj->rewrite['slug'] ) ? $type_obj->rewrite['slug'] : $type;
			} else {
				$rewrite_slug = $type;
			}

			// Check for problematic slug patterns
			if ( strlen( $rewrite_slug ) < 3 || preg_match( '/[^a-z0-9\-_]/', $rewrite_slug ) ) {
				$permalink_issues[ $type ] = $rewrite_slug;
			}
		}

		if ( ! empty( $permalink_issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post types with problematic permalink configuration', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-configuration',
				'details'      => array(
					'issue'                 => 'permalink_configuration',
					'problematic_slugs'     => $permalink_issues,
					'message'               => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post types have permalink configuration issues', 'wpshadow' ),
						count( $permalink_issues )
					),
					'slug_requirements'     => array(
						'Lowercase letters only',
						'Use hyphens for spaces',
						'No special characters',
						'Minimum 3 characters',
						'Descriptive and SEO-friendly',
					),
					'common_problems'       => array(
						'Too short'          => 'Slugs like "a", "pr" are not descriptive',
						'Special characters' => 'Characters like %, &, ? break URLs',
						'Uppercase letters'  => 'URLs should be lowercase',
						'Spaces'             => 'Spaces must be hyphens',
					),
					'seo_impact'            => array(
						'URLs should be readable',
						'Descriptive slugs improve SEO',
						'Clean URLs rank better',
					),
					'examples'              => array(
						'Bad: pr'               => 'Too short, not descriptive',
						'Bad: Product%Type'     => 'Special character, uppercase',
						'Good: products'        => 'Clear, descriptive, lowercase',
						'Good: portfolio-items' => 'Clear, uses hyphens',
					),
					'rewrite_configuration' => "register_post_type('product', array(
	'rewrite' => array(
		'slug' => 'products',
		'with_front' => false,
		'feeds' => true,
		'pages' => true,
	),
));",
					'after_changes'         => __( 'Always flush rewrite rules after changing permalink structure', 'wpshadow' ),
					'recommendation'        => __( 'Use descriptive, SEO-friendly slugs with only lowercase letters and hyphens', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: Custom post types missing important features
		$missing_features = array();
		foreach ( $custom_types as $type => $type_obj ) {
			$issues = array();

			// Check for missing thumbnail support
			if ( ! post_type_supports( $type, 'thumbnail' ) ) {
				$issues[] = 'thumbnail';
			}

			// Check for missing excerpt support
			if ( ! post_type_supports( $type, 'excerpt' ) ) {
				$issues[] = 'excerpt';
			}

			// Check if public but not searchable
			if ( $type_obj->public && ! $type_obj->publicly_queryable ) {
				$issues[] = 'not_queryable';
			}

			if ( ! empty( $issues ) ) {
				$missing_features[ $type ] = $issues;
			}
		}

		if ( ! empty( $missing_features ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post types missing recommended features', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cpt-configuration',
				'details'      => array(
					'issue'                   => 'missing_features',
					'affected_post_types'     => $missing_features,
					'message'                 => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post types missing recommended features', 'wpshadow' ),
						count( $missing_features )
					),
					'recommended_features'    => array(
						'thumbnail' => array(
							'purpose'    => 'Featured images for visual content',
							'use_cases'  => 'Listings, archives, social sharing',
							'how_to_add' => "'supports' => array('title', 'editor', 'thumbnail')",
						),
						'excerpt'   => array(
							'purpose'    => 'Short summaries for archive pages',
							'use_cases'  => 'Meta descriptions, previews, feeds',
							'how_to_add' => "'supports' => array('title', 'editor', 'excerpt')",
						),
						'queryable' => array(
							'purpose'    => 'Allow frontend queries',
							'use_cases'  => 'Custom queries, search results',
							'how_to_add' => "'publicly_queryable' => true",
						),
					),
					'feature_support_options' => array(
						'title'           => 'Post title field',
						'editor'          => 'Content editor',
						'author'          => 'Author dropdown',
						'thumbnail'       => 'Featured image',
						'excerpt'         => 'Excerpt field',
						'trackbacks'      => 'Trackback support',
						'custom-fields'   => 'Custom fields meta box',
						'comments'        => 'Comment support',
						'revisions'       => 'Revision tracking',
						'page-attributes' => 'Page attributes (order, parent)',
					),
					'full_example'            => "register_post_type('product', array(
	'public' => true,
	'publicly_queryable' => true,
	'show_ui' => true,
	'show_in_menu' => true,
	'supports' => array(
		'title',
		'editor',
		'thumbnail',
		'excerpt',
		'custom-fields',
		'revisions',
	),
));",
					'when_to_skip_features'   => __( 'Only include features that make sense for your content type', 'wpshadow' ),
					'recommendation'          => __( 'Add thumbnail and excerpt support for better content presentation', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
