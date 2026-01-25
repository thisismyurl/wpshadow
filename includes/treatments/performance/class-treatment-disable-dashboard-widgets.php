<?php
/**
 * Treatment: Disable Dashboard Widgets
 *
 * Disables unnecessary dashboard widgets.
 *
 * Philosophy: Helpful Neighbor (#1) - Clean interface, user choice
 * KB Link: https://wpshadow.com/kb/dashboard-widget-bloat
 * Training: https://wpshadow.com/training/dashboard-widget-bloat
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\KPI_Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disable Dashboard Widgets treatment
 */
class Treatment_Disable_Dashboard_Widgets extends Treatment_Base {

	/**
	 * Apply the treatment
	 *
	 * @param array $options Treatment options
	 * @return bool Success status
	 */
	public static function apply( array $options = array() ): bool {
		// Get list of widgets to disable (default: external only)
		$disable_list = isset( $options['widgets'] ) ? $options['widgets'] : 'external';

		// Create backup
		$backup = array(
			'disabled_widgets' => $disable_list,
			'timestamp'        => time(),
		);
		self::create_backup( $backup );

		// Store in options
		update_option( 'wpshadow_disabled_dashboard_widgets', $disable_list );

		// Hook to remove widgets
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'remove_dashboard_widgets' ), 999 );

		// Track KPI
		KPI_Tracker::record_treatment_applied( __CLASS__, 1 );

		return true;
	}

	/**
	 * Remove dashboard widgets
	 */
	public static function remove_dashboard_widgets(): void {
		global $wp_meta_boxes;

		$disable_list = get_option( 'wpshadow_disabled_dashboard_widgets', 'external' );

		$core_widgets = array(
			'dashboard_right_now',
			'dashboard_activity',
			'dashboard_quick_press',
			'dashboard_primary',
		);

		if ( $disable_list === 'external' ) {
			// Remove only external widgets
			foreach ( $wp_meta_boxes['dashboard'] as $context => $priority_groups ) {
				foreach ( $priority_groups as $priority => $widgets ) {
					foreach ( $widgets as $widget_id => $widget_data ) {
						if ( ! in_array( $widget_id, $core_widgets, true ) ) {
							remove_meta_box( $widget_id, 'dashboard', $context );
						}
					}
				}
			}
		} elseif ( $disable_list === 'all_except_wpshadow' ) {
			// Remove all except WPShadow widgets
			foreach ( $wp_meta_boxes['dashboard'] as $context => $priority_groups ) {
				foreach ( $priority_groups as $priority => $widgets ) {
					foreach ( $widgets as $widget_id => $widget_data ) {
						if ( strpos( $widget_id, 'wpshadow' ) !== 0 ) {
							remove_meta_box( $widget_id, 'dashboard', $context );
						}
					}
				}
			}
		} elseif ( is_array( $disable_list ) ) {
			// Remove specific widgets
			foreach ( $disable_list as $widget_id ) {
				remove_meta_box( $widget_id, 'dashboard', 'normal' );
				remove_meta_box( $widget_id, 'dashboard', 'side' );
			}
		}
	}

	/**
	 * Undo the treatment
	 *
	 * @return bool Success status
	 */
	public static function undo(): bool {
		delete_option( 'wpshadow_disabled_dashboard_widgets' );
		remove_action( 'wp_dashboard_setup', array( __CLASS__, 'remove_dashboard_widgets' ), 999 );
		return true;
	}

	/**
	 * Get display name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return __( 'Disable Dashboard Widgets', 'wpshadow' );
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return sprintf(
			__( 'Disables unnecessary dashboard widgets to speed up the dashboard. You can choose to disable only external widgets or all non-WPShadow widgets. <a href="%s" target="_blank">Learn about dashboard optimization</a>', 'wpshadow' ),
			'https://wpshadow.com/kb/dashboard-widget-bloat'
		);
	}
}
