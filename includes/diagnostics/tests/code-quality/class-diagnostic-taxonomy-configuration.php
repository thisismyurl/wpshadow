<?php
/**
 * Taxonomy Configuration Validation
 *
 * Validates custom taxonomy registration and configuration.
 *
 * @since   1.2034.1145
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Taxonomy_Configuration Class
 *
 * Checks custom taxonomy registration issues and configuration problems.
 *
 * @since 1.2034.1145
 */
class Diagnostic_Taxonomy_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'taxonomy-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Taxonomy Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates custom taxonomy registration and configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'custom-post-types';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1145
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_taxonomies;

		// Get all custom taxonomies (exclude built-in)
		$built_in_taxonomies = array( 'category', 'post_tag', 'nav_menu', 'link_category', 'post_format', 'wp_theme', 'wp_template_part_area' );
		$custom_taxonomies = array();

		foreach ( $wp_taxonomies as $tax => $tax_obj ) {
			if ( ! in_array( $tax, $built_in_taxonomies, true ) ) {
				$custom_taxonomies[ $tax ] = $tax_obj;
			}
		}

		// Pattern 1: Taxonomies with missing or incomplete labels
		$missing_labels = array();
		foreach ( $custom_taxonomies as $tax => $tax_obj ) {
			$required_labels = array( 'name', 'singular_name', 'search_items', 'all_items', 'parent_item', 'parent_item_colon', 'edit_item', 'update_item', 'add_new_item', 'new_item_name', 'menu_name' );
			$missing = array();

			foreach ( $required_labels as $label ) {
				if ( empty( $tax_obj->labels->$label ) ) {
					$missing[] = $label;
				}
			}

			if ( ! empty( $missing ) ) {
				$missing_labels[ $tax ] = $missing;
			}
		}

		if ( ! empty( $missing_labels ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom taxonomies missing important labels', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-configuration',
				'details'      => array(
					'issue' => 'missing_labels',
					'affected_taxonomies' => $missing_labels,
					'message' => sprintf(
						/* translators: %d: number of taxonomies */
						__( '%d custom taxonomies missing required labels', 'wpshadow' ),
						count( $missing_labels )
					),
					'why_labels_matter' => array(
						'Used throughout WordPress admin',
						'Improve editor experience',
						'Better accessibility',
						'Professional appearance',
					),
					'required_labels' => array(
						'name' => 'Plural name (e.g., "Genres")',
						'singular_name' => 'Singular name (e.g., "Genre")',
						'search_items' => 'Search button text',
						'all_items' => 'All items link text',
						'parent_item' => 'Parent item label (hierarchical)',
						'parent_item_colon' => 'Parent item with colon',
						'edit_item' => 'Edit page title',
						'update_item' => 'Update button text',
						'add_new_item' => 'Add new page title',
						'new_item_name' => 'New item name field label',
						'menu_name' => 'Menu name',
					),
					'hierarchical_vs_non_hierarchical' => array(
						'Hierarchical (like categories)' => 'Use parent_item labels',
						'Non-hierarchical (like tags)' => 'Use popular_items, separate_items_with_commas',
					),
					'user_experience_impact' => __( 'Generic labels like "Items" confuse content editors', 'wpshadow' ),
					'code_example' => "register_taxonomy('genre', 'book', array(
	'labels' => array(
		'name' => __('Genres', 'textdomain'),
		'singular_name' => __('Genre', 'textdomain'),
		'search_items' => __('Search Genres', 'textdomain'),
		'all_items' => __('All Genres', 'textdomain'),
		'parent_item' => __('Parent Genre', 'textdomain'),
		'parent_item_colon' => __('Parent Genre:', 'textdomain'),
		'edit_item' => __('Edit Genre', 'textdomain'),
		'update_item' => __('Update Genre', 'textdomain'),
		'add_new_item' => __('Add New Genre', 'textdomain'),
		'new_item_name' => __('New Genre Name', 'textdomain'),
		'menu_name' => __('Genres', 'textdomain'),
	),
));",
					'recommendation' => __( 'Define all taxonomy labels for better admin UX', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Taxonomies using reserved slugs
		$reserved_slugs = array( 'category', 'post_tag', 'tag', 'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'tag', 'taxonomy', 'term' );
		$problematic_slugs = array();

		foreach ( $custom_taxonomies as $tax => $tax_obj ) {
			if ( in_array( $tax, $reserved_slugs, true ) ) {
				$problematic_slugs[] = $tax;
			}
		}

		if ( ! empty( $problematic_slugs ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom taxonomies using reserved slugs', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-configuration',
				'details'      => array(
					'issue' => 'reserved_slugs',
					'problematic_slugs' => $problematic_slugs,
					'message' => sprintf(
						/* translators: %d: number of taxonomies */
						__( '%d custom taxonomies using reserved WordPress slugs', 'wpshadow' ),
						count( $problematic_slugs )
					),
					'why_this_is_critical' => array(
						'Conflicts with WordPress core',
						'Breaks taxonomy pages and archives',
						'Causes URL routing issues',
						'Query variable conflicts',
					),
					'reserved_slugs_list' => $reserved_slugs,
					'common_conflicts' => array(
						'category' => 'Conflicts with default category taxonomy',
						'tag' => 'Conflicts with default tag taxonomy',
						'author' => 'Conflicts with author query var',
						'taxonomy' => 'Reserved for taxonomy query parameter',
					),
					'symptoms' => array(
						'404 errors on taxonomy archives',
						'Wrong content displayed',
						'Admin pages not working',
						'Query results incorrect',
					),
					'how_to_fix' => array(
						'1. Choose a unique slug',
						'2. Update register_taxonomy() call',
						'3. Migrate term relationships (if any)',
						'4. Flush rewrite rules',
					),
					'data_migration_warning' => __( 'Changing slug requires updating all term_taxonomy rows in database', 'wpshadow' ),
					'recommendation' => __( 'Use descriptive, unique slugs (e.g., "genre", "product_category", "skill")', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Taxonomies without REST API support
		$no_rest_api = array();
		foreach ( $custom_taxonomies as $tax => $tax_obj ) {
			if ( empty( $tax_obj->show_in_rest ) ) {
				$no_rest_api[] = $tax;
			}
		}

		if ( ! empty( $no_rest_api ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom taxonomies not exposed to REST API', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-configuration',
				'details'      => array(
					'issue' => 'no_rest_api_support',
					'affected_taxonomies' => $no_rest_api,
					'message' => sprintf(
						/* translators: %d: number of taxonomies */
						__( '%d custom taxonomies not available in REST API', 'wpshadow' ),
						count( $no_rest_api )
					),
					'why_rest_api_matters' => array(
						'Required for block editor taxonomy selector',
						'Enables headless WordPress',
						'Allows mobile app integration',
						'Improves developer experience',
					),
					'block_editor_impact' => __( 'Taxonomies without REST API cannot be selected in block editor sidebar', 'wpshadow' ),
					'features_requiring_rest_api' => array(
						'Block editor' => 'Taxonomy selection panel in sidebar',
						'Site Editor' => 'Full Site Editing features',
						'Mobile apps' => 'WordPress mobile apps',
						'Headless WordPress' => 'Decoupled frontends',
						'Third-party integrations' => 'API-based integrations',
					),
					'how_to_enable' => "register_taxonomy('genre', 'book', array(
	'show_in_rest' => true,
	'rest_base' => 'genres', // Optional: custom REST base
	'rest_controller_class' => 'WP_REST_Terms_Controller', // Default
));",
					'security_considerations' => array(
						'REST API respects same capabilities',
						'Private terms remain private',
						'No additional exposure risk',
					),
					'best_practice' => __( 'Enable REST API for all modern taxonomies', 'wpshadow' ),
					'recommendation' => __( 'Add show_in_rest => true to taxonomy registration', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Non-hierarchical taxonomies without tag cloud support
		$no_tag_cloud = array();
		foreach ( $custom_taxonomies as $tax => $tax_obj ) {
			if ( ! $tax_obj->hierarchical && empty( $tax_obj->show_tagcloud ) ) {
				$no_tag_cloud[] = $tax;
			}
		}

		if ( ! empty( $no_tag_cloud ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Non-hierarchical taxonomies without tag cloud support', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-configuration',
				'details'      => array(
					'issue' => 'no_tag_cloud',
					'affected_taxonomies' => $no_tag_cloud,
					'message' => sprintf(
						/* translators: %d: number of taxonomies */
						__( '%d non-hierarchical taxonomies have tag cloud disabled', 'wpshadow' ),
						count( $no_tag_cloud )
					),
					'what_is_tag_cloud' => __( 'Widget displaying taxonomy terms sized by usage frequency', 'wpshadow' ),
					'benefits_of_tag_clouds' => array(
						'Visual representation of popular terms',
						'Improves content discovery',
						'Shows content relationships',
						'User navigation aid',
					),
					'when_to_enable' => array(
						'Non-hierarchical taxonomies (tags)',
						'Public-facing taxonomies',
						'Taxonomies with many terms',
						'Content discovery important',
					),
					'when_to_disable' => array(
						'Hierarchical taxonomies (categories)',
						'Admin-only taxonomies',
						'Few terms (< 10)',
						'Not useful for users',
					),
					'how_to_enable' => "register_taxonomy('skill', 'portfolio', array(
	'hierarchical' => false,
	'show_tagcloud' => true,
	'show_in_quick_edit' => true,
));",
					'using_tag_cloud_widget' => __( 'Add via Appearance > Widgets > Tag Cloud widget', 'wpshadow' ),
					'recommendation' => __( 'Enable tag cloud for public non-hierarchical taxonomies', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Taxonomies with poorly configured permalinks
		$permalink_issues = array();
		foreach ( $custom_taxonomies as $tax => $tax_obj ) {
			if ( false === $tax_obj->rewrite ) {
				continue; // Intentionally disabled
			}

			// Check rewrite slug
			if ( is_array( $tax_obj->rewrite ) ) {
				$rewrite_slug = isset( $tax_obj->rewrite['slug'] ) ? $tax_obj->rewrite['slug'] : $tax;
			} else {
				$rewrite_slug = $tax;
			}

			// Check for problematic slug patterns
			if ( strlen( $rewrite_slug ) < 3 || preg_match( '/[^a-z0-9\-_]/', $rewrite_slug ) ) {
				$permalink_issues[ $tax ] = $rewrite_slug;
			}
		}

		if ( ! empty( $permalink_issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom taxonomies with problematic permalink configuration', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-configuration',
				'details'      => array(
					'issue' => 'permalink_configuration',
					'problematic_slugs' => $permalink_issues,
					'message' => sprintf(
						/* translators: %d: number of taxonomies */
						__( '%d custom taxonomies have permalink configuration issues', 'wpshadow' ),
						count( $permalink_issues )
					),
					'slug_requirements' => array(
						'Lowercase letters only',
						'Use hyphens for spaces',
						'No special characters',
						'Minimum 3 characters',
						'Descriptive and SEO-friendly',
					),
					'common_problems' => array(
						'Too short' => 'Slugs like "t", "tp" not descriptive',
						'Special characters' => 'Characters like %, &, ? break URLs',
						'Uppercase letters' => 'URLs should be lowercase',
						'Underscores' => 'Prefer hyphens over underscores',
					),
					'seo_impact' => array(
						'URLs should be readable',
						'Descriptive slugs improve SEO',
						'Clean URLs rank better',
						'Breadcrumb clarity',
					),
					'examples' => array(
						'Bad: tp' => 'Too short, not descriptive',
						'Bad: Product_Type' => 'Uppercase, underscores',
						'Good: product-type' => 'Clear, descriptive, lowercase',
						'Good: genres' => 'Clear, simple',
					),
					'rewrite_configuration' => "register_taxonomy('genre', 'book', array(
	'rewrite' => array(
		'slug' => 'genres',
		'with_front' => false,
		'hierarchical' => true, // For hierarchical taxonomy URLs
	),
));",
					'hierarchical_url_structure' => __( 'Set hierarchical => true for category-style URLs (genre/parent/child)', 'wpshadow' ),
					'after_changes' => __( 'Always flush rewrite rules after changing permalink structure', 'wpshadow' ),
					'recommendation' => __( 'Use descriptive, SEO-friendly slugs with lowercase letters and hyphens', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: Taxonomies registered but not assigned to any post types
		$unassigned_taxonomies = array();
		foreach ( $custom_taxonomies as $tax => $tax_obj ) {
			if ( empty( $tax_obj->object_type ) ) {
				$unassigned_taxonomies[] = $tax;
			}
		}

		if ( ! empty( $unassigned_taxonomies ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom taxonomies not assigned to any post types', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/taxonomy-configuration',
				'details'      => array(
					'issue' => 'unassigned_taxonomies',
					'affected_taxonomies' => $unassigned_taxonomies,
					'message' => sprintf(
						/* translators: %d: number of taxonomies */
						__( '%d custom taxonomies not assigned to any post types', 'wpshadow' ),
						count( $unassigned_taxonomies )
					),
					'why_this_is_a_problem' => array(
						'Taxonomy cannot be used',
						'No UI to assign terms',
						'Wastes database resources',
						'Confuses administrators',
					),
					'common_causes' => array(
						'Post type registered after taxonomy',
						'Typo in post type name',
						'Post type registration failed',
						'Incomplete plugin activation',
					),
					'how_taxonomies_are_assigned' => array(
						'During registration' => "register_taxonomy('genre', 'book', array(...))",
						'After registration' => "register_taxonomy_for_object_type('genre', 'book')",
						'Multiple post types' => "register_taxonomy('genre', array('book', 'movie'), array(...))",
					),
					'checking_registration_order' => __( 'Taxonomies must be registered after post types or use register_taxonomy_for_object_type()', 'wpshadow' ),
					'code_example' => "// During taxonomy registration
register_taxonomy('genre', array('book', 'movie'), array(
	'labels' => array(...),
	'public' => true,
));

// Or after both are registered
register_taxonomy_for_object_type('genre', 'book');
register_taxonomy_for_object_type('genre', 'movie');",
					'troubleshooting' => array(
						'1. Check post type slug is correct',
						'2. Verify post type is registered',
						'3. Check registration hook priority',
						'4. Test with register_taxonomy_for_object_type()',
					),
					'recommendation' => __( 'Assign taxonomies to appropriate post types or remove unused taxonomies', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
