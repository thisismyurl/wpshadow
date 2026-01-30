<?php
/**
 * Ithemes Security Database Backups Diagnostic
 *
 * Ithemes Security Database Backups misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.857.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ithemes Security Database Backups Diagnostic Class
 *
 * @since 1.857.0000
 */
class Diagnostic_IthemesSecurityDatabaseBackups extends Diagnostic_Base {

	protected static $slug = 'ithemes-security-database-backups';
	protected static $title = 'Ithemes Security Database Backups';
	protected static $description = 'Ithemes Security Database Backups misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'itsec_load_textdomain' ) ) {
			return null;
		}
		
		$issues = array();
		
		$backup_enabled = false;
		$backup_interval = 0;
		$backup_destination = '';
		
		if ( class_exists( 'ITSEC_Modules' ) && method_exists( 'ITSEC_Modules', 'get_setting' ) ) {
			$backup_enabled = (bool) ITSEC_Modules::get_setting( 'backup', 'enabled' );
			$backup_interval = (int) ITSEC_Modules::get_setting( 'backup', 'interval' );
			$backup_destination = (string) ITSEC_Modules::get_setting( 'backup', 'location' );
		} else {
			$backup_settings = get_option( 'itsec_backup', array() );
			$backup_enabled = ! empty( $backup_settings['enabled'] );
			$backup_interval = isset( $backup_settings['interval'] ) ? (int) $backup_settings['interval'] : 0;
			$backup_destination = isset( $backup_settings['location'] ) ? (string) $backup_settings['location'] : '';
		}
		
		// Check 1: Verify backups are enabled
		if ( ! $backup_enabled ) {
			$issues[] = 'Database backups not enabled';
		}
		
		// Check 2: Check backup interval
		if ( $backup_enabled && $backup_interval <= 0 ) {
			$issues[] = 'Backup interval not configured';
		}
		
		// Check 3: Verify backup destination
		if ( $backup_enabled && empty( $backup_destination ) ) {
			$issues[] = 'Backup destination not configured';
		}
		
		// Check 4: Check email notification for backups
		$notify_email = get_option( 'itsec_backup_email', '' );
		if ( $backup_enabled && empty( $notify_email ) ) {
			$issues[] = 'Backup notification email not configured';
		}
		
		// Check 5: Verify backup retention
		$retention = get_option( 'itsec_backup_retention', 0 );
		if ( $backup_enabled && $retention <= 0 ) {
			$issues[] = 'Backup retention not configured';
		}
		
		// Check 6: Check for recent backups
		$last_backup = (int) get_option( 'itsec_backup_last_backup', 0 );
		if ( $backup_enabled && $last_backup > 0 && ( time() - $last_backup ) > 1209600 ) {
			$issues[] = 'No database backup in the last 14 days';
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
					'Found %d iThemes Security database backup issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ithemes-security-database-backups',
			);
		}
		
		return null;
	}
}
