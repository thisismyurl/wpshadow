<?php
/**
 * Penetration Testing Schedule Not Documented
 *
 * Checks if regular penetration testing is scheduled to identify
 * vulnerabilities before attackers do.
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
 * Diagnostic_Penetration_Testing_Schedule_Not_Documented Class
 *
 * Detects when sites don't have a documented schedule for security
 * penetration testing and vulnerability assessments.
 *
 * @since 1.6030.2200
 */
class Diagnostic_Penetration_Testing_Schedule_Not_Documented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'penetration-testing-schedule-not-documented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Penetration Testing Schedule Not Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies regular penetration testing schedule is documented and followed';

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
		// Check for documented penetration testing schedule.
		$pentest_schedule = get_option( 'wpshadow_penetration_testing_schedule', array() );

		if ( ! empty( $pentest_schedule['documented'] ) && ! empty( $pentest_schedule['last_test_date'] ) ) {
			// Check if last test was within acceptable timeframe (e.g., 12 months).
			$last_test = strtotime( $pentest_schedule['last_test_date'] );
			$now       = time();

			if ( ( $now - $last_test ) <= ( 365 * DAY_IN_SECONDS ) ) {
				return null; // Recent test documented.
			}
		}

		// Check for automated security scanning plugins (not as thorough as pentest but better than nothing).
		$scanning_plugins = array(
			'wordfence/wordfence.php',        // Wordfence Scan.
			'sucuri-scanner/sucuri.php',      // Sucuri Scanner.
			'defender-security/wp-defender.php', // Defender Security.
		);

		$regular_scanning = false;
		foreach ( $scanning_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$regular_scanning = true;
				break;
			}
		}

		// If automated scanning is active, still recommend pentesting but lower severity.
		$severity     = $regular_scanning ? 'medium' : 'high';
		$threat_level = $regular_scanning ? 60 : 70;

		// No penetration testing schedule.
		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __(
				'Your site has no documented penetration testing schedule. Penetration testing simulates real-world attacks to find vulnerabilities before malicious actors do. Automated scanners (like Wordfence) only catch 30-40% of vulnerabilities - they miss: business logic flaws (authentication bypasses), complex injection vectors (blind SQL injection), race conditions, privilege escalation chains, social engineering vectors. Professional penetration testing provides: manual testing by security experts, exploitation of chained vulnerabilities, detailed remediation guidance, compliance reports (PCI DSS requires annual pentests). Recommended frequency: Quarterly for eCommerce/high-value sites, annually for standard business sites, after major code changes or plugin updates. Average cost of penetration test: $3,000-10,000. Average cost of data breach: $4.24 million (IBM). Testing is 1,000x cheaper than breaches.',
				'wpshadow'
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'penetration-testing-schedule-guide',
			'details'      => array(
				'has_automated_scanning' => $regular_scanning,
				'last_documented_test'   => $pentest_schedule['last_test_date'] ?? __( 'Never', 'wpshadow' ),
			),
		);

		// Add upgrade path for WPShadow Pro Security (when available).
		$finding = Upgrade_Path_Helper::add_upgrade_path(
			$finding,
			'security',
			'penetration-testing',
			'pentest-vendors-guide'
		);

		return $finding;
	}
}
