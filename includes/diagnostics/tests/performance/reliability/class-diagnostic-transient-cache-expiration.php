<?php
/**
 * Transient Cache Expiration Diagnostic
 *
 * Issue #4937: Transient Cache Never Expires (Memory Leak)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if transients have expiration times.
 * Permanent transients fill database and slow queries.
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
 * Diagnostic_Transient_Cache_Expiration Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Transient_Cache_Expiration extends Diagnostic_Base {

	protected static $slug = 'transient-cache-expiration';
	protected static $title = 'Transient Cache Never Expires (Memory Leak)';
	protected static $description = 'Checks if transients have appropriate expiration times';
	protected static $family = 'reliability';

	public static function check() {
		$issues = array();

		$issues[] = __( 'ALWAYS set expiration time on set_transient()', 'wpshadow' );
		$issues[] = __( 'Short-lived data: 5-15 minutes (API responses)', 'wpshadow' );
		$issues[] = __( 'Medium-lived data: 1-24 hours (RSS feeds)', 'wpshadow' );
		$issues[] = __( 'Long-lived data: 7-30 days (expensive queries)', 'wpshadow' );
		$issues[] = __( 'Clean up orphaned transients regularly', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Transients without expiration fill the database forever. Always set expiration times appropriate to the data freshness needs.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/transient-expiration?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'         => $issues,
					'correct_usage'           => 'set_transient( "key", $data, HOUR_IN_SECONDS );',
					'wrong_usage'             => 'set_transient( "key", $data, 0 ); // NEVER EXPIRES!',
					'cleanup_query'           => 'DELETE FROM wp_options WHERE option_name LIKE "_transient_%"',
				),
			);
		}

		return null;
	}
}
