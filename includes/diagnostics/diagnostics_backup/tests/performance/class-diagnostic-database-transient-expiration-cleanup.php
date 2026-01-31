<?php
/**
 * Database Transient Expiration Cleanup Diagnostic
 *
 * Counts expired transients not cleaned up, wasting database space.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Transient Expiration Cleanup Class
 *
 * Tests transient cleanup.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Database_Transient_Expiration_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-transient-expiration-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Transient Expiration Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Counts expired transients not cleaned up, wasting database space';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$transient_check = self::check_expired_transients();
		
		if ( $transient_check['expired_count'] > 100 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of expired transients, 2: wasted space in MB */
					__( '%1$d expired transients found (%2$sMB of wasted space)', 'wpshadow' ),
					$transient_check['expired_count'],
					number_format( $transient_check['wasted_space'] / 1048576, 2 )
				),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-transient-expiration-cleanup',
				'meta'         => array(
					'expired_count' => $transient_check['expired_count'],
					'wasted_space'  => $transient_check['wasted_space'],
					'sample_transients' => $transient_check['sample_transients'],
				),
			);
		}

		return null;
	}

	/**
	 * Check for expired transients.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_expired_transients() {
		global $wpdb;

		$check = array(
			'expired_count'     => 0,
			'wasted_space'      => 0,
			'sample_transients' => array(),
		);

		$current_time = time();

		// Count expired transients.
		$check['expired_count'] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_value < %d",
				'_transient_timeout_%',
				$current_time
			)
		);

		// Calculate wasted space from expired transients.
		$expired_transient_timeouts = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name
				FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_value < %d
				LIMIT 1000",
				'_transient_timeout_%',
				$current_time
			)
		);

		if ( ! empty( $expired_transient_timeouts ) ) {
			foreach ( $expired_transient_timeouts as $timeout_name ) {
				// Get corresponding transient name.
				$transient_name = str_replace( '_transient_timeout_', '_transient_', $timeout_name );

				// Get transient size.
				$transient_size = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT LENGTH(option_value)
						FROM {$wpdb->options}
						WHERE option_name = %s",
						$transient_name
					)
				);

				if ( $transient_size ) {
					$check['wasted_space'] += (int) $transient_size;
				}

				// Sample first 10 transients.
				if ( count( $check['sample_transients'] ) < 10 ) {
					$check['sample_transients'][] = array(
						'name' => $transient_name,
						'size' => (int) $transient_size,
					);
				}
			}
		}

		return $check;
	}
}
