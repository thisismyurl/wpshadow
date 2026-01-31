<?php
/**
 * Search Bar Not Visible in Navigation Diagnostic
 *
 * Detects if search functionality is not visible in main navigation,
 * making content discovery difficult for users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since      1.6028.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Search Bar Not Visible Diagnostic Class
 *
 * Checks if search functionality is accessible and visible in the
 * site's main navigation areas.
 *
 * @since 1.6028.1430
 */
class Diagnostic_Search_Bar_Not_Visible extends Diagnostic_Base {

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
	protected static $description = 'Detects if search functionality is not visible in main navigation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ux-navigation';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$has_search = self::check_search_visibility();

		if ( ! $has_search['visible'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Search functionality is not easily accessible in your site navigation', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-bar-visibility',
				'meta'         => array(
					'search_widget_found' => $has_search['widget'],
					'search_in_menu'      => $has_search['menu'],
					'search_form_exists'  => $has_search['form'],
					'checked_areas'       => $has_search['checked'],
				),
				'details'      => array(
					'finding'        => __( 'No visible search functionality detected in navigation', 'wpshadow' ),
					'impact'         => __( 'Users have difficulty discovering content, reducing engagement and site usability', 'wpshadow' ),
					'recommendation' => __( 'Add search widget to header/sidebar or include search in navigation menu', 'wpshadow' ),
					'solution_free'  => array(
						'label' => __( 'Add Search Widget', 'wpshadow' ),
						'steps' => array(
							__( '1. Go to Appearance → Widgets', 'wpshadow' ),
							__( '2. Find "Search" widget', 'wpshadow' ),
							__( '3. Add to "Header" or "Sidebar" area', 'wpshadow' ),
							__( '4. Save changes', 'wpshadow' ),
						),
					),
					'solution_premium' => array(
						'label' => __( 'Header Search Integration', 'wpshadow' ),
						'steps' => array(
							__( '1. Install SearchWP or Relevanssi plugin', 'wpshadow' ),
							__( '2. Configure advanced search features', 'wpshadow' ),
							__( '3. Add search to header template', 'wpshadow' ),
							__( '4. Style search to match design', 'wpshadow' ),
						),
					),
					'solution_advanced' => array(
						'label' => __( 'Custom AJAX Search', 'wpshadow' ),
						'steps' => array(
							__( '1. Implement AJAX-powered instant search', 'wpshadow' ),
							__( '2. Add search suggestions/autocomplete', 'wpshadow' ),
							__( '3. Include keyboard shortcuts (Ctrl+K)', 'wpshadow' ),
							__( '4. Optimize for mobile display', 'wpshadow' ),
						),
					),
					'test_steps'     => array(
						__( '1. Load homepage as anonymous visitor', 'wpshadow' ),
						__( '2. Scan header, sidebar, and menu for search', 'wpshadow' ),
						__( '3. Test on mobile device as well', 'wpshadow' ),
						__( '4. Verify search actually functions', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}

	/**
	 * Check if search is visible in navigation.
	 *
	 * @since  1.6028.1430
	 * @return array Search visibility status.
	 */
	private static function check_search_visibility() {
		$result = array(
			'visible' => false,
			'widget'  => false,
			'menu'    => false,
			'form'    => false,
			'checked' => array(),
		);

		// Check for search widget in sidebars.
		$sidebars = wp_get_sidebars_widgets();
		foreach ( $sidebars as $sidebar_id => $widgets ) {
			if ( '_wp_inactive_widgets' === $sidebar_id ) {
				continue;
			}
			
			$result['checked'][] = $sidebar_id;
			
			if ( is_array( $widgets ) ) {
				foreach ( $widgets as $widget ) {
					if ( strpos( $widget, 'search-' ) === 0 ) {
						$result['widget']  = true;
						$result['visible'] = true;
						return $result;
					}
				}
			}
		}

		// Check navigation menus for search.
		$menu_locations = get_nav_menu_locations();
		if ( ! empty( $menu_locations ) ) {
			foreach ( $menu_locations as $location => $menu_id ) {
				$result['checked'][] = 'menu_' . $location;
				
				$menu_items = wp_get_nav_menu_items( $menu_id );
				if ( $menu_items ) {
					foreach ( $menu_items as $item ) {
						if ( strpos( strtolower( $item->title ), 'search' ) !== false ||
							 strpos( $item->url, '?s=' ) !== false ) {
							$result['menu']    = true;
							$result['visible'] = true;
							return $result;
						}
					}
				}
			}
		}

		// Check if theme has search form in header (generic check).
		if ( current_theme_supports( 'html5', 'search-form' ) ) {
			$result['form'] = true;
			// Note: Can't reliably detect if form is actually visible without rendering.
			// This is a hint that theme supports search forms.
		}

		return $result;
	}
}
