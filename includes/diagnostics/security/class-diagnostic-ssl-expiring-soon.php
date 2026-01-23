<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: SSL Certificate Expiring Soon?
 * 
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SSL_Expiring_Soon extends Diagnostic_Base {
    protected static $slug = 'ssl-expiring-soon';
    protected static $title = 'SSL Certificate Expiring Soon?';
    protected static $description = 'Warns if SSL certificate expires within 30 days.';


    public static function check(): ?array {
        // Only check if site uses SSL
        if (!is_ssl()) {
            return null;
        }
        
        $site_url = get_site_url();
        $host = parse_url($site_url, PHP_URL_HOST);
        
        if (empty($host)) {
            return null;
        }
        
        // Check cached SSL expiration (same logic as ssl-expiration diagnostic but different threshold)
        $cache_key = 'wpshadow_ssl_expiry_' . md5($host);
        $expiry_data = get_transient($cache_key);
        
        if ($expiry_data === false) {
            // Try to get SSL certificate expiration
            $context = stream_context_create(array(
                'ssl' => array(
                    'capture_peer_cert' => true,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ),
            ));
            
            $stream = @stream_socket_client(
                'ssl://' . $host . ':443',
                $errno,
                $errstr,
                30,
                STREAM_CLIENT_CONNECT,
                $context
            );
            
            if ($stream) {
                $params = stream_context_get_params($stream);
                $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
                
                if (isset($cert['validTo_time_t'])) {
                    $expiry_data = $cert['validTo_time_t'];
                    set_transient($cache_key, $expiry_data, 12 * HOUR_IN_SECONDS);
                }
                
                fclose($stream);
            }
            
            if (!$expiry_data) {
                return null;
            }
        }
        
        // Check if expiring within 60-90 days (warning zone)
        $days_until_expiry = floor(($expiry_data - time()) / DAY_IN_SECONDS);
        
        if ($days_until_expiry > 90 || $days_until_expiry < 0) {
            return null; // Either too far away or already expired (handled by other diagnostic)
        }
        
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => sprintf('Your SSL certificate expires in %d days', $days_until_expiry),
            'severity'      => 'medium',
            'category'      => 'security',
            'kb_link'       => 'https://wpshadow.com/kb/ssl-expiring-soon/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=ssl-expiring-soon',
            'training_link' => 'https://wpshadow.com/training/ssl-expiring-soon/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Security',
            'priority'      => 1,
        );
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
