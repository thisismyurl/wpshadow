<?php
/**
 * Backup Storage Location and Offsite Configuration
 *
 * Validates backup storage is offsite and properly configured.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Backup_Offsite_Storage Class
 *
 * Checks that backups are stored offsite for disaster recovery.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Backup_Offsite_Storage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-offsite-storage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Offsite Storage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates backups are stored offsite for disaster recovery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup-recovery';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for active backup plugin
		$backup_plugin = self::get_active_backup_plugin();

		if ( ! $backup_plugin ) {
			return null; // No backup plugin, skip check
		}

		// Pattern 1: Backups stored only on same server (no offsite)
		$storage_location = self::get_storage_location( $backup_plugin );

		if ( 'local' === $storage_location ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backups stored only on local server', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-offsite-storage',
				'details'      => array(
					'issue' => 'backups_local_only',
					'message' => __( 'Backups are stored on the same server as the live site', 'wpshadow' ),
					'why_critical' => __( 'If server is hacked, attacked, or fails, backups are lost too', 'wpshadow' ),
					'disaster_scenarios' => array(
						'Server hardware failure = both site AND backups lost',
						'Ransomware attack encrypts both site AND backup storage',
						'Hacker deletes both live site and backup copies',
						'Natural disaster (fire, flood) destroys server and backups',
					),
					'single_point_of_failure' => __( 'Having only local backups means no real disaster recovery', 'wpshadow' ),
					'recovery_failure_rate' => __( 'Without offsite backups, recovery success rate is 5-15%', 'wpshadow' ),
					'why_offsite_essential' => array(
						'Geographic redundancy (if data center fails)',
						'Protection from ransomware (can\'t encrypt offsite backups)',
						'Protection from malicious deletion',
						'Compliance requirement for most industries',
					),
					'offsite_options' => array(
						'Google Drive' => 'Free tier available, automatic sync, access from anywhere',
						'Dropbox' => 'Professional sync, version history, 30-day retention',
						'Amazon S3' => 'Enterprise option, geographic redundancy, audit logs',
						'Microsoft OneDrive' => 'Integrated with Microsoft ecosystem',
						'UpdraftVault' => 'Purpose-built for WordPress backups',
					),
					'recommendation' => __( 'Enable cloud storage in your backup plugin immediately', 'wpshadow' ),
					'action_priority' => 'URGENT - Configure offsite storage before next backup runs',
				),
			);
		}

		// Pattern 2: Cloud storage configured but API keys not set
		if ( 'cloud' === $storage_location ) {
			$cloud_service = self::get_cloud_service( $backup_plugin );

			if ( ! self::is_cloud_auth_configured( $backup_plugin, $cloud_service ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Cloud storage configured but not authenticated', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 80,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-offsite-storage',
					'details'      => array(
						'issue' => 'cloud_auth_missing',
						'cloud_service' => $cloud_service,
						'message' => sprintf(
							/* translators: %s: cloud service name */
							__( '%s is configured but not authenticated (missing API keys)', 'wpshadow' ),
							$cloud_service
						),
						'why_important' => __( 'Without authentication, backups cannot be uploaded to cloud', 'wpshadow' ),
						'effect' => __( 'Backups fail silently or only store locally', 'wpshadow' ),
						'setup_steps' => array(
							'1. Go to backup plugin settings',
							'2. Find cloud storage section',
							'3. Click "Authenticate" or "Connect"',
							'4. Follow provider login flow',
							'5. Grant permission for backup plugin to access account',
							'6. Verify connection successful',
						),
						'testing' => __( 'After authentication, run a test backup to verify', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 3: Single cloud storage location (no geographic redundancy)
		$cloud_services_count = self::count_cloud_services( $backup_plugin );

		if ( $cloud_services_count === 1 && $storage_location === 'cloud' ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Only one cloud storage location (no geographic redundancy)', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-offsite-storage',
				'details'      => array(
					'issue' => 'single_cloud_location',
					'message' => __( 'Backups stored in single cloud location with no secondary copy', 'wpshadow' ),
					'why_matters' => __( 'Cloud provider outages can make backups temporarily inaccessible', 'wpshadow' ),
					'scenario' => __( 'If Google Drive goes down (happened 2019), you can\'t restore for hours/days', 'wpshadow' ),
					'redundancy_benefits' => array(
						'Protection against cloud provider outages',
						'Geographic diversity (avoid region-specific disasters)',
						'Faster recovery with nearest backup location',
						'Enterprise compliance requirements',
					),
					'multi_cloud_setup' => array(
						'Option 1: Primary (Google Drive) + Secondary (Dropbox)',
						'Option 2: Primary (S3) + Local backup + Email',
						'Option 3: Service-specific (Google Drive + OneDrive + Dropbox)',
					),
					'recommendation' => __( 'Add second cloud storage for critical sites', 'wpshadow' ),
					'enterprise_requirement' => __( 'Enterprise/mission-critical sites require 3+ locations', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Storage quota nearly full
		$storage_usage = self::get_storage_usage( $backup_plugin );

		if ( $storage_usage && $storage_usage > 80 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backup storage nearly full', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-offsite-storage',
				'details'      => array(
					'issue' => 'storage_quota_full',
					'usage_percentage' => intval( $storage_usage ),
					'message' => sprintf(
						/* translators: %d: percentage */
						__( 'Backup storage is %d%% full', 'wpshadow' ),
						intval( $storage_usage )
					),
					'danger' => __( 'When full, next backup will fail silently', 'wpshadow' ),
					'solutions' => array(
						'Reduce retention period (keep fewer old backups)',
						'Delete unused backup versions manually',
						'Upgrade cloud storage plan',
						'Add second storage location',
						'Delete non-critical backup files',
					),
					'backup_failure_consequence' => __( 'Failed backups leave you unprotected without warning', 'wpshadow' ),
					'action_needed' => 'Clean up old backups or upgrade storage immediately',
				),
			);
		}

		// Pattern 5: FTP/SFTP storage without encryption
		if ( 'ftp' === $storage_location ) {
			$is_sftp = self::is_sftp_enabled( $backup_plugin );

			if ( ! $is_sftp ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Using unencrypted FTP for backup storage', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-offsite-storage',
					'details'      => array(
						'issue' => 'ftp_not_encrypted',
						'message' => __( 'Backups sent over unencrypted FTP (credentials and data visible)', 'wpshadow' ),
						'security_risk' => __( 'FTP credentials and backup data can be intercepted in transit', 'wpshadow' ),
						'attack_potential' => __( 'Attackers can intercept backups or use exposed credentials', 'wpshadow' ),
						'compliance_issue' => __( 'HIPAA, PCI-DSS, GDPR require encryption for data in transit', 'wpshadow' ),
						'solution' => 'Change FTP to SFTP (Secure File Transfer Protocol) in settings',
						'verification_steps' => array(
							'1. Backup plugin settings',
							'2. Look for "SFTP" or "SSH" option',
							'3. Ensure "SFTP" or "SSH" is selected (not plain FTP)',
							'4. Verify certificate is valid',
						),
						'recommendation' => __( 'Enable SFTP immediately for secure encryption', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 6: No encryption configured for stored backups
		$encryption_enabled = self::is_backup_encryption_enabled( $backup_plugin );

		if ( ! $encryption_enabled ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Backup files not encrypted at rest', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-offsite-storage',
				'details'      => array(
					'issue' => 'no_encryption_at_rest',
					'message' => __( 'Backup files are not encrypted while stored in cloud', 'wpshadow' ),
					'why_important' => __( 'Encrypted backups protect database passwords and sensitive data', 'wpshadow' ),
					'data_at_risk' => array(
						'Database passwords (users could access admin)',
						'Customer personal information (GDPR violation)',
						'Payment data (PCI-DSS violation)',
						'API keys for third-party services',
					),
					'compliance_risk' => __( 'Unencrypted backups violate GDPR, PCI-DSS, HIPAA', 'wpshadow' ),
					'encryption_algorithms' => array(
						'AES-256' => 'Industry standard, government approved',
						'GPG' => 'Open standard, widely supported',
						'Authenticated encryption' => 'Ensures backup integrity',
					),
					'recommendation' => __( 'Enable backup encryption (usually in plugin advanced settings)', 'wpshadow' ),
					'performance_note' => __( 'Encryption adds 2-5% to backup time, negligible cost for security', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}

	/**
	 * Get active backup plugin slug.
	 *
	 * @since 1.6093.1200
	 * @return string Plugin slug or empty.
	 */
	private static function get_active_backup_plugin() {
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
			'jetpack/jetpack.php',
		);

		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return basename( dirname( $plugin ) );
			}
		}

		return '';
	}

	/**
	 * Get backup storage location type.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return string Storage type: 'local', 'cloud', 'ftp', etc.
	 */
	private static function get_storage_location( $plugin ) {
		return get_option( $plugin . '_storage_location', 'local' );
	}

	/**
	 * Get configured cloud service name.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return string Cloud service name.
	 */
	private static function get_cloud_service( $plugin ) {
		return get_option( $plugin . '_cloud_service', '' );
	}

	/**
	 * Check if cloud authentication is configured.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @param  string $service Service name.
	 * @return bool True if authenticated.
	 */
	private static function is_cloud_auth_configured( $plugin, $service ) {
		$auth_token = get_option( $plugin . '_' . $service . '_auth_token', '' );
		return ! empty( $auth_token );
	}

	/**
	 * Count configured cloud services.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return int Number of cloud services.
	 */
	private static function count_cloud_services( $plugin ) {
		$services = get_option( $plugin . '_cloud_services', array() );
		return is_array( $services ) ? count( $services ) : 0;
	}

	/**
	 * Get storage usage percentage.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return int Percentage 0-100.
	 */
	private static function get_storage_usage( $plugin ) {
		return absint( get_option( $plugin . '_storage_usage_percent', 0 ) );
	}

	/**
	 * Check if SFTP is enabled.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return bool True if SFTP enabled.
	 */
	private static function is_sftp_enabled( $plugin ) {
		return (bool) get_option( $plugin . '_sftp_enabled', false );
	}

	/**
	 * Check if backup encryption is enabled.
	 *
	 * @since 1.6093.1200
	 * @param  string $plugin Plugin slug.
	 * @return bool True if encryption enabled.
	 */
	private static function is_backup_encryption_enabled( $plugin ) {
		return (bool) get_option( $plugin . '_backup_encryption', false );
	}
}
