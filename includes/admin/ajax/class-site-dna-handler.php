<?php
/**
 * Site DNA Report AJAX Handler
 *
 * Generates comprehensive site DNA reports by analyzing diagnostics
 * across all categories and producing visual health metrics.
 *
 * @package    WPShadow
 * @subpackage Admin\AJAX
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\AJAX;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Diagnostics\Diagnostic_Registry;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site_DNA_Handler Class
 *
 * Handles AJAX requests for generating Site DNA Reports.
 * Aggregates diagnostic results across categories to produce
 * a comprehensive health score and visualization data.
 *
 * @since 0.6093.1200
 */
class Site_DNA_Handler extends AJAX_Handler_Base {

	/**
	 * Register the AJAX action.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_generate_dna', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle the DNA generation request.
	 *
	 * Generates a comprehensive site DNA report by running diagnostics
	 * and aggregating results into category scores and overall health metrics.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle() {
		// Verify request security.
		self::verify_request( 'wpshadow_generate_dna', 'manage_options' );

		// Get form parameters.
		$depth     = self::get_post_param( 'depth', 'text', 'standard' );
		$benchmark = self::get_post_param( 'benchmark', 'text', 'industry' );

		// Validate depth.
		if ( ! in_array( $depth, array( 'quick', 'standard', 'deep' ), true ) ) {
			$depth = 'standard';
		}

		// Generate the report.
		$report = self::generate_report( $depth );

		// Add benchmark data.
		$report['benchmark']      = $benchmark;
		$report['benchmark_data'] = self::get_benchmark_data( $benchmark, $report['overall_score'] );

		self::send_success( $report );
	}

	/**
	 * Generate the DNA report.
	 *
	 * Runs diagnostics based on depth level and aggregates results
	 * into category scores and overall health metrics.
	 *
	 * @since 0.6093.1200
	 * @param  string $depth Analysis depth ('quick'|'standard'|'deep').
	 * @return array {
	 *     Report data.
	 *
	 *     @type float  $overall_score     Overall health score (0-100).
	 *     @type int    $diagnostics_checked Number of diagnostics run.
	 *     @type array  $categories        Category scores and data.
	 *     @type array  $insights          Key findings and recommendations.
	 *     @type string $timestamp         Report generation timestamp.
	 * }
	 */
	private static function generate_report( string $depth ): array {
		$registry    = new Diagnostic_Registry();
		$diagnostics = $registry->get_all();

		// Determine which diagnostics to run based on depth.
		$diagnostics_to_run = self::filter_diagnostics_by_depth( $diagnostics, $depth );

		// Run diagnostics and aggregate by category.
		$category_results = self::run_diagnostics_by_category( $diagnostics_to_run );

		// Calculate overall score.
		$overall_score = self::calculate_overall_score( $category_results );

		// Generate insights.
		$insights = self::generate_insights( $category_results, $overall_score );

		return array(
			'overall_score'       => $overall_score,
			'diagnostics_checked' => count( $diagnostics_to_run ),
			'categories'          => $category_results,
			'insights'            => $insights,
			'timestamp'           => current_time( 'mysql' ),
			'depth'               => $depth,
		);
	}

	/**
	 * Filter diagnostics based on analysis depth.
	 *
	 * @since 0.6093.1200
	 * @param  array  $diagnostics All available diagnostics.
	 * @param  string $depth       Analysis depth.
	 * @return array Filtered diagnostics to run.
	 */
	private static function filter_diagnostics_by_depth( array $diagnostics, string $depth ): array {
		// For now, return all diagnostics regardless of depth.
		// In future, we can prioritize by severity/importance.
		return $diagnostics;
	}

	/**
	 * Run diagnostics and aggregate by category.
	 *
	 * @since 0.6093.1200
	 * @param  array $diagnostics Diagnostics to run.
	 * @return array Category results with scores.
	 */
	private static function run_diagnostics_by_category( array $diagnostics ): array {
		$categories = array();

		foreach ( $diagnostics as $slug => $class ) {
			if ( ! class_exists( $class ) || ! method_exists( $class, 'execute' ) ) {
				continue;
			}

			// Get diagnostic family (category).
			$family = 'general';
			if ( property_exists( $class, 'family' ) ) {
				$family_property = new \ReflectionProperty( $class, 'family' );
				$family_property->setAccessible( true );
				$family = $family_property->getValue() ?: 'general';
			}

			// Initialize category if needed.
			if ( ! isset( $categories[ $family ] ) ) {
				$categories[ $family ] = array(
					'label'      => ucwords( str_replace( array( '_', '-' ), ' ', $family ) ),
					'checks_run' => 0,
					'issues'     => 0,
					'score'      => 100,
				);
			}

			// Run diagnostic.
			$result = call_user_func( array( $class, 'execute' ) );

			$categories[ $family ]['checks_run']++;

			if ( ! empty( $result ) && is_array( $result ) ) {
				$categories[ $family ]['issues']++;

				// Deduct points based on severity.
				$severity = $result['severity'] ?? 'low';
				$deduction = match ( $severity ) {
					'critical' => 20,
					'high'     => 15,
					'medium'   => 10,
					'low'      => 5,
					default    => 5,
				};

				$categories[ $family ]['score'] = max( 0, $categories[ $family ]['score'] - $deduction );
			}
		}

		return $categories;
	}

