<?php
/**
 * Media Audit Trail Missing Treatment
 *
 * Detects when media operations (uploads, deletions, modifications)
 * are not logged, lacking accountability and compliance audit trails.
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
 * Media Audit Trail Missing Treatment Class
 *
 * Checks if media file operations are logged for audit purposes.
 * Required for compliance (SOC 2, ISO 27001) and security investigations.
 *
 * @since 1.6033.1430
 */
class Treatment_Media_Audit_Trail_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-audit-trail-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Audit Trail for Media Operations';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing audit logging for media uploads, deletions, and modifications';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Checks if media operations are logged. Audit trails are critical
	 * for accountability, security investigations, and compliance.
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Audit_Trail_Missing' );
	}

	/**
	 * Check if audit logging is already enabled.
	 *
	 * Detects existing audit/activity log plugins for media operations.
	 *
	 * @since  1.6033.1430
	 * @return bool True if audit logging detected.
	 */
	private static function has_audit_logging() {
		// Check for activity log plugins.
		$audit_plugins = array(
			'wp-security-audit-log/wp-security-audit-log.php',
			'simple-history/index.php',
			'aryo-activity-log/aryo-activity-log.php',
			'stream/stream.php',
			'audit-trail/audit-trail.php',
		);

		foreach ( $audit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// Check for security plugins with audit features.
		$security_plugins = array(
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
			'ithemes-security-pro/ithemes-security-pro.php',
		);

		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				// These have audit logging capabilities.
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if site has compliance requirements.
	 *
	 * Detects indicators that site requires audit trails.
	 *
	 * @since  1.6033.1430
	 * @return bool True if compliance requirements detected.
	 */
	private static function has_compliance_requirements() {
		// Check for WooCommerce (PCI-DSS often requires audit logs).
		if ( class_exists( 'WooCommerce' ) ) {
			return true;
		}

		// Check for membership sites (accountability required).
		if ( class_exists( 'MemberPress' ) || class_exists( 'Restrict_Content_Pro' ) ) {
			return true;
		}

		// Check for multisite (admin accountability).
		if ( is_multisite() ) {
			return true;
		}

		return false;
	}
}
