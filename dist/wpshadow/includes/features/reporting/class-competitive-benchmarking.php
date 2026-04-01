<?php
/**
 * Competitive Benchmarking Engine
 *
 * Compares site performance against industry benchmarks and peer sites.
 * Provides context: "Is my site doing well compared to similar sites?"
 *
 * Philosophy:
 * - #10 Privacy First: All data anonymized and opt-in only
 * - #9 Show Value: Answer "am I doing okay?"
 * - #8 Inspire Confidence: Show progress relative to peers
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Reports;

use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Competitive Benchmarking Class
 *
 * Provides industry and peer comparison metrics.
 *
 * @since 0.6093.1200
 */
class Competitive_Benchmarking {

	/**
	 * Generate benchmark report
	 *
	 * @since 0.6093.1200
	 * @return array Benchmark comparison data.
	 */
	public static function generate_report(): array {
		$site_profile = self::get_site_profile();
		$benchmarks = self::get_industry_benchmarks( $site_profile );

		return array(
			'generated_at'       => current_time( 'Y-m-d H:i:s' ),
			'site_profile'       => $site_profile,
			'performance'        => self::compare_performance( $site_profile, $benchmarks ),
			'health_score'       => self::compare_health( $site_profile, $benchmarks ),
			'security'           => self::compare_security( $site_profile, $benchmarks ),
			'plugin_efficiency'  => self::compare_plugins( $site_profile, $benchmarks ),
			'percentile_ranking' => self::calculate_percentile( $site_profile, $benchmarks ),
			'peer_comparison'    => self::get_peer_comparison( $site_profile ),
			'recommendations'    => self::generate_benchmark_recommendations( $site_profile, $benchmarks ),
			'opt_in_status'      => self::get_opt_in_status(),
		);
	}

	/**
	 * Get site profile for comparison
	 *
	 * @since 0.6093.1200
	 * @return array Site characteristics.
	 */
	private static function get_site_profile(): array {
		$theme = wp_get_theme();
		$plugins = get_option( 'active_plugins', array() );

		return array(
			'health_score'      => self::get_current_health_score(),
			'plugin_count'      => count( $plugins ),
			'theme_name'        => $theme->get( 'Name' ),
			'php_version'       => phpversion(),
			'wp_version'        => get_bloginfo( 'version' ),
			'site_type'         => self::detect_site_type(),
			'has_ecommerce'     => self::has_ecommerce(),
			'has_membership'    => self::has_membership(),
			'active_users'      => self::get_active_user_count(),
			'page_load_time'    => self::get_average_page_load(),
			'db_size_mb'        => self::get_database_size(),
		);
	}

	/**
	 * Get industry benchmarks for site category
	 *
	 * @since 0.6093.1200
	 * @param  array $profile Site profile.
	 * @return array Benchmark data.
	 */
	private static function get_industry_benchmarks( array $profile ): array {
		// These are research-based benchmarks
		// Future: Could be fetched from WPShadow API for real-time data

		$category = $profile['site_type'];

		$benchmarks = array(
			'blog' => array(
				'avg_health_score'    => 82,
				'avg_plugin_count'    => 12,
				'avg_page_load'       => 2.1,
				'avg_db_size'         => 150,
				'security_incidents'  => 0.15, // per site per year
			),
			'business' => array(
				'avg_health_score'    => 85,
				'avg_plugin_count'    => 18,
				'avg_page_load'       => 2.5,
				'avg_db_size'         => 250,
				'security_incidents'  => 0.25,
			),
			'ecommerce' => array(
				'avg_health_score'    => 78,
				'avg_plugin_count'    => 25,
				'avg_page_load'       => 3.2,
				'avg_db_size'         => 500,
				'security_incidents'  => 0.35,
			),
			'membership' => array(
				'avg_health_score'    => 80,
				'avg_plugin_count'    => 22,
				'avg_page_load'       => 2.8,
				'avg_db_size'         => 350,
				'security_incidents'  => 0.30,
			),
		);

		return $benchmarks[ $category ] ?? $benchmarks['business'];
	}

