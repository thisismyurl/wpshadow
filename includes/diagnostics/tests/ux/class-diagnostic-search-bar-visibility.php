<?php
/**
 * Search Bar Visibility Diagnostic
 *
 * Detects if search functionality is not visible in main navigation,
 * making content discovery difficult for users.
 *
 * @since   1.6028.1510
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Search_Bar_Visibility Class
 *
 * Checks if search functionality is easily accessible in the site's
 * primary navigation for improved content discovery.
 *
 * @since 1.6028.1510
 */
class Diagnostic_Search_Bar_Visibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-bar-not-visible';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Search Bar Not Visible in Navigation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects if search functionality is not visible in main navigation, making content discovery difficult';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ux_navigation';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if search functionality is visible and accessible
	 * in the site's primary navigation areas.
	 *
	 * @since  1.6028.1510
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if site has enough content to warrant search
		$content_count = self::count_searchable_content();
		
		if ( $content_count < 20 ) {
			return null; // Too little content to need search
		}

		// Check for search in navigation
		$search_locations = self::detect_search_locations();

		if ( ! empty( $search_locations['header'] ) || ! empty( $search_locations['menu'] ) ) {
			return null; // Search is visible
		}

		// Calculate impact based on content volume
		$severity = $content_count > 100 ? 'medium' : 'low';
		$threat_level = $content_count > 100 ? 40 : 25;

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: number of searchable content items */
				__( 'Your site has %d searchable items but no visible search bar, making content discovery difficult for visitors.', 'wpshadow' ),
				$content_count
			),
			'severity'      => $severity,
			'threat_level'  => $threat_level,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/search-bar-visibility',
			'family'        => self::$family,
			'meta'          => array(
				'searchable_items'  => $content_count,
				'header_search'     => $search_locations['header'],
				'menu_search'       => $search_locations['menu'],
				'widget_search'     => $search_locations['widget'],
				'impact_level'      => $content_count > 100 ? __( 'Medium - Significant content discovery barrier', 'wpshadow' ) : __( 'Low - UX refinement opportunity', 'wpshadow' ),
				'immediate_actions' => array(
					__( 'Add search widget to header or primary menu', 'wpshadow' ),
					__( 'Enable theme\'s built-in search if available', 'wpshadow' ),
					__( 'Test search visibility on mobile devices', 'wpshadow' ),
					__( 'Verify search functionality works correctly', 'wpshadow' ),
				),
			),
			'details'       => array(
				'why_important'    => __( 'Search functionality is critical for content-rich sites. Studies show that 30-50% of users use site search when available, and those users convert at 2-3x higher rates than non-searchers. Without visible search, users may leave rather than navigate through categories. Mobile users especially rely on search since navigation menus are often hidden.', 'wpshadow' ),
				'user_impact'      => array(
					__( 'Content Discovery: Users can\'t quickly find specific information', 'wpshadow' ),
					__( 'Navigation Friction: Must browse categories instead of searching', 'wpshadow' ),
					__( 'Mobile Experience: Navigation is hidden, search is only option', 'wpshadow' ),
					__( 'Conversion: Search users convert 2-3x more than browsers', 'wpshadow' ),
				),
				'solution_options' => array(
					'Widget' => array(
						'description' => __( 'Add search widget to header area', 'wpshadow' ),
						'time'        => __( '5 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'steps'       => array(
							__( 'Go to Appearance > Widgets', 'wpshadow' ),
							__( 'Add Search widget to Header or Navigation area', 'wpshadow' ),
							__( 'Save changes', 'wpshadow' ),
							__( 'Test search on desktop and mobile', 'wpshadow' ),
						),
					),
					'Theme Settings' => array(
						'description' => __( 'Enable built-in search in theme customizer', 'wpshadow' ),
						'time'        => __( '5 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
					),
					'Search Plugin' => array(
						'description' => __( 'Install SearchWP or Ivory Search for enhanced search', 'wpshadow' ),
						'time'        => __( '15 minutes', 'wpshadow' ),
						'cost'        => __( 'Free-$99/year', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
					),
					'Custom Code' => array(
						'description' => __( 'Add search form to header.php template', 'wpshadow' ),
						'time'        => __( '30 minutes', 'wpshadow' ),
						'cost'        => __( 'Free (developer time)', 'wpshadow' ),
						'difficulty'  => __( 'Advanced', 'wpshadow' ),
					),
				),
				'best_practices'   => array(
					__( 'Position search in top-right of header (familiar location)', 'wpshadow' ),
					__( 'Use magnifying glass icon for instant recognition', 'wpshadow' ),
					__( 'Make search prominent on mobile (icon in sticky header)', 'wpshadow' ),
					__( 'Ensure search works with keyboard (accessibility)', 'wpshadow' ),
					__( 'Show recent/popular searches as suggestions (optional)', 'wpshadow' ),
					__( 'Track search analytics to improve content', 'wpshadow' ),
				),
				'testing_steps'    => array(
					'Step 1' => __( 'Visit homepage on desktop', 'wpshadow' ),
					'Step 2' => __( 'Look for search icon or bar in header', 'wpshadow' ),
					'Step 3' => __( 'Test search functionality with sample query', 'wpshadow' ),
					'Step 4' => __( 'Check mobile view - is search accessible?', 'wpshadow' ),
					'Step 5' => __( 'Verify search results are relevant and formatted well', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Count searchable content on the site.
	 *
	 * Counts posts, pages, and custom post types that are searchable.
	 *
	 * @since  1.6028.1510
	 * @return int Total searchable content count.
	 */
	private static function count_searchable_content() {
		$count = 0;

		// Get all public, searchable post types
		$post_types = get_post_types(
			array(
				'public'              => true,
				'exclude_from_search' => false,
			),
			'names'
		);

		foreach ( $post_types as $post_type ) {
			$posts = wp_count_posts( $post_type );
			$count += isset( $posts->publish ) ? $posts->publish : 0;
		}

		return $count;
	}

	/**
	 * Detect search functionality locations.
	 *
	 * Checks for search in header, menus, and widgets.
	 *
	 * @since  1.6028.1510
	 * @return array Search locations found.
	 */
	private static function detect_search_locations() {
		$locations = array(
			'header' => false,
			'menu'   => false,
			'widget' => false,
		);

		// Check if search widget is active in header areas
		$sidebars = array( 'header', 'navigation', 'primary', 'top', 'before-header' );
		foreach ( $sidebars as $sidebar_id ) {
			if ( is_active_sidebar( $sidebar_id ) ) {
				global $wp_registered_widgets;
				$sidebar_widgets = wp_get_sidebars_widgets();
				
				if ( isset( $sidebar_widgets[ $sidebar_id ] ) ) {
					foreach ( $sidebar_widgets[ $sidebar_id ] as $widget_id ) {
						if ( isset( $wp_registered_widgets[ $widget_id ] ) ) {
							$widget = $wp_registered_widgets[ $widget_id ];
							if ( isset( $widget['callback'][0] ) && is_object( $widget['callback'][0] ) ) {
								$widget_class = get_class( $widget['callback'][0] );
								if ( strpos( strtolower( $widget_class ), 'search' ) !== false ) {
									$locations['header'] = true;
									break 2;
								}
							}
						}
					}
				}
			}
		}

		// Check for search in any widget area
		if ( is_active_widget( false, false, 'search' ) ) {
			$locations['widget'] = true;
		}

		// Check if theme supports search in navigation
		if ( current_theme_supports( 'header-search' ) || current_theme_supports( 'nav-search' ) ) {
			$locations['menu'] = true;
		}

		return $locations;
	}
}
