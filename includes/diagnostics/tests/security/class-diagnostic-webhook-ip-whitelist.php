<?php
/**
 * Webhook IP Whitelist Diagnostic
 *
 * Checks that webhook IP whitelist is properly configured for auto-deployment.
 *
 * @since   1.26032.1000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Webhook_IP_Whitelist Class
 *
 * Verifies webhook IP whitelist is available for auto-deployment.
 *
 * @since 1.26032.1000
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
	 * @since  1.26032.1000
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
