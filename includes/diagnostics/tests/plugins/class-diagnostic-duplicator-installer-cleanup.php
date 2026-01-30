<?php
/**
 * Duplicator Installer Cleanup Diagnostic
 *
 * Duplicator installer files not removed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.393.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Duplicator Installer Cleanup Diagnostic Class
 *
 * @since 1.393.0000
 */
class Diagnostic_DuplicatorInstallerCleanup extends Diagnostic_Base {

	protected static $slug = 'duplicator-installer-cleanup';
	protected static $title = 'Duplicator Installer Cleanup';
	protected static $description = 'Duplicator installer files not removed';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'DUP_PRO_Package' ) || class_exists( 'DUP_Package' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Detect installer.php
		if ( file_exists( ABSPATH . 'installer.php' ) ) {
			$issues[] = 'installer.php file still present';
		}
		
		// Check 2: Detect installer-backup.php
		if ( file_exists( ABSPATH . 'installer-backup.php' ) ) {
			$issues[] = 'installer-backup.php file still present';
		}
		
		// Check 3: Detect DUP installer directory
		if ( is_dir( ABSPATH . 'dup-installer' ) ) {
			$issues[] = 'dup-installer directory still present';
		}
		
		// Check 4: Detect archive files in root
		$root_files = glob( ABSPATH . '*.zip' );
		if ( ! empty( $root_files ) ) {
			$issues[] = 'Archive files found in site root';
		}
		
		// Check 5: Detect installer log files
		$log_files = glob( ABSPATH . '*dup*log*.txt' );
		if ( ! empty( $log_files ) ) {
			$issues[] = 'Duplicator installer log files present';
		}
		
		// Check 6: Check for recent packages in uploads
		$upload_dir = wp_upload_dir();
		$dup_dir = trailingslashit( $upload_dir['basedir'] ) . 'duplicator';
		if ( is_dir( $dup_dir ) ) {
			$issues[] = 'Duplicator package directory exists in uploads';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Duplicator installer cleanup issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/duplicator-installer-cleanup',
			);
		}
		
		return null;
	}
}
