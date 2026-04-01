<?php
/**
 * Speed Optimization Program Treatment
 *
 * Tests for active speed optimization efforts.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Speed Optimization Program Treatment Class
 *
 * Verifies that performance tools or workflows are in place.
 *
 * @since 0.6093.1200
 */
class Treatment_Optimizes_Site_Speed extends Treatment_Base {

	protected static $slug = 'optimizes-site-speed';
	protected static $title = 'Speed Optimization Program';
	protected static $description = 'Tests for active speed optimization efforts';
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Optimizes_Site_Speed' );
	}
}
