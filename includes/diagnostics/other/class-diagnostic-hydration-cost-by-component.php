<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Hydration Cost by Component (FE-322)
 *
 * Profiles component-level hydration time for block/React themes.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_HydrationCostByComponent extends Diagnostic_Base {
	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$slow_components = get_transient( 'wpshadow_slow_hydration_components' );
		$slow_components = is_array( $slow_components ) ? $slow_components : array();

		if ( ! empty( $slow_components ) ) {
			return array(
				'id'              => 'hydration-cost-by-component',
				'title'           => __( 'High hydration cost components found', 'wpshadow' ),
				'description'     => __( 'Certain components are expensive to hydrate. Consider partial hydration, islands architecture, or server components.', 'wpshadow' ),
				'severity'        => 'medium',
				'category'        => 'other',
				'kb_link'         => 'https://wpshadow.com/kb/hydration-cost/',
				'training_link'   => 'https://wpshadow.com/training/react-performance/',
				'auto_fixable'    => false,
				'threat_level'    => 55,
				'slow_components' => $slow_components,
			);
		}

		return null;
	}
}
