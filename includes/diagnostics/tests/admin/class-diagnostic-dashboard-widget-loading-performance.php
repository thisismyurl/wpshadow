<?php
/**
 * Dashboard Widget Loading Performance Diagnostic
 *
 * Checks if dashboard widgets are loading efficiently.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Widget Loading Performance Diagnostic Class
 *
 * Detects dashboard widget performance issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Dashboard_Widget_Loading_Performance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'dashboard-widget-loading-performance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Dashboard Widget Loading Performance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks dashboard widget performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_filter;

		// Count dashboard widget hooks
		if ( isset( $wp_filter['wp_dashboard_setup'] ) ) {
			$dashboard_filters = count( $wp_filter['wp_dashboard_setup'] );
			
			if ( $dashboard_filters > 15 ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => sprintf(
						__( 'Dashboard has %d widget setup hooks. Too many widgets will slow down admin dashboard loading.', 'wpshadow' ),
						absint( $dashboard_filters )
					),
					'severity'      => 'medium',
					'threat_level'  => 35,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/dashboard-widget-loading-performance',
				);
			}
		}

		return null;
	}
}