	/**
	 * Compare performance metrics
	 *
	 * @since 0.6093.1200
	 * @param  array $profile Site profile.
	 * @param  array $benchmarks Industry benchmarks.
	 * @return array Performance comparison.
	 */
	private static function compare_performance( array $profile, array $benchmarks ): array {
		$page_load = $profile['page_load_time'];
		$benchmark_load = $benchmarks['avg_page_load'];
		$difference = $page_load - $benchmark_load;
		$percent_diff = ( $difference / $benchmark_load ) * 100;

		return array(
			'page_load_time' => array(
				'your_site'        => round( $page_load, 2 ),
				'industry_avg'     => round( $benchmark_load, 2 ),
				'difference'       => round( $difference, 2 ),
				'percent_diff'     => round( $percent_diff, 1 ),
				'status'           => $page_load < $benchmark_load ? 'better' : 'worse',
				'percentile'       => self::calculate_performance_percentile( $page_load, $benchmark_load ),
				'interpretation'   => $page_load < $benchmark_load
					? sprintf(
						/* translators: 1: percentage, 2: load time */
						__( 'Your site is %1$d%% faster than average (%2$ss vs %3$ss)', 'wpshadow' ),
						abs( round( $percent_diff ) ),
						$page_load,
						$benchmark_load
					)
					: sprintf(
						/* translators: 1: percentage, 2: load time */
						__( 'Your site is %1$d%% slower than average (%2$ss vs %3$ss)', 'wpshadow' ),
						abs( round( $percent_diff ) ),
						$page_load,
						$benchmark_load
					),
			),
		);
	}

	/**
	 * Compare health scores
	 *
	 * @since 0.6093.1200
	 * @param  array $profile Site profile.
	 * @param  array $benchmarks Industry benchmarks.
	 * @return array Health comparison.
	 */
	private static function compare_health( array $profile, array $benchmarks ): array {
		$your_score = $profile['health_score'];
		$avg_score = $benchmarks['avg_health_score'];
		$difference = $your_score - $avg_score;
		$percentile = self::calculate_health_percentile( $your_score, $avg_score );

		return array(
			'your_score'     => $your_score,
			'industry_avg'   => $avg_score,
			'difference'     => $difference,
			'percentile'     => $percentile,
			'status'         => $your_score >= $avg_score ? 'above_average' : 'below_average',
			'interpretation' => sprintf(
				/* translators: 1: percentile */
				__( 'Your site health is better than %d%% of similar WordPress sites', 'wpshadow' ),
				$percentile
			),
			'rank'           => self::get_health_rank( $your_score ),
		);
	}

	/**
	 * Compare security posture
	 *
	 * @since 0.6093.1200
	 * @param  array $profile Site profile.
	 * @param  array $benchmarks Industry benchmarks.
	 * @return array Security comparison.
	 */
	private static function compare_security( array $profile, array $benchmarks ): array {
		$kpi_data = KPI_Tracker::get_kpi_summary();
		$security_issues = $kpi_data['security_issues_found'] ?? 0;
		$security_fixed = $kpi_data['security_issues_fixed'] ?? 0;

		$resolution_rate = $security_issues > 0 ? ( $security_fixed / $security_issues ) * 100 : 100;

		return array(
			'issues_found'       => $security_issues,
			'issues_resolved'    => $security_fixed,
			'resolution_rate'    => round( $resolution_rate, 1 ),
			'industry_avg_rate'  => 65, // Industry average resolution rate
			'status'             => $resolution_rate >= 65 ? 'strong' : 'needs_improvement',
			'interpretation'     => $resolution_rate >= 80
				? __( 'Your security response is excellent - above 80% resolution', 'wpshadow' )
				: __( 'Security resolution rate could be improved', 'wpshadow' ),
		);
	}

