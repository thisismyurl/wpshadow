<?php
/**
 * Database Connection Pool Exhaustion Risk Diagnostic
 *
 * Tests if site is at risk of exhausting database connection limits.
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
 * Database Connection Pool Exhaustion Risk Class
 *
 * Tests connection pool.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Database_Connection_Pool_Exhaustion_Risk extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-connection-pool-exhaustion-risk';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Connection Pool Exhaustion Risk';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site is at risk of exhausting database connection limits';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$connection_check = self::check_connection_pool();
		
		if ( $connection_check['is_at_risk'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: current connections, 2: max connections, 3: percentage used */
					__( 'Connection pool at risk: %1$d of %2$d connections used (%3$d%% capacity)', 'wpshadow' ),
					$connection_check['current_connections'],
					$connection_check['max_connections'],
					$connection_check['usage_percentage']
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-connection-pool-exhaustion-risk',
				'meta'         => array(
					'max_connections'     => $connection_check['max_connections'],
					'current_connections' => $connection_check['current_connections'],
					'usage_percentage'    => $connection_check['usage_percentage'],
				),
			);
		}

		return null;
	}

	/**
	 * Check connection pool status.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_connection_pool() {
		global $wpdb;

		$check = array(
			'is_at_risk'          => false,
			'max_connections'     => 0,
			'current_connections' => 0,
			'usage_percentage'    => 0,
		);

		// Get max_connections setting.
		$max_result = $wpdb->get_row( "SHOW VARIABLES LIKE 'max_connections'" );
		
		if ( $max_result && isset( $max_result->Value ) ) {
			$check['max_connections'] = (int) $max_result->Value;
		}

		// Get current connections.
		$threads_result = $wpdb->get_row( "SHOW STATUS LIKE 'Threads_connected'" );
		
		if ( $threads_result && isset( $threads_result->Value ) ) {
			$check['current_connections'] = (int) $threads_result->Value;
		}

		// Calculate usage percentage.
		if ( $check['max_connections'] > 0 ) {
			$check['usage_percentage'] = (int) round( ( $check['current_connections'] / $check['max_connections'] ) * 100 );
		}

		// Flag as at risk if usage >70% OR max_connections <100.
		if ( $check['usage_percentage'] > 70 || $check['max_connections'] < 100 ) {
			$check['is_at_risk'] = true;
		}

		return $check;
	}
}
