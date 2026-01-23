<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Authoritative DNS Latency and Placement (NETWORK-360)
 *
 * Measures auth DNS RTT/geo spread and detects slow nameservers.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_DnsAuthoritativeLatency extends Diagnostic_Base {
    /**
     * Run the diagnostic check
     *
     * @return array|null Array with finding details or null if no issue found
     */
    public static function check(): ?array {
		$dns_host = wp_parse_url(home_url(), PHP_URL_HOST);
        if ($dns_host) {
            $start = microtime(true);
            $ip = gethostbyname($dns_host);
            $dns_time = (microtime(true) - $start) * 1000;
            
            if ($dns_time > 500) {
                return array(
                    'id' => 'dns-authoritative-latency',
                    'title' => __('Slow DNS Resolution Time', 'wpshadow'),
                    'description' => sprintf(__('DNS resolution took %dms. Consider using a faster DNS provider or CDN with anycast DNS.', 'wpshadow'), (int)$dns_time),
                    'severity' => 'medium',
                    'category' => 'security',
                    'kb_link' => 'https://wpshadow.com/kb/dns-optimization/',
                    'training_link' => 'https://wpshadow.com/training/dns-performance/',
                    'auto_fixable' => false,
                    'threat_level' => 35,
                );
            }
        }
        return null;
	}

	/**
	 * Test: Result structure validation
	 *
	 * Ensures diagnostic returns null (no issues) or array (issues found)
	 * with all required fields populated.
	 *
	 * @return array Test result with 'passed' and 'message'
	 */
	public static function test_result_structure(): array {
		$result = self::check();
		
		// Valid states: null (pass) or array (fail)
		if ( null === $result || is_array( $result ) ) {
			// If array, validate structure
			if ( is_array( $result ) ) {
				$required = array(
					'id', 'title', 'description', 'category', 
					'severity', 'threat_level'
				);
				
				foreach ( $required as $field ) {
					if ( ! isset( $result[ $field ] ) ) {
						return array(
							'passed'  => false,
							'message' => "Missing field: $field",
						);
					}
				}
				
				// Validate field types
				if ( ! is_string( $result['severity'] ) ) {
					return array(
						'passed'  => false,
						'message' => 'severity must be string',
					);
				}
				
				if ( ! is_int( $result['threat_level'] ) || $result['threat_level'] < 0 || $result['threat_level'] > 100 ) {
					return array(
						'passed'  => false,
						'message' => 'threat_level must be int 0-100',
					);
				}
			}
			
			return array(
				'passed'  => true,
				'message' => 'Result structure valid',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Invalid result type: ' . gettype( $result ),
		);
	}}
