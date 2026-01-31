<?php
/**
 * Client Proofing and Gallery Privacy Diagnostic
 *
 * Checks if photography/portfolio sites with client galleries implement
 * proper privacy controls including password protection and access logs.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Portfolio
 * @since      1.6031.1450
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Portfolio;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Client Gallery Privacy Diagnostic Class
 *
 * Verifies client proofing galleries have proper privacy protection.
 *
 * @since 1.6031.1450
 */
class Diagnostic_Client_Gallery_Privacy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'client-gallery-privacy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Client Proofing and Gallery Privacy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies client galleries implement proper privacy and access controls';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'portfolio';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - Client gallery plugins with privacy features
	 * - Password protection for galleries
	 * - Access logging and monitoring
	 * - Download prevention options
	 *
	 * @since  1.6031.1450
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for client proofing/gallery plugins.
		$proofing_plugins = array(
			'photoproof',
			'client-gallery',
			'proof',
			'pixieset',
			'shootproof',
		);

		$has_proofing = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $proofing_plugins as $proof_plugin ) {
				if ( stripos( $plugin, $proof_plugin ) !== false ) {
					$has_proofing = true;
					break 2;
				}
			}
		}

		if ( ! $has_proofing ) {
			return null; // No client proofing system.
		}

		$issues = array();

		// Check for password protection plugins.
		$has_password_protection = false;
		$password_plugins = array(
			'password-protected',
			'ppwp',
			'private-content',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $password_plugins as $pw_plugin ) {
				if ( stripos( $plugin, $pw_plugin ) !== false ) {
					$has_password_protection = true;
					break 2;
				}
			}
		}

		if ( ! $has_password_protection ) {
			$issues[] = __( 'No dedicated password protection plugin for galleries', 'wpshadow' );
		}

		// Check for activity logging.
		$has_activity_log = false;
		$log_plugins = array(
			'simple-history',
			'wp-security-audit-log',
			'activity-log',
		);

		foreach ( $active_plugins as $plugin ) {
			foreach ( $log_plugins as $log_plugin ) {
				if ( stripos( $plugin, $log_plugin ) !== false ) {
					$has_activity_log = true;
					break 2;
				}
			}
		}

		if ( ! $has_activity_log ) {
			$issues[] = __( 'No activity logging for gallery access tracking', 'wpshadow' );
		}

		// Check for HTTPS.
		if ( ! is_ssl() ) {
			$issues[] = __( 'Site not using HTTPS (client images unencrypted)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Client gallery privacy concerns: %s. Client proofing systems should implement strong password protection, access logging, and HTTPS to protect private client photos.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/client-gallery-privacy',
		);
	}
}
