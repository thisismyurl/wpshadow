<?php
/**
 * Breadcrumb Navigation Missing Diagnostic
 *
 * Detects missing breadcrumb navigation and schema markup,
 * impacting both UX and SEO opportunities.
 *
 * @since   1.6028.1505
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Breadcrumb_Navigation Class
 *
 * Checks for breadcrumb navigation presence and proper schema markup
 * to improve user orientation and SEO.
 *
 * @since 1.6028.1505
 */
class Diagnostic_Breadcrumb_Navigation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'breadcrumb-navigation-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Breadcrumb Navigation Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing breadcrumb navigation and schema markup, missing UX and SEO opportunity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ux_navigation';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for breadcrumb navigation on deep pages and validates
	 * proper BreadcrumbList schema markup.
	 *
	 * @since  1.6028.1505
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if breadcrumb support is available
		$has_breadcrumbs = self::detect_breadcrumb_support();

		if ( $has_breadcrumbs ) {
			// Has breadcrumbs, but check for schema
			$has_schema = self::detect_breadcrumb_schema();
			if ( $has_schema ) {
				return null; // Breadcrumbs with schema - all good
			}
		} else {
			// No breadcrumbs at all
			$has_schema = false;
		}

		// Calculate content depth (pages with parents, posts, etc.)
		$deep_pages = self::count_deep_content();

		if ( $deep_pages < 5 ) {
			return null; // Site is too simple to need breadcrumbs
		}

		$severity = $has_breadcrumbs ? 'low' : 'medium';
		$threat_level = $has_breadcrumbs ? 20 : 35;

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: 1: number of deep pages, 2: schema status */
				__( 'Your site has %1$d pages that would benefit from breadcrumb navigation. %2$s', 'wpshadow' ),
				$deep_pages,
				$has_breadcrumbs ? __( 'Breadcrumbs exist but lack proper BreadcrumbList schema markup.', 'wpshadow' ) : __( 'No breadcrumb navigation detected.', 'wpshadow' )
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/breadcrumb-navigation',
			'family'        => self::$family,
			'meta'          => array(
				'deep_pages'        => $deep_pages,
				'has_breadcrumbs'   => $has_breadcrumbs,
				'has_schema'        => $has_schema,
				'impact_level'      => $has_breadcrumbs ? __( 'Low - Schema optimization needed', 'wpshadow' ) : __( 'Medium - UX and SEO opportunity missed', 'wpshadow' ),
				'immediate_actions' => $has_breadcrumbs ? array(
					__( 'Add BreadcrumbList schema markup to existing breadcrumbs', 'wpshadow' ),
					__( 'Test schema with Google Rich Results Test', 'wpshadow' ),
				) : array(
					__( 'Install Yoast SEO or RankMath for automatic breadcrumbs', 'wpshadow' ),
					__( 'Enable breadcrumbs in theme settings', 'wpshadow' ),
					__( 'Add breadcrumbs to single post/page templates', 'wpshadow' ),
					__( 'Verify BreadcrumbList schema is present', 'wpshadow' ),
				),
			),
			'details'       => array(
				'why_important'    => __( 'Breadcrumb navigation helps users understand their location in your site hierarchy and provides an easy way to navigate back to parent pages. For SEO, breadcrumbs with proper schema markup appear in Google search results, improving click-through rates by showing the page structure. Google explicitly recommends breadcrumbs with BreadcrumbList schema for better understanding of site structure.', 'wpshadow' ),
				'user_impact'      => array(
					__( 'Navigation: Users can\'t easily return to category/parent pages', 'wpshadow' ),
					__( 'Orientation: Unclear where user is in site hierarchy', 'wpshadow' ),
					__( 'SEO: Miss breadcrumb display in Google search results (CTR boost)', 'wpshadow' ),
					__( 'Mobile: Especially important on mobile where navigation is hidden', 'wpshadow' ),
				),
				'solution_options' => array(
					'SEO Plugin' => array(
						'description' => __( 'Use Yoast SEO or RankMath for automatic breadcrumbs with schema', 'wpshadow' ),
						'time'        => __( '5-10 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Yoast SEO or RankMath', 'wpshadow' ),
							__( 'Enable breadcrumbs in plugin settings', 'wpshadow' ),
							__( 'Add breadcrumb code to theme template', 'wpshadow' ),
							__( 'Test with Google Rich Results Test', 'wpshadow' ),
						),
					),
					'Theme Support' => array(
						'description' => __( 'Enable breadcrumbs if your theme supports them', 'wpshadow' ),
						'time'        => __( '5 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
					),
					'Custom Code' => array(
						'description' => __( 'Implement breadcrumbs with proper schema markup', 'wpshadow' ),
						'time'        => __( '1-2 hours', 'wpshadow' ),
						'cost'        => __( 'Free (developer time)', 'wpshadow' ),
						'difficulty'  => __( 'Advanced', 'wpshadow' ),
					),
				),
				'best_practices'   => array(
					__( 'Display breadcrumbs on all post types except homepage', 'wpshadow' ),
					__( 'Use BreadcrumbList schema markup (not just visual breadcrumbs)', 'wpshadow' ),
					__( 'Show parent hierarchy: Home > Category > Subcategory > Page', 'wpshadow' ),
					__( 'Make breadcrumbs clickable except current page', 'wpshadow' ),
					__( 'Position breadcrumbs at top of content, before H1', 'wpshadow' ),
					__( 'Validate with Google Rich Results Test tool', 'wpshadow' ),
				),
				'testing_steps'    => array(
					'Step 1' => __( 'Visit a deep page (blog post, product, subpage)', 'wpshadow' ),
					'Step 2' => __( 'Look for breadcrumb navigation above the title', 'wpshadow' ),
					'Step 3' => __( 'Test breadcrumb links - do they work?', 'wpshadow' ),
					'Step 4' => __( 'Check schema: View source, search for "BreadcrumbList"', 'wpshadow' ),
					'Step 5' => __( 'Validate: https://search.google.com/test/rich-results', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Detect breadcrumb support in theme or plugins.
	 *
	 * Checks for popular breadcrumb implementations.
	 *
	 * @since  1.6028.1505
	 * @return bool True if breadcrumbs detected, false otherwise.
	 */
	private static function detect_breadcrumb_support() {
		// Check for theme support
		if ( current_theme_supports( 'yoast-seo-breadcrumbs' ) || current_theme_supports( 'breadcrumb-trail' ) ) {
			return true;
		}

		// Check for SEO plugins with breadcrumbs
		if ( function_exists( 'yoast_breadcrumb' ) || function_exists( 'rank_math_the_breadcrumbs' ) ) {
			return true;
		}

		// Check for standalone breadcrumb plugins
		$breadcrumb_plugins = array(
			'breadcrumb-navxt/breadcrumb-navxt.php',
			'flexy-breadcrumb/flexy-breadcrumb.php',
			'breadcrumb/breadcrumb.php',
		);

		foreach ( $breadcrumb_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Detect BreadcrumbList schema markup.
	 *
	 * Checks if proper schema markup is present.
	 *
	 * @since  1.6028.1505
	 * @return bool True if schema detected, false otherwise.
	 */
	private static function detect_breadcrumb_schema() {
		// Check if Yoast or RankMath schema is active
		if ( class_exists( 'WPSEO_Schema_Context' ) || class_exists( 'RankMath\\Schema\\DB' ) ) {
			return true; // These plugins automatically add schema
		}

		return false; // Would need to check actual page output for schema
	}

	/**
	 * Count deep content that would benefit from breadcrumbs.
	 *
	 * Counts posts, pages with parents, and taxonomies.
	 *
	 * @since  1.6028.1505
	 * @return int Count of deep content items.
	 */
	private static function count_deep_content() {
		$count = 0;

		// Count published posts
		$posts = wp_count_posts( 'post' );
		$count += isset( $posts->publish ) ? $posts->publish : 0;

		// Count pages with parents (hierarchical)
		global $wpdb;
		$subpages = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			WHERE post_type = 'page' 
			AND post_status = 'publish' 
			AND post_parent > 0"
		);
		$count += intval( $subpages );

		// Count categories with posts
		$categories = get_categories( array( 'hide_empty' => true ) );
		$count += count( $categories );

		return $count;
	}
}
