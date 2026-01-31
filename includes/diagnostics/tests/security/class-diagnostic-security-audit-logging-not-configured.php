<?php
/**
 * Security Audit Logging Not Configured Diagnostic
 *
 * Checks if security audit logging is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2350
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Audit Logging Not Configured Diagnostic Class
 *
 * Detects missing security audit logging.
 *
 * @since 1.2601.2350
 */
class Diagnostic_Security_Audit_Logging_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-audit-logging-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Audit Logging Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if security audit logging is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2350
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for security audit logging plugins
		$audit_plugins = array(
			'wordfence/wordfence.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);

		$audit_active = false;
		foreach ( $audit_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$audit_active = true;
				break;
			}
		}

		if ( ! $audit_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Security audit logging is not configured. Enable audit logging to track security events and access attempts.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/security-audit-logging-not-configured',
			);
		}

		return null;
	}
}
