<?php
/**
 * Footer Menu Diagnostic
 *
 * A footer navigation provides secondary utility links (Privacy Policy,
 * Terms, Contact, Sitemap) that visitors expect to find at the bottom of
 * every page. When a theme registers a footer menu location but no menu
 * is assigned, that opportunity is wasted and legal/support pages become
 * harder to discover.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Footer_Menu Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Footer_Menu extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'footer-menu';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Footer Menu';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that a footer navigation menu location is registered by the theme and has a menu assigned to it with links to utility pages.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'design';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Keywords that identify footer-related menu location names.
	 */
	private const FOOTER_KEYWORDS = array(
		'footer',
		'bottom',
		'secondary',
		'utility',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Gets all registered menu locations, finds those associated with the
	 * footer area, and checks whether a menu has been assigned to any of
	 * them. Returns null both when no footer location is registered (not
	 * the theme's fault) and when a footer menu is properly assigned.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$registered_locations = get_registered_nav_menus(); // location_key => description
		$assigned_locations   = get_nav_menu_locations();   // location_key => menu term ID

		if ( empty( $registered_locations ) ) {
			return null;
		}

		// Find footer-related location keys.
		$footer_location_keys = array();
		foreach ( $registered_locations as $location_key => $description ) {
			$needle = strtolower( $location_key . ' ' . $description );
			foreach ( self::FOOTER_KEYWORDS as $keyword ) {
				if ( str_contains( $needle, $keyword ) ) {
					$footer_location_keys[] = $location_key;
					break;
				}
			}
		}

		// No footer menu location registered — not applicable.
		if ( empty( $footer_location_keys ) ) {
			return null;
		}

		// Check whether any footer location has an assigned menu.
		foreach ( $footer_location_keys as $key ) {
			if ( ! empty( $assigned_locations[ $key ] ) ) {
				return null;
			}
		}

		$location_labels = array_map(
			static fn( $key ) => $registered_locations[ $key ] ?? $key,
			$footer_location_keys
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of footer menu location names */
				__( 'The theme registers a footer navigation area (%s) but no menu has been assigned to it. Visitors who reach the page footer cannot find links to utility pages such as Privacy Policy, Terms, or Contact.', 'wpshadow' ),
				esc_html( implode( ', ', $location_labels ) )
			),
			'severity'     => 'low',
			'threat_level' => 20,
			'kb_link'      => 'https://wpshadow.com/kb/footer-menu?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'footer_locations' => $footer_location_keys,
				'fix'              => __( 'Go to Appearance &rsaquo; Menus, create a Footer Navigation menu, add links to your Privacy Policy, Terms of Service, Contact, and Sitemap pages, then assign it to the footer menu location.', 'wpshadow' ),
			),
		);
	}
}
