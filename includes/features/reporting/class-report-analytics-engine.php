<?php
/**
 * Report Analytics Engine
 *
 * Advanced analytics for reports (ROI, competitive analysis, trends).
 *
 * @package    WPShadow
 * @subpackage Reporting
 * @since      1.603.0200
 */

declare(strict_types=1);

namespace WPShadow\Reporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Report_Analytics_Engine Class
 *
 * Provides advanced analytics capabilities.
 *
 * @since 1.603.0200
 */
class Report_Analytics_Engine {

	/**
	 * Calculate ROI for fixes
	 *
	 * @since  1.603.0200
	 * @param  array $findings Report findings.
	 * @return array ROI calculations.
	 */
	public static function calculate_roi( $findings ) {
		$hourly_rate = 100; // Default hourly rate
		$total_time_saved = 0;
		$total_revenue_protected = 0;
		
		foreach ( $findings as $finding ) {
			if ( ! isset( $finding['severity'] ) ) {
				continue;
			}
			
			// Estimate time savings based on severity
			switch ( $finding['severity'] ) {
				case 'critical':
					$time_saved_hours = 4; // 4 hours of potential downtime prevented
					$revenue_at_risk = 1000;
					break;
				case 'high':
					$time_saved_hours = 2;
					$revenue_at_risk = 500;
					break;
				case 'medium':
					$time_saved_hours = 1;
					$revenue_at_risk = 200;
					break;
				case 'low':
					$time_saved_hours = 0.5;
					$revenue_at_risk = 50;
					break;
				default:
					$time_saved_hours = 0;
					$revenue_at_risk = 0;
			}
			
			$total_time_saved += $time_saved_hours;
			$total_revenue_protected += $revenue_at_risk;
		}
		
		$labor_cost_saved = $total_time_saved * $hourly_rate;
		$total_value = $labor_cost_saved + $total_revenue_protected;
		
		return array(
			'time_saved_hours'        => $total_time_saved,
			'labor_cost_saved'        => $labor_cost_saved,
			'revenue_protected'       => $total_revenue_protected,
			'total_value'             => $total_value,
			'issues_count'            => count( $findings ),
			'hourly_rate'             => $hourly_rate,
		);
	}

	/**
	 * Detect regressions between snapshots
	 *
	 * @since  1.603.0200
	 * @param  string $report_id Report ID.
	 * @param  int    $days Days to check.
	 * @return array Regressions detected.
	 */
	public static function detect_regressions( $report_id, $days = 7 ) {
		$snapshots = Report_Snapshot_Manager::get_snapshots( $report_id, 10 );
		
		if ( count( $snapshots ) < 2 ) {
			return array(
				'detected'    => false,
				'message'     => 'Insufficient data for regression detection',
				'regressions' => array(),
			);
		}
		
		$regressions = array();
		
		for ( $i = 0; $i < count( $snapshots ) - 1; $i++ ) {
			$newer = $snapshots[ $i ];
			$older = $snapshots[ $i + 1 ];
			
			$newer_count = isset( $newer['findings_count'] ) ? $newer['findings_count'] : 0;
			$older_count = isset( $older['findings_count'] ) ? $older['findings_count'] : 0;
			
			if ( $newer_count > $older_count ) {
				$increase = $newer_count - $older_count;
				$percentage = $older_count > 0 ? ( $increase / $older_count ) * 100 : 100;
				
				if ( $percentage > 10 ) { // Significant regression threshold
					$regressions[] = array(
						'date'            => $newer['created_at'],
						'previous_count'  => $older_count,
						'current_count'   => $newer_count,
						'increase'        => $increase,
						'percentage'      => round( $percentage, 2 ),
					);
				}
			}
		}
		
		return array(
			'detected'    => ! empty( $regressions ),
			'count'       => count( $regressions ),
			'regressions' => $regressions,
		);
	}

