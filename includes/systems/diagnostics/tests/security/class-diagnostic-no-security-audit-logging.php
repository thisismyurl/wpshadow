<?php
/**
 * No Security Audit Logging Diagnostic
 *
 * Detects when security events are not being logged,
 * missing forensic evidence for security incidents.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Security Audit Logging
 *
 * Checks whether security events are being logged
 * for forensic analysis and compliance.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Security_Audit_Logging extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-security-audit-logging';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Audit Logging';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether security events are being logged';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for security logging plugins
		$has_audit_logging = is_plugin_active( 'wp-security-audit-log/wp-security-audit-log.php' ) ||
			is_plugin_active( 'simple-history/index.php' ) ||
			is_plugin_active( 'wordfence-security/wordfence.php' );

		if ( ! $has_audit_logging ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Security events aren\'t being logged, which means you have no record of what happens. Security logging tracks: login attempts (successful and failed), user changes, plugin/theme installs, file modifications, settings changes. Without logs, you can\'t: detect intrusions, investigate incidents, prove compliance, identify insider threats. Logs are your security camera footage—essential for both prevention and forensics.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Security Forensics & Compliance',
					'potential_gain' => 'Detect and investigate security incidents',
					'roi_explanation' => 'Audit logging provides forensic evidence for security incidents and is required for many compliance frameworks.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/security-audit-logging',
			);
		}

		return null;
	}
}