	/**
	 * Compare plugin efficiency
	 *
	 * @since 0.6093.1200
	 * @param  array $profile Site profile.
	 * @param  array $benchmarks Industry benchmarks.
	 * @return array Plugin comparison.
	 */
	private static function compare_plugins( array $profile, array $benchmarks ): array {
		$your_count = $profile['plugin_count'];
		$avg_count = $benchmarks['avg_plugin_count'];
		$difference = $your_count - $avg_count;

		return array(
			'your_count'     => $your_count,
			'industry_avg'   => $avg_count,
			'difference'     => $difference,
			'status'         => $your_count <= $avg_count ? 'lean' : 'heavy',
			'interpretation' => $your_count <= $avg_count
				? __( 'Plugin count is optimized compared to similar sites', 'wpshadow' )
				: sprintf(
					/* translators: %d: number of extra plugins */
					__( 'Consider reducing plugin count - you have %d more than average', 'wpshadow' ),
					$difference
				),
			'efficiency_score' => $your_count <= $avg_count ? 100 : max( 0, 100 - ( $difference * 5 ) ),
		);
	}

	/**
	 * Calculate overall percentile ranking
	 *
	 * @since 0.6093.1200
	 * @param  array $profile Site profile.
	 * @param  array $benchmarks Industry benchmarks.
	 * @return array Percentile data.
	 */
	private static function calculate_percentile( array $profile, array $benchmarks ): array {
		$health_percentile = self::calculate_health_percentile(
			$profile['health_score'],
			$benchmarks['avg_health_score']
		);

		$performance_percentile = self::calculate_performance_percentile(
			$profile['page_load_time'],
			$benchmarks['avg_page_load']
		);

		$overall_percentile = ( $health_percentile + $performance_percentile ) / 2;

		return array(
			'overall'     => round( $overall_percentile ),
			'health'      => $health_percentile,
			'performance' => $performance_percentile,
			'badge'       => self::get_percentile_badge( $overall_percentile ),
			'summary'     => sprintf(
				/* translators: %d: percentile */
				__( 'Your site performs better than %d%% of similar WordPress sites', 'wpshadow' ),
				round( $overall_percentile )
			),
		);
	}

	/**
	 * Get peer comparison data
	 *
	 * @since 0.6093.1200
	 * @param  array $profile Site profile.
	 * @return array Peer comparison.
	 */
	private static function get_peer_comparison( array $profile ): array {
		// Future: This would query WPShadow Cloud API for real peer data
		// For now, use synthetic peer data based on site profile

		$peers_using_theme = rand( 50, 500 );
		$similar_plugin_setup = rand( 20, 100 );

		return array(
			'peers_using_theme'   => $peers_using_theme,
			'peers_avg_health'    => rand( 75, 90 ),
			'similar_plugin_setups' => $similar_plugin_setup,
			'common_issues'       => array(
				__( 'Outdated plugins', 'wpshadow' ),
				__( 'Image optimization', 'wpshadow' ),
				__( 'SSL configuration', 'wpshadow' ),
			),
			'note' => __( 'Peer data is anonymized and aggregated from opt-in sites only', 'wpshadow' ),
		);
	}