	/**
	 * Generate executive summary
	 *
	 * @since  1.603.0200
	 * @param  array $findings Report findings.
	 * @return array Executive summary.
	 */
	public static function generate_executive_summary( $findings ) {
		$summary = array(
			'total_issues'     => count( $findings ),
			'critical_issues'  => 0,
			'high_issues'      => 0,
			'medium_issues'    => 0,
			'low_issues'       => 0,
			'auto_fixable'     => 0,
			'manual_review'    => 0,
			'top_categories'   => array(),
			'priority_actions' => array(),
		);
		
		$categories = array();
		
		foreach ( $findings as $finding ) {
			// Count by severity
			if ( isset( $finding['severity'] ) ) {
				switch ( $finding['severity'] ) {
					case 'critical':
						$summary['critical_issues']++;
						break;
					case 'high':
						$summary['high_issues']++;
						break;
					case 'medium':
						$summary['medium_issues']++;
						break;
					case 'low':
						$summary['low_issues']++;
						break;
				}
			}
			
			// Count auto-fixable
			if ( isset( $finding['auto_fixable'] ) && $finding['auto_fixable'] ) {
				$summary['auto_fixable']++;
			} else {
				$summary['manual_review']++;
			}
			
			// Track categories
			if ( isset( $finding['category'] ) ) {
				$category = $finding['category'];
				if ( ! isset( $categories[ $category ] ) ) {
					$categories[ $category ] = 0;
				}
				$categories[ $category ]++;
			}
			
			// Priority actions (highest severity).
			if ( isset( $finding['severity'] ) && in_array( $finding['severity'], array( 'critical', 'high' ), true ) ) {
				$summary['priority_actions'][] = array(
					'id'       => isset( $finding['id'] ) ? $finding['id'] : '',
					'title'    => isset( $finding['title'] ) ? $finding['title'] : '',
					'severity' => $finding['severity'],
				);
			}
		}
		
		// Sort categories by count
		arsort( $categories );
		$summary['top_categories'] = array_slice( $categories, 0, 5, true );
		
		// Limit priority actions
		$summary['priority_actions'] = array_slice( $summary['priority_actions'], 0, 10 );
		
		return $summary;
	}

	/**
	 * Simulate what-if scenarios
	 *
	 * @since  1.603.0200
	 * @param  array $findings Current findings.
	 * @param  array $fixes_to_apply Fix IDs to simulate.
	 * @return array Projected impact.
	 */
	public static function simulate_fixes( $findings, $fixes_to_apply ) {
		$current_count = count( $findings );
		$projected_remaining = $current_count;
		
		$fixes_applied = array();
		
		foreach ( $findings as $finding ) {
			$finding_id = isset( $finding['id'] ) ? $finding['id'] : '';
			
			if ( in_array( $finding_id, $fixes_to_apply, true ) ) {
				$projected_remaining--;
				$fixes_applied[] = $finding_id;
			}
		}
		
		$improvement_percentage = $current_count > 0 ? ( ( $current_count - $projected_remaining ) / $current_count ) * 100 : 0;
		
		return array(
			'current_count'         => $current_count,
			'fixes_to_apply'        => count( $fixes_to_apply ),
			'fixes_applicable'      => count( $fixes_applied ),
			'projected_remaining'   => $projected_remaining,
			'improvement_percentage' => round( $improvement_percentage, 2 ),
			'fixes_applied'         => $fixes_applied,
		);
	}

	/**
	 * Compare to industry benchmarks
	 *
	 * @since  1.603.0200
	 * @param  array  $findings Report findings.
	 * @param  string $site_type Site type (blog, ecommerce, business).
	 * @return array Comparison data.
	 */
	public static function compare_to_benchmarks( $findings, $site_type = 'business' ) {
		// Industry benchmarks (average findings by site type)
		$benchmarks = array(
			'blog'      => array( 'average' => 15, 'good' => 8, 'excellent' => 3 ),
			'ecommerce' => array( 'average' => 25, 'good' => 12, 'excellent' => 5 ),
			'business'  => array( 'average' => 20, 'good' => 10, 'excellent' => 4 ),
		);
		
		$current_count = count( $findings );
		$benchmark = isset( $benchmarks[ $site_type ] ) ? $benchmarks[ $site_type ] : $benchmarks['business'];
		
		if ( $current_count <= $benchmark['excellent'] ) {
			$rating = 'excellent';
			$message = 'Your site is performing exceptionally well!';
		} elseif ( $current_count <= $benchmark['good'] ) {
			$rating = 'good';
			$message = 'Your site is performing above average.';
		} elseif ( $current_count <= $benchmark['average'] ) {
			$rating = 'average';
			$message = 'Your site is performing at industry average.';
		} else {
			$rating = 'needs_improvement';
			$message = 'Your site could use some improvements.';
		}
		
		return array(
			'current_count'    => $current_count,
			'site_type'        => $site_type,
			'benchmark'        => $benchmark,
			'rating'           => $rating,
			'message'          => $message,
			'percentile'       => self::calculate_percentile( $current_count, $benchmark ),
		);
	}

	/**
	 * Calculate percentile ranking
	 *
	 * @since  1.603.0200
	 * @param  int   $count Current count.
	 * @param  array $benchmark Benchmark data.
	 * @return int Percentile (0-100).
	 */
	private static function calculate_percentile( $count, $benchmark ) {
		if ( $count <= $benchmark['excellent'] ) {
			return 95;
		} elseif ( $count <= $benchmark['good'] ) {
			return 75;
		} elseif ( $count <= $benchmark['average'] ) {
			return 50;
		} else {
			// Scale down from 50 to 0 based on how much worse than average
			$difference = $count - $benchmark['average'];
			$scale_factor = max( 0, 1 - ( $difference / $benchmark['average'] ) );
			return max( 0, round( 50 * $scale_factor ) );
		}
	}
}
