<?php
/**
 * Site Health Integration
 *
 * Integrates WPS features with WordPress Site Health system.
 * Provides scoring, metrics, and upstream data flow.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Site_Health_Integration
 *
 * Manages health scoring and WordPress Site Health integration.
 */
final class WPSHADOW_Site_Health_Integration {

	/**
	 * Maximum possible health score.
	 */
	private const MAX_SCORE = 100;

	/**
	 * Health status thresholds.
	 */
	private const STATUS_GOOD = 80;
	private const STATUS_RECOMMENDED = 60;
	private const STATUS_CRITICAL = 40;

	/**
	 * Feature weight categories (impact on overall score).
	 */
	private const WEIGHTS = array(
		'critical'   => 1.0,  // Security, core integrity.
		'high'       => 0.75, // Performance, caching.
		'medium'     => 0.5,  // Optimization, cleanup.
		'low'        => 0.25, // Minor tweaks, cosmetic.
	);

	/**
	 * Initialize hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Add WPS tests to WordPress Site Health.
		add_filter( 'site_status_tests', array( __CLASS__, 'register_site_health_tests' ) );

		// Add debug info to Site Health Info tab.
		add_filter( 'debug_information', array( __CLASS__, 'add_debug_information' ) );

		// AJAX endpoint for health score.
		add_action( 'wp_ajax_WPSHADOW_get_health_score', array( __CLASS__, 'ajax_get_health_score' ) );
	}

	/**
	 * Register WPS tests with WordPress Site Health.
	 *
	 * @param array $tests Existing tests.
	 * @return array Modified tests.
	 */
	public static function register_site_health_tests( array $tests ): array {
		// Direct tests (run immediately).
		$tests['direct']['wpshadow_security_score'] = array(
			'label' => __( 'WPShadow Security Score', 'plugin-wpshadow' ),
			'test'  => array( __CLASS__, 'test_security_score' ),
		);

		$tests['direct']['wpshadow_performance_score'] = array(
			'label' => __( 'WPShadow Performance Score', 'plugin-wpshadow' ),
			'test'  => array( __CLASS__, 'test_performance_score' ),
		);

		$tests['direct']['wpshadow_overall_health'] = array(
			'label' => __( 'WPShadow Overall Health', 'plugin-wpshadow' ),
			'test'  => array( __CLASS__, 'test_overall_health' ),
		);

		// Async tests (run via AJAX).
		$tests['async']['wpshadow_feature_status'] = array(
			'label'             => __( 'WPShadow Feature Status', 'plugin-wpshadow' ),
			'test'              => 'wpshadow_feature_status',
			'has_rest'          => false,
			'async_direct_test' => array( __CLASS__, 'test_feature_status' ),
		);

		return $tests;
	}

