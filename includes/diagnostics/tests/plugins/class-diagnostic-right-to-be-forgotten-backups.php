<?php
/**
 * Right To Be Forgotten Backups Diagnostic
 *
 * Right To Be Forgotten Backups not compliant.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1132.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Right To Be Forgotten Backups Diagnostic Class
 *
 * @since 1.1132.0000
 */
class Diagnostic_RightToBeForgottenBackups extends Diagnostic_Base {

	protected static $slug = 'right-to-be-forgotten-backups';
	protected static $title = 'Right To Be Forgotten Backups';
	protected static $description = 'Right To Be Forgotten Backups not compliant';
	protected static $family = 'functionality';

	public static function check() {
		// Check for backup plugins
		$has_backup = class_exists( 'UpdraftPlus' ) ||
		              defined( 'DUPLICATOR_VERSION' ) ||
		              class_exists( 'BackWPup' );
		
		if ( ! $has_backup ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Backup retention policy
		$retention_days = get_option( 'backup_retention_days', 0 );
		if ( $retention_days === 0 || $retention_days > 365 ) {
			$issues[] = __( 'Indefinite backup retention (GDPR violation)', 'wpshadow' );
		}
		
		// Check 2: User data in backups tracked
		$track_user_data = get_option( 'rtbf_track_user_data_in_backups', false );
		if ( ! $track_user_data ) {
			$issues[] = __( 'User data in backups not tracked (erasure incomplete)', 'wpshadow' );
		}
		
		// Check 3: Automated backup deletion
		$auto_delete = get_option( 'rtbf_auto_delete_backups', false );
		if ( ! $auto_delete ) {
			$issues[] = __( 'Manual backup deletion required (compliance burden)', 'wpshadow' );
		}
		
		// Check 4: Backup notification for erasure
		$notify_erasure = get_option( 'rtbf_notify_backup_erasure', false );
		if ( ! $notify_erasure ) {
			$issues[] = __( 'No notification when user data in old backups (tracking gap)', 'wpshadow' );
		}
		
		// Check 5: Documentation of backup process
		$document_policy = get_option( 'rtbf_backup_policy_documented', false );
		if ( ! $document_policy ) {
			$issues[] = __( 'Backup GDPR policy not documented (audit risk)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of RTBF backup issues */
				__( 'Right to be Forgotten backups have %d compliance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/right-to-be-forgotten-backups',
		);
	}
}
