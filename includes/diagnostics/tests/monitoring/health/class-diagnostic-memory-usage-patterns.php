<?php
/**
 * Memory Usage Patterns Diagnostic
 *
 * Analyzes memory consumption to detect memory leaks
 * and plugins consuming excessive memory.
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
 * Diagnostic_Memory_Usage_Patterns Class
 *
 * Monitors memory usage patterns.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Memory_Usage_Patterns extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'memory-usage-patterns';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Memory Usage Patterns';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes memory consumption';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'health';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if memory issues, null otherwise.
	 */
	public static function check() {
		$memory_status = self::analyze_memory_usage();

		if ( ! $memory_status['has_issue'] ) {
			return null; // Memory usage healthy
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %dMB: memory used */
				__( 'Using %dMB of %dMB PHP memory limit (%d%%). Approaching limit = white screen errors. Plugin leak = each page load uses more memory.', 'wpshadow' ),
				$memory_status['used'],
				$memory_status['limit'],
				$memory_status['percent']
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/memory-usage',
			'family'       => self::$family,
			'meta'         => array(
				'memory_used'    => $memory_status['used'] . 'MB',
				'memory_limit'   => $memory_status['limit'] . 'MB',
				'usage_percent'  => $memory_status['percent'] . '%',
			),
			'details'      => array(
				'php_memory_limits'           => array(
					'Default: 128MB' => __( 'WordPress minimum; tight for most sites' ),
					'Recommended: 256MB' => __( 'WooCommerce, plugins; comfortable' ),
					'Large sites: 512MB+' => __( 'High-traffic, many plugins; necessary' ),
				),
				'memory_usage_by_component'   => array(
					'WordPress Core' => array(
						'Load: 2-5MB',
						'Posts, pages, categories',
					),
					'Database Connection' => array(
						'Query results: 5-20MB',
						'Per-query memory varies',
					),
					'Plugins' => array(
						'Each plugin: 0.5-5MB',
						'20 plugins = 10-100MB',
						'Heavy plugins = 20-50MB each',
					),
					'Assets in Memory' => array(
						'Images loaded: 1MB per image',
						'CSS/JS parsed: 5-20MB',
						'Object cache: 50-200MB',
					),
				),
				'memory_leak_signs'           => array(
					'Memory Grows with Each Request' => array(
						'Symptom: Request 1 uses 80MB, request 2 uses 100MB',
						'Cause: Objects not freed after use',
						'Solution: Identify plugin with leak',
					),
					'Fatal Error: Allowed Memory Exceeded' => array(
						'Example: "Fatal error: Allowed memory of 128MB exhausted"',
						'Cause: Object/array kept in memory',
						'Solution: Increase limit or fix plugin',
					),
					'Slow Over Time' => array(
						'Symptom: Site works fine morning, slow by evening',
						'Cause: Memory leak accumulating',
						'Solution: Daily restart web server',
					),
				),
				'analyzing_memory_usage'      => array(
					'Enable Debug Logging' => array(
						'wp-config.php:',
						'define( \'SAVEQUERIES\', true );',
						'Then: global $wpdb; echo "<pre>"; print_r($wpdb->queries);',
					),
					'Memory Usage Function' => array(
						'echo memory_get_usage();',
						'echo memory_get_peak_usage();',
						'Place in functions.php for testing',
					),
					'Performance Plugins' => array(
						'Query Monitor: Shows memory per component',
						'New Relic: Tracks memory over time',
						'Datadog: APM with memory profiling',
					),
				),
				'reducing_memory_usage'       => array(
					'Increase Limit (Quick Fix)' => array(
						'wp-config.php: define( \'WP_MEMORY_LIMIT\', \'256M\' );',
						'Not ideal: Masks underlying issue',
						'Temporary while investigating',
					),
					'Optimize Plugins' => array(
						'Disable and test: Which plugin causes issue?',
						'Update: Plugins may have fixes',
						'Replace: Use lighter alternative',
					),
					'Optimize Database' => array(
						'Cleanup old data: Revisions, spam, logs',
						'Optimize tables: OPTIMIZE TABLE wp_posts',
						'Add indexes: Common query columns',
					),
					'Use Object Cache' => array(
						'Redis/Memcached: 10-50MB permanent',
						'Saves: 100-200MB per request',
						'Net: 50-150MB memory savings',
					),
				),
			),
		);
	}

	/**
	 * Analyze memory usage.
	 *
	 * @since  1.2601.2148
	 * @return array Memory analysis.
	 */
	private static function analyze_memory_usage() {
		$limit = (int) wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$used = (int) memory_get_usage();

		// Convert to MB for display
		$limit_mb = round( $limit / 1048576 );
		$used_mb = round( $used / 1048576 );
		$percent = round( ( $used / $limit ) * 100 );

		$has_issue = $percent > 75;

		return array(
			'used'     => $used_mb,
			'limit'    => $limit_mb,
			'percent'  => $percent,
			'has_issue' => $has_issue,
		);
	}
}
