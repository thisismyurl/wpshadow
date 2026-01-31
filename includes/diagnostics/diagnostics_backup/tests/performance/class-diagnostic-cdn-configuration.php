<?php
/**
 * CDN Configuration Diagnostic
 *
 * Verifies Content Delivery Network (CDN) is configured to serve
 * static assets faster by distributing them globally.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_CDN_Configuration Class
 *
 * Checks if CDN is properly configured.
 *
 * @since 1.2601.2148
 */
class Diagnostic_CDN_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cdn-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CDN Configuration Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies CDN is configured for static assets';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if CDN not configured, null otherwise.
	 */
	public static function check() {
		$cdn_status = self::check_cdn_status();

		if ( $cdn_status['configured'] ) {
			return null; // CDN is active
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'CDN not configured. Visitors far from server experience 200-500ms slower page loads. Global traffic suffering poor performance.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 60,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/cdn-setup',
			'family'       => self::$family,
			'meta'         => array(
				'cdn_configured'       => false,
				'performance_impact'   => __( 'International visitors 2-5x slower' ),
				'bandwidth_savings'    => __( '40-60% reduction with CDN' ),
				'setup_difficulty'     => __( 'Easy (15-30 minutes)' ),
			),
			'details'      => array(
				'how_cdn_works'           => array(
					__( 'CDN stores copies of static files (images, CSS, JS) on edge servers globally' ),
					__( 'Visitor requests served from nearest geographic location' ),
					__( 'Your origin server only handles dynamic content' ),
					__( 'Reduces latency by 200-500ms for distant visitors' ),
				),
				'performance_benefits'    => array(
					'Page Load Speed' => '30-50% faster globally',
					'Server Load' => '40-60% reduction (CDN handles static)',
					'Bandwidth Costs' => '30-50% savings',
					'Uptime' => 'Better (multiple edge servers)',
					'SEO' => 'Better rankings (faster = higher rank)',
				),
				'cdn_providers'           => array(
					'Cloudflare (Free)' => array(
						'Free plan includes CDN',
						'Global edge network',
						'DDoS protection included',
						'Easy DNS setup',
						'Best for: Most sites',
					),
					'BunnyCDN ($1-10/month)' => array(
						'Pay-per-use pricing',
						'Very fast performance',
						'Video streaming support',
						'Best for: High-traffic sites',
					),
					'Amazon CloudFront' => array(
						'AWS integration',
						'Pay-per-use',
						'Enterprise-grade',
						'Best for: AWS-hosted sites',
					),
					'KeyCDN ($4-40/month)' => array(
						'Developer-friendly',
						'HTTP/2 support',
						'Real-time analytics',
						'Best for: Tech-savvy users',
					),
				),
				'setup_cloudflare_free'   => array(
					'Step 1: Sign up at cloudflare.com (free)',
					'Step 2: Add your domain',
					'Step 3: Cloudflare shows nameserver changes',
					'Step 4: Update nameservers at domain registrar',
					'Step 5: Wait 24 hours for DNS propagation',
					'Step 6: Enable "Auto Minify" in Speed settings',
					'Step 7: Set cache level to "Standard"',
					'Step 8: Test with GTmetrix/Pingdom',
				),
				'wordpress_cdn_plugins'   => array(
					'WP Rocket (Premium)' => array(
						'Built-in CDN integration',
						'One-click Cloudflare setup',
						'Automatic asset rewriting',
						'Cost: $50-250/year',
					),
					'CDN Enabler (Free)' => array(
						'Simple CDN URL rewriting',
						'Works with any CDN',
						'Minimal configuration',
					),
				),
				'testing_cdn'             => array(
					'Method 1: Check Image URL' => array(
						'View page source (Ctrl+U)',
						'Find <img> tag',
						'URL should be: cdn.yoursite.com or cloudflare URL',
						'Not: yoursite.com',
					),
					'Method 2: Network Tab' => array(
						'Open DevTools → Network',
						'Load page',
						'Images should show CDN server in headers',
						'Look for "cf-cache-status: HIT" (Cloudflare)',
					),
					'Method 3: Online Tools' => array(
						'GTmetrix: Shows CDN in waterfall chart',
						'Pingdom: Displays CDN hostnames',
						'WebPageTest: Multi-location testing',
					),
				),
				'common_cdn_issues'       => array(
					'Mixed Content (HTTPS)' => array(
						'Problem: CDN serves HTTP, site is HTTPS',
						'Fix: Enable SSL/TLS in CDN settings',
						'Cloudflare: Crypto → SSL → Full',
					),
					'Cache Not Purging' => array(
						'Problem: Old versions served after update',
						'Fix: Purge CDN cache manually',
						'Or: Use plugin with auto-purge',
					),
					'Plugin Conflicts' => array(
						'Problem: CDN URLs break dynamic content',
						'Fix: Exclude admin/login URLs from CDN',
					),
				),
			),
		);
	}

	/**
	 * Check CDN status.
	 *
	 * @since  1.2601.2148
	 * @return array CDN configuration status.
	 */
	private static function check_cdn_status() {
		// Check if CDN plugin active
		$cdn_plugins = array(
			'wp-rocket/wp-rocket.php',
			'cdn-enabler/cdn-enabler.php',
			'w3-total-cache/w3-total-cache.php',
		);

		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return array( 'configured' => true );
			}
		}

		// Check if Cloudflare detected
		$home_url = home_url();
		$response = wp_remote_head( $home_url );
		if ( ! is_wp_error( $response ) ) {
			$headers = wp_remote_retrieve_headers( $response );
			if ( isset( $headers['server'] ) && strpos( strtolower( $headers['server'] ), 'cloudflare' ) !== false ) {
				return array( 'configured' => true );
			}
		}

		return array( 'configured' => false );
	}
}
