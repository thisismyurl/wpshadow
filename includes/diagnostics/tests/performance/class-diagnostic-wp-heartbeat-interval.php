<?php
/**
 * Diagnostic: WordPress Heartbeat Interval
 *
 * Checks if WordPress Heartbeat is optimized (not running too frequently).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Wp_Heartbeat_Interval
 *
 * Tests WordPress Heartbeat frequency settings.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Heartbeat_Interval extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-heartbeat-interval';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Heartbeat Interval';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WordPress Heartbeat is optimized for performance';

	/**
	 * Check Heartbeat interval.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Check if Heartbeat is disabled globally.
		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'WordPress Heartbeat is disabled (DISABLE_WP_CRON). This can cause scheduled events to fail. Ensure an external cron is configured.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_heartbeat_interval',
				'meta'        => array(
					'wp_cron_disabled' => true,
				),
			);
		}

		// Heartbeat API is handled by JavaScript on admin pages.
		// Default interval is 15-60 seconds; we can't check this without frontend access.
		// Suggest checking for Heartbeat Control plugin or manual configuration.

		return null;
	}
}
