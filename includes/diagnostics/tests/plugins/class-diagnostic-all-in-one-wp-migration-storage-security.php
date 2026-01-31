<?php
/**
 * All-in-One WP Migration Storage Diagnostic
 *
 * AIO WP Migration backups publicly accessible.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.386.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All-in-One WP Migration Storage Diagnostic Class
 *
 * @since 1.386.0000
 */
class Diagnostic_AllInOneWpMigrationStorageSecurity extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-migration-storage-security';
	protected static $title = 'All-in-One WP Migration Storage';
	protected static $description = 'AIO WP Migration backups publicly accessible';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'AI1WM_PLUGIN_NAME' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Storage directory location.
		$storage_path = defined( 'AI1WM_STORAGE_PATH' ) ? AI1WM_STORAGE_PATH : WP_CONTENT_DIR . '/ai1wm-backups';
		$is_public = strpos( $storage_path, WP_CONTENT_DIR ) !== false;
		if ( $is_public ) {
			$issues[] = 'backups stored in wp-content (potentially publicly accessible)';
		}

		// Check 2: .htaccess protection.
		$htaccess_file = $storage_path . '/.htaccess';
		if ( ! file_exists( $htaccess_file ) ) {
			$issues[] = 'no .htaccess file protecting backup directory';
		} else {
			$htaccess_content = @file_get_contents( $htaccess_file );
			if ( strpos( $htaccess_content, 'Deny from all' ) === false ) {
				$issues[] = '.htaccess exists but does not deny access';
			}
		}

		// Check 3: index.php protection.
		$index_file = $storage_path . '/index.php';
		if ( ! file_exists( $index_file ) ) {
			$issues[] = 'no index.php file to prevent directory listing';
		}

		// Check 4: Old backup files.
		if ( is_dir( $storage_path ) ) {
			$backups = glob( $storage_path . '/*.wpress' );
			if ( is_array( $backups ) ) {
				$old_backups = 0;
				foreach ( $backups as $backup ) {
					if ( ( time() - filemtime( $backup ) ) > ( 30 * DAY_IN_SECONDS ) ) {
						$old_backups++;
					}
				}
				if ( $old_backups > 0 ) {
					$issues[] = "{$old_backups} backups older than 30 days (security risk if exposed)";
				}
			}
		}

		// Check 5: Backup file permissions.
		if ( is_dir( $storage_path ) ) {
			$backups = glob( $storage_path . '/*.wpress' );
			if ( is_array( $backups ) && ! empty( $backups ) ) {
				$sample_file = $backups[0];
				$perms = fileperms( $sample_file );
				$world_readable = ( $perms & 0x0004 );
				if ( $world_readable ) {
					$issues[] = 'backup files are world-readable (chmod 600 recommended)';
				}
			}
		}

		// Check 6: Auto-cleanup enabled.
		$auto_cleanup = get_option( 'ai1wm_auto_cleanup', '0' );
		if ( '0' === $auto_cleanup ) {
			$issues[] = 'automatic cleanup disabled (old backups accumulate)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'All-in-One WP Migration storage security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-migration-storage-security',
			);
		}

		return null;
	}
}