	/**
	 * Calculate overall health score.
	 *
	 * @since 0.6093.1200
	 * @param  array $category_results Category results.
	 * @return float Overall score (0-100).
	 */
	private static function calculate_overall_score( array $category_results ): float {
		if ( empty( $category_results ) ) {
			return 0.0;
		}

		$total = 0.0;
		$count = 0;

		foreach ( $category_results as $category ) {
			$total += $category['score'];
			$count++;
		}

		return $count > 0 ? round( $total / $count, 2 ) : 0.0;
	}

	/**
	 * Generate insights based on results.
	 *
	 * @since 0.6093.1200
	 * @param  array $category_results Category results.
	 * @param  float $overall_score    Overall score.
	 * @return array Insights array.
	 */
	private static function generate_insights( array $category_results, float $overall_score ): array {
		$insights = array();

		// Overall health insight.
		if ( $overall_score >= 90 ) {
			$insights[] = array(
				'type'    => 'success',
				'title'   => __( 'Excellent Site Health', 'wpshadow' ),
				'message' => __( 'Your site is performing exceptionally well across all categories.', 'wpshadow' ),
			);
		} elseif ( $overall_score >= 70 ) {
			$insights[] = array(
				'type'    => 'warning',
				'title'   => __( 'Good Site Health', 'wpshadow' ),
				'message' => __( 'Your site is healthy but there are some areas that could be improved.', 'wpshadow' ),
			);
		} else {
			$insights[] = array(
				'type'    => 'error',
				'title'   => __( 'Site Health Needs Attention', 'wpshadow' ),
				'message' => __( 'Your site has multiple issues that should be addressed to improve overall health.', 'wpshadow' ),
			);
		}

		// Find weakest categories.
		$weak_categories = array();
		foreach ( $category_results as $key => $category ) {
			if ( $category['score'] < 60 ) {
				$weak_categories[ $key ] = $category;
			}
		}

		if ( ! empty( $weak_categories ) ) {
			$category_names = array_map(
				function ( $cat ) {
					return $cat['label'];
				},
				$weak_categories
			);

			$insights[] = array(
				'type'    => 'warning',
				'title'   => __( 'Focus Areas', 'wpshadow' ),
				/* translators: %s: comma-separated list of category names */
				'message' => sprintf( __( 'These categories need attention: %s', 'wpshadow' ), implode( ', ', $category_names ) ),
			);
		}

		// Find strongest category.
		$strongest_category = null;
		$highest_score      = 0;
		foreach ( $category_results as $key => $category ) {
			if ( $category['score'] > $highest_score ) {
				$highest_score      = $category['score'];
				$strongest_category = $category;
			}
		}

		if ( $strongest_category && $highest_score >= 90 ) {
			$insights[] = array(
				'type'    => 'success',
				'title'   => __( 'Strong Performance', 'wpshadow' ),
				/* translators: %s: category name */
				'message' => sprintf( __( 'Your site excels in %s with a score of %d.', 'wpshadow' ), $strongest_category['label'], (int) $highest_score ),
			);
		}

		return $insights;
	}

	/**
	 * Get benchmark comparison data.
	 *
	 * @since 0.6093.1200
	 * @param  string $type  Benchmark type.
	 * @param  float  $score User's score.
	 * @return array Benchmark comparison data.
	 */
	private static function get_benchmark_data( string $type, float $score ): array {
		// In production, these would come from real data.
		$benchmarks = array(
			'industry'        => array(
				'label'   => __( 'Industry Average', 'wpshadow' ),
				'average' => 72.5,
			),
			'similar'         => array(
				'label'   => __( 'Similar Sites', 'wpshadow' ),
				'average' => 68.3,
			),
			'top-performers'  => array(
				'label'   => __( 'Top 10% of Sites', 'wpshadow' ),
				'average' => 94.2,
			),
			'historical'      => array(
				'label'   => __( 'Your Historical Average', 'wpshadow' ),
				'average' => $score * 0.95, // 5% improvement simulation.
			),
		);

		$benchmark = $benchmarks[ $type ] ?? $benchmarks['industry'];
		$benchmark['comparison'] = $score - $benchmark['average'];
		$benchmark['percentile'] = self::calculate_percentile( $score );

		return $benchmark;
	}

	/**
	 * Calculate percentile ranking.
	 *
	 * @since 0.6093.1200
	 * @param  float $score User's score.
	 * @return int Percentile (0-100).
	 */
	private static function calculate_percentile( float $score ): int {
		// Simplified percentile calculation.
		// In production, this would use real distribution data.
		if ( $score >= 95 ) {
			return 99;
		} elseif ( $score >= 90 ) {
			return 95;
		} elseif ( $score >= 80 ) {
			return 80;
		} elseif ( $score >= 70 ) {
			return 65;
		} elseif ( $score >= 60 ) {
			return 50;
		} else {
			return max( 10, (int) ( $score / 2 ) );
		}
	}

}
