<?php
/**
 * Transient Cleanup Diagnostic
 *
 * Checks for expired transients that should be cleaned up.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2066
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
 * Detects excessive expired transients. Old transients bloat
 * the options table and slow queries.
 *
 * @since 1.26033.2066
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
	protected static $title = 'Transient Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for expired transients needing cleanup';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Counts expired transients in options table.
	 * Threshold: >500 expired transients
	 *
	 * @since  1.26033.2066
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;
		
		$current_time = time();
		
		// Count expired transients
		$expired_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				$current_time
			)
		);
		
		// Count total transients
		$total_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->options} 
				WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_' ) . '%'
			)
		);
		
		// Get size of transient data
		$transient_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(LENGTH(option_value)) 
				FROM {$wpdb->options} 
				WHERE option_name LIKE %s",
				$wpdb->esc_like( '_transient_' ) . '%'
			)
		);
		
		$expired_count    = (int) $expired_count;
		$total_transients = (int) $total_transients;
		$transient_size   = (int) $transient_size;
		
		// Check thresholds
		if ( $expired_count < 100 ) {
			return null; // Acceptable
		}
		
		$severity = 'low';
		$threat_level = 25;
		
		if ( $expired_count > 1000 ) {
			$severity = 'high';
			$threat_level = 70;
		} elseif ( $expired_count > 500 ) {
			$severity = 'medium';
			$threat_level = 50;
		}
		
		$description = sprintf(
			/* translators: 1: number of expired transients, 2: total transients, 3: total size */
			__( '%1$d expired transients found (of %2$d total, %3$s). Expired transients should be cleaned up to prevent options table bloat and improve query performance.', 'wpshadow' ),
			$expired_count,
			$total_transients,
			size_format( $transient_size )
		);
		
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/clean-expired-transients',
			'meta'         => array(
				'expired_count'    => $expired_count,
				'total_transients' => $total_transients,
				'transient_size'   => $transient_size,
				'transient_size_formatted' => size_format( $transient_size ),
				'expiration_percentage' => $total_transients > 0 ? round( ( $expired_count / $total_transients ) * 100 ) : 0,
				'cleanup_sql'      => "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_%' AND option_value < " . $current_time,
			),
		);
	}
}
