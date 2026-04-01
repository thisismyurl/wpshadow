<?php
/**
 * Theme Sidebar Registration Diagnostic
 *
 * Detects issues with theme's sidebar registration and widget areas.
 *
 * @package    WPShadow
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
 * Theme Sidebar Registration Diagnostic Class
 *
 * Checks if theme properly registers sidebars and if they have widgets assigned.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Sidebar_Registration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-sidebar-registration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Sidebar Registration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for theme sidebar registration issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_registered_sidebars;

		$theme = wp_get_theme();
		$issues = array();

		// Check if theme registers any sidebars.
		if ( empty( $wp_registered_sidebars ) ) {
			$issues[] = __( 'Theme does not register any widget areas', 'wpshadow' );
		} else {
			// Check for sidebar.php template.
			$sidebar_template = locate_template( 'sidebar.php' );
			if ( empty( $sidebar_template ) ) {
				$issues[] = __( 'Theme missing sidebar.php template', 'wpshadow' );
			}

			// Check if any sidebars are active (have widgets).
			$active_sidebars = array();
			foreach ( array_keys( $wp_registered_sidebars ) as $sidebar_id ) {
				if ( is_active_sidebar( $sidebar_id ) ) {
					$active_sidebars[] = $sidebar_id;
				}
			}

			if ( empty( $active_sidebars ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of registered sidebars */
					_n(
						'%d registered sidebar has no active widgets',
						'%d registered sidebars have no active widgets',
						count( $wp_registered_sidebars ),
						'wpshadow'
					),
					count( $wp_registered_sidebars )
				);
			}

			// Check for duplicate sidebar IDs.
			$sidebar_ids = array_keys( $wp_registered_sidebars );
			if ( count( $sidebar_ids ) !== count( array_unique( $sidebar_ids ) ) ) {
				$issues[] = __( 'Duplicate sidebar IDs detected', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of issues */
					__( 'Theme sidebar issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'     => array(
					'theme'            => $theme->get( 'Name' ),
					'registered_count' => ! empty( $wp_registered_sidebars ) ? count( $wp_registered_sidebars ) : 0,
					'issues'           => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-sidebar-registration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
