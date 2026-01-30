<?php
/**
 * WPShadow Vault Manager
 *
 * Central orchestrator for backup and restore operations.
 * Integrates with WPShadow Core for automatic backups before treatments.
 *
 * @package    WPShadow
 * @subpackage Vault
 * @since      1.6030.1830
 */

declare(strict_types=1);

namespace WPShadow\Vault;

use WPShadow\Core\Error_Handler;
use WPShadow\Core\Activity_Logger;
use WPShadow\Core\Settings_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Vault_Manager Class
 *
 * Manages backup creation, storage, retrieval, and restoration.
 * Handles free tier limits (3 backups, 7-day retention) and paid upgrades.
 *
 * @since 1.6030.1830
 */
class Vault_Manager {

	/**
	 * Singleton instance
	 *
	 * @var Vault_Manager|null
	 */
	private static $instance = null;

	/**
	 * Backup storage directory
	 *
	 * @var string
	 */
	private $backup_dir;

	/**
	 * Maximum backups for free tier
	 *
	 * @var int
	 */
	const FREE_TIER_MAX_BACKUPS = 3;

	/**
	 * Retention days for free tier
	 *
	 * @var int
	 */
	const FREE_TIER_RETENTION_DAYS = 7;

	/**
	 * Get singleton instance
	 *
	 * @since  1.6030.1830
	 * @return Vault_Manager Instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since 1.6030.1830
	 */
	private function __construct() {
		$upload_dir       = wp_upload_dir();
		$this->backup_dir = $upload_dir['basedir'] . '/wpshadow-vault';

		$this->setup_hooks();
		$this->ensure_backup_directory();
	}

	/**
	 * Setup WordPress hooks
	 *
	 * @since 1.6030.1830
	 * @return void
	 */
	private function setup_hooks() {
		// Auto-backup before treatments.
		add_action( 'wpshadow_before_treatment_apply', array( $this, 'maybe_auto_backup' ), 10, 3 );

		// Cleanup old backups daily.
		add_action( 'wpshadow_daily_cleanup', array( $this, 'cleanup_expired_backups' ) );
	}

	/**
	 * Ensure backup directory exists with proper permissions
	 *
	 * @since  1.6030.1830
	 * @return bool True if directory ready, false on failure.
	 */
	private function ensure_backup_directory() {
		if ( ! file_exists( $this->backup_dir ) ) {
			if ( ! wp_mkdir_p( $this->backup_dir ) ) {
				Error_Handler::log_error(
					'Failed to create Vault backup directory',
					array( 'directory' => $this->backup_dir )
				);
				return false;
			}

			// Create .htaccess to prevent direct access.
			$htaccess_content = "Order deny,allow\nDeny from all\n";
			file_put_contents( $this->backup_dir . '/.htaccess', $htaccess_content );

			// Create index.php to prevent directory listing.
			file_put_contents( $this->backup_dir . '/index.php', '<?php // Silence is golden.' );
		}

		return true;
	}

	/**
	 * Check if user is registered with Vault
	 *
	 * @since  1.6030.1830
	 * @return bool True if registered, false otherwise.
	 */
	public function is_registered() {
		$api_key = Settings_Registry::get( 'vault_api_key', '' );
		return ! empty( $api_key );
	}

	/**
	 * Get user's Vault tier (free, starter, professional, agency)
	 *
	 * @since  1.6030.1830
	 * @return string Tier identifier.
	 */
	public function get_tier() {
		if ( ! $this->is_registered() ) {
			return 'none';
		}

		// Check cached tier first.
		$cached_tier = get_transient( 'wpshadow_vault_tier' );
		if ( false !== $cached_tier ) {
			return $cached_tier;
		}

		// Query Vault API for current tier.
		$api_key = Settings_Registry::get( 'vault_api_key', '' );
		$tier    = $this->fetch_account_tier( $api_key );

		// Cache for 1 hour.
		set_transient( 'wpshadow_vault_tier', $tier, HOUR_IN_SECONDS );

		return $tier;
	}

