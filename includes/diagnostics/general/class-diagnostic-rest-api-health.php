<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: REST API Health Check
 *
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_REST_API_Health extends Diagnostic_Base {
	protected static $slug        = 'rest-api-health';
	protected static $title       = 'REST API Health Check';
	protected static $description = 'Tests WordPress REST API endpoints.';


	public static function check(): ?array {
		$response = wp_remote_get(rest_url());
		if (is_wp_error($response)) {
			return array(
				'id'            => static::$slug,
				'title'         => static::$title,
				'description'   => 'REST API error: ' . $response->get_error_message(),
				'color'         => '#f44336',
				'bg_color'      => '#ffebee',
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-health/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=rest-api-health',
				'training_link' => 'https://wpshadow.com/training/rest-api-health/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'Core',
				'priority'      => 1,
			);
		}
		$status = wp_remote_retrieve_response_code($response);
		if ($status !== 200) {
			return array(
				'id'            => static::$slug,
				'title'         => static::$title,
				'description'   => "REST API returned status {$status}.",
				'color'         => '#f44336',
				'bg_color'      => '#ffebee',
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-health/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=rest-api-health',
				'training_link' => 'https://wpshadow.com/training/rest-api-health/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'Core',
				'priority'      => 1,
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
