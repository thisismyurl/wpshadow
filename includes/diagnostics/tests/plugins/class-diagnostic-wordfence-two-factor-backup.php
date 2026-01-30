<?php
/**
 * Wordfence Two Factor Backup Diagnostic
 *
 * Wordfence Two Factor Backup misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.842.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Two Factor Backup Diagnostic Class
 *
 * @since 1.842.0000
 */
class Diagnostic_WordfenceTwoFactorBackup extends Diagnostic_Base {

	protected static $slug = 'wordfence-two-factor-backup';
	protected static $title = 'Wordfence Two Factor Backup';
	protected static $description = 'Wordfence Two Factor Backup misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WORDFENCE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Two-factor authentication enabled
		$tfa = get_option( 'wordfence_two_factor_enabled', 0 );
		if ( ! $tfa ) {
			$issues[] = 'Two-factor authentication not enabled';
		}
		
		// Check 2: Backup codes generated
		$backup_codes = get_option( 'wordfence_backup_codes_generated', 0 );
		if ( ! $backup_codes ) {
			$issues[] = 'Backup codes not generated';
		}
		
		// Check 3: Backup codes secured
		$backup_secured = get_option( 'wordfence_backup_codes_stored_securely', 0 );
		if ( ! $backup_secured ) {
			$issues[] = 'Backup codes not stored securely';
		}
		
		// Check 4: Totp/authenticator app
		$totp = get_option( 'wordfence_totp_app_configured', 0 );
		if ( ! $totp ) {
			$issues[] = 'TOTP/Authenticator app not configured';
		}
		
		// Check 5: SMS backup enabled
		$sms = get_option( 'wordfence_sms_backup_enabled', 0 );
		if ( ! $sms ) {
			$issues[] = 'SMS backup method not enabled';
		}
		
		// Check 6: Recovery methods tested
		$recovery = get_option( 'wordfence_recovery_methods_tested', 0 );
		if ( ! $recovery ) {
			$issues[] = 'Recovery methods not recently tested';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d 2FA backup issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wordfence-two-factor-backup',
			);
		}
		
		return null;
	}
}
