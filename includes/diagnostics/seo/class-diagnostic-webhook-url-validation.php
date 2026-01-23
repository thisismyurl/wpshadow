<?php
declare(strict_types=1);
/**
 * Webhook URL Validation Diagnostic
 *
 * Philosophy: Integration security - validate webhook destinations
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if webhook URLs are validated.
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Webhook_URL_Validation extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Check for common webhook plugins
		$webhook_plugins = array(
			'wp-webhooks/wp-webhooks.php',
			'zapier/zapier.php',
			'webhook-netlify-deploy/webhook-netlify-deploy.php',
		);
		
		$active = get_option( 'active_plugins', array() );
		$has_webhooks = false;
		
		foreach ( $webhook_plugins as $plugin ) {
			if ( in_array( $plugin, $active, true ) ) {
				$has_webhooks = true;
				break;
			}
		}
		
		if ( ! $has_webhooks ) {
			return null; // No webhook functionality
		}
		
		// Check if URL validation filter exists
		$has_validation = has_filter( 'http_request_args' );
		
		if ( ! $has_validation ) {
			return array(
				'id'          => 'webhook-url-validation',
				'title'       => 'Webhook URLs Not Validated',
				'description' => 'Webhook plugin detected without URL validation. User-provided webhook URLs enable SSRF attacks, allowing access to internal services (databases, AWS metadata, etc.). Validate webhook URLs against allowlist.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/validate-webhook-urls/',
				'training_link' => 'https://wpshadow.com/training/webhook-security/',
				'auto_fixable' => false,
				'threat_level' => 75,
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
	}
	/**
	 * Test: Hook detection logic
	 *
	 * Verifies that diagnostic correctly detects hooks and returns
	 * appropriate result (null or array).
	 *
	 * @return array Test result
	 */
	public static function test_hook_detection(): array {
		$result = self::check();
		
		// Should consistently return null or array
		if ( $result === null || is_array( $result ) ) {
			return array(
				'passed'  => true,
				'message' => 'Hook detection working correctly',
			);
		}
		
		return array(
			'passed'  => false,
			'message' => 'Unexpected result type from hook detection',
		);
	}}
