<?php
/**
 * Backup Restoration Testing Diagnostic
 *
 * Tests if backups can actually be restored (not just created).
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
 * Backup Restoration Testing Class
 *
 * Tests whether backups are created and can be restored.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Backup_Restoration_Testing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-restoration-testing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Restoration Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if backups can actually be restored (not just created)';

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
		$issues = array();

		// Detect backup solution.
		$backup_info = self::detect_backup_solution();
		
		if ( ! $backup_info['backup_solution'] ) {
			$issues[] = __( 'No backup solution detected (WordPress site is not being backed up)', 'wpshadow' );
		} else {
			// Check if backups are being created.
			if ( ! $backup_info['recent_backup'] ) {
				$issues[] = sprintf(
					/* translators: %s: backup solution name */
					__( '%s is configured but no recent backups found', 'wpshadow' ),
					$backup_info['backup_solution']
				);
			}

			// Check for offsite storage.
			if ( ! $backup_info['offsite_storage'] ) {
				$issues[] = __( 'Backups stored only on same server (not offsite) - vulnerable to server failure', 'wpshadow' );
			}

			// Check backup age.
			if ( $backup_info['latest_backup_age'] > 7 * DAY_IN_SECONDS ) {
				$issues[] = sprintf(
					/* translators: %s: backup age */
					__( 'Latest backup is %s old (backups may not be running)', 'wpshadow' ),
					human_time_diff( time() - $backup_info['latest_backup_age'] )
				);
			}

			// Check for test restoration records.
			if ( ! $backup_info['restoration_tested'] ) {
				$issues[] = __( 'No evidence of backup restoration testing (untested backups may not work)', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-restoration-testing',
				'meta'         => array(
					'backup_solution'      => $backup_info['backup_solution'],
					'recent_backup'        => $backup_info['recent_backup'],
					'offsite_storage'      => $backup_info['offsite_storage'],
					'latest_backup_age'    => $backup_info['latest_backup_age'],
					'restoration_tested'   => $backup_info['restoration_tested'],
					'issues_found'         => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Detect backup solution and status.
	 *
	 * @since  1.26028.1905
	 * @return array Backup information.
	 */
	private static function detect_backup_solution() {
		$info = array(
			'backup_solution'    => false,
			'recent_backup'      => false,
			'offsite_storage'    => false,
			'latest_backup_age'  => 0,
			'restoration_tested' => false,
		);

		// Check for common backup plugins.
		$backup_plugins = array(
			'updraftplus/updraftplus.php'            => array(
				'name'            => 'UpdraftPlus',
				'option'          => 'updraft_last_backup',
				'offsite_option'  => 'updraft_service',
			),
			'backwpup/backwpup.php'                  => array(
				'name'            => 'BackWPup',
				'option'          => 'backwpup_cfg_jobrunsuccess',
				'offsite_option'  => 'backwpup_cfg_jobtype',
			),
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => array(
				'name'            => 'All-in-One WP Migration',
				'option'          => 'ai1wm_backups',
				'offsite_option'  => false,
			),
			'wp-database-backup/wp-database-backup.php' => array(
				'name'            => 'WP Database Backup',
				'option'          => 'wp_db_backup_last_backup',
				'offsite_option'  => false,
			),
			'jetpack/jetpack.php'                    => array(
				'name'            => 'Jetpack Backup',
				'option'          => 'jetpack_options',
				'offsite_option'  => true, // Jetpack backups are always offsite.
			),
			'blogvault-real-time-backup/blogvault.php' => array(
				'name'            => 'BlogVault',
				'option'          => false,
				'offsite_option'  => true,
			),
		);

		foreach ( $backup_plugins as $plugin => $config ) {
			if ( is_plugin_active( $plugin ) ) {
				$info['backup_solution'] = $config['name'];

				// Check for recent backups.
				if ( $config['option'] ) {
					$last_backup = get_option( $config['option'] );
					if ( $last_backup ) {
						$info['recent_backup'] = true;
						
						// Try to determine backup age.
						if ( is_numeric( $last_backup ) ) {
							$info['latest_backup_age'] = time() - (int) $last_backup;
						}
					}
				}

				// Check for offsite storage.
				if ( true === $config['offsite_option'] ) {
					$info['offsite_storage'] = true;
				} elseif ( $config['offsite_option'] ) {
					$offsite_config = get_option( $config['offsite_option'] );
					if ( $offsite_config && ! empty( $offsite_config ) ) {
						$info['offsite_storage'] = true;
					}
				}

				break;
			}
		}

		// Check for manual backups or cron jobs.
		if ( ! $info['backup_solution'] ) {
			$upload_dir = wp_upload_dir();
			$backup_dirs = array(
				$upload_dir['basedir'] . '/backups/',
				WP_CONTENT_DIR . '/backups/',
				WP_CONTENT_DIR . '/ai1wm-backups/',
			);

			foreach ( $backup_dirs as $dir ) {
				if ( is_dir( $dir ) ) {
					$files = glob( $dir . '*.{zip,tar,gz,sql}', GLOB_BRACE );
					if ( ! empty( $files ) ) {
						$info['backup_solution'] = 'Manual/Cron Backups';
						$info['recent_backup'] = true;
						
						// Get newest file age.
						$newest_time = 0;
						foreach ( $files as $file ) {
							$mtime = filemtime( $file );
							if ( $mtime > $newest_time ) {
								$newest_time = $mtime;
							}
						}
						$info['latest_backup_age'] = time() - $newest_time;
						break;
					}
				}
			}
		}

		// Check for restoration test records (custom meta or options).
		$restoration_test = get_option( 'wpshadow_backup_restoration_tested' );
		if ( $restoration_test ) {
			$info['restoration_tested'] = true;
		}

		return $info;
	}
}
