<?php
/**
 * Post Type and Taxonomy Relationships Validation
 *
 * Validates relationships between post types, taxonomies, and terms.
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
 * Diagnostic_Post_Type_Relationships Class
 *
 * Checks post type and taxonomy relationship issues.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Post_Type_Relationships extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-type-relationships';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Type and Taxonomy Relationships';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates relationships between post types, taxonomies, and terms';

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
		global $wpdb;

		// Pattern 1: Orphaned term relationships
		$orphaned_relationships = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->term_relationships} tr
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID
			WHERE p.ID IS NULL"
		);

		if ( $orphaned_relationships > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database contains orphaned term relationships', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-type-relationships',
				'details'      => array(
					'issue'                           => 'orphaned_term_relationships',
					'orphaned_count'                  => $orphaned_relationships,
					'message'                         => sprintf(
						/* translators: %d: number of orphaned relationships */
						__( '%d orphaned term relationships in database', 'wpshadow' ),
						$orphaned_relationships
					),
					'what_are_orphaned_relationships' => __( 'Term relationships pointing to deleted posts', 'wpshadow' ),
					'how_this_happens'                => array(
						'Posts deleted without cleanup',
						'Direct database manipulation',
						'Plugin/theme deactivation',
						'Import/export issues',
						'Database corruption',
					),
					'impact'                          => array(
						'Wastes database space',
						'Inflates term counts',
						'Slows taxonomy queries',
						'Confuses analytics',
					),
					'database_bloat'                  => sprintf(
						/* translators: %d: estimated size in KB */
						__( 'Approximately %d KB wasted database space', 'wpshadow' ),
						intval( $orphaned_relationships * 0.1 )
					),
					'cleanup_query'                   => "-- BACKUP DATABASE FIRST!
