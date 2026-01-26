<?php
/**
 * Diagnostic: Env Caching Effectiveness
 *
 * Analyzes cache effectiveness by measuring cache hit rates and transient health.
 * Proper cache configuration can reduce server load and improve response times.
 *
 * Category: Environment & Infrastructure
 * Priority: 2
 * Philosophy: 7, 8, 9 (Ridiculously Good, Inspire Confidence, Everything Has a KPI)
 *
 * Test Description:
 * Measures cache effectiveness using transient analysis, object cache detection,
 * and page cache plugin verification. Returns findings if cache hit rate is poor
 * or if caching infrastructure needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 *
 * @verified 2026-01-26 - Full implementation complete
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Env_Caching_Effectiveness Class
 *
 * Evaluates cache effectiveness by analyzing:
 * - Object cache availability (Redis, Memcached)
 * - Page cache plugin status
 * - Transient health (expired transients indicate poor cache management)
 * - Overall cache configuration quality
 */
class Diagnostic_Env_Caching_Effectiveness extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'env-caching-effectiveness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cache Effectiveness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures cache hit rate effectiveness and identifies optimization opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'environment';

	/**
	 * Display name for the family
	 *
	 * @var string
	 */
	protected static $family_label = 'Environment & Infrastructure';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'env-caching-effectiveness';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic display name.
	 */
	public static function get_name(): string {
		return __( 'Cache Effectiveness', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Analyzes cache hit rate and effectiveness. Identifies cache configuration issues that impact performance.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic category.
	 */
	public static function get_category(): string {
		return 'environment';
	}

	/**
	 * Get threat level
	 *
	 * @since  1.2601.2148
	 * @return int Threat level 0-100 (dynamically calculated in check()).
	 */
	public static function get_threat_level(): int {
		return 50;
	}

	/**
	 * Run diagnostic test
	 *
	 * @deprecated Use check() method instead.
	 * @since      1.2601.2148
	 * @return     array Diagnostic results.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Cache is working effectively', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'warning',
			'message' => $result['description'] ?? __( 'Cache effectiveness issues detected', 'wpshadow' ),
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string KB article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/env-caching-effectiveness';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-environment';
	}

	/**
	 * Run the diagnostic check
	 *
	 * Analyzes cache effectiveness by checking:
	 * 1. Object cache availability (Redis, Memcached, etc.)
	 * 2. Page cache plugin status
	 * 3. Transient health (expired transients indicate poor cache management)
	 * 4. Overall cache hit rate estimation
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null if cache is effective.
	 */
	public static function check(): ?array {
		global $wpdb;

		// Step 1: Check if any caching is enabled
		$has_object_cache = function_exists( 'wp_using_ext_object_cache' ) && wp_using_ext_object_cache();
		$has_page_cache   = self::detect_page_cache_plugin();

		// If no caching at all, this is critical
		if ( ! $has_object_cache && ! $has_page_cache ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No caching system detected. Your site is regenerating every page on every request, significantly impacting performance and server resources.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => self::get_kb_article(),
				'data'         => array(
					'has_object_cache' => false,
					'has_page_cache'   => false,
					'recommendation'   => __( 'Install a caching plugin like WP Super Cache, W3 Total Cache, or enable server-level caching (Redis/Memcached).', 'wpshadow' ),
				),
			);
		}

		// Step 2: Analyze transient health as a proxy for cache effectiveness
		$transient_analysis = self::analyze_transient_health( $wpdb );

		// Step 3: Calculate estimated effectiveness score
		$effectiveness_score = self::calculate_effectiveness_score(
			$has_object_cache,
			$has_page_cache,
			$transient_analysis
		);

		// Step 4: Determine if this is an issue worth reporting
		if ( $effectiveness_score >= 70 ) {
			// Cache is working well, no finding
			return null;
		}

		// Cache effectiveness is poor, return finding
		return self::build_effectiveness_finding(
			$effectiveness_score,
			$has_object_cache,
			$has_page_cache,
			$transient_analysis
		);
	}

	/**
	 * Detect if a page cache plugin is active
	 *
	 * @since  1.2601.2148
	 * @return bool True if page cache plugin detected.
	 */
	private static function detect_page_cache_plugin(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$cache_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-rocket/wp-rocket.php',
			'cache-enabler/cache-enabler.php',
			'litespeed-cache/litespeed-cache.php',
			'swift-performance-lite/performance.php',
			'wp-fastest-cache/wpFastestCache.php',
			'hummingbird-performance/wp-hummingbird.php',
		);

		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Analyze transient health as proxy for cache effectiveness
	 *
	 * Expired transients indicate poor cache management and can be a sign
	 * that the cache is not being utilized effectively.
	 *
	 * @since  1.2601.2148
	 * @param  object $wpdb WordPress database object.
	 * @return array Transient analysis data.
	 */
	private static function analyze_transient_health( $wpdb ): array {
		// Count total transients
		$total_transients = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
				'_transient_%'
			)
		);

		// Count expired transients
		$expired_transients = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				'_transient_timeout_%',
				time()
			)
		);

		// Calculate health percentage
		$health_percentage = 100;
		if ( $total_transients > 0 ) {
			$health_percentage = ( ( $total_transients - $expired_transients ) / $total_transients ) * 100;
		}

		return array(
			'total_transients'   => $total_transients,
			'expired_transients' => $expired_transients,
			'health_percentage'  => $health_percentage,
		);
	}

	/**
	 * Calculate overall cache effectiveness score
	 *
	 * Score based on:
	 * - Object cache presence: +40 points
	 * - Page cache presence: +30 points
	 * - Transient health: up to +30 points
	 *
	 * @since  1.2601.2148
	 * @param  bool  $has_object_cache Whether object cache is enabled.
	 * @param  bool  $has_page_cache Whether page cache plugin is active.
	 * @param  array $transient_analysis Transient health data.
	 * @return int Effectiveness score 0-100.
	 */
	private static function calculate_effectiveness_score(
		bool $has_object_cache,
		bool $has_page_cache,
		array $transient_analysis
	): int {
		$score = 0;

		// Object cache is most important
		if ( $has_object_cache ) {
			$score += 40;
		}

		// Page cache is also important
		if ( $has_page_cache ) {
			$score += 30;
		}

		// Transient health indicates cache is being used well
		$transient_health_score = ( $transient_analysis['health_percentage'] * 30 ) / 100;
		$score                 += $transient_health_score;

		return (int) $score;
	}

	/**
	 * Build effectiveness finding based on analysis
	 *
	 * @since  1.2601.2148
	 * @param  int   $effectiveness_score Overall effectiveness score.
	 * @param  bool  $has_object_cache Whether object cache is enabled.
	 * @param  bool  $has_page_cache Whether page cache plugin is active.
	 * @param  array $transient_analysis Transient health data.
	 * @return array Finding array.
	 */
	private static function build_effectiveness_finding(
		int $effectiveness_score,
		bool $has_object_cache,
		bool $has_page_cache,
		array $transient_analysis
	): array {
		// Determine severity based on score
		if ( $effectiveness_score < 30 ) {
			$severity     = 'high';
			$threat_level = 70;
		} elseif ( $effectiveness_score < 50 ) {
			$severity     = 'medium';
			$threat_level = 55;
		} else {
			$severity     = 'low';
			$threat_level = 35;
		}

		// Build helpful description
		$issues = array();

		if ( ! $has_object_cache ) {
			$issues[] = __( 'No persistent object cache (Redis/Memcached) detected', 'wpshadow' );
		}

		if ( ! $has_page_cache ) {
			$issues[] = __( 'No page cache plugin active', 'wpshadow' );
		}

		if ( $transient_analysis['expired_transients'] > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of expired transients */
				__( '%d expired transients need cleanup', 'wpshadow' ),
				$transient_analysis['expired_transients']
			);
		}

		$description = sprintf(
			/* translators: %d: effectiveness score percentage */
			__( 'Cache effectiveness score: %d%%. ', 'wpshadow' ),
			$effectiveness_score
		);

		if ( ! empty( $issues ) ) {
			$description .= __( 'Issues found: ', 'wpshadow' ) . implode( ', ', $issues ) . '.';
		}

		$description .= ' ' . __( 'Improving cache effectiveness can reduce server load by 50-80% and significantly improve response times.', 'wpshadow' );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => self::get_kb_article(),
			'data'         => array(
				'effectiveness_score' => $effectiveness_score,
				'has_object_cache'    => $has_object_cache,
				'has_page_cache'      => $has_page_cache,
				'transient_health'    => $transient_analysis,
				'issues'              => $issues,
			),
		);
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Env Caching Effectiveness
	 * Slug: env-caching-effectiveness
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when cache effectiveness is good (>=70%)
	 * - FAIL: check() returns array when cache effectiveness is poor (<70%)
	 * - Description: Analyzes cache hit rate and effectiveness by checking object cache,
	 *   page cache plugins, and transient health.
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result with pass/fail status.
	 *
	 *     @type bool   $passed  Whether the test passed (true = cache is effective).
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_env_caching_effectiveness(): array {
		$result = self::check();

		// If check() returns null, cache is effective (passing condition)
		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'Cache is working effectively. No issues detected.', 'wpshadow' ),
			);
		}

		// If check() returns an array, there are cache effectiveness issues
		$effectiveness_score = $result['data']['effectiveness_score'] ?? 0;
		$issues              = $result['data']['issues'] ?? array();

		$message = sprintf(
			/* translators: 1: effectiveness score, 2: comma-separated list of issues */
			__( 'Cache effectiveness score: %1$d%%. Issues: %2$s', 'wpshadow' ),
			$effectiveness_score,
			! empty( $issues ) ? implode( ', ', $issues ) : __( 'None specified', 'wpshadow' )
		);

		return array(
			'passed'  => false,
			'message' => $message,
		);
	}
}
