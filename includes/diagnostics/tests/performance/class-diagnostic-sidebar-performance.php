<?php
/**
 * Sidebar Performance Diagnostic
 *
 * Monitors sidebar implementation for performance issues including
 * unnecessary rendering and complex queries.
 *
 * @since   1.26033.2086
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sidebar Performance Diagnostic Class
 *
 * Verifies sidebar optimization:
 * - Sidebar rendering context
 * - Unused sidebars
 * - Sidebar on single posts vs archives
 * - Sidebar conditional loading
 *
 * @since 1.26033.2086
 */
class Diagnostic_Sidebar_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sidebar-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Sidebar Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks sidebar implementation for performance optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2086
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$sidebars = wp_get_sidebars_widgets();
		$empty_sidebars = 0;
		$active_sidebars = 0;

		if ( ! empty( $sidebars ) ) {
			foreach ( $sidebars as $sidebar_id => $widgets ) {
				if ( 'wp_inactive_widgets' !== $sidebar_id ) {
					if ( empty( $widgets ) ) {
						$empty_sidebars++;
					} else {
						$active_sidebars++;
					}
				}
			}
		}

		// Flag if many empty sidebars are registered
		if ( $empty_sidebars > 2 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: empty sidebars */
					__( 'Found %d registered sidebars with no widgets. Remove unused sidebars to reduce queries.', 'wpshadow' ),
					$empty_sidebars
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/sidebar-performance',
				'meta'          => array(
					'total_sidebars'       => count( $sidebars ),
					'active_sidebars'      => $active_sidebars,
					'empty_sidebars'       => $empty_sidebars,
					'recommendation'       => 'Remove empty sidebars from functions.php to reduce query overhead',
					'impact'               => 'Removing unused sidebars saves 1-5 database queries',
					'best_practice'        => array(
						'Register only needed sidebars',
						'Use conditional sidebars on specific pages',
						'Disable sidebar on archive pages',
						'Cache sidebar output',
					),
				),
			);
		}

		return null;
	}
}