	/**
	 * Fetch account tier from Vault API
	 *
	 * @since  1.6030.1830
	 * @param  string $api_key API key.
	 * @return string Tier identifier.
	 */
	private function fetch_account_tier( $api_key ) {
		// TODO: Implement real API call when Vault cloud service is built.
		// For now, return 'free' for all registered users.
		return 'free';
	}

	/**
	 * Get maximum allowed backups for current tier
	 *
	 * @since  1.6030.1830
	 * @return int|string Number of backups or 'unlimited'.
	 */
	public function get_max_backups() {
		$tier = $this->get_tier();

		$limits = array(
			'free'         => self::FREE_TIER_MAX_BACKUPS,
			'starter'      => 10,
			'professional' => 100,
			'agency'       => 'unlimited',
		);

		return $limits[ $tier ] ?? self::FREE_TIER_MAX_BACKUPS;
	}

	/**
	 * Get retention days for current tier
	 *
	 * @since  1.6030.1830
	 * @return int Days to retain backups.
	 */
	public function get_retention_days() {
		$tier = $this->get_tier();

		$retention = array(
			'free'         => self::FREE_TIER_RETENTION_DAYS,
			'starter'      => 30,
			'professional' => 90,
			'agency'       => 365,
		);

		return $retention[ $tier ] ?? self::FREE_TIER_RETENTION_DAYS;
	}