DELETE tr FROM {$wpdb->prefix}term_relationships tr
LEFT JOIN {$wpdb->prefix}posts p ON tr.object_id = p.ID
WHERE p.ID IS NULL;",
					'wp_cli_command'                  => 'wp term recount $(wp taxonomy list --field=name)',
					'prevention'                      => array(
						'Use wp_delete_post()' => 'WordPress function handles cleanup',
						'Avoid direct queries' => 'Use WordPress APIs',
						'Regular maintenance'  => 'Run cleanup periodically',
					),
					'term_count_update'               => __( 'After cleanup, recalculate term counts with wp_update_term_count_now()', 'wpshadow' ),
					'testing_recommendation'          => __( 'Test cleanup query on staging first', 'wpshadow' ),
					'recommendation'                  => __( 'Clean up orphaned term relationships to improve performance', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Terms without any post relationships
		$unused_terms = $wpdb->get_results(
			"SELECT t.term_id, t.name, tt.taxonomy, tt.count
			FROM {$wpdb->terms} t
			JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
			LEFT JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
			WHERE tr.term_taxonomy_id IS NULL
			AND tt.count = 0
			AND tt.taxonomy NOT IN ('nav_menu', 'link_category', 'post_format')
			LIMIT 50",
			ARRAY_A
		);

		if ( count( $unused_terms ) > 20 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Many unused taxonomy terms exist', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-type-relationships',
				'details'      => array(
					'issue'                => 'unused_terms',
					'unused_terms'         => $unused_terms,
					'total_unused'         => count( $unused_terms ),
					'message'              => sprintf(
						/* translators: %d: number of unused terms */
						__( '%d taxonomy terms assigned to no posts', 'wpshadow' ),
						count( $unused_terms )
					),
					'why_this_happens'     => array(
						'Posts deleted but terms kept',
						'Terms created but never used',
						'Import cleanup incomplete',
						'Taxonomy reorganization',
					),
					'impact'               => array(
						'Database clutter',
						'Confusing admin dropdowns',
						'Harder term management',
						'Longer load times',
					),
					'when_to_keep_terms'   => array(
						'Planned for future use',
						'Part of taxonomy structure',
						'Historical reference',
						'Parent categories',
					),
					'when_to_delete_terms' => array(
						'Typos or mistakes',
						'Duplicate terms',
						'No longer relevant',
						'Post type discontinued',
					),
					'safe_deletion_check'  => "// Check if term truly unused
\$term = get_term(\$term_id, 'category');
if (\$term && \$term->count === 0) {
	// No posts assigned
	\$children = get_term_children(\$term_id, 'category');
	if (empty(\$children)) {
		// No child terms either - safe to delete
		wp_delete_term(\$term_id, 'category');
	}
}",
					'bulk_deletion_wp_cli' => array(
						'List unused'   => 'wp term list category --count=0 --format=ids',
						'Delete unused' => 'wp term delete category $(wp term list category --count=0 --format=ids)',
					),
					'caution'              => __( 'Hierarchical taxonomies: keep parent terms even if no direct assignments', 'wpshadow' ),
					'best_practice'        => __( 'Regular term audits prevent buildup of unused terms', 'wpshadow' ),
					'recommendation'       => __( 'Review and delete truly unused taxonomy terms', 'wpshadow' ),
				),
			);
		}

		// Pattern 3: Post types with term count mismatches
		$count_mismatches = $wpdb->get_results(
			"SELECT tt.taxonomy, tt.term_id, tt.count as stored_count, 
			COUNT(DISTINCT tr.object_id) as actual_count
			FROM {$wpdb->term_taxonomy} tt
			LEFT JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
			LEFT JOIN {$wpdb->posts} p ON tr.object_id = p.ID AND p.post_status = 'publish'
			GROUP BY tt.term_taxonomy_id
			HAVING stored_count != actual_count
			LIMIT 20",
			ARRAY_A
		);

		if ( ! empty( $count_mismatches ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Taxonomy term counts are inaccurate', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-type-relationships',
				'details'      => array(
					'issue'                  => 'term_count_mismatch',
					'mismatched_terms'       => $count_mismatches,
					'message'                => sprintf(
						/* translators: %d: number of terms */
						__( '%d taxonomy terms have incorrect counts', 'wpshadow' ),
						count( $count_mismatches )
					),
					'what_are_term_counts'   => __( 'Cached counts of how many posts use each term', 'wpshadow' ),
					'why_counts_matter'      => array(
						'Used in admin category lists',
						'Shown in tag clouds',
						'Filter empty terms',
						'Analytics and reports',
					),
					'how_mismatches_happen'  => array(
						'Direct database edits',
						'Bulk post deletions',
						'Plugin conflicts',
						'Import/export issues',
						'Database corruption',
					),
					'user_experience_impact' => __( 'Admin sees "5 posts" but clicking shows 3 posts', 'wpshadow' ),
					'recalculating_counts'   => "// Recalculate all terms in a taxonomy
wp_update_term_count_now(array(\$term_id), 'category');

// Or for all taxonomies
\$taxonomies = get_taxonomies();
foreach (\$taxonomies as \$taxonomy) {
	\$terms = get_terms(array(
		'taxonomy' => \$taxonomy,
		'hide_empty' => false,
	));
	\$term_ids = wp_list_pluck(\$terms, 'term_id');
	wp_update_term_count_now(\$term_ids, \$taxonomy);
}",
					'wp_cli_command'         => 'wp term recount category',
					'automatic_recounting'   => __( 'WordPress recalculates on post save, but bulk operations can skip this', 'wpshadow' ),
					'when_to_recount'        => array(
						'After import/export',
						'After bulk deletions',
						'After plugin deactivation',
						'Periodically (monthly)',
					),
					'performance_note'       => __( 'Recounting large taxonomies can take time; run during low-traffic periods', 'wpshadow' ),
					'prevention'             => __( 'Always use wp_delete_post() instead of direct database queries', 'wpshadow' ),
					'recommendation'         => __( 'Recalculate term counts for accurate reporting', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Custom post types without taxonomy assignments
		global $wp_post_types;
		$built_in_types           = array( 'post', 'page', 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'oembed_cache', 'user_request', 'wp_block', 'wp_template', 'wp_template_part', 'wp_global_styles', 'wp_navigation' );
		$types_without_taxonomies = array();

		foreach ( $wp_post_types as $type => $type_obj ) {
			if ( ! in_array( $type, $built_in_types, true ) && $type_obj->public ) {
				$taxonomies = get_object_taxonomies( $type );
				if ( empty( $taxonomies ) ) {
					$types_without_taxonomies[] = $type;
				}
			}
		}

		if ( ! empty( $types_without_taxonomies ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Custom post types without taxonomy assignments', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-type-relationships',
				'details'      => array(
					'issue'                         => 'no_taxonomies',
					'affected_post_types'           => $types_without_taxonomies,
					'message'                       => sprintf(
						/* translators: %d: number of post types */
						__( '%d custom post types have no taxonomies', 'wpshadow' ),
						count( $types_without_taxonomies )
					),
					'why_taxonomies_matter'         => array(
						'Organize and categorize content',
						'Filter and search capabilities',
						'Archive pages by category',
						'Better content discovery',
						'SEO benefits',
					),
					'when_taxonomies_needed'        => array(
						'Multiple items in collection',
						'Content needs categorization',
						'Users browse by topic',
						'Filtering is important',
					),
					'when_taxonomies_not_needed'    => array(
						'Single instance post types',
						'Simple lists without categories',
						'Purely functional content',
					),
					'common_taxonomy_patterns'      => array(
						'Portfolio'    => 'Project Type, Client, Technology',
						'Products'     => 'Product Category, Brand, Tag',
						'Events'       => 'Event Type, Location, Topic',
						'Team Members' => 'Department, Role, Location',
					),
					'assigning_existing_taxonomies' => "// Assign built-in taxonomies
register_post_type('portfolio', array(
	'taxonomies' => array('category', 'post_tag'),
	// ... other args
));",
					'creating_custom_taxonomies'    => "// Create custom taxonomy
register_taxonomy('project_type', 'portfolio', array(
	'labels' => array(
		'name' => __('Project Types', 'textdomain'),
		'singular_name' => __('Project Type', 'textdomain'),
	),
	'hierarchical' => true,
	'public' => true,
	'show_in_rest' => true,
));",
					'hierarchical_vs_tags'          => array(
						'Hierarchical (like categories)' => 'Nested structure, parent-child relationships',
						'Non-hierarchical (like tags)'   => 'Flat structure, flexible tagging',
					),
					'seo_benefits'                  => array(
						'Category/tag archive pages',
						'Better internal linking',
						'Keyword-rich URLs',
						'Breadcrumb navigation',
					),
					'user_experience'               => __( 'Taxonomies help users find related content easily', 'wpshadow' ),
					'recommendation'                => __( 'Add relevant taxonomies to organize custom post types', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Excessive taxonomy assignments per post
		$excessive_terms = $wpdb->get_results(
			"SELECT p.ID, p.post_title, p.post_type, COUNT(tr.term_taxonomy_id) as term_count
			FROM {$wpdb->posts} p
			JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
			WHERE p.post_status = 'publish'
			GROUP BY p.ID
			HAVING term_count > 30
			ORDER BY term_count DESC
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $excessive_terms ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Posts with excessive taxonomy term assignments', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-type-relationships',
				'details'      => array(
					'issue'                   => 'excessive_terms',
					'affected_posts'          => $excessive_terms,
					'message'                 => sprintf(
						/* translators: %d: number of posts */
						__( '%d posts assigned to 30+ taxonomy terms', 'wpshadow' ),
						count( $excessive_terms )
					),
					'why_this_is_problematic' => array(
						'Dilutes content relevance',
						'Confuses users and search engines',
						'Slows queries',
						'Poor UX in editor',
					),
					'recommended_limits'      => array(
						'Categories'        => '2-5 per post (hierarchical)',
						'Tags'              => '5-15 per post (non-hierarchical)',
						'Custom taxonomies' => '3-10 per post',
					),
					'seo_impact'              => __( 'Search engines may penalize over-categorized content as keyword stuffing', 'wpshadow' ),
					'user_experience'         => __( 'Long lists of categories/tags confuse readers', 'wpshadow' ),
					'common_causes'           => array(
						'Automated tagging plugins',
						'Import from other systems',
						'Over-enthusiastic editors',
						'Tag spam',
					),
					'cleanup_strategy'        => array(
						'1. Review top posts with most terms',
						'2. Keep only most relevant 5-10',
						'3. Remove redundant terms',
						'4. Set editorial guidelines',
					),
					'finding_excessive_terms' => "// Get posts with many terms
\$args = array(
	'post_type' => 'post',
	'posts_per_page' => 10,
	'orderby' => 'meta_value_num',
	'order' => 'DESC',
);

\$posts = get_posts(\$args);
foreach (\$posts as \$post) {
	\$terms = wp_get_post_terms(\$post->ID);
	if (count(\$terms) > 20) {
		echo \$post->post_title . ': ' . count(\$terms) . ' terms<br>';
	}
}",
					'editorial_guidelines'    => array(
						'Set maximum term limits',
						'Train content editors',
						'Regular audits',
						'Use primary category plugins',
					),
					'quality_over_quantity'   => __( 'Better to have few relevant terms than many loosely related ones', 'wpshadow' ),
					'recommendation'          => __( 'Limit taxonomy assignments to most relevant 5-15 terms per post', 'wpshadow' ),
				),
			);
		}

		// Pattern 6: Taxonomies shared across incompatible post types
		$taxonomies_to_check      = get_taxonomies( array(), 'objects' );
		$incompatible_assignments = array();

		foreach ( $taxonomies_to_check as $taxonomy => $tax_obj ) {
			$object_types = $tax_obj->object_type;

			// Check if taxonomy assigned to very different post types
			if ( count( $object_types ) > 1 ) {
				$has_post   = in_array( 'post', $object_types, true );
				$has_page   = in_array( 'page', $object_types, true );
				$has_custom = false;

				foreach ( $object_types as $type ) {
					if ( ! in_array( $type, array( 'post', 'page', 'attachment' ), true ) ) {
						$has_custom = true;
					}
				}

				// Mixing posts/pages with custom types might be intentional or problematic
				if ( ( $has_post || $has_page ) && $has_custom && count( $object_types ) > 3 ) {
					$incompatible_assignments[ $taxonomy ] = $object_types;
				}
			}
		}

		if ( ! empty( $incompatible_assignments ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Taxonomies assigned to many different post types', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/post-type-relationships',
				'details'      => array(
					'issue'                          => 'mixed_post_type_assignments',
					'affected_taxonomies'            => $incompatible_assignments,
					'message'                        => sprintf(
						/* translators: %d: number of taxonomies */
						__( '%d taxonomies assigned to multiple unrelated post types', 'wpshadow' ),
						count( $incompatible_assignments )
					),
					'why_this_might_be_problematic'  => array(
						'Terms for one type irrelevant for another',
						'Confusing admin dropdown lists',
						'Mixed content in archives',
						'Poor content organization',
					),
					'when_sharing_makes_sense'       => array(
						'Related content types' => 'Products and Reviews share Categories',
						'Cross-type tagging'    => 'Posts, Pages, Projects share Tags',
						'Location taxonomies'   => 'Events, Venues, Team all use Location',
					),
					'when_sharing_doesnt_make_sense' => array(
						'Product Categories on Blog Posts',
						'Event Types on Team Members',
						'Book Genres on Portfolio Items',
					),
					'examples'                       => array(
						'Good: Tags on Posts, Pages, Projects' => 'General keywords apply to all',
						'Good: Location on Events, Venues, Team' => 'Geographic organization',
						'Bad: Product Category on Posts, Events, Team' => 'Product-specific, irrelevant elsewhere',
					),
					'refactoring_approach'           => array(
						'1. Identify which post types truly need taxonomy',
						'2. Create separate taxonomies for unrelated types',
						'3. Migrate existing terms if needed',
						'4. Update taxonomy registration',
					),
					'unassigning_taxonomy'           => "// Remove post type from taxonomy
unregister_taxonomy_for_object_type('category', 'portfolio');

// Or modify registration
register_taxonomy('product_category', array('product'), array(
	// Only assign to products, not posts/pages
	// ... taxonomy args
));",
					'data_migration_needed'          => __( 'If splitting taxonomy, migrate existing term assignments carefully', 'wpshadow' ),
					'best_practice'                  => __( 'Share taxonomies only between closely related content types', 'wpshadow' ),
					'recommendation'                 => __( 'Review taxonomy assignments and separate unrelated content types', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
