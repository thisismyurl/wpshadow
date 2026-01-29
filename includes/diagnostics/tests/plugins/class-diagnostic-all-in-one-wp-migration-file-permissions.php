<?php
/**
 * All-in-One WP Migration File Permissions Diagnostic
 *
 * AIO WP Migration files insecure permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.387.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All-in-One WP Migration File Permissions Diagnostic Class
 *
 * @since 1.387.0000
 */
class Diagnostic_AllInOneWpMigrationFilePermissions extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-migration-file-permissions';
	protected static $title = 'All-in-One WP Migration File Permissions';
	protected static $description = 'AIO WP Migration files insecure permissions';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AI1WM_PLUGIN_NAME' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Check backup storage directory
		$upload_dir = wp_upload_dir();
		$backup_dir = $upload_dir['basedir'] . '/ai1wm-backups';

		if ( ! file_exists( $backup_dir ) ) {
			return null; // No backups directory
		}

		// Check directory permissions
		$perms = substr( sprintf( '%o', fileperms( $backup_dir ) ), -4 );
		if ( $perms === '0777' ) {
			$issues[] = 'directory_too_permissive';
			$threat_level += 25;
		}

		// Check htaccess protection
		$htaccess = $backup_dir . '/.htaccess';
		if ( ! file_exists( $htaccess ) ) {
			$issues[] = 'no_htaccess_protection';
			$threat_level += 25;
		} else {
			$htaccess_content = file_get_contents( $htaccess );
			if ( strpos( $htaccess_content, 'deny from all' ) === false ) {
				$issues[] = 'weak_htaccess_protection';
				$threat_level += 20;
			}
		}

		// Check for publicly accessible backup files
		$backup_url = $upload_dir['baseurl'] . '/ai1wm-backups/';
		$response = wp_remote_head( $backup_url, array( 'timeout' => 5 ) );
		if ( ! is_wp_error( $response ) && wp_remote_retrieve_response_code( $response ) === 200 ) {
			$issues[] = 'backups_publicly_accessible';
			$threat_level += 30;
		}

		// Check for old backup files
		$backup_files = glob( $backup_dir . '/*.wpress' );
		if ( $backup_files ) {
			$old_backups = 0;
			foreach ( $backup_files as $file ) {
				if ( file_exists( $file ) && ( time() - filemtime( $file ) ) > 2592000 ) {
					$old_backups++;
				}
			}
			if ( $old_backups > 5 ) {
				$issues[] = 'old_backups_not_cleaned';
				$threat_level += 15;
			}
		}

		// Check backup file permissions
		if ( $backup_files ) {
			foreach ( array_slice( $backup_files, 0, 5 ) as $file ) {
				$file_perms = substr( sprintf( '%o', fileperms( $file ) ), -4 );
				if ( in_array( $file_perms, array( '0777', '0666', '0644' ), true ) ) {
					$issues[] = 'backup_files_too_permissive';
					$threat_level += 20;
					break;
				}
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of file permission issues */
				__( 'All-in-One WP Migration has insecure file permissions: %s. This can expose full site backups to unauthorized download and data breaches.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-migration-file-permissions',
			);
		}
		
		return null;
	}
}