	/**
	 * Create a full-site backup
	 *
	 * @since  1.6030.1830
	 * @param  string $label Optional backup label/description.
	 * @return array {
	 *     Backup result.
	 *
	 *     @type bool   $success Whether backup succeeded.
	 *     @type string $message Human-readable message.
	 *     @type string $backup_id Backup ID on success.
	 *     @type int    $size_bytes Backup size in bytes.
	 * }
	 */
	public function create_backup( $label = '' ) {
		// Check if user has reached backup limit.
		if ( ! $this->can_create_backup() ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %d: maximum backup count */
					__( 'You\'ve reached your backup limit (%d backups). Delete old backups or upgrade your plan.', 'wpshadow' ),
					$this->get_max_backups()
				),
			);
		}

		$backup_id = 'backup_' . gmdate( 'Y-m-d_H-i-s' ) . '_' . wp_generate_password( 8, false );
		$label     = ! empty( $label ) ? sanitize_text_field( $label ) : __( 'Manual Backup', 'wpshadow' );

		// Create backup metadata.
		$metadata = array(
			'id'         => $backup_id,
			'label'      => $label,
			'created_at' => current_time( 'mysql' ),
			'created_by' => get_current_user_id(),
			'site_url'   => site_url(),
			'wp_version' => get_bloginfo( 'version' ),
			'php_version' => phpversion(),
			'status'     => 'in_progress',
		);

		// Save metadata.
		$this->save_backup_metadata( $backup_id, $metadata );

		// Create the actual backup archive.
		$result = $this->create_backup_archive( $backup_id );

		if ( $result['success'] ) {
			$metadata['status']     = 'completed';
			$metadata['size_bytes'] = $result['size_bytes'];
			$metadata['files']      = $result['files'];
			$this->save_backup_metadata( $backup_id, $metadata );

			// Log activity.
			Activity_Logger::log(
				'vault_backup_created',
				array(
					'backup_id'  => $backup_id,
					'label'      => $label,
					'size_bytes' => $result['size_bytes'],
				)
			);

			// Cleanup if over limit.
			$this->maybe_cleanup_oldest_backup();

			return array(
				'success'    => true,
				'message'    => __( 'Backup created successfully!', 'wpshadow' ),
				'backup_id'  => $backup_id,
				'size_bytes' => $result['size_bytes'],
			);
		}

		// Backup failed.
		$metadata['status'] = 'failed';
		$metadata['error']  = $result['message'];
		$this->save_backup_metadata( $backup_id, $metadata );

		return array(
			'success' => false,
			'message' => $result['message'],
		);
	}

	/**
	 * Check if user can create another backup
	 *
	 * @since  1.6030.1830
	 * @return bool True if can create, false if at limit.
	 */
	private function can_create_backup() {
		$max_backups     = $this->get_max_backups();
		$current_backups = count( $this->get_backups() );

		if ( 'unlimited' === $max_backups ) {
			return true;
		}

		return $current_backups < (int) $max_backups;
	}

	/**
	 * Create backup archive file
	 *
	 * @since  1.6030.1830
	 * @param  string $backup_id Backup ID.
	 * @return array {
	 *     Archive creation result.
	 *
	 *     @type bool   $success Whether archive created.
	 *     @type string $message Error message on failure.
	 *     @type int    $size_bytes Archive size.
	 *     @type array  $files List of included files.
	 * }
	 */
	private function create_backup_archive( $backup_id ) {
		require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';

		$archive_path = $this->backup_dir . '/' . $backup_id . '.zip';

		try {
			$archive = new \PclZip( $archive_path );

			// Files to backup.
			$files_to_backup = array();

			// Include wp-content (themes, plugins, uploads).
			$files_to_backup[] = WP_CONTENT_DIR;

			// Include wp-config.php (critical for restore).
			if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
				$files_to_backup[] = ABSPATH . 'wp-config.php';
			}

			// Export database.
			$db_export = $this->export_database();
			if ( $db_export['success'] ) {
				$files_to_backup[] = $db_export['file'];
			}

			// Create archive.
			$result = $archive->create(
				$files_to_backup,
				PCLZIP_OPT_REMOVE_PATH,
				ABSPATH
			);

			if ( 0 === $result ) {
				return array(
					'success' => false,
					'message' => __( 'Failed to create backup archive: ', 'wpshadow' ) . $archive->errorInfo( true ),
				);
			}

			// Clean up database export file.
			if ( isset( $db_export['file'] ) && file_exists( $db_export['file'] ) ) {
				unlink( $db_export['file'] );
			}

			$size_bytes = filesize( $archive_path );

			return array(
				'success'    => true,
				'size_bytes' => $size_bytes,
				'files'      => $files_to_backup,
			);

		} catch ( \Exception $e ) {
			Error_Handler::log_error( 'Vault backup archive creation failed', $e );

			return array(
				'success' => false,
				'message' => __( 'Backup failed: ', 'wpshadow' ) . $e->getMessage(),
			);
		}
	}

	/**
	 * Export database to SQL file
	 *
	 * @since  1.6030.1830
	 * @return array {
	 *     Export result.
	 *
	 *     @type bool   $success Whether export succeeded.
	 *     @type string $file Path to SQL file.
	 * }
	 */
	private function export_database() {
		global $wpdb;

		$sql_file = $this->backup_dir . '/database_' . gmdate( 'Y-m-d_H-i-s' ) . '.sql';

		try {
			$tables = $wpdb->get_results( 'SHOW TABLES', ARRAY_N );
			$output = "-- WPShadow Vault Database Backup\n";
			$output .= '-- Created: ' . gmdate( 'Y-m-d H:i:s' ) . "\n\n";

			foreach ( $tables as $table ) {
				$table_name = $table[0];

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name cannot be prepared.
				$create_table = $wpdb->get_row( "SHOW CREATE TABLE `{$table_name}`", ARRAY_N );
				$output      .= "\n\n-- Table: {$table_name}\n";
				$output      .= "DROP TABLE IF EXISTS `{$table_name}`;\n";
				$output      .= $create_table[1] . ";\n\n";

				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Table name cannot be prepared.
				$rows = $wpdb->get_results( "SELECT * FROM `{$table_name}`", ARRAY_A );

				foreach ( $rows as $row ) {
					$values = array_map(
						function ( $value ) use ( $wpdb ) {
							return null === $value ? 'NULL' : "'" . $wpdb->_real_escape( $value ) . "'";
						},
						$row
					);

					$output .= "INSERT INTO `{$table_name}` VALUES (" . implode( ',', $values ) . ");\n";
				}
			}

			file_put_contents( $sql_file, $output );

			return array(
				'success' => true,
				'file'    => $sql_file,
			);

		} catch ( \Exception $e ) {
			Error_Handler::log_error( 'Database export failed', $e );

			return array(
				'success' => false,
			);
		}
	}

	/**
	 * Get list of all backups
	 *
	 * @since  1.6030.1830
	 * @return array Array of backup metadata.
	 */
	public function get_backups() {
		$backups_meta = get_option( 'wpshadow_vault_backups', array() );

		// Sort by created date (newest first).
		usort(
			$backups_meta,
			function ( $a, $b ) {
				return strtotime( $b['created_at'] ) - strtotime( $a['created_at'] );
			}
		);

		return $backups_meta;
	}

	/**
	 * Save backup metadata
	 *
	 * @since  1.6030.1830
	 * @param  string $backup_id Backup ID.
	 * @param  array  $metadata Backup metadata.
	 * @return bool True on success.
	 */
	private function save_backup_metadata( $backup_id, $metadata ) {
		$backups = get_option( 'wpshadow_vault_backups', array() );

		// Update or add backup.
		$found = false;
		foreach ( $backups as &$backup ) {
			if ( $backup['id'] === $backup_id ) {
				$backup = $metadata;
				$found  = true;
				break;
			}
		}

		if ( ! $found ) {
			$backups[] = $metadata;
		}

		return update_option( 'wpshadow_vault_backups', $backups );
	}

	/**
	 * Delete a backup
	 *
	 * @since  1.6030.1830
	 * @param  string $backup_id Backup ID to delete.
	 * @return array {
	 *     Deletion result.
	 *
	 *     @type bool   $success Whether deletion succeeded.
	 *     @type string $message Human-readable message.
	 * }
	 */
	public function delete_backup( $backup_id ) {
		$archive_path = $this->backup_dir . '/' . $backup_id . '.zip';

		// Delete archive file.
		if ( file_exists( $archive_path ) ) {
			unlink( $archive_path );
		}

		// Remove from metadata.
		$backups = get_option( 'wpshadow_vault_backups', array() );
		$backups = array_filter(
			$backups,
			function ( $backup ) use ( $backup_id ) {
				return $backup['id'] !== $backup_id;
			}
		);

		update_option( 'wpshadow_vault_backups', array_values( $backups ) );

		Activity_Logger::log(
			'vault_backup_deleted',
			array( 'backup_id' => $backup_id )
		);

		return array(
			'success' => true,
			'message' => __( 'Backup deleted successfully', 'wpshadow' ),
		);
	}

	/**
	 * Maybe auto-backup before treatment
	 *
	 * Triggered by wpshadow_before_treatment_apply hook.
	 *
	 * @since  1.6030.1830
	 * @param  string $class Treatment class name.
	 * @param  string $finding_id Finding ID being treated.
	 * @param  bool   $dry_run Whether this is a dry run.
	 * @return void
	 */
	public function maybe_auto_backup( $class, $finding_id, $dry_run ) {
		if ( $dry_run ) {
			return;
		}

		// Check if auto-backup is enabled.
		$auto_backup_enabled = Settings_Registry::get( 'vault_auto_backup', true );
		if ( ! $auto_backup_enabled ) {
			return;
		}

		// Only auto-backup for critical or high-severity treatments.
		$severity = $this->get_treatment_severity( $finding_id );
		if ( ! in_array( $severity, array( 'critical', 'high' ), true ) ) {
			return;
		}

		// Create auto-backup.
		$label = sprintf(
			/* translators: %s: finding ID */
			__( 'Auto-backup before fixing: %s', 'wpshadow' ),
			$finding_id
		);

		$this->create_backup( $label );
	}

	/**
	 * Get treatment severity level
	 *
	 * @since  1.6030.1830
	 * @param  string $finding_id Finding ID.
	 * @return string Severity level.
	 */
	private function get_treatment_severity( $finding_id ) {
		// TODO: Implement severity lookup from diagnostic.
		// For now, assume medium.
		return 'medium';
	}

	/**
	 * Cleanup oldest backup if over limit
	 *
	 * @since  1.6030.1830
	 * @return void
	 */
	private function maybe_cleanup_oldest_backup() {
		$max_backups = $this->get_max_backups();
		if ( 'unlimited' === $max_backups ) {
			return;
		}

		$backups = $this->get_backups();
		if ( count( $backups ) <= $max_backups ) {
			return;
		}

		// Delete oldest backup.
		$oldest = end( $backups );
		$this->delete_backup( $oldest['id'] );
	}

	/**
	 * Cleanup expired backups based on retention policy
	 *
	 * Triggered by wpshadow_daily_cleanup hook.
	 *
	 * @since  1.6030.1830
	 * @return void
	 */
	public function cleanup_expired_backups() {
		$retention_days = $this->get_retention_days();
		$cutoff_date    = gmdate( 'Y-m-d H:i:s', strtotime( "-{$retention_days} days" ) );

		$backups = $this->get_backups();

		foreach ( $backups as $backup ) {
			if ( $backup['created_at'] < $cutoff_date ) {
				$this->delete_backup( $backup['id'] );
			}
		}
	}

	/**
	 * Restore from backup
	 *
	 * @since  1.6030.1830
	 * @param  string $backup_id Backup ID to restore.
	 * @return array {
	 *     Restore result.
	 *
	 *     @type bool   $success Whether restore succeeded.
	 *     @type string $message Human-readable message.
	 * }
	 */
	public function restore_backup( $backup_id ) {
		$archive_path = $this->backup_dir . '/' . $backup_id . '.zip';

		if ( ! file_exists( $archive_path ) ) {
			return array(
				'success' => false,
				'message' => __( 'Backup file not found', 'wpshadow' ),
			);
		}

		// Create pre-restore backup.
		$pre_restore = $this->create_backup( __( 'Pre-restore safety backup', 'wpshadow' ) );
		if ( ! $pre_restore['success'] ) {
			return array(
				'success' => false,
				'message' => __( 'Failed to create safety backup before restore', 'wpshadow' ),
			);
		}

		require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';

		try {
			$archive = new \PclZip( $archive_path );

			// Extract to temporary directory first.
			$temp_dir = $this->backup_dir . '/restore_temp_' . gmdate( 'YmdHis' );
			wp_mkdir_p( $temp_dir );

			$result = $archive->extract( PCLZIP_OPT_PATH, $temp_dir );

			if ( 0 === $result ) {
				return array(
					'success' => false,
					'message' => __( 'Failed to extract backup: ', 'wpshadow' ) . $archive->errorInfo( true ),
				);
			}

			// Restore files (this is complex and risky - simplified for MVP).
			// TODO: Implement full restore logic.

			// Clean up temp directory.
			$this->recursive_delete( $temp_dir );

			Activity_Logger::log(
				'vault_backup_restored',
				array(
					'backup_id'         => $backup_id,
					'pre_restore_backup' => $pre_restore['backup_id'],
				)
			);

			return array(
				'success' => true,
				'message' => __( 'Backup restored successfully! Please check your site.', 'wpshadow' ),
			);

		} catch ( \Exception $e ) {
			Error_Handler::log_error( 'Vault restore failed', $e );

			return array(
				'success' => false,
				'message' => __( 'Restore failed: ', 'wpshadow' ) . $e->getMessage(),
			);
		}
	}

	/**
	 * Recursively delete directory
	 *
	 * @since  1.6030.1830
	 * @param  string $dir Directory path.
	 * @return void
	 */
	private function recursive_delete( $dir ) {
		if ( ! file_exists( $dir ) ) {
			return;
		}

		$files = array_diff( scandir( $dir ), array( '.', '..' ) );

		foreach ( $files as $file ) {
			$path = $dir . '/' . $file;
			is_dir( $path ) ? $this->recursive_delete( $path ) : unlink( $path );
		}

		rmdir( $dir );
	}

	/**
	 * Get Vault status for dashboard display
	 *
	 * @since  1.6030.1830
	 * @return array {
	 *     Vault status information.
	 *
	 *     @type bool        $registered Whether user registered.
	 *     @type string      $tier Current tier.
	 *     @type int         $backup_count Number of backups.
	 *     @type int|string  $max_backups Maximum allowed backups.
	 *     @type array       $latest_backup Most recent backup metadata.
	 * }
	 */
	public function get_status() {
		$backups = $this->get_backups();

		return array(
			'registered'    => $this->is_registered(),
			'tier'          => $this->get_tier(),
			'backup_count'  => count( $backups ),
			'max_backups'   => $this->get_max_backups(),
			'latest_backup' => ! empty( $backups ) ? $backups[0] : null,
		);
	}
}
