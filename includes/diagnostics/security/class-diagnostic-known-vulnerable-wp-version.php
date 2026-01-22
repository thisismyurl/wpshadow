<?php
declare(strict_types=1);
/**
 * Known Vulnerable WordPress Version Diagnostic
 *
 * Philosophy: Core security - track known CVEs
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if WordPress version has known vulnerabilities.
 */
class Diagnostic_Known_Vulnerable_WP_Version extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		global $wp_version;
		
		// Sample list - in production would use CVE database
		$vulnerable_versions = array(
			'5.7' => array( 'CVE-2021-24405', 'CVE-2021-24406' ),
			'5.6' => array( 'CVE-2021-24405', 'CVE-2021-21345' ),
			'5.5' => array( 'CVE-2021-21345', 'CVE-2020-12447' ),
		);
		
		foreach ( $vulnerable_versions as $vuln_version => $cves ) {
			if ( strpos( $wp_version, $vuln_version ) === 0 ) {
				return array(
					'id'          => 'known-vulnerable-wp-version',
					'title'       => 'Known Security Vulnerabilities in WordPress Version',
					'description' => sprintf(
						'WordPress %s has %d known public CVEs: %s. Update to latest stable version immediately.',
						$wp_version,
						count( $cves ),
						implode( ', ', $cves )
					),
					'severity'    => 'critical',
					'category'    => 'security',
					'kb_link'     => 'https://wpshadow.com/kb/wordpress-security-updates/',
					'training_link' => 'https://wpshadow.com/training/core-updates/',
					'auto_fixable' => false,
					'threat_level' => 85,
				);
			}
		}
		
		return null;
	}
}
