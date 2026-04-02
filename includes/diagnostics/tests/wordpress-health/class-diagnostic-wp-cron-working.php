<?php
/**
 * WP-Cron Working Diagnostic
 *
 * Checks whether WP-Cron is enabled and scheduled events exist.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_WP_Cron_Working Class
 *
 * Validates WP-Cron configuration and scheduled events.
 *
 * @since 1.6093.1200
 */
class Diagnostic_WP_Cron_Working extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-cron-working';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WP-Cron Working';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WP-Cron is enabled and scheduled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( defined( 'DISABLE_WP_CRON' ) && true === DISABLE_WP_CRON ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WP-Cron is disabled. Ensure a real cron job is configured to run scheduled tasks.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-cron-working',
			);
		}

		$cron = _get_cron_array();
		if ( empty( $cron ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No WP-Cron events found. Scheduled tasks may not be running.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-cron-working',
			);
		}

		return null;
	}
}