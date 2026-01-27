<?php
/**
 * Diagnostic: WordPress Timezone Configuration
 *
 * Checks if WordPress timezone is properly configured.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Timezone_Setting Class
 *
 * Detects if WordPress timezone is properly configured. If the timezone
 * is wrong, several things go wrong:
 *
 * - Scheduled posts publish at incorrect times
 * - Cron jobs run at wrong times
 * - Analytics show traffic at wrong times
 * - Comments have wrong timestamps
 * - User-facing times are confusing
 *
 * WordPress has two timezone settings:
 * 1. PHP timezone (set in php.ini)
 * 2. WordPress timezone offset (in settings)
 *
 * The most reliable method is to set WordPress to a specific timezone
 * in Settings > General instead of just using UTC offset.
 *
 * @since 1.2601.2200
 */
class Diagnostic_Timezone_Setting extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'timezone-setting';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Timezone Configuration';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Verifies WordPress timezone is properly configured for accurate timestamps and scheduled tasks';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks the WordPress timezone setting.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if timezone may be wrong, null if properly set.
	 */
	public static function check() {
		$timezone_string = get_option( 'timezone_string' );
		$gmt_offset      = get_option( 'gmt_offset' );

		// Good: If timezone_string is set to a specific timezone (not UTC, not empty)
		if ( ! empty( $timezone_string ) && 'UTC' !== $timezone_string ) {
			return null;
		}

		// Warning: Using UTC (0 offset) when site might be in different timezone
		// This only triggers if explicitly using UTC
		if ( empty( $timezone_string ) && ( '0' === $gmt_offset || 0 === (int) $gmt_offset ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __(
					'Your WordPress timezone is set to UTC with no offset. If your site is in a different timezone, scheduled posts may publish at the wrong time and timestamps will be confusing. Set your timezone properly in Settings > General.',
					'wpshadow'
				),
				'severity'           => 'low',
				'threat_level'       => 20,
				'site_health_status' => 'recommended',
				'auto_fixable'       => true,
				'kb_link'            => 'https://wpshadow.com/kb/settings-timezone',
				'family'             => self::$family,
				'details'            => array(
					'current_timezone_string' => $timezone_string,
					'current_gmt_offset'      => $gmt_offset,
					'recommendation'          => 'Set timezone to your site\'s actual location in WordPress settings',
				),
			);
		}

		// All good - timezone appears to be properly configured
		return null;
	}
}
