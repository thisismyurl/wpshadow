<?php
/**
 * Database Connection Pool Efficiency Diagnostic
 *
 * Checks database connection pooling configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1401
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Connection Pool Efficiency Diagnostic Class
 *
 * Checks database connection settings for optimal pooling.
 *
 * @since 1.5049.1401
 */
class Diagnostic_Database_Connection_Pool_Efficiency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-connection-pool-efficiency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Connection Pool Efficiency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks database connection pooling efficiency';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$max_connections = (int) $wpdb->get_var( 'SELECT @@max_connections' );
		$max_user_connections = (int) $wpdb->get_var( 'SELECT @@max_user_connections' );

		$issues = array();

		if ( $max_connections < 100 ) {
			$issues[] = sprintf(
				/* translators: %d: max connections setting */
				__( 'max_connections is only %d. Consider increasing for high-traffic sites.', 'wpshadow' ),
				$max_connections
			);
		}

		if ( $max_user_connections < 50 ) {
			$issues[] = sprintf(
				/* translators: %d: max user connections setting */
				__( 'max_user_connections is only %d. Consider increasing for better connection pooling.', 'wpshadow' ),
				$max_user_connections
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database connection pooling may be limited.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'details'      => array(
					'issues'                 => $issues,
					'max_connections'        => $max_connections,
					'max_user_connections'   => $max_user_connections,
				),
				'kb_link'      => 'https://wpshadow.com/kb/database-connection-pool-efficiency',
			);
		}

		return null;
	}
}
