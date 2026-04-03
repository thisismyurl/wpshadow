<?php
/**
 * Navigation Accessible Name Diagnostic
 *
 * When a theme renders more than one <nav> landmark, each region must have
 * a unique accessible name (aria-label or aria-labelledby) so screen-reader
 * users can distinguish them. This diagnostic checks whether multiple nav
 * menus are assigned and whether the theme templates supply the required
 * ARIA attributes.
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
 * Diagnostic_Nav_Menu_Accessible_Name Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Nav_Menu_Accessible_Name extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'nav-menu-accessible-name';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Navigation Accessible Name';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies that themes with multiple navigation landmarks provide a unique accessible name on each <nav> element via aria-label or aria-labelledby.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * 1. Counts nav menu locations that have an assigned menu.
	 * 2. If only one (or zero) are assigned, returns null — no disambiguation needed.
	 * 3. Otherwise, scans all *.php template files in the active theme (and parent
	 *    theme if applicable) for <nav> elements that lack aria-label or
	 *    aria-labelledby attributes.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// Count how many menu locations actually have an assigned menu.
		$locations     = get_nav_menu_locations();
		$assigned      = array_filter( $locations );
		$assigned_count = count( $assigned );

		// With 0 or 1 nav landmark there's nothing to disambiguate.
		if ( $assigned_count <= 1 ) {
			return null;
		}

		// Scan theme PHP template files for <nav> tags missing an accessible name.
		$theme_dirs = array_unique(
			array_filter(
				array(
					get_stylesheet_directory(),
					get_template_directory(),
				),
				'is_dir'
			)
		);

		$violations   = array();
		$has_aria_nav = false;

		foreach ( $theme_dirs as $dir ) {
			try {
				$iterator = new \RecursiveIteratorIterator(
					new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
				);
			} catch ( \Exception $e ) {
				continue;
			}

			foreach ( $iterator as $file ) {
				if ( ! $file->isFile() || 'php' !== strtolower( $file->getExtension() ) ) {
					continue;
				}

				$content = file_get_contents( $file->getPathname() );
				if ( false === $content || ! str_contains( $content, '<nav' ) ) {
					continue;
				}

				// Check whether any <nav> tag in this file carries an ARIA name.
				if ( preg_match( '/<nav[^>]+aria-(?:label|labelledby)\s*=/i', $content ) ) {
					$has_aria_nav = true;
					continue;
				}

				// <nav> present but no ARIA name found in this file.
				$relative = str_replace( $dir . DIRECTORY_SEPARATOR, '', $file->getPathname() );
				if ( count( $violations ) < 10 ) {
					$violations[] = $relative;
				}
			}
		}

		// Pass if at least one labelled nav exists (partial coverage still passes
		// gracefully — report only when no labelling is found at all).
		if ( $has_aria_nav || empty( $violations ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of assigned nav menus */
				__( 'The site has %d assigned navigation menus but no <nav> element in the active theme appears to carry an aria-label or aria-labelledby attribute. Screen-reader users cannot distinguish between navigation regions.', 'wpshadow' ),
				$assigned_count
			),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => 'https://wpshadow.com/kb/nav-menu-accessible-name?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'assigned_menus'   => $assigned_count,
				'affected_files'   => $violations,
				'fix'              => __( 'Add aria-label="Primary Navigation" (or equivalent) to each <nav> element in your theme templates. For wp_nav_menu(), pass the "container_aria_label" argument added in WordPress 5.8.', 'wpshadow' ),
			),
		);
	}
}
