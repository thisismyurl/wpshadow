<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Is SSL Certificate Valid?
 * 
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_SSL_Valid extends Diagnostic_Base {
    protected static $slug = 'ssl-valid';
    protected static $title = 'Is SSL Certificate Valid?';
    protected static $description = 'Checks if your site has a working security certificate.';


    public static function check(): ?array {
        // Only check if site claims to use SSL
        if (!is_ssl()) {
            return null; // Site doesn't use SSL, not applicable
        }
        
        $site_url = get_site_url();
        $host = parse_url($site_url, PHP_URL_HOST);
        
        if (empty($host)) {
            return null;
        }
        
        // Try to verify SSL certificate validity
        $context = stream_context_create(array(
            'ssl' => array(
                'capture_peer_cert' => true,
                'verify_peer' => true,
                'verify_peer_name' => true,
                'allow_self_signed' => false,
            ),
        ));
        
        $stream = @stream_socket_client(
            'ssl://' . $host . ':443',
            $errno,
            $errstr,
            10,
            STREAM_CLIENT_CONNECT,
            $context
        );
        
        if (!$stream) {
            return array(
                'id'            => static::$slug,
                'title'         => 'SSL Certificate Invalid',
                'description'   => sprintf('SSL certificate validation failed: %s', $errstr),
                'severity'      => 'high',
                'category'      => 'security',
                'kb_link'       => 'https://wpshadow.com/kb/ssl-valid/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=ssl-valid',
                'training_link' => 'https://wpshadow.com/training/ssl-valid/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Security',
                'priority'      => 1,
            );
        }
        
        fclose($stream);
        return null; // SSL is valid
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
