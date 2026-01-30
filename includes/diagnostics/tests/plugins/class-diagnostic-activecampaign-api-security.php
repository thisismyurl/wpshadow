<?php
/**
 * Activecampaign Api Security Diagnostic
 *
 * Activecampaign Api Security configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.727.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Activecampaign Api Security Diagnostic Class
 *
 * @since 1.727.0000
 */
class Diagnostic_ActivecampaignApiSecurity extends Diagnostic_Base {

	protected static $slug = 'activecampaign-api-security';
	protected static $title = 'Activecampaign Api Security';
	protected static $description = 'Activecampaign Api Security configuration issues';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'ActiveCampaign' ) && ! defined( 'ACTIVECAMPAIGN_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: API key storage location.
		$api_key = get_option( 'activecampaign_api_key', '' );
		if ( ! empty( $api_key ) && ! defined( 'ACTIVECAMPAIGN_API_KEY' ) ) {
			$issues[] = 'API key stored in database (use wp-config.php constants)';
		}

		// Check 2: API URL validation.
		$api_url = get_option( 'activecampaign_api_url', '' );
		if ( ! empty( $api_url ) && ! filter_var( $api_url, FILTER_VALIDATE_URL ) ) {
			$issues[] = 'invalid API URL configured';
		} elseif ( ! empty( $api_url ) && 0 !== strpos( $api_url, 'https://' ) ) {
			$issues[] = 'API URL not using HTTPS (insecure connection)';
		}

		// Check 3: Failed API requests logged.
		global $wpdb;
		$failed_requests = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE 'activecampaign_api_error_%'"
		);
		if ( $failed_requests > 10 ) {
			$issues[] = "{$failed_requests} failed API requests logged (check credentials)";
		}

		// Check 4: API rate limiting.
		$rate_limit = get_option( 'activecampaign_rate_limit', '0' );
		if ( '0' === $rate_limit ) {
			$issues[] = 'API rate limiting not enabled (risk of account suspension)';
		}

		// Check 5: Webhook security.
		$webhook_secret = get_option( 'activecampaign_webhook_secret', '' );
		if ( empty( $webhook_secret ) ) {
			$issues[] = 'webhook secret not configured (accepts unverified data)';
		}

		// Check 6: API key permissions.
		$user_roles = wp_roles()->get_names();
		foreach ( $user_roles as $role => $name ) {
			$role_obj = get_role( $role );
			if ( $role_obj && $role_obj->has_cap( 'manage_activecampaign' ) && 'administrator' !== $role ) {
				$issues[] = "non-admin role '{$name}' has ActiveCampaign management access";
				break;
			}
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'ActiveCampaign API security issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/activecampaign-api-security',
			);
		}

		return null;
	}
}
