<?php
/**
 * Backup Encryption Advanced Status Diagnostic
 *
 * Advanced checks if backups are encrypted for GDPR/compliance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Encryption Advanced Status Diagnostic Class
 *
 * Advanced verification that database and file backups are encrypted
 * with strong algorithms to meet GDPR and compliance requirements.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Backup_Encryption_Advanced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-encryption-advanced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Encryption Advanced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Advanced backup encryption verification';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the backup encryption status check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if backup encryption not configured, null otherwise.
	 */
	public static function check() {
		$stats = array();
		$issues = array();

		// Check for multiple backup methods.
		$backup_methods = array();

		// Check for local backups.
		$local_backup_enabled = get_option( 'wpshadow_local_backup_enabled' );
		if ( $local_backup_enabled ) {
			$backup_methods[] = 'Local';
		}

		// Check for cloud backups.
		$cloud_backup_enabled = get_option( 'wpshadow_cloud_backup_enabled' );
		if ( $cloud_backup_enabled ) {
			$backup_methods[] = 'Cloud';
		}

		$stats['backup_methods'] = $backup_methods;

		// Check AES encryption strength.
		$encryption_algorithm = get_option( 'wpshadow_backup_encryption_algorithm', 'none' );
		$stats['encryption_algorithm'] = $encryption_algorithm;

		$strong_encryption = ( 'aes256' === $encryption_algorithm || 'aes-256-gcm' === $encryption_algorithm );

		if ( ! $strong_encryption && 'none' !== $encryption_algorithm ) {
			$issues[] = sprintf(
				/* translators: %s: algorithm */
				__( 'Using weak encryption: %s - recommend AES-256-GCM', 'wpshadow' ),
				$encryption_algorithm
			);
		} elseif ( 'none' === $encryption_algorithm ) {
			$issues[] = __( 'Backup encryption not enabled - critical compliance gap', 'wpshadow' );
		}

		// Check for key management service integration.
		$kms_integration = get_option( 'wpshadow_backup_kms_integration' );
		$stats['kms_integration'] = boolval( $kms_integration );

		if ( ! $kms_integration ) {
			$issues[] = __( 'No Key Management Service (KMS) integration - manual key management risky', 'wpshadow' );
		}

		// Check backup integrity verification.
		$integrity_check = get_option( 'wpshadow_backup_integrity_check' );
		$stats['integrity_verification'] = boolval( $integrity_check );

		if ( ! $integrity_check ) {
			$issues[] = __( 'Backup integrity verification not enabled', 'wpshadow' );
		}

		// Check for test restore schedule.
		$test_restore_enabled = get_option( 'wpshadow_backup_test_restore_enabled' );
		$stats['test_restore_enabled'] = boolval( $test_restore_enabled );

		if ( ! $test_restore_enabled ) {
			$issues[] = __( 'Regular backup restore tests not scheduled', 'wpshadow' );
		}

		// Check encryption audit trail.
		$audit_trail = get_option( 'wpshadow_backup_audit_trail_enabled' );
		$stats['audit_trail_enabled'] = boolval( $audit_trail );

		if ( ! $audit_trail ) {
			$issues[] = __( 'Encryption audit trail not enabled - cannot prove compliance', 'wpshadow' );
		}

		// Check certificate pinning for cloud backup.
		$cert_pinning = get_option( 'wpshadow_backup_cert_pinning' );
		$stats['cert_pinning_enabled'] = boolval( $cert_pinning );

		if ( $cloud_backup_enabled && ! $cert_pinning ) {
			$issues[] = __( 'Certificate pinning not enabled for cloud backups', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backup encryption issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-encryption',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null; // Backup encryption properly configured.
	}
}
