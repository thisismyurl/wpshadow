<?php
/**
 * Predictive Analytics Engine
 *
 * Uses historical data to forecast future issues, resource needs, and health trends.
 * Machine learning-inspired pattern recognition for proactive site management.
 *
 * Philosophy:
 * - #9 Show Value: Predict issues before they happen
 * - #8 Inspire Confidence: Give users control over their future
 * - #1 Helpful Neighbor: "Here's what's coming, and here's how to prepare"
 *
 * @package    WPShadow
 * @subpackage Reports
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Reports;

use WPShadow\Core\Activity_Logger;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Predictive Analytics Class
 *
 * Forecasts future site health, resource usage, and potential issues.
 *
 * @since 0.6093.1200
 */
class Predictive_Analytics {

	/**
	 * Generate predictive forecast
	 *
	 * @since 0.6093.1200
	 * @param  int $days_ahead How many days to forecast (default: 30).
	 * @return array Prediction data with confidence scores.
	 */
	public static function generate_forecast( int $days_ahead = 30 ): array {
		$historical_data = self::get_historical_data( 90 ); // 90 days of history

		return array(
			'generated_at'       => current_time( 'Y-m-d H:i:s' ),
			'forecast_period'    => $days_ahead,
			'health_prediction'  => self::predict_health_score( $historical_data, $days_ahead ),
			'resource_forecast'  => self::forecast_resources( $historical_data, $days_ahead ),
			'issue_predictions'  => self::predict_issues( $historical_data, $days_ahead ),
			'cost_forecast'      => self::forecast_costs( $historical_data, $days_ahead ),
			'risk_assessment'    => self::assess_risks( $historical_data, $days_ahead ),
			'recommendations'    => self::generate_recommendations( $historical_data, $days_ahead ),
			'confidence_level'   => self::calculate_confidence( $historical_data ),
		);
	}

	/**
	 * Predict future health score
	 *
	 * @since 0.6093.1200
	 * @param  array $data Historical data.
	 * @param  int   $days Days ahead to predict.
	 * @return array Health prediction with trend.
	 */
	private static function predict_health_score( array $data, int $days ): array {
		$health_history = self::extract_health_scores( $data );

		if ( empty( $health_history ) ) {
			return array(
				'current_score'    => 0,
				'predicted_score'  => 0,
				'trend'            => 'insufficient_data',
				'confidence'       => 0,
				'change_percent'   => 0,
			);
		}

		$current_score = end( $health_history );
		$trend_slope   = self::calculate_trend_slope( $health_history );
		$predicted     = $current_score + ( $trend_slope * $days );
		$predicted     = max( 0, min( 100, $predicted ) ); // Clamp to 0-100

		return array(
			'current_score'    => round( $current_score, 1 ),
			'predicted_score'  => round( $predicted, 1 ),
			'trend'            => $trend_slope > 0.1 ? 'improving' : ( $trend_slope < -0.1 ? 'declining' : 'stable' ),
			'confidence'       => self::calculate_confidence( $data ),
			'change_percent'   => round( ( ( $predicted - $current_score ) / $current_score ) * 100, 1 ),
			'trend_slope'      => round( $trend_slope, 4 ),
			'days_to_critical' => $trend_slope < 0 ? self::calculate_days_to_threshold( $current_score, $trend_slope, 70 ) : null,
		);
	}

