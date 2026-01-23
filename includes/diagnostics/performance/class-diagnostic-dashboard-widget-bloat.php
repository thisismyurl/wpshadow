<?php
/**
 * Diagnostic: Dashboard Widget Bloat
 *
 * Detects excessive dashboard widgets slowing down the admin dashboard.
 *
 * Philosophy: Inspire Confidence (#8) - Clean dashboard = professional
 * KB Link: https://wpshadow.com/kb/dashboard-widget-bloat
 * Training: https://wpshadow.com/training/dashboard-widget-bloat
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Widget Bloat diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Dashboard_Widget_Bloat extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		global $wp_meta_boxes;

		// Trigger dashboard widgets loading
		if ( ! isset( $wp_meta_boxes['dashboard'] ) ) {
			// Simulate dashboard context
			set_current_screen( 'dashboard' );
			do_action( 'wp_dashboard_setup' );
		}

		if ( empty( $wp_meta_boxes['dashboard'] ) ) {
			return null;
		}

		// Count total widgets
		$widget_count = 0;
		$external_widgets = [];
		$core_widgets = [
			'dashboard_right_now',
			'dashboard_activity',
			'dashboard_quick_press',
			'dashboard_primary',
		];

		foreach ( $wp_meta_boxes['dashboard'] as $context => $priority_groups ) {
			foreach ( $priority_groups as $priority => $widgets ) {
				foreach ( $widgets as $widget_id => $widget_data ) {
					$widget_count++;

					// Identify external widgets
					if ( ! in_array( $widget_id, $core_widgets, true ) ) {
						$external_widgets[] = [
							'id'    => $widget_id,
							'title' => $widget_data['title'] ?? $widget_id,
						];
					}
				}
			}
		}

		// Only flag if excessive (more than 8 widgets)
		if ( $widget_count < 8 ) {
			return null;
		}

		$severity = $widget_count > 12 ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your dashboard has %d widgets. Each widget loads data on every dashboard view, slowing down the admin. %d are from plugins/themes.', 'wpshadow' ),
			$widget_count,
			count( $external_widgets )
		);

		if ( ! empty( $external_widgets ) ) {
			$widget_names = array_slice( array_column( $external_widgets, 'title' ), 0, 5 );
			$description .= ' ' . __( 'External widgets: ', 'wpshadow' ) . implode( ', ', $widget_names );
		}

		return [
			'id'                => 'dashboard-widget-bloat',
			'title'             => __( 'Too Many Dashboard Widgets', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'medium',
			'effort'            => 'low',
			'kb_link'           => 'https://wpshadow.com/kb/dashboard-widget-bloat',
			'training_link'     => 'https://wpshadow.com/training/dashboard-widget-bloat',
			'affected_resource' => sprintf( '%d widgets', $widget_count ),
			'metadata'          => [
				'widget_count'     => $widget_count,
				'external_count'   => count( $external_widgets ),
				'external_widgets' => $external_widgets,
			],
		];
	}

}