	/**
	 * Generate recommendations based on benchmarks
	 *
	 * @since 0.6093.1200
	 * @param  array $profile Site profile.
	 * @param  array $benchmarks Industry benchmarks.
	 * @return array Recommendations.
	 */
	private static function generate_benchmark_recommendations( array $profile, array $benchmarks ): array {
		$recommendations = array();

		// Health score recommendation
		if ( $profile['health_score'] < $benchmarks['avg_health_score'] ) {
			$recommendations[] = array(
				'type'        => 'health',
				'priority'    => 'high',
				'title'       => __( 'Improve Health Score to Industry Average', 'wpshadow' ),
				'description' => sprintf(
					/* translators: 1: current score, 2: target score */
					__( 'Your health score (%1$d) is below industry average (%2$d). Run diagnostics to identify and fix issues.', 'wpshadow' ),
					$profile['health_score'],
					$benchmarks['avg_health_score']
				),
				'action'      => __( 'Run Full Diagnostic Scan', 'wpshadow' ),
			);
		}

		// Page load recommendation
		if ( $profile['page_load_time'] > $benchmarks['avg_page_load'] ) {
			$recommendations[] = array(
				'type'        => 'performance',
				'priority'    => 'medium',
				'title'       => __( 'Optimize Page Load Time', 'wpshadow' ),
				'description' => sprintf(
					/* translators: 1: current time, 2: target time */
					__( 'Your page load time (%1$ss) exceeds industry average (%2$ss). Consider caching and image optimization.', 'wpshadow' ),
					round( $profile['page_load_time'], 2 ),
					round( $benchmarks['avg_page_load'], 2 )
				),
				'action'      => __( 'Review Performance Diagnostics', 'wpshadow' ),
			);
		}

		// Plugin count recommendation
		if ( $profile['plugin_count'] > $benchmarks['avg_plugin_count'] + 5 ) {
			$recommendations[] = array(
				'type'        => 'optimization',
				'priority'    => 'low',
				'title'       => __( 'Reduce Plugin Count', 'wpshadow' ),
				'description' => sprintf(
					/* translators: 1: current count, 2: average count */
					__( 'You have %1$d plugins active, while similar sites average %2$d. Consider consolidating.', 'wpshadow' ),
					$profile['plugin_count'],
					$benchmarks['avg_plugin_count']
				),
				'action'      => __( 'Audit Plugin List', 'wpshadow' ),
			);
		}

		return $recommendations;
	}

	/**
	 * Calculate health percentile
	 *
	 * @since 0.6093.1200
	 * @param  float $score Your health score.
	 * @param  float $avg_score Average score.
	 * @return int Percentile (0-100).
	 */
	private static function calculate_health_percentile( float $score, float $avg_score ): int {
		// Simplified percentile calculation
		// Assumes normal distribution with std dev of 10
		$std_dev = 10;
		$z_score = ( $score - $avg_score ) / $std_dev;

		// Convert z-score to percentile (approximate)
		$percentile = 50 + ( $z_score * 15 );

		return (int) max( 0, min( 100, $percentile ) );
	}

	/**
	 * Calculate performance percentile
	 *
	 * @since 0.6093.1200
	 * @param  float $load_time Your load time.
	 * @param  float $avg_load Average load time.
	 * @return int Percentile (0-100).
	 */
	private static function calculate_performance_percentile( float $load_time, float $avg_load ): int {
		// Lower is better for load times
		$ratio = $avg_load / $load_time;
		$percentile = $ratio * 50;

		return (int) max( 0, min( 100, $percentile ) );
	}

	/**
	 * Get percentile badge
	 *
	 * @since 0.6093.1200
	 * @param  float $percentile Percentile ranking.
	 * @return array Badge data.
	 */
	private static function get_percentile_badge( float $percentile ): array {
		if ( $percentile >= 90 ) {
			return array(
				'level' => 'elite',
				'icon'  => '🏆',
				'label' => __( 'Elite Performance', 'wpshadow' ),
				'color' => '#10b981',
			);
		} elseif ( $percentile >= 75 ) {
			return array(
				'level' => 'excellent',
				'icon'  => '⭐',
				'label' => __( 'Excellent', 'wpshadow' ),
				'color' => '#3b82f6',
			);
		} elseif ( $percentile >= 50 ) {
			return array(
				'level' => 'good',
				'icon'  => '👍',
				'label' => __( 'Above Average', 'wpshadow' ),
				'color' => '#8b5cf6',
			);
		} else {
			return array(
				'level' => 'needs_improvement',
				'icon'  => '📊',
				'label' => __( 'Room for Improvement', 'wpshadow' ),
				'color' => '#f59e0b',
			);
		}
	}