	/**
	 * Forecast resource usage
	 *
	 * @since 0.6093.1200
	 * @param  array $data Historical data.
	 * @param  int   $days Days ahead to forecast.
	 * @return array Resource predictions.
	 */
	private static function forecast_resources( array $data, int $days ): array {
		global $wpdb;

		// Database size forecast
		$db_size_history = self::get_database_size_history( 90 );
		$current_db_size = self::get_current_database_size();
		$db_growth_rate  = self::calculate_growth_rate( $db_size_history );

		$predicted_db_size = $current_db_size + ( $db_growth_rate * $days );

		// Plugin count forecast
		$plugin_history = self::get_plugin_count_history( 90 );
		$current_plugins = count( get_option( 'active_plugins', array() ) );
		$plugin_growth = self::calculate_growth_rate( $plugin_history );

		return array(
			'database' => array(
				'current_size_mb'   => round( $current_db_size, 2 ),
				'predicted_size_mb' => round( $predicted_db_size, 2 ),
				'growth_rate_mb'    => round( $db_growth_rate, 2 ),
				'days_to_1gb'       => $db_growth_rate > 0 ? self::calculate_days_to_threshold( $current_db_size, $db_growth_rate, 1024 ) : null,
				'recommendation'    => $predicted_db_size > 500 ? __( 'Consider database optimization soon', 'wpshadow' ) : '',
			),
			'plugins' => array(
				'current_count'   => $current_plugins,
				'predicted_count' => round( $current_plugins + ( $plugin_growth * $days ) ),
				'growth_rate'     => round( $plugin_growth, 2 ),
				'warning'         => ( $current_plugins + ( $plugin_growth * $days ) ) > 30 ? __( 'Plugin count may impact performance', 'wpshadow' ) : '',
			),
			'storage' => array(
				'uploads_size_mb'      => self::get_uploads_size(),
				'predicted_uploads_mb' => self::predict_uploads_growth( $days ),
			),
		);
	}

	/**
	 * Predict likely issues
	 *
	 * @since 0.6093.1200
	 * @param  array $data Historical data.
	 * @param  int   $days Days ahead to predict.
	 * @return array Issue predictions.
	 */
	private static function predict_issues( array $data, int $days ): array {
		$recurring_issues = self::identify_recurring_patterns( $data );
		$predictions = array();

		foreach ( $recurring_issues as $issue ) {
			$pattern = $issue['pattern'];
			$frequency = $issue['frequency'];

			// Calculate probability based on frequency
			$probability = min( 100, ( $frequency / 90 ) * 100 * ( $days / 30 ) );

			if ( $probability > 20 ) {
				$predictions[] = array(
					'issue_type'         => $issue['type'],
					'description'        => $issue['description'],
					'probability'        => round( $probability, 1 ),
					'expected_days'      => round( $issue['avg_interval'] ),
					'last_occurrence'    => $issue['last_seen'],
					'severity'           => $issue['severity'],
					'preventive_action'  => $issue['prevention'],
				);
			}
		}

		// Sort by probability
		usort( $predictions, function( $a, $b ) {
			return $b['probability'] <=> $a['probability'];
		} );

		return $predictions;
	}

	/**
	 * Forecast costs
	 *
	 * @since 0.6093.1200
	 * @param  array $data Historical data.
	 * @param  int   $days Days ahead to forecast.
	 * @return array Cost predictions.
	 */
	private static function forecast_costs( array $data, int $days ): array {
		$historical_costs = self::calculate_historical_costs( $data );
		$current_monthly = $historical_costs['current_monthly'] ?? 0;
		$trend = $historical_costs['trend'] ?? 0;

		$predicted_monthly = $current_monthly + ( $trend * ( $days / 30 ) );

		return array(
			'current_monthly_cost'   => round( $current_monthly, 2 ),
			'predicted_monthly_cost' => round( $predicted_monthly, 2 ),
			'cost_change'            => round( $predicted_monthly - $current_monthly, 2 ),
			'cost_change_percent'    => $current_monthly > 0 ? round( ( ( $predicted_monthly - $current_monthly ) / $current_monthly ) * 100, 1 ) : 0,
			'breakdown' => array(
				'server_resources' => array(
					'current' => round( $historical_costs['server_cost'] ?? 0, 2 ),
					'predicted' => round( ( $historical_costs['server_cost'] ?? 0 ) *1.0, 2 ), // 5% growth assumption
				),
				'inefficiency_cost' => array(
					'current' => round( $historical_costs['inefficiency_cost'] ?? 0, 2 ),
					'predicted' => round( ( $historical_costs['inefficiency_cost'] ?? 0 ) * 0.9, 2 ), // Assuming improvements
				),
			),
			'savings_opportunity' => round( $historical_costs['potential_savings'] ?? 0, 2 ),
		);
	}

