<?php
/**
 * Transient Cleanup Status
 *
 * Verifies that expired WordPress transients are being cleaned up properly.
 * Stale transients can cause memory leaks and database bloat.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Database
 * @since      1.6028.1050
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Transient Cleanup Diagnostic Class
 *
 * Checks for expired transients that haven't been cleaned up properly.
 *
 * @since 1.6028.1050
 */
class Diagnostic_Transient_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'transient-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Transient Cleanup Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies expired transients are cleaned up properly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1050
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_transient_cleanup_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$analysis = self::analyze_transients();

		if ( $analysis['expired_count'] < 50 ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of expired transients */
				__( 'Found %d expired transients not cleaned up, causing database bloat.', 'wpshadow' ),
				$analysis['expired_count']
			),
			'severity'     => $analysis['expired_count'] >= 200 ? 'medium' : 'low',
			'threat_level' => min( 60, 35 + ( $analysis['expired_count'] / 10 ) ),
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/transient-cleanup',
			'meta'         => array(
				'expired_count'    => $analysis['expired_count'],
				'total_transients' => $analysis['total_count'],
				'wasted_space_kb'  => $analysis['wasted_space_kb'],
			),
			'details'      => array(
				__( 'Expired transients waste database space', 'wpshadow' ),
				__( 'Can slow down queries and backups', 'wpshadow' ),
				sprintf(
					/* translators: %d: KB of wasted space */
					__( 'Estimated %d KB of wasted space', 'wpshadow' ),
					$analysis['wasted_space_kb']
				),
			),
			'recommendation' => __( 'Delete expired transients to free up database space.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 12 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Analyze transients.
	 *
	 * @since  1.6028.1050
	 * @return array Analysis results.
	 */
	private static function analyze_transients() {
		global $wpdb;

		// Count total transients.
		$total_count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_%'
			AND option_name NOT LIKE '_transient_timeout_%'"
		);

		// Count expired transients.
		$expired_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->options} o1
				INNER JOIN {$wpdb->options} o2 ON o2.option_name = CONCAT('_transient_timeout_', SUBSTRING(o1.option_name, 12))
				WHERE o1.option_name LIKE '_transient_%'
				AND o2.option_value < %d",
				time()
			)
		);

		// Estimate wasted space (rough calculation).
		$wasted_space = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(LENGTH(o1.option_value))
				FROM {$wpdb->options} o1
				INNER JOIN {$wpdb->options} o2 ON o2.option_name = CONCAT('_transient_timeout_', SUBSTRING(o1.option_name, 12))
				WHERE o1.option_name LIKE '_transient_%'
				AND o2.option_value < %d",
				time()
			)
		);

		return array(
			'total_count'      => (int) $total_count,
			'expired_count'    => (int) $expired_count,
			'wasted_space_kb'  => (int) round( ( $wasted_space ?? 0 ) / 1024 ),
		);
	}
}