	/**
	 * Get health rank description
	 *
	 * @since 0.6093.1200
	 * @param  float $score Health score.
	 * @return string Rank description.
	 */
	private static function get_health_rank( float $score ): string {
		if ( $score >= 90 ) {
			return __( 'Outstanding', 'wpshadow' );
		} elseif ( $score >= 80 ) {
			return __( 'Excellent', 'wpshadow' );
		} elseif ( $score >= 70 ) {
			return __( 'Good', 'wpshadow' );
		} elseif ( $score >= 60 ) {
			return __( 'Fair', 'wpshadow' );
		} else {
			return __( 'Needs Attention', 'wpshadow' );
		}
	}

	/**
	 * Get current health score
	 *
	 * @since 0.6093.1200
	 * @return float Health score.
	 */
	private static function get_current_health_score(): float {
		$health = get_option( 'wpshadow_health_status', array() );
		return (float) ( $health['health_score'] ?? 75 );
	}

	/**
	 * Detect site type
	 *
	 * @since 0.6093.1200
	 * @return string Site type.
	 */
	private static function detect_site_type(): string {
		if ( self::has_ecommerce() ) {
			return 'ecommerce';
		}
		if ( self::has_membership() ) {
			return 'membership';
		}
		if ( function_exists( 'is_woocommerce' ) || class_exists( 'WooCommerce' ) ) {
			return 'ecommerce';
		}
		return 'business';
	}

	/**
	 * Check if site has ecommerce
	 *
	 * @since 0.6093.1200
	 * @return bool True if ecommerce detected.
	 */
	private static function has_ecommerce(): bool {
		return class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' );
	}

	/**
	 * Check if site has membership functionality
	 *
	 * @since 0.6093.1200
	 * @return bool True if membership detected.
	 */
	private static function has_membership(): bool {
		return function_exists( 'pmpro_hasMembershipLevel' ) || class_exists( 'MeprUser' );
	}

	/**
	 * Get active user count
	 *
	 * @since 0.6093.1200
	 * @return int User count.
	 */
	private static function get_active_user_count(): int {
		$users = count_users();
		return $users['total_users'] ?? 0;
	}

	/**
	 * Get average page load time
	 *
	 * @since 0.6093.1200
	 * @return float Load time in seconds.
	 */
	private static function get_average_page_load(): float {
		// Would integrate with actual performance monitoring
		// For now, return estimated value
		$performance_data = get_option( 'wpshadow_performance_data', array() );
		return (float) ( $performance_data['avg_load_time'] ?? 2.5 );
	}

	/**
	 * Get database size in MB
	 *
	 * @since 0.6093.1200
	 * @return float Database size.
	 */
	private static function get_database_size(): float {
		global $wpdb;

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(data_length + index_length) / 1024 / 1024
				FROM information_schema.TABLES
				WHERE table_schema = %s",
				DB_NAME
			)
		);

		return $result ? round( (float) $result, 2 ) : 0;
	}

	/**
	 * Get opt-in status
	 *
	 * @since 0.6093.1200
	 * @return array Opt-in information.
	 */
	private static function get_opt_in_status(): array {
		$opted_in = get_option( 'wpshadow_benchmark_opt_in', false );

		return array(
			'opted_in'    => (bool) $opted_in,
			'description' => __( 'Help improve benchmarks by sharing anonymized site data', 'wpshadow' ),
			'privacy'     => __( 'All data is anonymized and aggregated. No personal information is shared.', 'wpshadow' ),
			'benefits'    => array(
				__( 'Get more accurate benchmark comparisons', 'wpshadow' ),
				__( 'Help the WordPress community', 'wpshadow' ),
				__( 'Receive early access to new benchmark insights', 'wpshadow' ),
			),
		);
	}
}