	/**
	 * Assess risk levels
	 *
	 * @since 0.6093.1200
	 * @param  array $data Historical data.
	 * @param  int   $days Days ahead to assess.
	 * @return array Risk assessment.
	 */
	private static function assess_risks( array $data, int $days ): array {
		$risks = array();

		// Check for declining health trend
		$health_trend = self::predict_health_score( $data, $days );
		if ( $health_trend['trend'] === 'declining' && $health_trend['predicted_score'] < 70 ) {
			$risks[] = array(
				'type'        => 'health_decline',
				'severity'    => 'high',
				'probability' => 80,
				'description' => sprintf(
					/* translators: %d: predicted health score */
					__( 'Site health predicted to drop to %d%% in %d days', 'wpshadow' ),
					$health_trend['predicted_score'],
					$days
				),
				'impact'      => __( 'Performance degradation, potential outages', 'wpshadow' ),
				'mitigation'  => __( 'Address declining issues immediately', 'wpshadow' ),
			);
		}

		// Check for resource exhaustion
		$resources = self::forecast_resources( $data, $days );
		if ( isset( $resources['database']['days_to_1gb'] ) && $resources['database']['days_to_1gb'] < $days ) {
			$risks[] = array(
				'type'        => 'resource_exhaustion',
				'severity'    => 'medium',
				'probability' => 70,
				'description' => sprintf(
					/* translators: %d: days until threshold */
					__( 'Database will exceed 1GB in %d days', 'wpshadow' ),
					$resources['database']['days_to_1gb']
				),
				'impact'      => __( 'Increased hosting costs, slower queries', 'wpshadow' ),
				'mitigation'  => __( 'Schedule database optimization', 'wpshadow' ),
			);
		}

		// Check for plugin overload
		if ( $resources['plugins']['predicted_count'] > 30 ) {
			$risks[] = array(
				'type'        => 'plugin_overload',
				'severity'    => 'medium',
				'probability' => 60,
				'description' => sprintf(
					/* translators: %d: predicted plugin count */
					__( 'Plugin count predicted to reach %d', 'wpshadow' ),
					$resources['plugins']['predicted_count']
				),
				'impact'      => __( 'Slower load times, compatibility issues', 'wpshadow' ),
				'mitigation'  => __( 'Audit and consolidate plugins', 'wpshadow' ),
			);
		}

		return $risks;
	}

