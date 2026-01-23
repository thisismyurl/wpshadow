<?php
declare(strict_types=1);
/**
 * Subdomain Takeover Risk Diagnostic
 *
 * Philosophy: DNS security - detect dangling records
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for dangling DNS records vulnerable to takeover.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Subdomain_Takeover extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$domain = wp_parse_url( home_url(), PHP_URL_HOST );

		// Get all DNS records
		$dns_records = @dns_get_record( $domain, DNS_CNAME + DNS_A );

		if ( empty( $dns_records ) ) {
			return null;
		}

		$vulnerable_patterns = array(
			'cloudfront.net'    => 'AWS CloudFront',
			'azurewebsites.net' => 'Azure',
			's3.amazonaws.com'  => 'AWS S3',
			'herokuapp.com'     => 'Heroku',
			'github.io'         => 'GitHub Pages',
			'wordpress.com'     => 'WordPress.com',
			'zendesk.com'       => 'Zendesk',
			'unbounce.com'      => 'Unbounce',
		);

		$vulnerable_subdomains = array();

		foreach ( $dns_records as $record ) {
			if ( isset( $record['target'] ) ) {
				$target = $record['target'];
				foreach ( $vulnerable_patterns as $pattern => $service ) {
					if ( strpos( $target, $pattern ) !== false ) {
						// Test if target responds with error (indicating takeover risk)
						$test_url = 'http://' . $record['host'];
						$response = wp_remote_get(
							$test_url,
							array(
								'timeout'   => 5,
								'sslverify' => false,
							)
						);

						if ( ! is_wp_error( $response ) ) {
							$body   = wp_remote_retrieve_body( $response );
							$status = wp_remote_retrieve_response_code( $response );

							// Check for service-specific error messages
							if ( $status === 404 ||
								strpos( $body, 'There isn\'t a GitHub Pages site here' ) !== false ||
								strpos( $body, 'NoSuchBucket' ) !== false ||
								strpos( $body, 'ERROR: The request could not be satisfied' ) !== false ) {
								$vulnerable_subdomains[] = sprintf( '%s (points to %s)', $record['host'], $service );
							}
						}
					}
				}
			}
		}

		if ( ! empty( $vulnerable_subdomains ) ) {
			return array(
				'id'            => 'subdomain-takeover',
				'title'         => 'Subdomain Takeover Risk Detected',
				'description'   => sprintf(
					'Dangling DNS records found that could be taken over by attackers: %s. Remove unused DNS records or reclaim the resources they point to.',
					implode( ', ', $vulnerable_subdomains )
				),
				'severity'      => 'high',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/prevent-subdomain-takeover/',
				'training_link' => 'https://wpshadow.com/training/dns-security/',
				'auto_fixable'  => false,
				'threat_level'  => 75,
			);
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
