<?php
declare(strict_types=1);

namespace WPShadow\Reports;

use WPShadow\Core\Activity_Logger;

/**
 * Report Engine - Advanced analytics and reporting
 * 
 * Philosophy:
 * - #9 Show Value: Comprehensive metrics to prove ROI
 * - #5 Drive to KB: Reports link to knowledge base
 * - #6 Drive to Training: Reports reference training videos
 * 
 * Features:
 * - Advanced filtering by date range, category, action type
 * - Trend analysis (growth, patterns, predictions)
 * - Comparative analysis (period-over-period)
 * - Custom report generation
 * - Export to CSV/PDF formats
 * 
 * @package WPShadow\Reports
 */
class Report_Engine {
	
	/**
	 * Generate comprehensive report
	 * 
	 * @param array $filters Report filters (date_from, date_to, category, action, format)
	 * @return array Report data with metrics, trends, and recommendations
	 */
	public static function generate( array $filters = array() ): array {
		// Set defaults
		$date_from = $filters['date_from'] ?? date( 'Y-m-d', strtotime( '-30 days' ) );
		$date_to = $filters['date_to'] ?? date( 'Y-m-d' );
		$category = $filters['category'] ?? '';
		$action = $filters['action'] ?? '';
		$report_type = $filters['type'] ?? 'summary';
		
		// Fetch activities
		$activity_filters = array(
			'date_from' => $date_from,
			'date_to'   => $date_to,
		);
		
		if ( ! empty( $category ) ) {
			$activity_filters['category'] = $category;
		}
		
		$activities_result = Activity_Logger::get_activities( $activity_filters, 10000, 0 );
		$activities = $activities_result['activities'] ?? array();
		
		// Calculate metrics
		$metrics = self::calculate_metrics( $activities, $date_from, $date_to );
		
		// Build report
		$report = array(
			'title'           => self::get_report_title( $category, $report_type ),
			'type'            => $report_type,
			'date_range'      => array(
				'from' => $date_from,
				'to'   => $date_to,
			),
			'generated_at'    => current_time( 'Y-m-d H:i:s' ),
			'total_activities' => count( $activities ),
			'metrics'         => $metrics,
			'trends'          => self::calculate_trends( $activities ),
			'top_activities'  => self::get_top_activities( $activities ),
			'recommendations' => self::generate_recommendations( $metrics ),
		);
		
		// Add detailed data for detailed reports
		if ( $report_type === 'detailed' ) {
			$report['activities'] = $activities;
		}
		
		return $report;
	}
	
	/**
	 * Calculate key metrics
	 * 
	 * @param array  $activities Activities to analyze
	 * @param string $date_from Start date
	 * @param string $date_to End date
	 * @return array Calculated metrics
	 */
	private static function calculate_metrics( array $activities, string $date_from, string $date_to ): array {
		$metrics = array(
			'total_activities'    => count( $activities ),
			'by_category'         => array(),
			'by_action'           => array(),
			'by_user'             => array(),
			'daily_average'       => 0,
			'time_saved_hours'    => 0,
			'issues_fixed'        => 0,
			'workflows_created'   => 0,
			'success_rate'        => 0,
		);
		
		if ( empty( $activities ) ) {
			return $metrics;
		}
		
		// Group by category
		foreach ( $activities as $activity ) {
			$category = $activity['category'] ?? 'uncategorized';
			$action = $activity['action'] ?? 'unknown';
			$user = $activity['user_name'] ?? 'Unknown User';
			
			// Count by category
			if ( ! isset( $metrics['by_category'][ $category ] ) ) {
				$metrics['by_category'][ $category ] = 0;
			}
			$metrics['by_category'][ $category ]++;
			
			// Count by action
			if ( ! isset( $metrics['by_action'][ $action ] ) ) {
				$metrics['by_action'][ $action ] = 0;
			}
			$metrics['by_action'][ $action ]++;
			
			// Count by user
			if ( ! isset( $metrics['by_user'][ $user ] ) ) {
				$metrics['by_user'][ $user ] = 0;
			}
			$metrics['by_user'][ $user ]++;
			
			// Calculate KPIs
			if ( strpos( $action, 'workflow' ) !== false ) {
				$metrics['workflows_created']++;
				$metrics['time_saved_hours'] += 0.5; // 30 mins per workflow
			}
			if ( strpos( $action, 'fixed' ) !== false || strpos( $action, 'treatment' ) !== false ) {
				$metrics['issues_fixed']++;
				$metrics['time_saved_hours'] += 0.25; // 15 mins per fix
			}
		}
		
		// Calculate daily average
		$days = (int) floor( ( strtotime( $date_to ) - strtotime( $date_from ) ) / 86400 ) + 1;
		$metrics['daily_average'] = $days > 0 ? round( count( $activities ) / $days, 2 ) : 0;
		
		// Calculate success rate (workflows that executed successfully)
		$total_workflows = $metrics['workflows_created'] + 
							( $metrics['by_action']['workflow_executed'] ?? 0 );
		$successful = $metrics['by_action']['workflow_run_success'] ?? 0;
		$metrics['success_rate'] = $total_workflows > 0 ? 
			round( ( $successful / $total_workflows ) * 100, 2 ) : 0;
		
		// Sort by count
		arsort( $metrics['by_category'] );
		arsort( $metrics['by_action'] );
		arsort( $metrics['by_user'] );
		
		// Limit to top 10
		$metrics['by_category'] = array_slice( $metrics['by_category'], 0, 10 );
		$metrics['by_action'] = array_slice( $metrics['by_action'], 0, 10 );
		$metrics['by_user'] = array_slice( $metrics['by_user'], 0, 10 );
		
		return $metrics;
	}
	
