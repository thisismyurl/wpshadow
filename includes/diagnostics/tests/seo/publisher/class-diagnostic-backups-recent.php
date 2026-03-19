<?php
/**
 * Backups Recent Diagnostic
 *
 * Checks if content is backed up daily.
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
 * Backups Recent Diagnostic Class
 *
 * Verifies that regular backups are being created and that the most
 * recent backup is current (within the last 24 hours).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Backups_Recent extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backups-recent';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backups Recent';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if content is backed up daily';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the backups recent diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if backup issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for backup plugins.
		$backup_plugins = array(
			'backwpup/backwpup.php'                   => 'BackWPup',
			'updraftplus/updraftplus.php'             => 'UpdraftPlus',
			'jetpack-backup/jetpack-backup.php'       => 'Jetpack Backup',
			'wp-staging/wp-staging.php'               => 'WP Staging',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
		);

		$active_backup_plugin = null;
		foreach ( $backup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup_plugin = $name;
				break;
			}
		}

		$stats['backup_plugin'] = $active_backup_plugin;

		if ( ! $active_backup_plugin ) {
			$issues[] = __( 'No backup plugin installed - backups are not automated', 'wpshadow' );
		}

		// Check for manual backups in uploads directory.
		$upload_dir = wp_upload_dir();
		$backup_dir = $upload_dir['basedir'] . '/backups';

		$recent_backups = array();
		$last_backup_time = null;

		if ( is_dir( $backup_dir ) ) {
			$backup_files = glob( $backup_dir . '/*' );
			
			foreach ( $backup_files as $file ) {
				$mtime = filemtime( $file );
				$recent_backups[] = $mtime;
				
				if ( $last_backup_time === null || $mtime > $last_backup_time ) {
					$last_backup_time = $mtime;
				}
			}
		}

		$stats['backup_directory_exists'] = is_dir( $backup_dir );
		$stats['backup_count'] = count( $recent_backups );

		// Check backup age.
		if ( $last_backup_time ) {
			$backup_age_hours = ( time() - $last_backup_time ) / 3600;
			$stats['last_backup_age_hours'] = round( $backup_age_hours, 1 );

			if ( $backup_age_hours > 24 ) {
				$warnings[] = sprintf(
					/* translators: %d: hours */
					__( 'Last backup is %d hours old (more than 24 hours)', 'wpshadow' ),
					intval( $backup_age_hours )
				);
			} elseif ( $backup_age_hours > 7 * 24 ) {
				// More than a week.
				$issues[] = sprintf(
					/* translators: %d: days */
					__( 'Last backup is %d days old - too old for daily backup schedule', 'wpshadow' ),
					intval( $backup_age_hours / 24 )
				);
			}
		} else {
			$issues[] = __( 'No recent backups found', 'wpshadow' );
		}

		// Check for database backup files.
		$wp_content_dir = WP_CONTENT_DIR;
		$db_backup_files = glob( $wp_content_dir . '/db-*.sql' );

		if ( ! empty( $db_backup_files ) ) {
			$latest_db_backup = max( array_map( 'filemtime', $db_backup_files ) );
			$db_backup_age_hours = ( time() - $latest_db_backup ) / 3600;
			$stats['db_backup_age_hours'] = round( $db_backup_age_hours, 1 );

			if ( $db_backup_age_hours > 24 ) {
				$warnings[] = sprintf(
					/* translators: %d: hours */
					__( 'Database backup is %d hours old', 'wpshadow' ),
					intval( $db_backup_age_hours )
				);
			}
		}

		// Check backup schedule (if available through plugin options).
		$backup_schedule = get_option( 'backwpup_backup_schedule' );
		if ( $backup_schedule ) {
			$stats['backup_schedule'] = $backup_schedule;
		}

		// Check for cloud backup storage.
		$updraft_options = get_option( 'updraft_settings' );
		$backup_cloud_storage = false;

		if ( $updraft_options ) {
			$stats['has_cloud_backup'] = true;
			$backup_cloud_storage = true;
		}

		if ( ! $backup_cloud_storage ) {
			$warnings[] = __( 'No cloud backup storage detected - consider adding offsite backup', 'wpshadow' );
		}

		// Check database size for backup viability.
		global $wpdb;
		$database_size_query = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT SUM(ROUND(((data_length + index_length) / 1024 / 1024), 2)) as size FROM information_schema.TABLES WHERE table_schema = %s',
				DB_NAME
			)
		);

		$db_size_mb = isset( $database_size_query[0] ) ? floatval( $database_size_query[0]->size ) : 0;
		$stats['database_size_mb'] = round( $db_size_mb, 2 );

		if ( $db_size_mb > 500 ) {
			$warnings[] = sprintf(
				/* translators: %d: MB */
				__( 'Large database size (%dMB) - ensure backup storage is sufficient', 'wpshadow' ),
				intval( $db_size_mb )
			);
		}

		// Check WordPress backup completion status.
		$last_wp_backup = get_option( 'wp_last_backup_time' );
		if ( $last_wp_backup ) {
			$wp_backup_age = time() - intval( $last_wp_backup );
			$stats['last_wp_backup_age'] = round( $wp_backup_age / 3600, 1 );

			if ( $wp_backup_age > 86400 ) { // 24 hours.
				$warnings[] = __( 'WordPress backup not completed in last 24 hours', 'wpshadow' );
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backups have critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backups-recent',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backups have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backups-recent',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Backups are recent and good.
	}
}
