<?php
/**
 * Ewww Image Optimizer Backup Diagnostic
 *
 * Ewww Image Optimizer Backup detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.754.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ewww Image Optimizer Backup Diagnostic Class
 *
 * @since 1.754.0000
 */
class Diagnostic_EwwwImageOptimizerBackup extends Diagnostic_Base {

	protected static $slug = 'ewww-image-optimizer-backup';
	protected static $title = 'Ewww Image Optimizer Backup';
	protected static $description = 'Ewww Image Optimizer Backup detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'EWWW_Image_Optimizer' ) && ! defined( 'EWWW_IMAGE_OPTIMIZER_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Backup originals setting
		$backup_enabled = get_option( 'ewww_image_optimizer_backup_files', '' );
		if ( empty( $backup_enabled ) ) {
			$issues[] = 'original image backups disabled (cannot restore)';
		}

		// Check 2: Backup directory exists and writable
		$backup_dir = WP_CONTENT_DIR . '/ewww/original/';
		if ( ! empty( $backup_enabled ) ) {
			if ( ! is_dir( $backup_dir ) ) {
				$issues[] = 'backup directory missing';
			} elseif ( ! is_writable( $backup_dir ) ) {
				$issues[] = 'backup directory not writable';
			}
		}

		// Check 3: Backup storage size
		if ( is_dir( $backup_dir ) ) {
			$backup_size = 0;
			if ( function_exists( 'ewwwio_get_backup_size' ) ) {
				$backup_size = ewwwio_get_backup_size();
			}
			if ( $backup_size > 1073741824 ) { // 1GB
				$size_gb = round( $backup_size / 1073741824, 2 );
				$issues[] = "large backup directory ({$size_gb}GB, consider cleanup)";
			}
		}

		// Check 4: Optimized images count vs backup count
		global $wpdb;
		$optimized_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = 'ewww_image_optimizer'"
		);
		if ( ! empty( $backup_enabled ) && $optimized_count > 0 && is_dir( $backup_dir ) ) {
			$backup_files = glob( $backup_dir . '*' );
			$backup_count = is_array( $backup_files ) ? count( $backup_files ) : 0;
			if ( $backup_count < ( $optimized_count * 0.5 ) ) {
				$issues[] = "missing backups for many images ({$backup_count} backups vs {$optimized_count} optimized)";
			}
		}

		// Check 5: Automatic cleanup settings
		$cleanup_days = get_option( 'ewww_image_optimizer_backup_cleanup_days', 0 );
		if ( empty( $cleanup_days ) && ! empty( $backup_enabled ) ) {
			$issues[] = 'no automatic backup cleanup configured (disk space may grow)';
		}

		// Check 6: Backup permissions security
		if ( is_dir( $backup_dir ) ) {
			$perms = fileperms( $backup_dir );
			if ( ( $perms & 0004 ) > 0 ) {
				$issues[] = 'backup directory world-readable (security concern)';
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'EWWW Image Optimizer backup issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ewww-image-optimizer-backup',
			);
		}

		return null;
	}
}