	/**
	 * Calculate trends over time
	 * 
	 * @param array $activities Activities to analyze
	 * @return array Trend data by day
	 */
	private static function calculate_trends( array $activities ): array {
		$trends = array();
		
		foreach ( $activities as $activity ) {
			$date = date( 'Y-m-d', $activity['timestamp'] );
			if ( ! isset( $trends[ $date ] ) ) {
				$trends[ $date ] = array(
					'date'       => $date,
					'total'      => 0,
					'workflows'  => 0,
					'treatments' => 0,
					'fixes'      => 0,
				);
			}
			
			$trends[ $date ]['total']++;
			
			$action = $activity['action'] ?? '';
			if ( strpos( $action, 'workflow' ) !== false ) {
				$trends[ $date ]['workflows']++;
			}
			if ( strpos( $action, 'treatment' ) !== false ) {
				$trends[ $date ]['treatments']++;
			}
			if ( strpos( $action, 'fixed' ) !== false ) {
				$trends[ $date ]['fixes']++;
			}
		}
		
		// Sort by date
		ksort( $trends );
		
		return array_values( $trends );
	}
	
	/**
	 * Get top activities
	 * 
	 * @param array $activities Activities to analyze
	 * @param int   $limit Limit results
	 * @return array Top activities
	 */
	private static function get_top_activities( array $activities, int $limit = 10 ): array {
		$action_counts = array();
		
		foreach ( $activities as $activity ) {
			$action = $activity['action'] ?? 'unknown';
			if ( ! isset( $action_counts[ $action ] ) ) {
				$action_counts[ $action ] = array(
					'action'  => $action,
					'count'   => 0,
					'details' => array(),
				);
			}
			$action_counts[ $action ]['count']++;
			$action_counts[ $action ]['details'][] = $activity['details'];
		}
		
		// Sort by count
		usort( $action_counts, function( $a, $b ) {
			return $b['count'] - $a['count'];
		} );
		
		// Limit and return
		return array_slice( $action_counts, 0, $limit );
	}
	
	/**
	 * Generate recommendations based on metrics
	 * 
	 * @param array $metrics Calculated metrics
	 * @return array Recommendations
	 */
	private static function generate_recommendations( array $metrics ): array {
		$recommendations = array();
		
		// Low activity recommendation
		if ( $metrics['daily_average'] < 1 ) {
			$recommendations[] = array(
				'type'        => 'low_activity',
				'title'       => __( 'Increase Automation Usage', 'wpshadow' ),
				'description' => __( 'Consider creating more workflows to automate routine tasks.', 'wpshadow' ),
				'kb_link'     => 'https://wpshadow.com/kb/workflow-automation',
				'severity'    => 'info',
			);
		}
		
		// Low success rate recommendation
		if ( $metrics['success_rate'] < 80 && $metrics['success_rate'] > 0 ) {
			$recommendations[] = array(
				'type'        => 'low_success_rate',
				'title'       => __( 'Review Workflow Performance', 'wpshadow' ),
				'description' => sprintf( 
					__( 'Workflow success rate is %s%%. Check failing workflows and adjust triggers or actions.', 'wpshadow' ),
					$metrics['success_rate']
				),
				'kb_link'     => 'https://wpshadow.com/kb/workflow-troubleshooting',
				'severity'    => 'warning',
			);
		}
		
		// High fixes recommendation
		if ( $metrics['issues_fixed'] > 20 ) {
			$recommendations[] = array(
				'type'        => 'high_fixes',
				'title'       => __( 'You\'re Doing Great!', 'wpshadow' ),
				'description' => sprintf(
					__( 'You\'ve fixed %d issues in this period. Keep up the great work!', 'wpshadow' ),
					$metrics['issues_fixed']
				),
				'kb_link'     => 'https://wpshadow.com/kb/maintenance-best-practices',
				'severity'    => 'success',
			);
		}
		
		return $recommendations;
	}
	
