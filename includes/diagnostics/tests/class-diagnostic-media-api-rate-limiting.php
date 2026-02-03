<?php
/**
 * Media API Rate Limiting Diagnostic
 *
 * Detects if media API endpoints have proper rate limiting controls.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.1635
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Media_API_Rate_Limiting Class
 *
 * Tests if REST API media endpoints implement proper rate limiting to prevent
 * abuse, DoS attacks, and excessive resource consumption.
 *
 * @since 1.26033.1635
 */
class Diagnostic_Media_API_Rate_Limiting extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-api-rate-limiting';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media API Rate Limiting';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies media API endpoints have proper rate limiting';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.26033.1635
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! rest_is_enabled() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API is disabled. Enable it to implement rate limiting.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-api-rate-limiting',
			);
		}

		// Check if rate limiting plugin is active
		$rate_limiting_plugins = array(
			'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
			'ninja-firewall/ninja-firewall.php',
			'wordfence/wordfence.php',
			'all-in-one-wp-security-and-firewall/wp-security-and-firewall.php',
		);

		$has_rate_limiting = false;
		foreach ( $rate_limiting_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_rate_limiting = true;
				break;
			}
		}

		// Check for WordPress native rate limiting (WP 6.2+)
		global $wp_version;
		if ( version_compare( $wp_version, '6.2', '>=' ) && function_exists( 'wp_is_request_rate_limited' ) ) {
			$has_rate_limiting = true;
		}

		if ( ! $has_rate_limiting ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No rate limiting is configured for API endpoints. Install a security plugin to add rate limiting.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/media-api-rate-limiting',
			);
		}

		return null;
	}
}
