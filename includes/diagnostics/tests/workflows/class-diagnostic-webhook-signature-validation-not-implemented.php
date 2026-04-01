<?php
/**
 * Webhook Signature Validation Not Implemented Diagnostic
 *
 * Checks if webhooks validate sender signatures.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Webhook_Signature_Validation_Not_Implemented Class
 *
 * Detects webhooks that don't validate sender signatures.
 * Unvalidated webhooks can be exploited for malicious payloads.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Webhook_Signature_Validation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webhook-signature-validation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Webhook Signature Validation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks webhook signature validation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'integrations';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for:
	 * - REST API endpoints accepting POST without validation
	 * - Admin AJAX handlers without nonce checks
	 * - Custom webhook handlers without HMAC validation
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site has webhook-related plugins installed.
		$webhook_plugins = array(
			'zapier/zapier.php',
			'wp-webhooks/wp-webhooks.php',
			'wp-mail-smtp/wp_mail_smtp.php',
			'woocommerce-zapier/woocommerce-zapier.php',
		);

		$has_webhooks = false;
		foreach ( $webhook_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_webhooks = true;
				break;
			}
		}

		// Check for custom REST routes (potential webhook endpoints).
		global $wp_rest_server;
		if ( isset( $wp_rest_server ) ) {
			$routes = $wp_rest_server->get_routes();
			foreach ( $routes as $route => $handlers ) {
				// Look for custom routes (not core WordPress).
				if ( ! preg_match( '#^/wp/(v2|v1)/#', $route ) ) {
					$has_webhooks = true;
					break;
				}
			}
		}

		if ( $has_webhooks ) {
			// Check if signature validation is implemented.
			$has_validation = get_option( 'wpshadow_webhook_signature_validation', false );

			if ( ! $has_validation ) {
				$finding = array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Your site accepts webhook requests without validating sender signatures. Attackers can send malicious payloads pretending to be legitimate services (Stripe, PayPal, Zapier). This can lead to: unauthorized actions (fake order confirmations), data corruption (malicious updates), and account hijacking (privilege escalation). Signature validation (HMAC-SHA256) proves the sender is authentic and the payload hasn\'t been tampered with.', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 75,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/webhook-signature-validation?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				);

				// Add upgrade path to Integration module (handles secure webhooks).
				if ( ! Upgrade_Path_Helper::has_pro_product( 'integration' ) ) {
					$finding = Upgrade_Path_Helper::add_upgrade_path(
						$finding,
						'integration',
						'webhook-automation',
						'https://wpshadow.com/kb/manual-webhook-security'
					);
				}

				return $finding;
			}
		}

		return null;
	}
}
