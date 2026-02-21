<?php
/**
 * Media Files Unencrypted Treatment
 *
 * Detects when media files are stored without encryption at rest,
 * posing compliance and security risks for sensitive content.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Files Unencrypted Treatment Class
 *
 * Checks if media files are encrypted at rest. For sites handling
 * sensitive content (medical, legal, financial), unencrypted storage
 * violates compliance requirements (HIPAA, GDPR, PCI-DSS).
 *
 * @since 1.6033.1430
 */
class Treatment_Media_Files_Unencrypted extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-files-unencrypted';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Files Stored Without Encryption';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects unencrypted media files at rest that may contain sensitive content';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Checks if media files are encrypted. For compliance-sensitive sites,
	 * encryption at rest is required for sensitive file types (PDFs, DOCX, etc.).
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Files_Unencrypted' );
	}

	/**
	 * Check if filesystem is encrypted.
	 *
	 * Detects common filesystem encryption methods.
	 *
	 * @since  1.6033.1430
	 * @return bool True if encrypted filesystem detected.
	 */
	private static function is_filesystem_encrypted() {
		// Check for AWS EFS encryption (env variable).
		if ( getenv( 'AWS_EFS_ENCRYPTED' ) === 'true' ) {
			return true;
		}

		// Check for EncryptFS signature (not reliable but indicative).
		$uploads_dir = wp_upload_dir();
		if ( isset( $uploads_dir['basedir'] ) && is_dir( $uploads_dir['basedir'] ) ) {
			// If we can read the directory but files have .ecryptfs extension.
			$ecryptfs_files = glob( $uploads_dir['basedir'] . '/.ecryptfs*' );
			if ( ! empty( $ecryptfs_files ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Count total media files in uploads directory.
	 *
	 * @since  1.6033.1430
	 * @param  string $directory Upload directory path.
	 * @return int Total file count.
	 */
	private static function count_media_files( $directory ) {
		global $wpdb;

		// Use WordPress attachment count (more reliable than filesystem scan).
		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'attachment'"
		);

		return (int) $count;
	}

	/**
	 * Count sensitive file types in uploads directory.
	 *
	 * @since  1.6033.1430
	 * @param  string $directory Upload directory path.
	 * @return int Sensitive file count.
	 */
	private static function count_sensitive_files( $directory ) {
		global $wpdb;

		// Sensitive MIME types.
		$sensitive_mimes = array(
			'application/pdf',
			'application/msword',
			'application/vnd.ms-excel',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		);

		$mime_placeholders = implode( ',', array_fill( 0, count( $sensitive_mimes ), '%s' ) );

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = 'attachment' 
				AND post_mime_type IN ({$mime_placeholders})",
				...$sensitive_mimes
			)
		);

		return (int) $count;
	}

	/**
	 * Check if site has compliance requirements.
	 *
	 * Detects indicators that site handles compliance-sensitive data.
	 *
	 * @since  1.6033.1430
	 * @return bool True if compliance requirements detected.
	 */
	private static function has_compliance_requirements() {
		// Check for healthcare/medical plugins.
		$healthcare_plugins = array(
			'medical-practice-management',
			'health-insurance',
			'patient-portal',
			'hipaa-forms',
		);

		foreach ( $healthcare_plugins as $plugin_slug ) {
			if ( is_plugin_active( $plugin_slug . '/' . $plugin_slug . '.php' ) ) {
				return true;
			}
		}

		// Check for ecommerce (PCI-DSS compliance).
		if ( class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' ) ) {
			return true;
		}

		// Check for membership/LMS (often handles sensitive data).
		if ( class_exists( 'MemberPress' ) || class_exists( 'LearnDash' ) ) {
			return true;
		}

		return false;
	}
}