	/**
	 * Generate recommendations based on predictions
	 *
	 * @since 0.6093.1200
	 * @param  array $data Historical data.
	 * @param  int   $days Days ahead predicted.
	 * @return array Actionable recommendations.
	 */
	private static function generate_recommendations( array $data, int $days ): array {
		$recommendations = array();
		$health_pred = self::predict_health_score( $data, $days );
		$resources = self::forecast_resources( $data, $days );

		if ( $health_pred['trend'] === 'declining' ) {
			$recommendations[] = array(
				'priority'    => 'high',
				'action'      => __( 'Address Declining Health Trend', 'wpshadow' ),
				'description' => sprintf(
					/* translators: 1: predicted score, 2: days */
					__( 'Your site health is predicted to drop to %1$d%% in %2$d days. Take action now to prevent issues.', 'wpshadow' ),
					$health_pred['predicted_score'],
					$days
				),
				'steps'       => array(
					__( 'Run full diagnostic scan', 'wpshadow' ),
					__( 'Fix critical and high-priority issues first', 'wpshadow' ),
					__( 'Update outdated plugins and themes', 'wpshadow' ),
				),
				'time_saved'  => 120, // Minutes
				'kb_link'     => 'https://wpshadow.com/kb/health-score-declining?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		if ( $resources['database']['predicted_size_mb'] > 500 ) {
			$recommendations[] = array(
				'priority'    => 'medium',
				'action'      => __( 'Schedule Database Optimization', 'wpshadow' ),
				'description' => sprintf(
					/* translators: %d: predicted database size */
					__( 'Database predicted to reach %dMB. Optimize now to prevent performance issues.', 'wpshadow' ),
					$resources['database']['predicted_size_mb']
				),
				'steps'       => array(
					__( 'Back up database', 'wpshadow' ),
					__( 'Run database optimization', 'wpshadow' ),
					__( 'Clean up post revisions and trash', 'wpshadow' ),
				),
				'time_saved'  => 60,
				'kb_link'     => 'https://wpshadow.com/kb/database-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return $recommendations;
	}

	/**
	 * Get historical data for analysis
	 *
	 * @since 0.6093.1200
	 * @param  int $days Days of history to retrieve.
	 * @return array Historical data.
	 */
	private static function get_historical_data( int $days ): array {
		$activities = Activity_Logger::get_activities(
			array(
				'date_from' => date( 'Y-m-d', strtotime( "-{$days} days" ) ),
				'date_to'   => date( 'Y-m-d' ),
			),
			10000,
			0
		);

		return array(
			'activities'     => $activities['activities'] ?? array(),
			'health_history' => get_option( 'wpshadow_health_history', array() ),
			'kpi_data'       => KPI_Tracker::get_kpi_summary(),
		);
	}

	/**
	 * Extract health scores from historical data
	 *
	 * @since 0.6093.1200
	 * @param  array $data Historical data.
	 * @return array Health scores over time.
	 */
	private static function extract_health_scores( array $data ): array {
		$health_history = $data['health_history'] ?? array();
		$scores = array();

		foreach ( $health_history as $entry ) {
			if ( isset( $entry['health_score'] ) ) {
				$scores[] = (float) $entry['health_score'];
			}
		}

		return $scores;
	}

	/**
	 * Calculate trend slope using linear regression
	 *
	 * @since 0.6093.1200
	 * @param  array $values Historical values.
	 * @return float Slope (daily change rate).
	 */
	private static function calculate_trend_slope( array $values ): float {
		$n = count( $values );
		if ( $n < 2 ) {
			return 0;
		}

		$sum_x = 0;
		$sum_y = 0;
		$sum_xy = 0;
		$sum_x_squared = 0;

		foreach ( $values as $i => $y ) {
			$x = $i;
			$sum_x += $x;
			$sum_y += $y;
			$sum_xy += $x * $y;
			$sum_x_squared += $x * $x;
		}

		$denominator = ( $n * $sum_x_squared ) - ( $sum_x * $sum_x );
		if ( $denominator == 0 ) {
			return 0;
		}

		$slope = ( ( $n * $sum_xy ) - ( $sum_x * $sum_y ) ) / $denominator;

		return $slope;
	}

	/**
	 * Calculate days until threshold is reached
	 *
	 * @since 0.6093.1200
	 * @param  float $current Current value.
	 * @param  float $rate Rate of change per day.
	 * @param  float $threshold Threshold value.
	 * @return int|null Days to threshold, or null if never reached.
	 */
	private static function calculate_days_to_threshold( float $current, float $rate, float $threshold ): ?int {
		if ( $rate == 0 ) {
			return null;
		}

		$difference = $threshold - $current;
		$days = abs( $difference / $rate );

		return (int) ceil( $days );
	}

	/**
	 * Calculate growth rate from historical values
	 *
	 * @since 0.6093.1200
	 * @param  array $values Historical values.
	 * @return float Growth rate per day.
	 */
	private static function calculate_growth_rate( array $values ): float {
		return self::calculate_trend_slope( $values );
	}

	/**
	 * Get current database size in MB
	 *
	 * @since 0.6093.1200
	 * @return float Database size in MB.
	 */
	private static function get_current_database_size(): float {
		global $wpdb;

		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT SUM(data_length + index_length) / 1024 / 1024
				FROM information_schema.TABLES
				WHERE table_schema = %s",
				DB_NAME
			)
		);

		return $result ? (float) $result : 0;
	}

	/**
	 * Get database size history
	 *
	 * @since 0.6093.1200
	 * @param  int $days Days of history.
	 * @return array Historical database sizes.
	 */
	private static function get_database_size_history( int $days ): array {
		$history = get_option( 'wpshadow_db_size_history', array() );
		return array_slice( $history, -$days );
	}

	/**
	 * Get plugin count history
	 *
	 * @since 0.6093.1200
	 * @param  int $days Days of history.
	 * @return array Historical plugin counts.
	 */
	private static function get_plugin_count_history( int $days ): array {
		$history = get_option( 'wpshadow_plugin_count_history', array() );
		return array_slice( $history, -$days );
	}

	/**
	 * Get uploads directory size
	 *
	 * @since 0.6093.1200
	 * @return float Size in MB.
	 */
	private static function get_uploads_size(): float {
		$upload_dir = wp_upload_dir();
		$size = 0;

		if ( is_dir( $upload_dir['basedir'] ) ) {
			$size = self::get_directory_size( $upload_dir['basedir'] );
		}

		return round( $size / 1024 / 1024, 2 );
	}

	/**
	 * Get directory size recursively
	 *
	 * @since 0.6093.1200
	 * @param  string $directory Directory path.
	 * @return int Size in bytes.
	 */
	private static function get_directory_size( string $directory ): int {
		$size = 0;
		foreach ( new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $directory ) ) as $file ) {
			$size += $file->getSize();
		}
		return $size;
	}

	/**
	 * Predict uploads growth
	 *
	 * @since 0.6093.1200
	 * @param  int $days Days ahead.
	 * @return float Predicted size in MB.
	 */
	private static function predict_uploads_growth( int $days ): float {
		$current = self::get_uploads_size();
		$growth_rate = 5; // MB per month average
		return $current + ( ( $growth_rate / 30 ) * $days );
	}

	/**
	 * Identify recurring issue patterns
	 *
	 * @since 0.6093.1200
	 * @param  array $data Historical data.
	 * @return array Recurring patterns.
	 */
	private static function identify_recurring_patterns( array $data ): array {
		$patterns = array();
		$activities = $data['activities'] ?? array();

		// Group by issue type
		$issue_occurrences = array();
		foreach ( $activities as $activity ) {
			if ( isset( $activity['category'] ) && $activity['category'] === 'diagnostic' ) {
				$type = $activity['action'] ?? 'unknown';
				if ( ! isset( $issue_occurrences[ $type ] ) ) {
					$issue_occurrences[ $type ] = array();
				}
				$issue_occurrences[ $type ][] = $activity['timestamp'];
			}
		}

		// Analyze frequency
		foreach ( $issue_occurrences as $type => $timestamps ) {
			if ( count( $timestamps ) >= 3 ) {
				$intervals = array();
				for ( $i = 1; $i < count( $timestamps ); $i++ ) {
					$intervals[] = ( $timestamps[ $i ] - $timestamps[ $i - 1 ] ) / 86400; // Days
				}

				$avg_interval = array_sum( $intervals ) / count( $intervals );

				$patterns[] = array(
					'type'         => $type,
					'description'  => ucwords( str_replace( '_', ' ', $type ) ),
					'frequency'    => count( $timestamps ),
					'avg_interval' => $avg_interval,
					'last_seen'    => date( 'Y-m-d', end( $timestamps ) ),
					'severity'     => 'medium',
					'prevention'   => __( 'Regular maintenance recommended', 'wpshadow' ),
					'pattern'      => 'recurring',
				);
			}
		}

		return $patterns;
	}

	/**
	 * Calculate historical costs
	 *
	 * @since 0.6093.1200
	 * @param  array $data Historical data.
	 * @return array Cost breakdown.
	 */
	private static function calculate_historical_costs( array $data ): array {
		// Placeholder - would integrate with actual hosting costs
		$kpi_data = $data['kpi_data'] ?? array();
		$time_saved_hours = $kpi_data['total_time_saved_hours'] ?? 0;

		return array(
			'current_monthly'     => 50, // Base hosting cost estimate
			'trend'               => 2, // Growing $2/month
			'server_cost'         => 50,
			'inefficiency_cost'   => 15,
			'potential_savings'   => $time_saved_hours * 50, // $50/hour
		);
	}

	/**
	 * Calculate confidence level
	 *
	 * @since 0.6093.1200
	 * @param  array $data Historical data.
	 * @return float Confidence percentage (0-100).
	 */
	private static function calculate_confidence( array $data ): float {
		$activities = $data['activities'] ?? array();
		$health_history = $data['health_history'] ?? array();

		// More data = higher confidence
		$data_points = count( $activities ) + count( $health_history );
		$confidence = min( 100, ( $data_points / 100 ) * 100 );

		// Adjust for data quality
		if ( $data_points < 10 ) {
			$confidence *= 0.5;
		}

		return round( $confidence, 1 );
	}
}
