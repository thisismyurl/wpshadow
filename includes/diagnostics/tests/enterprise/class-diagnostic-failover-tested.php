<?php
/**
 * Failover Tested Diagnostic
 *
 * Checks if failover capability has been tested and validated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Failover Tested Diagnostic Class
 *
 * Verifies that failover mechanisms have been tested to ensure business
 * continuity in case of infrastructure failures.
 *
 * @since 1.6035.1200
 */
class Diagnostic_Failover_Tested extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'failover-tested';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Failover Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if failover capability has been tested and validated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the failover testing diagnostic check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if failover testing gaps detected, null otherwise.
	 */
	public static function check() {
		// Check for failover testing documentation in options.
		$last_failover_test = get_option( 'wpshadow_last_failover_test' );
		$failover_test_log  = get_option( 'wpshadow_failover_test_log', array() );

		$has_failover_test_record = ! empty( $last_failover_test );
		$days_since_test          = null;

		if ( $has_failover_test_record ) {
			$last_test_timestamp = strtotime( $last_failover_test );
			$days_since_test     = floor( ( time() - $last_test_timestamp ) / DAY_IN_SECONDS );
		}

		// Determine if this is an enterprise environment.
		$is_enterprise = self::is_enterprise_environment();

		// Check if they have HA/failover infrastructure.
		$has_ha_infrastructure = self::has_high_availability();

		// If enterprise with HA but no failover testing.
		if ( $is_enterprise && $has_ha_infrastructure && ! $has_failover_test_record ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'High availability infrastructure detected but no record of failover testing. Regular failover tests ensure business continuity during incidents.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/failover-testing',
				'context'      => array(
					'has_ha_infrastructure' => $has_ha_infrastructure,
					'last_failover_test'    => $last_failover_test,
				),
			);
		}

		// If last test was over 6 months ago.
		if ( $has_failover_test_record && $days_since_test > 180 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: days since last test */
					__( 'Last failover test was %d days ago. Enterprise best practice recommends quarterly failover testing.', 'wpshadow' ),
					$days_since_test
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/failover-testing',
				'context'      => array(
					'last_failover_test' => $last_failover_test,
					'days_since_test'    => $days_since_test,
					'test_log'           => $failover_test_log,
				),
			);
		}

		// If last test was over 3 months ago.
		if ( $has_failover_test_record && $days_since_test > 90 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: days since last test */
					__( 'Last failover test was %d days ago. Consider scheduling a quarterly failover test.', 'wpshadow' ),
					$days_since_test
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/failover-testing',
				'context'      => array(
					'last_failover_test' => $last_failover_test,
					'days_since_test'    => $days_since_test,
					'test_log'           => $failover_test_log,
				),
			);
		}

		return null; // Failover testing is up to date or not required.
	}

	/**
	 * Check if high availability infrastructure is present.
	 *
	 * @since  1.6035.1200
	 * @return bool True if HA indicators detected, false otherwise.
	 */
	private static function has_high_availability() {
		// Check for load balancer indicators.
		$lb_headers = array(
			'HTTP_X_FORWARDED_FOR',
			'HTTP_X_REAL_IP',
			'HTTP_X_FORWARDED_PROTO',
		);

		foreach ( $lb_headers as $header ) {
			if ( isset( $_SERVER[ $header ] ) && ! empty( $_SERVER[ $header ] ) ) {
				return true;
			}
		}

		// Check for replication indicators.
		if ( defined( 'DB_HOST_SLAVE' ) || defined( 'DB_REPLICA_HOST' ) ) {
			return true;
		}

		// Check for cluster indicators.
		if ( defined( 'WP_CLUSTER_CONFIG' ) || defined( 'WPE_CLUSTER_ID' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Determine if this is an enterprise environment.
	 *
	 * @since  1.6035.1200
	 * @return bool True if enterprise indicators detected, false otherwise.
	 */
	private static function is_enterprise_environment() {
		$enterprise_indicators = array(
			defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV,
			defined( 'WPE_CLUSTER_ID' ),
			defined( 'PANTHEON_ENVIRONMENT' ),
			is_multisite() && get_blog_count() > 50,
		);

		return in_array( true, $enterprise_indicators, true );
	}
}
