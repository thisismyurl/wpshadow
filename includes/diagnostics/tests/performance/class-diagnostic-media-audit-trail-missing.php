<?php
/**
 * Media Audit Trail Missing Diagnostic
 *
 * Detects when media operations (uploads, deletions, modifications)
 * are not logged, lacking accountability and compliance audit trails.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.1430
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Audit Trail Missing Diagnostic Class
 *
 * Checks if media file operations are logged for audit purposes.
 * Required for compliance (SOC 2, ISO 27001) and security investigations.
 *
 * @since 1.6033.1430
 */
class Diagnostic_Media_Audit_Trail_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-audit-trail-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Audit Trail for Media Operations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing audit logging for media uploads, deletions, and modifications';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks if media operations are logged. Audit trails are critical
	 * for accountability, security investigations, and compliance.
	 *
	 * @since  1.6033.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Don't flag if Vault is already active.
		if ( Upgrade_Path_Helper::has_pro_product( 'vault' ) ) {
			return null;
		}

		// Check for existing audit logging solutions.
		if ( self::has_audit_logging() ) {
			return null;
		}

		// Check if site has compliance requirements.
		$compliance_requirement = self::has_compliance_requirements();

		// Identify untracked operations.
		$operations_untracked = array( 'upload', 'delete', 'modify', 'access' );

		return array(
			'id'                     => self::$slug,
			'title'                  => self::$title,
			'description'            => __( 'Media file uploads, deletions, and modifications are not logged. For accountability and compliance, enable audit journaling to track who changed what and when.', 'wpshadow' ),
			'severity'               => $compliance_requirement ? 'medium' : 'low',
			'threat_level'           => $compliance_requirement ? 35 : 25,
			'auto_fixable'           => false,
			'operations_untracked'   => $operations_untracked,
			'compliance_requirement' => $compliance_requirement,
			'kb_link'                => 'https://wpshadow.com/kb/media-audit-trail',
		);
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
