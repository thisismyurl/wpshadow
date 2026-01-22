<?php declare(strict_types=1);
/**
 * Webhook URL Validation Diagnostic
 *
 * Philosophy: Integration security - validate webhook destinations
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check if webhook URLs are validated.
 */
class Diagnostic_Webhook_URL_Validation {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
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
}
