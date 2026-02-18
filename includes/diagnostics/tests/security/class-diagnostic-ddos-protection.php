<?php
/**
 * DDoS Protection Diagnostic
 *
 * Analyzes DDoS protection and rate limiting configuration.
 *
 * @since   1.6033.2145
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * DDoS Protection Diagnostic
 *
 * Evaluates DDoS protection mechanisms and rate limiting.
 *
 * @since 1.6033.2145
 */
class Diagnostic_DDoS_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ddos-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'DDoS Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes DDoS protection and rate limiting configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2145
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for DDoS protection plugins/services
		$protection_plugins = array(
			'wordfence/wordfence.php'                   => 'Wordfence',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
			'jetpack/jetpack.php'                       => 'Jetpack',
		);

		$active_plugin = null;
		foreach ( $protection_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_plugin = $name;
				break;
			}
		}

		// Check for Cloudflare (common DDoS protection)
		$using_cloudflare = false;
		if ( isset( $_SERVER['HTTP_CF_RAY'] ) || isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$using_cloudflare = true;
		}

		// Check server headers for rate limiting
		$has_rate_limiting = false;
		if ( function_exists( 'apache_response_headers' ) ) {
			$headers = apache_response_headers();
			if ( isset( $headers['X-RateLimit-Limit'] ) || isset( $headers['RateLimit-Limit'] ) ) {
				$has_rate_limiting = true;
			}
		}

		// Estimate site importance
		$post_count = wp_count_posts()->publish ?? 0;
		$is_high_value_target = $post_count > 100;

		// Generate findings if no DDoS protection
		if ( ! $active_plugin && ! $using_cloudflare && $is_high_value_target ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No DDoS protection configured. High-value sites should use Cloudflare or WAF to mitigate attacks.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ddos-protection',
				'meta'         => array(
					'active_plugin'        => $active_plugin,
					'using_cloudflare'     => $using_cloudflare,
					'has_rate_limiting'    => $has_rate_limiting,
					'is_high_value_target' => $is_high_value_target,
					'post_count'           => $post_count,
					'recommendation'       => 'Configure Cloudflare free tier or install security plugin',
					'protection_layers'    => array(
						'Cloudflare (CDN + DDoS protection)',
						'Wordfence (application firewall)',
						'Server-level rate limiting',
						'IP blocking for repeat offenders',
					),
					'attack_types'         => array(
						'Volumetric attacks (bandwidth exhaustion)',
						'Application-layer attacks (resource exhaustion)',
						'Brute force login attempts',
						'XML-RPC amplification',
					),
				),
			);
		}

		// Check for rate limiting
		if ( ! $has_rate_limiting && ! $using_cloudflare ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No rate limiting detected. Implement rate limiting to prevent brute force and resource exhaustion attacks.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ddos-protection',
				'meta'         => array(
					'has_rate_limiting' => $has_rate_limiting,
					'recommendation'    => 'Configure rate limiting at server level or use Cloudflare',
					'typical_limits'    => array(
						'Login attempts: 5 per 15 minutes',
						'API requests: 60 per minute',
						'Page views: 100 per minute per IP',
					),
				),
			);
		}

		return null;
	}
}
