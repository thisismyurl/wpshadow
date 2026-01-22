<?php declare(strict_types=1);
/**
 * DNS Filtering Protection Diagnostic
 *
 * Philosophy: Network security - DNS-level filtering
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if DNS filtering is configured.
 */
class Diagnostic_DNS_Filtering_Protection {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		$dns_filtered = get_option( 'wpshadow_dns_filtering_enabled' );
		
		if ( empty( $dns_filtered ) ) {
			return array(
				'id'          => 'dns-filtering-protection',
				'title'       => 'No DNS Filtering Protection',
				'description' => 'DNS filtering not configured. Malware/phishing domains accessed without blocking. Use DNS security service (Cloudflare, Quad9) to block malicious domains.',
				'severity'    => 'medium',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/enable-dns-filtering/',
				'training_link' => 'https://wpshadow.com/training/dns-security/',
				'auto_fixable' => false,
				'threat_level' => 60,
			);
		}
		
		return null;
	}
}
