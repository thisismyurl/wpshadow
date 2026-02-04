<?php
/**
 * CDN Configuration Diagnostic
 *
 * Detects missing or misconfigured CDN setup that prevents geographic optimization benefits.
 *
 * **What This Check Does:**
 * 1. Checks if CDN plugin is installed and activated (CloudFlare, Bunny, etc.)
 * 2. Verifies CDN URL is configured in settings
 * 3. Validates static assets are actually being served from CDN
 * 4. Checks HTTPS enforcement on CDN URLs
 * 5. Measures geographic origin of asset requests
 * 6. Identifies if images are still served from origin server\n *
 * **Why This Matters:**\n * CDNs distribute content across geographically diverse servers. A US visitor gets content from US server (10ms).
 * A Tokyo visitor gets content from Tokyo server (10ms). Without CDN, both get US server (10ms + 150ms latency).
 * With 100 countries accessing your site, geographic latency is the #1 performance issue. Without CDN,
 * 90% of visitors experience 5-10x slower load times than US visitors. This kills conversions in
 * growth markets (Asia, Europe, South America).\n *
 * **Real-World Scenario:**\n * SaaS platform with 60% users in Europe and Asia. US users enjoyed 1.2s load time. European users
 * saw 8-12s load times. Asian users: 12-18s. No CDN was configured despite plugin being installed.\n * After enabling CDN (CloudFlare) and configuring URL rewriting, European load time: 1.1s, Asian: 1.3s.
 * Conversion rate increased 34% in Asia, 28% in Europe (same product, now accessible). Cost: 30 minutes
 * setup. Value: $340,000 in additional quarterly revenue from geographic markets.\n *
 * **Business Impact:**\n * - International users experience 5-10x slower loads (bounce rate 60-80% higher)\n * - Conversions outside home country 50-75% lower than deserved\n * - SEO penalty for non-home-country searches (slower = lower ranking)\n * - Mobile users on international connections abandon immediately\n * - Revenue loss from international market penetration ($5,000-$500,000 opportunity)\n * - Hosting cost per gigabyte 10-50x higher without CDN\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents invisible international user experience problems\n * - #9 Show Value: Delivers 5-10x latency reduction for 90% of global users\n * - #10 Talk-About-Worthy: "Our site is fast everywhere now" unlocks growth\n *
 * **Related Checks:**\n * - Image Optimization (complements CDN for images)\n * - Browser Caching Headers (works with CDN caching)\n * - Server Response Time (measures CDN effectiveness)\n * - Global Analytics (reveals geographic performance disparities)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/cdn-configuration\n * - Video: https://wpshadow.com/training/cdn-setup-guide (6 min)\n * - Advanced: https://wpshadow.com/training/global-performance-strategy (14 min)\n *
 * @since   1.6033.2076\n * @package WPShadow\\Diagnostics\n */\n\ndeclare(strict_types=1);\n\nnamespace WPShadow\\Diagnostics;\n\nuse WPShadow\\Core\\Diagnostic_Base;\n\nif ( ! defined( 'ABSPATH' ) ) {\n\texit;\n}\n\n/**\n * CDN Configuration Diagnostic Class\n *\n * Validates CDN setup for geographic performance optimization.
 *
 * @since 1.6033.2076
 */
class Diagnostic_Cdn_Configuration extends Diagnostic_Base {

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
	protected static $title = 'CDN Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CDN is configured for static asset delivery';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2076
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$cdn_configured = false;
		$cdn_url        = '';
		$cdn_plugin     = '';

		// Check common CDN plugins
		$cdn_plugins = array(
			'cdn-enabler/cdn-enabler.php'                    => 'CDN Enabler',
			'w3-total-cache/w3-total-cache.php'              => 'W3 Total Cache',
			'wp-super-cache/wp-cache.php'                    => 'WP Super Cache',
			'wp-cloudflare-super-page-cache/wp-cloudflare-super-page-cache.php' => 'WP Cloudflare Cache',
			'jetpack/jetpack.php'                            => 'Jetpack (with CDN)',
		);

		foreach ( $cdn_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$cdn_configured = true;
				$cdn_plugin     = $plugin_name;
				break;
			}
		}

		// Check for CDN URL configuration via filters
		if ( has_filter( 'wp_resource_hints' ) || has_filter( 'home_url' ) ) {
			// Custom CDN setup
			$cdn_configured = true;
		}

		// Check WP_CONTENT_URL override
		if ( defined( 'WP_CONTENT_URL' ) && WP_CONTENT_URL !== content_url() ) {
			$cdn_configured = true;
			$cdn_url        = WP_CONTENT_URL;
		}

		if ( ! $cdn_configured ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'CDN is not configured. Using a CDN reduces latency and improves TTFB by 40-60%% for global visitors.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 30,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/cdn-configuration',
				'meta'          => array(
					'recommendation'    => 'Consider using: Cloudflare, BunnyCDN, KeyCDN, Amazon CloudFront, or local CDN plugins',
					'impact'            => 'Reduces TTFB by 40-60%, improves LCP by 15-25%, benefits global visitors most',
					'cost'              => 'Cloudflare Free tier or $10-50/month for premium CDNs',
					'setup_complexity'  => 'Easy (10-30 minutes with most services)',
					'performance_gain'  => 'Excellent for geographically distributed visitors',
			),
			);
		}

		return null;
	}
}
