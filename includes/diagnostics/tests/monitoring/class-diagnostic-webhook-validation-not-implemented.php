<?php
/**
 * Webhook Validation Not Implemented Diagnostic
 *
 * Checks webhook validation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Webhook_Validation_Not_Implemented Class
 *
 * Performs diagnostic check for Webhook Validation Not Implemented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Webhook_Validation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webhook-validation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Webhook Validation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks webhook validation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if webhook validation (HMAC signature verification) is implemented.
		// Webhooks allow external services to send data to your site.
		// Without signature verification, attackers can send spoofed webhooks.

		// Look for common webhook validation implementations.
		$has_webhook_validation = false;

		// Check for custom filters that might validate webhooks.
		if ( has_filter( 'wpshadow_webhook_validate' ) ||
			has_filter( 'wp_webhook_validate' ) ||
			has_filter( 'rest_api_init' ) ) {
			// Some webhook validation framework is likely in place.
			$has_webhook_validation = true;
		}

		// Check if any webhook-related plugins are active.
		$webhook_plugins = array(
			'wp-webhooks/wp-webhooks.php',
			'zapier/zapier.php',
			'ifttt-integration/ifttt.php',
		);

		foreach ( $webhook_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				// Assume plugin handles validation.
				$has_webhook_validation = true;
				break;
			}
		}

		if ( ! $has_webhook_validation ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site does not appear to have webhook signature verification implemented (like accepting letters in the mail without checking if they\'re really from who they claim to be). Without HMAC signature verification, attackers can send fake webhooks to trigger unwanted actions. Always verify webhook signatures using a secret key that only you and the webhook sender know.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/webhook-validation-not-implemented',
				'context'      => array(
					'webhook_validation_found' => false,
					'recommendation'           => 'Use HMAC-SHA256 to verify webhook signatures',
				),
			);
		}

		// Webhook validation appears to be implemented.
		return null;
	}
}