	/**
	 * Get report title
	 * 
	 * @param string $category Report category
	 * @param string $type Report type
	 * @return string Report title
	 */
	private static function get_report_title( string $category, string $type ): string {
		$type_names = array(
			'summary'   => __( 'Summary Report', 'wpshadow' ),
			'detailed'  => __( 'Detailed Report', 'wpshadow' ),
			'executive' => __( 'Executive Summary', 'wpshadow' ),
		);
		
		$title = $type_names[ $type ] ?? __( 'Report', 'wpshadow' );
		
		if ( ! empty( $category ) ) {
			$title = sprintf( '%s - %s', $title, ucfirst( $category ) );
		}
		
		return $title;
	}
	
	/**
	 * Compare two date ranges
	 * 
	 * @param string $date_from1 First period start
	 * @param string $date_to1 First period end
	 * @param string $date_from2 Second period start
	 * @param string $date_to2 Second period end
	 * @return array Comparison analysis
	 */
	public static function compare_periods( 
		string $date_from1, 
		string $date_to1, 
		string $date_from2, 
		string $date_to2 
	): array {
		$report1 = self::generate( array(
			'date_from' => $date_from1,
			'date_to'   => $date_to1,
		) );
		
		$report2 = self::generate( array(
			'date_from' => $date_from2,
			'date_to'   => $date_to2,
		) );
		
		$metrics1 = $report1['metrics'];
		$metrics2 = $report2['metrics'];
		
		return array(
			'period_1' => array(
				'date_from' => $date_from1,
				'date_to'   => $date_to1,
				'metrics'   => $metrics1,
			),
			'period_2' => array(
				'date_from' => $date_from2,
				'date_to'   => $date_to2,
				'metrics'   => $metrics2,
			),
			'comparison' => array(
				'activities_change'    => self::calculate_change( $metrics1['total_activities'], $metrics2['total_activities'] ),
				'time_saved_change'    => self::calculate_change( $metrics1['time_saved_hours'], $metrics2['time_saved_hours'] ),
				'issues_fixed_change'  => self::calculate_change( $metrics1['issues_fixed'], $metrics2['issues_fixed'] ),
				'workflows_change'     => self::calculate_change( $metrics1['workflows_created'], $metrics2['workflows_created'] ),
			),
		);
	}
	
	/**
	 * Calculate percentage change
	 * 
	 * @param mixed $old Old value
	 * @param mixed $new New value
	 * @return array Change data
	 */
	private static function calculate_change( $old, $new ): array {
		$change = 0;
		$percent = 0;
		
		if ( $old > 0 ) {
			$change = $new - $old;
			$percent = round( ( $change / $old ) * 100, 2 );
		} elseif ( $new > 0 ) {
			$percent = 100;
		}
		
		return array(
			'value'   => $change,
			'percent' => $percent,
			'trend'   => $change > 0 ? 'up' : ( $change < 0 ? 'down' : 'flat' ),
		);
	}
	
	/**
	 * Export report to CSV format
	 * 
	 * @param array  $report Report data
	 * @param string $filename Output filename
	 * @return string CSV content
	 */
	public static function export_csv( array $report, string $filename = 'report.csv' ): string {
		$csv = array();
		
		// Header
		$csv[] = sprintf( 'Report: %s', $report['title'] );
		$csv[] = sprintf( 'Generated: %s', $report['generated_at'] );
		$csv[] = sprintf( 'Period: %s to %s', $report['date_range']['from'], $report['date_range']['to'] );
		$csv[] = '';
		
		// Metrics section
		$csv[] = 'METRICS';
		$csv[] = 'Key,Value';
		
		$metrics = $report['metrics'];
		$csv[] = sprintf( 'Total Activities,%d', $metrics['total_activities'] );
		$csv[] = sprintf( 'Daily Average,%.2f', $metrics['daily_average'] );
		$csv[] = sprintf( 'Time Saved (hours),%.2f', $metrics['time_saved_hours'] );
		$csv[] = sprintf( 'Issues Fixed,%d', $metrics['issues_fixed'] );
		$csv[] = sprintf( 'Workflows Created,%d', $metrics['workflows_created'] );
		$csv[] = sprintf( 'Success Rate (%%),%.2f', $metrics['success_rate'] );
		$csv[] = '';
		
		// Activities section (if detailed)
		if ( ! empty( $report['activities'] ) ) {
			$csv[] = 'DETAILED ACTIVITIES';
			$csv[] = 'Date,Action,Details,Category,User';
			
			foreach ( $report['activities'] as $activity ) {
				$csv[] = sprintf( 
					'"%s","%s","%s","%s","%s"',
					date( 'Y-m-d H:i:s', $activity['timestamp'] ),
					$activity['action'],
					str_replace( '"', '""', $activity['details'] ),
					$activity['category'],
					$activity['user_name']
				);
			}
		}
		
		return implode( "\n", $csv );
	}
}
