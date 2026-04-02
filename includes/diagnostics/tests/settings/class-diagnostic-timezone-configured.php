<?php
/**
 * Timezone Configured Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 42.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Timezone Configured Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Timezone_Configured extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'timezone-configured';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Timezone Configured';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Timezone Configured. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check timezone_string or gmt_offset option validity.
	 *
	 * TODO Fix Plan:
	 * Fix by setting correct timezone.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		$tz = WP_Settings::get_timezone_data();

		// Named timezone set and valid — healthy.
		if ( $tz['is_named'] && $tz['is_valid'] ) {
			return null;
		}

		// Numeric UTC offset explicitly set (not zero) — intentional enough.
		if ( ! $tz['is_named'] && 0.0 !== $tz['gmt_offset'] ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site timezone is set to UTC (the WordPress default). Dates, scheduled posts, and event plugins will show incorrect times for your audience. Set a named timezone matching your business location.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/timezone-configured',
			'details'      => array(
				'timezone_string' => $tz['timezone_string'],
				'gmt_offset'      => $tz['gmt_offset'],
			),
		);
	}
}
