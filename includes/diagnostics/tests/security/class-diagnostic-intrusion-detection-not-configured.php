<?php
/**
 * Intrusion Detection System Not Configured
 *
 * Checks if an intrusion detection system (IDS) is configured to monitor
 * and alert on suspicious activity patterns.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Intrusion_Detection_Not_Configured Class
 *
 * Detects when no intrusion detection system is monitoring for suspicious
 * activity patterns like SQL injection attempts, file manipulation, or
 * privilege escalation attacks.
 *
 * @since 1.6030.2200
 */
class Diagnostic_Intrusion_Detection_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'intrusion-detection-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Intrusion Detection Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies an intrusion detection system is monitoring for attack patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for pro security module first.
		if ( Upgrade_Path_Helper::has_pro_product( 'security' ) ) {
			return null;
		}

		// Check for common IDS/IPS plugins.
		$ids_plugins = array(
			'wordfence/wordfence.php',                    // Wordfence Security.
			'sucuri-scanner/sucuri.php',                  // Sucuri Security.
			'all-in-one-wp-security-and-firewall/wp-security.php', // AIOSEC.
			'better-wp-security/better-wp-security.php',  // iThemes Security.
			'ninjafirewall/ninjafirewall.php',            // NinjaFirewall.
		);

		$ids_active = false;
		foreach ( $ids_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$ids_active = true;
				break;
			}
		}

		// Check if user has manually configured IDS.
		$manual_ids = get_option( 'wpshadow_intrusion_detection_configured', false );

		if ( $ids_active || $manual_ids ) {
			return null;
		}

		// Check server-level IDS (mod_security, fail2ban).
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules = apache_get_modules();
			if ( in_array( 'mod_security2', $modules, true ) || in_array( 'mod_security', $modules, true ) ) {
				return null; // mod_security provides IDS.
			}
		}

		// Check for fail2ban presence (common on Linux servers).
		if ( file_exists( '/var/log/fail2ban.log' ) || file_exists( '/etc/fail2ban/jail.conf' ) ) {
			return null; // fail2ban is configured.
		}

		// No IDS detected.
		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __(
				'Your site has no intrusion detection system monitoring for attack patterns. Without IDS, you won\'t know when attackers are probing for vulnerabilities, attempting SQL injection, or trying to upload malicious files. By the time you notice (days or weeks later), the damage is done. Average undetected breach duration: 197 days (IBM Security). IDS detects: brute-force attacks, SQL injection attempts, file integrity violations, privilege escalation, suspicious database queries, malware uploads.',
				'wpshadow'
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'manual-intrusion-detection-setup',
		);

		// Add upgrade path for WPShadow Pro Security (when available).
		$finding = Upgrade_Path_Helper::add_upgrade_path(
			$finding,
			'security',
			'intrusion-detection',
			'intrusion-detection-manual-setup'
		);

		return $finding;
	}
}
