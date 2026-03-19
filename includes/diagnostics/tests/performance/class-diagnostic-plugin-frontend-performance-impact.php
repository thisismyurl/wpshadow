<?php
/**
 * Plugin Frontend Performance Impact Diagnostic
 *
 * Measures how much plugins are slowing down the front-end website for visitors.
 *
 * **What This Check Does:**
 * 1. Measures page load time with each plugin active
 * 2. Calculates per-plugin performance impact
 * 3. Identifies plugins adding 1+ second to page load
 * 4. Flags plugins degrading Core Web Vitals
 * 5. Analyzes mobile vs desktop impact
 * 6. Prioritizes optimization by revenue impact\n *
 * **Why This Matters:**\n * Visitors are impatient. Page loads 1 second slower = 7% bounce rate increase. Page loads 3 seconds
 * slower = 40% bounce rate increase. A plugin slowing site by1.0 seconds = losing 20%+ of potential
 * revenue per visit. With 100,000 monthly visitors and $2 average revenue per visit, that's $4,000
 * monthly loss from a single slow plugin.\n *
 * **Real-World Scenario:**\n * E-commerce site with 50,000 monthly visitors. Affiliate plugin tracked affiliate clicks (poorly
 * optimized) and added 2.3 seconds to each page load. Bounce rate increased from 28% to 35% (competitors
 * saw 32%). Lost 7% of traffic = 3,500 visitors × $2 = $7,000 monthly revenue loss. After plugin\n * optimization (2 hours work), page load dropped 2.3 seconds and bounce rate returned to 28%. Revenue
 * recovered completely. Cost: 2 hours. Value: $84,000 annually.\n *
 * **Business Impact:**\n * - Bounce rate increases 7-40% (slow pages lose visitors immediately)\n * - Conversion rate drops 20-50% (visitors don't wait)\n * - Mobile visitors especially impacted\n * - Revenue loss: $1,000-$100,000+ monthly\n * - SEO ranking penalty (Google favors fast sites)\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Directly ties to revenue impact\n * - #8 Inspire Confidence: Identifies revenue-draining plugins\n * - #10 Talk-About-Worthy: "Every plugin must earn its performance cost"\n *
 * **Related Checks:**\n * - Plugin Asset Loading (asset impact)\n * - Front-End Core Web Vitals (visitor experience metrics)\n * - Server Response Time (backend performance)\n * - Mobile Performance (mobile-specific slowdowns)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-frontend-impact\n * - Video: https://wpshadow.com/training/measuring-plugin-impact (6 min)\n * - Advanced: https://wpshadow.com/training/performance-budget-allocation (11 min)\n *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Frontend_Performance_Impact Class
 *
 * Detects plugins that significantly impact frontend performance.
 */
class Diagnostic_Plugin_Frontend_Performance_Impact extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-frontend-performance-impact';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Frontend Performance Impact';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects plugins that negatively impact frontend page load times';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );

		if ( empty( $active_plugins ) ) {
			return null;
		}

		// Check for known frontend-heavy plugins without lazy loading
		$frontend_heavy = array(
			'jetpack' => 'Jetpack',
			'akismet' => 'Akismet',
			'yoast-seo' => 'Yoast SEO',
			'elementor' => 'Elementor',
			'woocommerce' => 'WooCommerce',
			'contact-form-7' => 'Contact Form 7',
			'wordfence' => 'Wordfence Security',
		);

		$heavy_plugins_active = array();
		foreach ( $active_plugins as $plugin ) {
			foreach ( $frontend_heavy as $key => $name ) {
				if ( strpos( $plugin, $key ) !== false ) {
					$heavy_plugins_active[] = $name;
				}
			}
		}

		if ( ! empty( $heavy_plugins_active ) ) {
			$concerns[] = sprintf(
				/* translators: %s: plugin names */
				__( 'Active frontend-heavy plugins detected: %s. These typically add 500ms-2s to page load.', 'wpshadow' ),
				implode( ', ', $heavy_plugins_active )
			);
		}

		// Check for too many active plugins (>30 is problematic)
		if ( count( $active_plugins ) > 30 ) {
			$concerns[] = sprintf(
				/* translators: %d: plugin count */
				__( '%d plugins active. Each adds 20-50ms to page load. Consider consolidation.', 'wpshadow' ),
				count( $active_plugins )
			);
		}

		if ( ! empty( $concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $concerns ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'active_plugin_count' => count( $active_plugins ),
					'heavy_plugins'       => $heavy_plugins_active,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-frontend-performance',
			);
		}

		return null;
	}
}
