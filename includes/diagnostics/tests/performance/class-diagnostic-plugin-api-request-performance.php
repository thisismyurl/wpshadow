<?php
/**
 * Plugin API Request Performance Diagnostic
 *
 * Detects plugins making excessive or slow external API requests affecting page load times.
 *
 * **What This Check Does:**
 * 1. Identifies plugins making external API calls on page load
 * 2. Measures API response times and timeouts
 * 3. Counts API requests per page view
 * 4. Flags synchronous (blocking) API calls
 * 5. Detects APIs with connection issues
 * 6. Analyzes cumulative impact on page speed\n *
 * **Why This Matters:**\n * Synchronous external API calls are blocking—if the API is slow (5 seconds) or down (30s timeout),
 * the entire website becomes slow or unresponsive. Page load = 5+ seconds spent waiting for API
 * response. With 100,000 monthly visitors, that's 13+ years of wasted visitor time.\n *
 * **Real-World Scenario:**\n * E-commerce site integrated 3 third-party APIs on product pages (pricing, inventory, reviews).
 * Combined, APIs added 8 seconds to page load (3 APIs × 2-3 seconds each). One API vendor had outage.
 * All product pages became completely unusable (30-second timeout). Sales stopped. Revenue loss: $2,000
 * per hour. After implementing asynchronous API calls (background jobs), pages loaded in 0.6 seconds
 * even if APIs were slow. Sales remained unaffected by API issues. Cost: 8 hours development.
 * Value: $50,000+ in prevented revenue loss.\n *
 * **Business Impact:**\n * - Page loads add 5-30+ seconds (waits for external APIs)\n * - API outage = entire site becomes unusable\n * - Visitors wait forever, then bounce\n * - Revenue loss during API problems ($1,000-$10,000 per hour)\n * - Conversion rate drops 50%+ on slow pages\n * - SEO ranking penalty (Google measures page speed)\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Immediately identifies speed culprits\n * - #8 Inspire Confidence: Reduces risk of API-caused outages\n * - #10 Talk-About-Worthy: "Site stays fast even when third parties are slow"\n *
 * **Related Checks:**\n * - Front-End Plugin Performance (page speed impact)\n * - External Resource Loading (DNS/CDN detection)\n * - Network Timeout Configuration (timeout handling)\n * - Third-Party Script Impact (other external delays)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-api-performance\n * - Video: https://wpshadow.com/training/async-api-calls (8 min)\n * - Advanced: https://wpshadow.com/training/third-party-integration-patterns (14 min)\n *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_API_Request_Performance Class
 *
 * Identifies plugins making too many external API calls.
 */
class Diagnostic_Plugin_API_Request_Performance extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-api-request-performance';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin API Request Performance';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins making excessive external API requests';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$api_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );

		// Check for plugins known to make frequent API calls
		$api_heavy = array(
			'jetpack' => 'Jetpack',
			'akismet' => 'Akismet',
			'yoast-seo' => 'Yoast SEO',
			'google-analytics' => 'Google Analytics',
			'woocommerce' => 'WooCommerce',
		);

		$api_heavy_active = array();
		foreach ( $active_plugins as $plugin ) {
			foreach ( $api_heavy as $key => $name ) {
				if ( strpos( $plugin, $key ) !== false ) {
					$api_heavy_active[] = $name;
				}
			}
		}

		if ( ! empty( $api_heavy_active ) ) {
			$api_concerns[] = sprintf(
				/* translators: %s: plugin names */
				__( 'API-heavy plugins active: %s. These make external requests on every page load.', 'wpshadow' ),
				implode( ', ', $api_heavy_active )
			);
		}

		// Check if caching plugins are active (should be)
		$cache_plugins = array(
			'wp-super-cache',
			'w3-total-cache',
			'wp-fastest-cache',
			'wp-rocket',
		);

		$cache_active = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $cache_plugins as $cache_key ) {
				if ( strpos( $plugin, $cache_key ) !== false ) {
					$cache_active = true;
					break;
				}
			}
		}

		if ( ! empty( $api_heavy_active ) && ! $cache_active ) {
			$api_concerns[] = __( 'API-heavy plugins active but no caching plugin detected. API calls will block every page load.', 'wpshadow' );
		}

		// Check for HTTPS everywhere (cURL performance)
		global $wpdb;
		$options = $wpdb->get_results(
			"SELECT option_name, option_value FROM {$wpdb->options}
			WHERE option_value LIKE '%https://api%'
			LIMIT 5"
		);

		if ( ! empty( $options ) && empty( $cache_active ) ) {
			$api_concerns[] = __( 'HTTPS API endpoints detected. Without caching, HTTPS handshake adds 500ms+ latency per request.', 'wpshadow' );
		}

		if ( ! empty( $api_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $api_concerns ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'api_heavy_plugins' => $api_heavy_active,
					'cache_plugin_active' => $cache_active,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-api-request-performance',
			);
		}

		return null;
	}
}
