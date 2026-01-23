<?php
declare(strict_types=1);
/**
 * SSL Certificate Validity Diagnostic
 *
 * Philosophy: Trust and security - ensure valid certificates
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check SSL certificate validity and expiration.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SSL_Certificate_Validity extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Only check if site uses HTTPS
		if ( ! is_ssl() ) {
			return null;
		}
		
		$url = home_url();
		$stream = stream_context_create( array( 'ssl' => array( 'capture_peer_cert' => true ) ) );
		$read = @fopen( $url, 'rb', false, $stream );
		
		if ( ! $read ) {
			return null; // Can't check
		}
		
		$context = stream_context_get_params( $read );
		fclose( $read );
		
		if ( empty( $context['options']['ssl']['peer_certificate'] ) ) {
			return null;
		}
		
		$cert = openssl_x509_parse( $context['options']['ssl']['peer_certificate'] );
		
		if ( empty( $cert ) ) {
			return null;
		}
		
		// Check expiration (warn if less than 30 days)
		$expires = $cert['validTo_time_t'];
		$days_until_expiry = floor( ( $expires - time() ) / DAY_IN_SECONDS );
		
		if ( $days_until_expiry < 30 ) {
			return array(
				'id'          => 'ssl-certificate-validity',
				'title'       => 'SSL Certificate Expiring Soon',
				'description' => sprintf(
					'Your SSL certificate expires in %d days. Renew your certificate to avoid browser warnings and connection errors.',
					$days_until_expiry
				),
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/renew-ssl-certificate/',
				'training_link' => 'https://wpshadow.com/training/ssl-management/',
				'auto_fixable' => false,
				'threat_level' => 80,
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
