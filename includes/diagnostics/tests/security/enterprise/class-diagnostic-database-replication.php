<?php
/**
 * Database Replication Diagnostic
 *
 * Checks if database replication (master-slave) is configured for redundancy.
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
 * Database Replication Diagnostic Class
 *
 * Verifies that database replication is configured for high availability,
 * disaster recovery, and read scaling in enterprise environments.
 *
 * @since 1.6035.1200
 */
class Diagnostic_Database_Replication extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-replication';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Replication';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if database replication (master-slave) is configured for redundancy';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'enterprise';

	/**
	 * Run the database replication diagnostic check.
	 *
	 * @since  1.6035.1200
	 * @return array|null Finding array if replication gaps detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$replication_configured = false;
		$replication_info       = array();
		$warnings               = array();

		// Check for HyperDB (advanced DB replication for WordPress).
		if ( class_exists( 'hyperdb' ) || defined( 'DB_CONFIG_FILE' ) ) {
			$replication_configured              = true;
			$replication_info['hyperdb']         = __( 'HyperDB detected', 'wpshadow' );
		}

		// Check for LudicrousDB (fork of HyperDB).
		if ( class_exists( 'LudicrousDB' ) ) {
			$replication_configured              = true;
			$replication_info['ludicrousdb']     = __( 'LudicrousDB detected', 'wpshadow' );
		}

		// Check for WordPress.com VIP's DB class.
		if ( defined( 'WPCOM_IS_VIP_ENV' ) && WPCOM_IS_VIP_ENV ) {
			$replication_configured              = true;
			$replication_info['vip']             = __( 'WordPress VIP DB replication', 'wpshadow' );
		}

		// Check for custom DB_HOST configurations indicating read replicas.
		if ( defined( 'DB_HOST_SLAVE' ) || 
			 defined( 'DB_REPLICA_HOST' ) ||
			 defined( 'DB_READ_REPLICA' ) ) {
			$replication_configured              = true;
			$replication_info['read_replica']    = __( 'Read replica configured', 'wpshadow' );
		}

		// Check for multi-database configuration.
		if ( defined( 'DB_HOSTS' ) || defined( 'DB_CLUSTER_CONFIG' ) ) {
			$replication_configured              = true;
			$replication_info['cluster']         = __( 'Database cluster configured', 'wpshadow' );
		}

		// Try to detect MySQL replication status (if we have permission).
		if ( ! $replication_configured ) {
			// Suppress errors as we may not have SUPER/REPLICATION CLIENT privileges.
			$replication_status = $wpdb->get_results( "SHOW SLAVE STATUS", ARRAY_A );
			
			if ( ! empty( $replication_status ) ) {
				$replication_configured              = true;
				$replication_info['mysql_native']    = __( 'MySQL native replication detected', 'wpshadow' );
				
				// Check replication health.
				$slave_status = $replication_status[0];
				if ( isset( $slave_status['Slave_IO_Running'] ) && 'Yes' !== $slave_status['Slave_IO_Running'] ) {
					$warnings[] = __( 'Slave IO thread not running', 'wpshadow' );
				}
				if ( isset( $slave_status['Slave_SQL_Running'] ) && 'Yes' !== $slave_status['Slave_SQL_Running'] ) {
					$warnings[] = __( 'Slave SQL thread not running', 'wpshadow' );
				}
				if ( isset( $slave_status['Seconds_Behind_Master'] ) && $slave_status['Seconds_Behind_Master'] > 60 ) {
					$warnings[] = sprintf(
						/* translators: %d: seconds behind master */
						__( 'Replication lag: %d seconds', 'wpshadow' ),
						$slave_status['Seconds_Behind_Master']
					);
				}
			}
		}

		// Check for managed database services with built-in replication.
		$db_host = defined( 'DB_HOST' ) ? DB_HOST : '';
		if ( strpos( $db_host, 'rds.amazonaws.com' ) !== false ) {
			$replication_info['aws_rds'] = __( 'AWS RDS (may have Multi-AZ enabled)', 'wpshadow' );
		} elseif ( strpos( $db_host, 'database.azure.com' ) !== false ) {
			$replication_info['azure_db'] = __( 'Azure Database (geo-replication available)', 'wpshadow' );
		} elseif ( strpos( $db_host, 'cloudsql' ) !== false || strpos( $db_host, 'googleapis.com' ) !== false ) {
			$replication_info['gcp_sql'] = __( 'Google Cloud SQL (high availability available)', 'wpshadow' );
		}

		// Determine if this is an enterprise environment.
		$is_enterprise = self::is_enterprise_environment();

		// If enterprise and no replication configured.
		if ( $is_enterprise && ! $replication_configured && empty( $replication_info ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your database could benefit from replication (think of it like keeping backup copies of your work while you\'re working). If your main database has problems, a replica can take over automatically—like having a spare tire ready.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-replication',
				'context'      => array(
					'replication_configured' => $replication_configured,
					'db_host'                => $db_host,
				),
			);
		}

		// If some indicators but not confirmed replication.
		if ( ! $replication_configured && ! empty( $replication_info ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Managed database service detected but replication status unclear. Verify Multi-AZ or geo-replication is enabled.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-replication',
				'context'      => array(
					'replication_info' => $replication_info,
				),
			);
		}

		// If replication is configured but has warnings.
		if ( $replication_configured && ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database replication is configured but has issues: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/database-replication',
				'context'      => array(
					'replication_info' => $replication_info,
					'warnings'         => $warnings,
				),
			);
		}

		return null; // Database replication is properly configured.
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
