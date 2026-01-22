<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: A/B Test and Experiment Performance Impact (EXPERIMENT-341)
 *
 * Quantifies perf cost of A/B and recommendation scripts.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_AbTestPerformanceImpact extends Diagnostic_Base {
	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$ab_overhead_ms = (int) get_transient( 'wpshadow_ab_test_overhead_ms' );
		$ab_scripts     = (int) get_transient( 'wpshadow_ab_test_script_count' );

		if ( $ab_overhead_ms > 100 || $ab_scripts > 2 ) {
			return array(
				'id'            => 'ab-test-performance-impact',
				'title'         => sprintf( __( 'A/B testing adds %1$dms and %2$d scripts', 'wpshadow' ), $ab_overhead_ms, $ab_scripts ),
				'description'   => __( 'Experiment scripts are adding noticeable overhead. Consider server-side testing, async loading, or consolidating vendors.', 'wpshadow' ),
				'severity'      => 'medium',
				'category'      => 'other',
				'kb_link'       => 'https://wpshadow.com/kb/ab-test-performance/',
				'training_link' => 'https://wpshadow.com/training/experimentation-performance/',
				'auto_fixable'  => false,
				'threat_level'  => 55,
			);
		}

		return null;
	}
}
