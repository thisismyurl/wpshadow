<?php
/**
 * No Content Performance Tracking Treatment
 *
 * Tests whether content performance is being tracked. Not tracking which
 * content performs leads to blind content strategy.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_No_Performance_Tracking Class
 *
 * Detects when sites lack analytics integration to track content performance.
 * Data-driven content strategy requires knowing what works and what doesn't.
 *
 * @since 1.6093.1200
 */
class Treatment_No_Performance_Tracking extends Treatment_Base {

	protected static $slug = 'no-performance-tracking';
	protected static $title = 'No Content Performance Tracking';
	protected static $description = 'Tests whether content performance is being tracked';
	protected static $family = 'analytics';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_Performance_Tracking' );
	}
}
