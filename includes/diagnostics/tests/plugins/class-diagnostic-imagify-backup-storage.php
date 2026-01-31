<?php
/**
 * Imagify Backup Storage Diagnostic
 *
 * Imagify Backup Storage detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.741.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imagify Backup Storage Diagnostic Class
 *
 * @since 1.741.0000
 */
class Diagnostic_ImagifyBackupStorage extends Diagnostic_Base {

	protected static $slug = 'imagify-backup-storage';
	protected static $title = 'Imagify Backup Storage';
	protected static $description = 'Imagify Backup Storage detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'IMAGIFY_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Backup enabled
		$backup_enabled = get_option( 'imagify_backup', 0 );
		if ( ! $backup_enabled ) {
			$issues[] = __( 'Backups disabled (no original recovery)', 'wpshadow' );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Imagify backups are disabled - original images cannot be restored', 'wpshadow' ),
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/imagify-backup-storage',
			);
		}
		
		// Check 2: Backup location
		$upload_dir = wp_upload_dir();
		$backup_dir = $upload_dir['basedir'] . '/backup/';
		
		if ( ! is_dir( $backup_dir ) ) {
			$issues[] = __( 'Backup directory missing', 'wpshadow' );
		}
		
		// Check 3: Backup disk usage
		if ( is_dir( $backup_dir ) ) {
			$backup_size = 0;
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $backup_dir, RecursiveDirectoryIterator::SKIP_DOTS )
			);
			
			foreach ( $iterator as $file ) {
				$backup_size += $file->getSize();
			}
			
			if ( $backup_size > ( 1024 * 1024 * 1024 ) ) { // 1GB
				$size_mb = round( $backup_size / ( 1024 * 1024 ), 2 );
				$issues[] = sprintf( __( '%s MB in backups (disk space)', 'wpshadow' ), number_format( $size_mb ) );
			}
		}
		
		// Check 4: Auto-delete old backups
		$auto_delete = get_option( 'imagify_auto_delete_backups', 0 );
		if ( ! $auto_delete ) {
			$issues[] = __( 'Auto-delete disabled (backups accumulate)', 'wpshadow' );
		}
		
		// Check 5: Backup retention
		$retention = get_option( 'imagify_backup_retention_days', 0 );
		if ( $retention === 0 ) {
			$issues[] = __( 'No retention policy (indefinite storage)', 'wpshadow' );
		}
		
		
		// Check 6: Feature initialization
		if ( ! (get_option( "features_init" ) !== false) ) {
			$issues[] = __( 'Feature initialization', 'wpshadow' );
		}

		// Check 7: Database tables
		if ( ! (! empty( $GLOBALS["wpdb"] )) ) {
			$issues[] = __( 'Database tables', 'wpshadow' );
		}

		// Check 8: Hook registration
		if ( ! (has_action( "init" )) ) {
			$issues[] = __( 'Hook registration', 'wpshadow' );
		}
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = (40 + min(35, count($issues) * 8));
		if ( count( $issues ) >= 4 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = (40 + min(35, count($issues) * 8));
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of backup storage issues */
				__( 'Imagify backup storage has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/imagify-backup-storage',
		);
	}
}