	/**
	 * Test: Security Score
	 *
	 * @return array Test result.
	 */
	public static function test_security_score(): array {
		$score = self::calculate_security_score();

		$status = 'good';
		$label  = __( 'Your security configuration is excellent', 'plugin-wpshadow' );

		if ( $score < self::STATUS_GOOD ) {
			$status = 'recommended';
			$label  = __( 'Your security could be improved', 'plugin-wpshadow' );
		}

		if ( $score < self::STATUS_CRITICAL ) {
			$status = 'critical';
			$label  = __( 'Your security needs immediate attention', 'plugin-wpshadow' );
		}

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Security', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: security score */
					__( 'WPShadow Security Score: %d/100', 'plugin-wpshadow' ),
					$score
				)
			),
			'actions'     => sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=wpshadow-settings&tab=security' ),
				__( 'Review Security Settings', 'plugin-wpshadow' )
			),
			'test'        => 'wpshadow_security_score',
		);
	}

	/**
	 * Test: Performance Score
	 *
	 * @return array Test result.
	 */
	public static function test_performance_score(): array {
		$score = self::calculate_performance_score();

		$status = 'good';
		$label  = __( 'Your performance optimization is excellent', 'plugin-wpshadow' );

		if ( $score < self::STATUS_GOOD ) {
			$status = 'recommended';
			$label  = __( 'Your performance could be improved', 'plugin-wpshadow' );
		}

		if ( $score < self::STATUS_CRITICAL ) {
			$status = 'critical';
			$label  = __( 'Your performance needs attention', 'plugin-wpshadow' );
		}

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: performance score */
					__( 'WPShadow Performance Score: %d/100', 'plugin-wpshadow' ),
					$score
				)
			),
			'actions'     => sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=wpshadow-settings&tab=performance' ),
				__( 'Review Performance Settings', 'plugin-wpshadow' )
			),
			'test'        => 'wpshadow_performance_score',
		);
	}

	/**
	 * Test: Overall Health
	 *
	 * @return array Test result.
	 */
	public static function test_overall_health(): array {
		$score = self::calculate_overall_health();

		$status = 'good';
		$label  = __( 'WPShadow is optimally configured', 'plugin-wpshadow' );

		if ( $score < self::STATUS_GOOD ) {
			$status = 'recommended';
			$label  = __( 'WPShadow configuration could be improved', 'plugin-wpshadow' );
		}

		if ( $score < self::STATUS_CRITICAL ) {
			$status = 'critical';
			$label  = __( 'WPShadow needs configuration', 'plugin-wpshadow' );
		}

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'WPShadow', 'plugin-wpshadow' ),
				'color' => 'green',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: overall health score */
					__( 'Overall Health Score: %d/100', 'plugin-wpshadow' ),
					$score
				)
			),
			'actions'     => sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=wpshadow' ),
				__( 'View WPShadow Dashboard', 'plugin-wpshadow' )
			),
			'test'        => 'wpshadow_overall_health',
		);
	}

	/**
	 * Test: Feature Status (async)
	 *
	 * @return array Test result.
	 */
	public static function test_feature_status(): array {
		$enabled_count = count( self::get_enabled_features() );
		$total_count   = count( self::get_all_feature_scores() );

		$status = $enabled_count > 0 ? 'good' : 'recommended';
		$label  = $enabled_count > 0
			? __( 'WPShadow features are active', 'plugin-wpshadow' )
			: __( 'No WPShadow features are enabled', 'plugin-wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Features', 'plugin-wpshadow' ),
				'color' => 'purple',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: 1: enabled count, 2: total count */
					__( '%1$d of %2$d features enabled', 'plugin-wpshadow' ),
					$enabled_count,
					$total_count
				)
			),
			'actions'     => sprintf(
				'<a href="%s">%s</a>',
				admin_url( 'admin.php?page=wpshadow-features' ),
				__( 'Manage Features', 'plugin-wpshadow' )
			),
			'test'        => 'wpshadow_feature_status',
		);
	}

	/**
	 * Calculate security score.
	 *
	 * @return int Score (0-100).
	 */
	public static function calculate_security_score(): int {
		return self::calculate_category_score( 'security' );
	}

	/**
	 * Calculate performance score.
	 *
	 * @return int Score (0-100).
	 */
	public static function calculate_performance_score(): int {
		return self::calculate_category_score( 'performance' );
	}

	/**
	 * Calculate accessibility score.
	 *
	 * @return int Score (0-100).
	 */
	public static function calculate_accessibility_score(): int {
		return self::calculate_category_score( 'accessibility' );
	}

	/**
	 * Calculate tools score.
	 *
	 * @return int Score (0-100).
	 */
	public static function calculate_tools_score(): int {
		return self::calculate_category_score( 'tools' );
	}

	/**
	 * Calculate reporting score.
	 *
	 * @return int Score (0-100).
	 */
	public static function calculate_reporting_score(): int {
		return self::calculate_category_score( 'reporting' );
	}

	/**
	 * Calculate privacy score.
	 *
	 * @return int Score (0-100).
	 */
	public static function calculate_privacy_score(): int {
		return self::calculate_category_score( 'privacy' );
	}

	/**
	 * Calculate diagnostic score.
	 *
	 * @return int Score (0-100).
	 */
	public static function calculate_diagnostic_score(): int {
		return self::calculate_category_score( 'diagnostic' );
	}

	/**
	 * Calculate score for a specific category.
	 *
	 * @param string $category Category name.
	 * @return int Score (0-100).
	 */
	private static function calculate_category_score( string $category ): int {
		$all_features = self::get_all_feature_scores();
		$category_features = array_filter(
			$all_features,
			function( $feature ) use ( $category ) {
				return isset( $feature['category'] ) && $feature['category'] === $category;
			}
		);

		if ( empty( $category_features ) ) {
			return 0;
		}

		$total_score = 0;
		$enabled_count = 0;

		foreach ( $category_features as $feature ) {
			if ( $feature['enabled'] ) {
				$total_score += $feature['score'];
				$enabled_count++;
			}
		}

		if ( $enabled_count === 0 ) {
			return 0;
		}

		// Average score of enabled features in this category.
		return (int) ( $total_score / $enabled_count );
	}

	/**
	 * Calculate overall health score.
	 *
	 * @return int Score (0-100).
	 */
	public static function calculate_overall_health(): int {
		// Category weights for overall score.
		$weights = array(
			'security'      => 0.35,  // 35% - Most critical.
			'performance'   => 0.30,  // 30% - Very important.
			'accessibility' => 0.10,  // 10% - Important for compliance.
			'tools'         => 0.10,  // 10% - Maintenance & reliability.
			'reporting'     => 0.05,  // 5% - Visibility & insights.
			'privacy'       => 0.05,  // 5% - Compliance.
			'diagnostic'    => 0.05,  // 5% - Monitoring.
		);

		$weighted_sum = 0;
		$weighted_sum += self::calculate_security_score() * $weights['security'];
		$weighted_sum += self::calculate_performance_score() * $weights['performance'];
		$weighted_sum += self::calculate_accessibility_score() * $weights['accessibility'];
		$weighted_sum += self::calculate_tools_score() * $weights['tools'];
		$weighted_sum += self::calculate_reporting_score() * $weights['reporting'];
		$weighted_sum += self::calculate_privacy_score() * $weights['privacy'];
		$weighted_sum += self::calculate_diagnostic_score() * $weights['diagnostic'];

		return (int) $weighted_sum;
	}

	/**
	 * Get feature score with sub-feature breakdown.
	 *
	 * @param string $feature_id Feature identifier.
	 * @return array|null Feature score data or null if not found.
	 */
	public static function get_feature_score( string $feature_id ): ?array {
		$all_scores = self::get_all_feature_scores();
		return $all_scores[ $feature_id ] ?? null;
	}

	/**
	 * Get all feature scores.
	 *
	 * @return array Feature scores indexed by feature ID.
	 */
	public static function get_all_feature_scores(): array {
		static $cache = null;

		if ( null !== $cache ) {
			return $cache;
		}

		$cache = array(
			// ===== SECURITY FEATURES =====
			'hardening' => array(
				'enabled'      => self::is_feature_enabled( 'hardening' ),
				'score'        => self::is_feature_enabled( 'hardening' ) ? 100 : 0,
				'category'     => 'security',
				'sub_features' => array(),
			),
			'firewall' => array(
				'enabled'      => self::is_feature_enabled( 'firewall' ),
				'score'        => self::calculate_firewall_score(),
				'category'     => 'security',
				'sub_features' => array(
					'ip_blocking'      => array( 'enabled' => true, 'points' => 30 ),
					'rate_limiting'    => array( 'enabled' => true, 'points' => 30 ),
					'attack_detection' => array( 'enabled' => true, 'points' => 40 ),
				),
			),
			'malware-scanner' => array(
				'enabled'      => self::is_feature_enabled( 'malware-scanner' ),
				'score'        => self::calculate_malware_scanner_score(),
				'category'     => 'security',
				'sub_features' => array(
					'pattern_detection'  => array( 'enabled' => true, 'points' => 40 ),
					'real_time_scanning' => array( 'enabled' => true, 'points' => 30 ),
					'quarantine'         => array( 'enabled' => true, 'points' => 30 ),
				),
			),
			'core-integrity' => array(
				'enabled'      => self::is_feature_enabled( 'core-integrity' ),
				'score'        => self::calculate_core_integrity_score(),
				'category'     => 'security',
				'sub_features' => array(
					'checksum_verification' => array( 'enabled' => true, 'points' => 50 ),
					'auto_repair'           => array( 'enabled' => true, 'points' => 50 ),
				),
			),
			'traffic-monitor' => array(
				'enabled'      => self::is_feature_enabled( 'traffic-monitor' ),
				'score'        => self::is_feature_enabled( 'traffic-monitor' ) ? 100 : 0,
				'category'     => 'security',
				'sub_features' => array(),
			),
			'conflict-sandbox' => array(
				'enabled'      => self::is_feature_enabled( 'conflict-sandbox' ),
				'score'        => self::is_feature_enabled( 'conflict-sandbox' ) ? 100 : 0,
				'category'     => 'security',
				'sub_features' => array(),
			),
			'visual-regression' => array(
				'enabled'      => self::is_feature_enabled( 'visual-regression' ),
				'score'        => self::is_feature_enabled( 'visual-regression' ) ? 100 : 0,
				'category'     => 'security',
				'sub_features' => array(),
			),

			// ===== PERFORMANCE FEATURES =====
			'page-cache' => array(
				'enabled'      => self::is_feature_enabled( 'page-cache' ),
				'score'        => self::calculate_page_cache_score(),
				'category'     => 'performance',
				'sub_features' => array(
					'html_caching'     => array( 'enabled' => true, 'points' => 50 ),
					'device_detection' => array( 'enabled' => true, 'points' => 20 ),
					'auto_invalidation' => array( 'enabled' => true, 'points' => 30 ),
				),
			),
			'cdn-integration' => array(
				'enabled'      => self::is_feature_enabled( 'cdn-integration' ),
				'score'        => self::calculate_cdn_score(),
				'category'     => 'performance',
				'sub_features' => array(
					'url_rewriting'   => array( 'enabled' => true, 'points' => 60 ),
					'api_integration' => array( 'enabled' => true, 'points' => 40 ),
				),
			),
			'image-optimizer' => array(
				'enabled'      => self::is_feature_enabled( 'image-optimizer' ),
				'score'        => self::calculate_image_optimizer_score(),
				'category'     => 'performance',
				'sub_features' => array(
					'compression'       => array( 'enabled' => true, 'points' => 60 ),
					'auto_optimization' => array( 'enabled' => true, 'points' => 40 ),
				),
			),
			'script-deferral' => array(
				'enabled'      => self::is_feature_enabled( 'script-deferral' ),
				'score'        => self::is_feature_enabled( 'script-deferral' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'critical-css' => array(
				'enabled'      => self::is_feature_enabled( 'critical-css' ),
				'score'        => self::is_feature_enabled( 'critical-css' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'asset-minification' => array(
				'enabled'      => self::is_feature_enabled( 'asset-minification' ),
				'score'        => self::is_feature_enabled( 'asset-minification' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'database-cleanup' => array(
				'enabled'      => self::is_feature_enabled( 'database-cleanup' ),
				'score'        => self::calculate_database_cleanup_score(),
				'category'     => 'performance',
				'sub_features' => self::get_database_cleanup_sub_features(),
			),
			'image-lazy-loading' => array(
				'enabled'      => self::is_feature_enabled( 'image-lazy-loading' ),
				'score'        => self::is_feature_enabled( 'image-lazy-loading' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'script-optimizer' => array(
				'enabled'      => self::is_feature_enabled( 'script-optimizer' ),
				'score'        => self::is_feature_enabled( 'script-optimizer' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'conditional-loading' => array(
				'enabled'      => self::is_feature_enabled( 'conditional-loading' ),
				'score'        => self::is_feature_enabled( 'conditional-loading' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'head-cleanup' => array(
				'enabled'      => self::is_feature_enabled( 'head-cleanup' ),
				'score'        => self::calculate_head_cleanup_score(),
				'category'     => 'performance',
				'sub_features' => self::get_head_cleanup_sub_features(),
			),
			'resource-hints' => array(
				'enabled'      => self::is_feature_enabled( 'resource-hints' ),
				'score'        => self::is_feature_enabled( 'resource-hints' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'embed-disable' => array(
				'enabled'      => self::is_feature_enabled( 'embed-disable' ),
				'score'        => self::is_feature_enabled( 'embed-disable' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'jquery-cleanup' => array(
				'enabled'      => self::is_feature_enabled( 'jquery-cleanup' ),
				'score'        => self::is_feature_enabled( 'jquery-cleanup' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'block-css-cleanup' => array(
				'enabled'      => self::is_feature_enabled( 'block-css-cleanup' ),
				'score'        => self::is_feature_enabled( 'block-css-cleanup' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'google-fonts-disabler' => array(
				'enabled'      => self::is_feature_enabled( 'google-fonts-disabler' ),
				'score'        => self::is_feature_enabled( 'google-fonts-disabler' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'asset-version-removal' => array(
				'enabled'      => self::is_feature_enabled( 'asset-version-removal' ),
				'score'        => self::is_feature_enabled( 'asset-version-removal' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'block-cleanup' => array(
				'enabled'      => self::is_feature_enabled( 'block-cleanup' ),
				'score'        => self::is_feature_enabled( 'block-cleanup' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'css-class-cleanup' => array(
				'enabled'      => self::is_feature_enabled( 'css-class-cleanup' ),
				'score'        => self::is_feature_enabled( 'css-class-cleanup' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'plugin-cleanup' => array(
				'enabled'      => self::is_feature_enabled( 'plugin-cleanup' ),
				'score'        => self::is_feature_enabled( 'plugin-cleanup' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'html-cleanup' => array(
				'enabled'      => self::is_feature_enabled( 'html-cleanup' ),
				'score'        => self::is_feature_enabled( 'html-cleanup' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),
			'interactivity-cleanup' => array(
				'enabled'      => self::is_feature_enabled( 'interactivity-cleanup' ),
				'score'        => self::is_feature_enabled( 'interactivity-cleanup' ) ? 100 : 0,
				'category'     => 'performance',
				'sub_features' => array(),
			),

			// ===== ACCESSIBILITY FEATURES =====
			'nav-accessibility' => array(
				'enabled'      => self::is_feature_enabled( 'nav-accessibility' ),
				'score'        => self::is_feature_enabled( 'nav-accessibility' ) ? 100 : 0,
				'category'     => 'accessibility',
				'sub_features' => array(),
			),
			'skiplinks' => array(
				'enabled'      => self::is_feature_enabled( 'skiplinks' ),
				'score'        => self::is_feature_enabled( 'skiplinks' ) ? 100 : 0,
				'category'     => 'accessibility',
				'sub_features' => array(),
			),

			// ===== MAINTENANCE & TOOLS =====
			'maintenance-cleanup' => array(
				'enabled'      => self::is_feature_enabled( 'maintenance-cleanup' ),
				'score'        => self::is_feature_enabled( 'maintenance-cleanup' ) ? 100 : 0,
				'category'     => 'tools',
				'sub_features' => array(),
			),
			'auto-rollback' => array(
				'enabled'      => self::is_feature_enabled( 'auto-rollback' ),
				'score'        => self::is_feature_enabled( 'auto-rollback' ) ? 100 : 0,
				'category'     => 'tools',
				'sub_features' => array(),
			),

			// ===== REPORTING & ANALYTICS =====
			'weekly-performance-report' => array(
				'enabled'      => self::is_feature_enabled( 'weekly-performance-report' ),
				'score'        => self::is_feature_enabled( 'weekly-performance-report' ) ? 100 : 0,
				'category'     => 'reporting',
				'sub_features' => array(),
			),
			'performance-alerts' => array(
				'enabled'      => self::is_feature_enabled( 'performance-alerts' ),
				'score'        => self::is_feature_enabled( 'performance-alerts' ) ? 100 : 0,
				'category'     => 'reporting',
				'sub_features' => array(),
			),
			'smart-recommendations' => array(
				'enabled'      => self::is_feature_enabled( 'smart-recommendations' ),
				'score'        => self::is_feature_enabled( 'smart-recommendations' ) ? 100 : 0,
				'category'     => 'reporting',
				'sub_features' => array(),
			),

			// ===== PRIVACY & COMPLIANCE =====
			'consent-checks' => array(
				'enabled'      => self::is_feature_enabled( 'consent-checks' ),
				'score'        => self::is_feature_enabled( 'consent-checks' ) ? 100 : 0,
				'category'     => 'privacy',
				'sub_features' => array(),
			),

			// ===== DIAGNOSTIC & MONITORING =====
			'core-diagnostics' => array(
				'enabled'      => self::is_feature_enabled( 'core-diagnostics' ),
				'score'        => self::is_feature_enabled( 'core-diagnostics' ) ? 100 : 0,
				'category'     => 'diagnostic',
				'sub_features' => array(),
			),
			'vault-audit' => array(
				'enabled'      => self::is_feature_enabled( 'vault-audit' ),
				'score'        => self::is_feature_enabled( 'vault-audit' ) ? 100 : 0,
				'category'     => 'diagnostic',
				'sub_features' => array(),
			),
			'vulnerability-watch' => array(
				'enabled'      => self::is_feature_enabled( 'vulnerability-watch' ),
				'score'        => self::is_feature_enabled( 'vulnerability-watch' ) ? 100 : 0,
				'category'     => 'diagnostic',
				'sub_features' => array(),
			),
			'image-smart-focus' => array(
				'enabled'      => self::is_feature_enabled( 'image-smart-focus' ),
				'score'        => self::is_feature_enabled( 'image-smart-focus' ) ? 100 : 0,
				'category'     => 'diagnostic',
				'sub_features' => array(),
			),
		);

		return $cache;
	}

	/**
	 * Calculate firewall score based on enabled sub-features.
	 *
	 * @return int Score (0-100).
	 */
	private static function calculate_firewall_score(): int {
		if ( ! self::is_feature_enabled( 'firewall' ) ) {
			return 0;
		}

		$score = 0;
		$blocked_ips = get_option( 'wpshadow_firewall_blocked_ips', array() );

		// Base score for being enabled.
		$score += 40;

		// Bonus for active blocklist.
		if ( ! empty( $blocked_ips ) ) {
			$score += 30;
		}

		// Bonus for rate limiting configuration.
		if ( get_option( 'wpshadow_firewall_rate_limit', 100 ) < 100 ) {
			$score += 30;
		}

		return min( 100, $score );
	}

	/**
	 * Calculate malware scanner score.
	 *
	 * @return int Score (0-100).
	 */
	private static function calculate_malware_scanner_score(): int {
		if ( ! self::is_feature_enabled( 'malware-scanner' ) ) {
			return 0;
		}

		$last_scan = get_option( 'wpshadow_last_malware_scan', 0 );
		$threats   = get_option( 'wpshadow_malware_threats', array() );

		$score = 50; // Base for being enabled.

		// Recent scan bonus.
		if ( $last_scan && ( time() - $last_scan ) < DAY_IN_SECONDS ) {
			$score += 30;
		}

		// No threats bonus.
		if ( empty( $threats ) ) {
			$score += 20;
		}

		return min( 100, $score );
	}

	/**
	 * Calculate core integrity score.
	 *
	 * @return int Score (0-100).
	 */
	private static function calculate_core_integrity_score(): int {
		if ( ! self::is_feature_enabled( 'core-integrity' ) ) {
			return 0;
		}

		$last_check = get_option( 'wpshadow_core_integrity_last_check', 0 );
		$issues     = get_option( 'wpshadow_core_integrity_issues', array() );

		$score = 50; // Base for being enabled.

		// Recent check bonus.
		if ( $last_check && ( time() - $last_check ) < DAY_IN_SECONDS ) {
			$score += 30;
		}

		// No issues bonus.
		if ( empty( $issues ) ) {
			$score += 20;
		}

		return min( 100, $score );
	}

	/**
	 * Calculate page cache score.
	 *
	 * @return int Score (0-100).
	 */
	private static function calculate_page_cache_score(): int {
		if ( ! self::is_feature_enabled( 'page-cache' ) ) {
			return 0;
		}

		$cache_dir = WP_CONTENT_DIR . '/cache/wps-page-cache';
		$score     = 60; // Base for being enabled.

		// Check if cache is actually being used.
		if ( file_exists( $cache_dir ) ) {
			$files = glob( $cache_dir . '/*.html' );
			if ( $files && count( $files ) > 0 ) {
				$score += 40;
			}
		}

		return min( 100, $score );
	}

	/**
	 * Calculate CDN score.
	 *
	 * @return int Score (0-100).
	 */
	private static function calculate_cdn_score(): int {
		if ( ! self::is_feature_enabled( 'cdn-integration' ) ) {
			return 0;
		}

		$cdn_hostname = get_option( 'wpshadow_cdn_hostname', '' );

		if ( ! empty( $cdn_hostname ) ) {
			return 100;
		}

		return 50; // Enabled but not configured.
	}

	/**
	 * Calculate image optimizer score.
	 *
	 * @return int Score (0-100).
	 */
	private static function calculate_image_optimizer_score(): int {
		if ( ! self::is_feature_enabled( 'image-optimizer' ) ) {
			return 0;
		}

		$optimized = get_option( 'wpshadow_images_optimized', 0 );

		if ( $optimized > 100 ) {
			return 100;
		} elseif ( $optimized > 50 ) {
			return 80;
		} elseif ( $optimized > 10 ) {
			return 60;
		}

		return 40; // Enabled but minimal usage.
	}

	/**
	 * Calculate head cleanup score based on enabled sub-features.
	 *
	 * @return int Score (0-100).
	 */
	private static function calculate_head_cleanup_score(): int {
		if ( ! self::is_feature_enabled( 'head-cleanup' ) ) {
			return 0;
		}

		$score = 0;
		$sub_features = self::get_head_cleanup_sub_features();

		foreach ( $sub_features as $sub ) {
			if ( $sub['enabled'] ) {
				$score += $sub['points'];
			}
		}

		return min( 100, $score );
	}

	/**
	 * Calculate database cleanup score.
	 *
	 * @return int Score (0-100).
	 */
	private static function calculate_database_cleanup_score(): int {
		if ( ! self::is_feature_enabled( 'database-cleanup' ) ) {
			return 0;
		}

		$score = 0;
		$sub_features = self::get_database_cleanup_sub_features();

		foreach ( $sub_features as $sub ) {
			if ( $sub['enabled'] ) {
				$score += $sub['points'];
			}
		}

		return min( 100, $score );
	}

	/**
	 * Get head cleanup sub-feature states and points.
	 *
	 * @return array<string, array{enabled:bool,points:int}>
	 */
	private static function get_head_cleanup_sub_features(): array {
		return array(
			'rsd_link'         => array( 'enabled' => (bool) get_option( 'wpshadow_remove_rsd_link', false ), 'points' => 5 ),
			'wlwmanifest_link' => array( 'enabled' => (bool) get_option( 'wpshadow_remove_wlwmanifest', false ), 'points' => 5 ),
			'shortlink'        => array( 'enabled' => (bool) get_option( 'wpshadow_remove_shortlink', false ), 'points' => 5 ),
			'wp_generator'     => array( 'enabled' => (bool) get_option( 'wpshadow_remove_wp_version', false ), 'points' => 10 ),
			'feed_links'       => array( 'enabled' => (bool) get_option( 'wpshadow_remove_feed_links', false ), 'points' => 10 ),
			'rest_api_link'    => array( 'enabled' => (bool) get_option( 'wpshadow_remove_rest_api_link', false ), 'points' => 10 ),
			'oembed_links'     => array( 'enabled' => (bool) get_option( 'wpshadow_remove_oembed', false ), 'points' => 15 ),
			'emoji_scripts'    => array( 'enabled' => (bool) get_option( 'wpshadow_disable_emojis', false ), 'points' => 20 ),
			'dns_prefetch'     => array( 'enabled' => (bool) get_option( 'wpshadow_remove_dns_prefetch', false ), 'points' => 20 ),
		);
	}

	/**
	 * Get database cleanup sub-feature states and points.
	 *
	 * @return array<string, array{enabled:bool,points:int}>
	 */
	private static function get_database_cleanup_sub_features(): array {
		return array(
			'revisions'       => array( 'enabled' => (bool) get_option( 'wpshadow_cleanup_revisions', false ), 'points' => 25 ),
			'auto_drafts'     => array( 'enabled' => (bool) get_option( 'wpshadow_cleanup_autodrafts', false ), 'points' => 20 ),
			'trashed_posts'   => array( 'enabled' => (bool) get_option( 'wpshadow_cleanup_trash', false ), 'points' => 15 ),
			'spam_comments'   => array( 'enabled' => (bool) get_option( 'wpshadow_cleanup_spam', false ), 'points' => 15 ),
			'transients'      => array( 'enabled' => (bool) get_option( 'wpshadow_cleanup_transients', false ), 'points' => 15 ),
			'optimize_tables' => array( 'enabled' => (bool) get_option( 'wpshadow_optimize_tables', false ), 'points' => 10 ),
		);
	}

	/**
	 * Check if a feature is enabled.
	 *
	 * @param string $feature_id Feature identifier.
	 * @return bool True if enabled.
	 */
	private static function is_feature_enabled( string $feature_id ): bool {
		return WPSHADOW_Feature_Registry::is_feature_enabled( $feature_id );
	}

	/**
	 * Get list of enabled features.
	 *
	 * @return array Enabled feature IDs.
	 */
	private static function get_enabled_features(): array {
		$enabled = array();
		$all_scores = self::get_all_feature_scores();

		foreach ( $all_scores as $feature_id => $data ) {
			if ( $data['enabled'] ) {
				$enabled[] = $feature_id;
			}
		}

		return $enabled;
	}

	/**
	 * Add WPS debug information to Site Health Info tab.
	 *
	 * @param array $info Existing debug information.
	 * @return array Modified debug information.
	 */
	public static function add_debug_information( array $info ): array {
		$overall_score     = self::calculate_overall_health();
		$enabled_features  = self::get_enabled_features();
		$category_breakdown = self::get_category_breakdown();

		$info['wpshadow'] = array(
			'label'  => __( 'WPShadow', 'plugin-wpshadow' ),
			'fields' => array(
				'version' => array(
					'label' => __( 'Plugin Version', 'plugin-wpshadow' ),
					'value' => defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : 'Unknown',
				),
				'overall_health' => array(
					'label' => __( 'Overall Health Score', 'plugin-wpshadow' ),
					'value' => $overall_score . '/100',
				),
				'enabled_features' => array(
					'label' => __( 'Enabled Features', 'plugin-wpshadow' ),
					'value' => count( $enabled_features ),
				),
				'feature_list' => array(
					'label' => __( 'Active Features', 'plugin-wpshadow' ),
					'value' => ! empty( $enabled_features ) ? implode( ', ', $enabled_features ) : __( 'None', 'plugin-wpshadow' ),
				),
			),
		);

		// Add category scores.
		foreach ( $category_breakdown as $category => $data ) {
			$info['wpshadow']['fields'][ $category . '_score' ] = array(
				'label' => sprintf(
					/* translators: %s: category name */
					__( '%s Score', 'plugin-wpshadow' ),
					ucfirst( $category )
				),
				'value' => $data['score'] . '/100 (' . $data['enabled'] . '/' . $data['total'] . ' features)',
			);
		}

		return $info;
	}

	/**
	 * Get category breakdown with scores and feature counts.
	 *
	 * @return array Category breakdown data.
	 */
	public static function get_category_breakdown(): array {
		$all_features = self::get_all_feature_scores();
		$categories   = array(
			'security'      => array( 'score' => 0, 'enabled' => 0, 'total' => 0 ),
			'performance'   => array( 'score' => 0, 'enabled' => 0, 'total' => 0 ),
			'accessibility' => array( 'score' => 0, 'enabled' => 0, 'total' => 0 ),
			'tools'         => array( 'score' => 0, 'enabled' => 0, 'total' => 0 ),
			'reporting'     => array( 'score' => 0, 'enabled' => 0, 'total' => 0 ),
			'privacy'       => array( 'score' => 0, 'enabled' => 0, 'total' => 0 ),
			'diagnostic'    => array( 'score' => 0, 'enabled' => 0, 'total' => 0 ),
		);

		foreach ( $all_features as $feature ) {
			$category = $feature['category'] ?? 'other';
			if ( isset( $categories[ $category ] ) ) {
				$categories[ $category ]['total']++;
				if ( $feature['enabled'] ) {
					$categories[ $category ]['enabled']++;
				}
			}
		}

		// Calculate scores.
		foreach ( array_keys( $categories ) as $category ) {
			$categories[ $category ]['score'] = self::calculate_category_score( $category );
		}

		return $categories;
	}

	/**
	 * AJAX handler to get health score.
	 *
	 * @return void
	 */
	public static function ajax_get_health_score(): void {
		check_ajax_referer( 'wps-health', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'You don\'t have permission to do that', 'plugin-wpshadow' ) ) );
		}

		$overall            = self::calculate_overall_health();
		$all_scores         = self::get_all_feature_scores();
		$category_breakdown = self::get_category_breakdown();

		wp_send_json_success(
			array(
				'overall'    => $overall,
				'categories' => $category_breakdown,
				'features'   => $all_scores,
			)
		);
	}
}
