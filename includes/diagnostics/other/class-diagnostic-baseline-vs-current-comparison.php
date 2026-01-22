<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Baseline vs Current Performance Comparison (HISTORICAL-003)
 *
 * Compares current performance against initial baseline or best recorded state.
 * Philosophy: Show value (#9) - "You were 2× faster 3 months ago, let's fix it".
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Baseline_Vs_Current_Comparison extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$baseline_score = (float) get_option( 'wpshadow_perf_baseline_score', 0 );
		$current_score  = (float) get_option( 'wpshadow_perf_current_score', 0 );
		$baseline_date  = get_option( 'wpshadow_perf_baseline_date', '' );

		if ( $baseline_score > 0 && $current_score > 0 && $current_score < ( $baseline_score * 0.85 ) ) {
			$regression_pct = round( ( 1 - ( $current_score / $baseline_score ) ) * 100, 1 );
			return array(
				'id'            => 'baseline-vs-current-comparison',
				'title'         => sprintf( __( 'Performance regressed by %.1f%% vs baseline', 'wpshadow' ), $regression_pct ),
				'description'   => __( 'Current performance is below your best-known baseline. Review recent deployments, plugins, and theme changes.', 'wpshadow' ),
				'severity'      => 'medium',
				'category'      => 'other',
				'kb_link'       => 'https://wpshadow.com/kb/performance-baseline/',
				'training_link' => 'https://wpshadow.com/training/performance-regressions/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'baseline_date' => $baseline_date,
			);
		}

		return null;
	}
}
