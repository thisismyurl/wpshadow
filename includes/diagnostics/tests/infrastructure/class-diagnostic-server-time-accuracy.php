<?php
/**
 * Diagnostic: Server Time Accuracy
 *
 * Checks if server time is synchronized (not skewed vs. NTP).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Server_Time_Accuracy
 *
 * Tests server time accuracy via PHP time function.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Server_Time_Accuracy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'server-time-accuracy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Server Time Accuracy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if server time is accurate and synchronized';

	/**
	 * Check server time accuracy.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$server_time = time();
		$wp_time     = strtotime( current_time( 'mysql' ) );

		// Check if server time matches WordPress time (within 5 seconds).
		$diff = abs( $server_time - $wp_time );

		if ( $diff > 5 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Server time appears inaccurate or out of sync. This can break SSL certificates, JWT tokens, and scheduled tasks. Check system time and NTP synchronization.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/server_time_accuracy',
				'meta'        => array(
					'server_time'  => $server_time,
					'wp_time'      => $wp_time,
					'difference_s' => $diff,
				),
			);
		}

		return null;
	}
}
