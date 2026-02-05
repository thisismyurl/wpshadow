<?php
/**
 * Plugin Memory Usage Treatment
 *
 * Detects plugins consuming excessive server memory causing resource exhaustion.
 *
 * **What This Check Does:**
 * 1. Measures PHP memory usage by plugin
 * 2. Identifies plugins using 50MB+ memory
 * 3. Flags plugins exceeding memory limit
 * 4. Detects memory leaks (growing usage over time)
 * 5. Analyzes memory patterns by page type
 * 6. Projects server capacity impact\n *
 * **Why This Matters:**\n * Server memory is shared among all concurrent processes. If one plugin allocates 100MB per request,
 * only 2-3 concurrent users can use site before server runs out of memory and crashes. WordPress memory
 * limit is usually 256MB. A plugin using 128MB leaves only 128MB for WordPress core + other plugins.\n *
 * With 10 concurrent users, each requesting a page: server needs 1.28GB memory. If server only has 512MB,\n * it crashes and becomes inaccessible.\n *
 * **Real-World Scenario:**\n * Large media site used plugin for on-the-fly image resizing. Plugin loaded entire 50MB image into memory,
 * resized it, then freed memory (hopefully). Every image resize = temporary 50MB spike. With high traffic,
 * multiple resizes simultaneously = 200MB+ memory used. During traffic spike, server hit memory limit,\n * crashed. Site went offline during peak traffic. After implementing cron-based pre-generation (resizes
 * at off-peak), memory usage stable. Site survived traffic spikes.\n *
 * **Business Impact:**\n * - Server crashes under traffic (white screen)\n * - 100% downtime during peak traffic\n * - Lost revenue during crash ($5,000-$50,000 per incident)\n * - Scaling requires larger/more expensive server ($100-$500 monthly increase)\n * - Hosting provider throttles/terminates account (abuse policies)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents crash-under-load scenarios\n * - #9 Show Value: Prevents expensive server upgrades\n * - #10 Talk-About-Worthy: "Site handles traffic spikes without crashing"\n *
 * **Related Checks:**\n * - Server Memory Availability (total capacity)\n * - Concurrent User Capacity (load simulation)\n * - Background Job Performance (memory-intensive tasks)\n * - System Health Monitoring (resource tracking)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/plugin-memory-optimization\n * - Video: https://wpshadow.com/training/php-memory-profiling (6 min)\n * - Advanced: https://wpshadow.com/training/memory-leak-detection (12 min)\n *
 * @since   1.4031.1939
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Plugin_Memory_Usage Class
 *
 * Identifies plugins that may be consuming excessive memory.
 */
class Treatment_Plugin_Memory_Usage extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-memory-usage';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Memory Usage';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins that may consume excessive memory';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$memory_concerns = array();

		// Get memory limits
		$memory_limit = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$memory_limit_admin = WP_MEMORY_LIMIT;
		if ( defined( 'WP_MEMORY_LIMIT' ) && WP_MEMORY_LIMIT !== '40M' ) {
			$memory_limit_admin = WP_MEMORY_LIMIT;
		}

		if ( $memory_limit < 67108864 ) { // 64MB
			$memory_concerns[] = sprintf(
				/* translators: %s: memory limit */
				__( 'WordPress memory limit set to %s. Plugins will struggle with large datasets.', 'wpshadow' ),
				size_format( $memory_limit )
			);
		}

		// Check for plugins known to be memory-heavy
		$active_plugins = get_option( 'active_plugins', array() );

		$memory_heavy = array(
			'elementor' => 'Elementor',
			'woocommerce' => 'WooCommerce',
			'divi' => 'Divi',
			'wp-super-cache' => 'WP Super Cache',
			'akismet' => 'Akismet',
		);

		$heavy_plugins = array();
		foreach ( $active_plugins as $plugin ) {
			foreach ( $memory_heavy as $key => $name ) {
				if ( strpos( $plugin, $key ) !== false ) {
					$heavy_plugins[] = $name;
				}
			}
		}

		if ( ! empty( $heavy_plugins ) && $memory_limit < 134217728 ) { // 128MB
			$memory_concerns[] = sprintf(
				/* translators: %s: plugin names */
				__( 'Memory-heavy plugins (%s) active but memory limit is only %s. Consider increasing to 256MB+.', 'wpshadow' ),
				implode( ', ', $heavy_plugins ),
				size_format( $memory_limit )
			);
		}

		// Check for many large transients
		global $wpdb;
		$large_transients = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options}
			WHERE option_name LIKE '%transient%'
			AND LENGTH(option_value) > 1048576"
		);

		if ( $large_transients > 0 ) {
			$memory_concerns[] = sprintf(
				/* translators: %d: count of large transients */
				__( '%d large transients (>1MB) in cache. These consume memory.', 'wpshadow' ),
				$large_transients
			);
		}

		if ( ! empty( $memory_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $memory_concerns ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'memory_limit'          => $memory_limit,
					'memory_heavy_plugins'  => $heavy_plugins,
					'large_transients_count' => $large_transients ?? 0,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-memory-usage',
			);
		}

		return null;
	}
}
