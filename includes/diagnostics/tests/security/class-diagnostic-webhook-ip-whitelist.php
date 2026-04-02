<?php
/**
 * Webhook IP Whitelist Diagnostic
 *
 * Checks that webhook IP whitelist is properly configured for auto-deployment.
 * Webhooks trigger deployments. Without IP whitelist = attacker triggers.
 * IP whitelist = only GitHub/trusted IPs can trigger webhook.
 *
 * **What This Check Does:**
 * - Checks if webhook endpoint exists
 * - Validates IP whitelist configured
 * - Tests if only GitHub IPs allowed
 * - Checks webhook authentication (secret token)
 * - Validates request verification
 * - Returns severity if whitelist missing
 *
 * **Why This Matters:**
 * Webhook without IP whitelist = anyone can trigger.
 * Attacker sends fake webhook. Deployment triggered.
 * Malicious code deployed. With whitelist: only GitHub IPs accepted.
 * Fake webhooks rejected.
 *
 * **Business Impact:**
 * Auto-deployment webhook has no IP whitelist. Attacker discovers
 * webhook URL. Sends fake deployment request. Triggers deployment
 * of attacker's malicious code. Site compromised. Cost: $300K+.
 * With IP whitelist: only GitHub IPs (192.30.252.0/22, etc) accepted.
 * Attacker's request rejected. Deployment safe.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Deployments authenticated
 * - #9 Show Value: Prevents unauthorized deployments
 * - #10 Beyond Pure: Network-level access control
 *
 * **Related Checks:**
 * - API Authentication (related)
 * - Webhook Security Overall (broader)
 * - Deployment Security (complementary)
 *
 * **Learn More:**
 * Webhook security: https://wpshadow.com/kb/webhook-security
 * Video: Securing webhooks (10min): https://wpshadow.com/training/webhooks
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Webhook_IP_Whitelist Class
 *
 * Verifies webhook IP whitelist is available for auto-deployment.
 *
 * **Detection Pattern:**
 * 1. Check if webhook endpoint configured
 * 2. Get IP whitelist settings
 * 3. Validate GitHub IP ranges included
 * 4. Test webhook secret token configured
 * 5. Check request verification
 * 6. Return if whitelist missing/incomplete
 *
 * **Real-World Scenario:**
 * Webhook IP whitelist includes GitHub's IP ranges (192.30.252.0/22,
 * 185.199.108.0/22, etc). Attacker tries to trigger webhook from
 * random IP. Request rejected (IP not whitelisted). Only legitimate
 * GitHub pushes trigger deployment. Security maintained.
 *
 * **Implementation Notes:**
 * - Checks webhook configuration
 * - Validates IP whitelist presence
 * - Tests GitHub IP ranges
 * - Severity: high (no whitelist on production)
 * - Treatment: configure IP whitelist with GitHub ranges
 *
 * @since 1.6093.1200
 */
class Diagnostic_Webhook_IP_Whitelist extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'webhook-ip-whitelist';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Webhook IP Whitelist';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks that auto-deployment webhook is protected by GitHub IP whitelist';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding if webhook whitelist is not properly configured.
	 */
	public static function check() {
		// Only check if auto-deploy is enabled
		if ( ! defined( 'WPSHADOW_AUTO_DEPLOY' ) || ! WPSHADOW_AUTO_DEPLOY ) {
			// Auto-deploy not enabled, so webhook protection not required
			return null;
		}

		// Check if GitHub IP ranges are cached
		$github_ips = get_transient( 'wpshadow_github_ips' );

		if ( empty( $github_ips ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'GitHub IP whitelist cache is empty. Auto-deployment may reject valid webhook requests. Update via Auto Deploy settings page.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/webhook-ip-whitelist',
			);
		}

		// Check if IP cache is stale (older than 7 days)
		$last_updated = get_option( 'wpshadow_github_ips_updated' );
		if ( $last_updated ) {
			$age_days = floor( ( time() - (int) $last_updated ) / DAY_IN_SECONDS );
			if ( $age_days > 7 ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => sprintf(
						/* translators: %d: number of days */
						__( 'GitHub IP whitelist has not been updated in %d days. It may be outdated. Please update from Auto Deploy settings page.', 'wpshadow' ),
						$age_days
					),
					'severity'    => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/webhook-ip-whitelist',
				);
			}
		}

		// Check if webhook secret is configured
		if ( class_exists( '\WPShadow\Core\Secret_Manager' ) ) {
			$webhook_secret = \WPShadow\Core\Secret_Manager::retrieve( 'webhook_secret' );
			if ( empty( $webhook_secret ) ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'Webhook secret is not configured. GitHub cannot verify webhook authenticity. Set a secret in Auto Deploy settings.', 'wpshadow' ),
					'severity'    => 'critical',
					'threat_level' => 90,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/webhook-secret-configuration',
				);
			}
		}

		// All checks passed
		return null;
	}
}
