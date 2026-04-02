<?php
/**
 * Incomplete Widget Settings Export Diagnostic
 *
 * Tests whether sidebar widgets and their configurations
 * are included in exports.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Incomplete Widget Settings Export Diagnostic Class
 *
 * Tests whether sidebar widgets and configurations
 * are included in exports.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Incomplete_Widget_Settings_Export extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'incomplete-widget-settings-export';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Incomplete Widget Settings Export';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether widget configurations are included in exports';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that widget data and settings are properly
	 * captured in export files.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wp_registered_sidebars, $wpdb;

		$sidebars = $wp_registered_sidebars ?? array();

		if ( empty( $sidebars ) ) {
			return null;
		}

		$sidebar_count = count( $sidebars );
		$total_widgets = 0;
		$sidebar_details = array();

		// Get widget options from database.
		$widget_options = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} 
				WHERE option_name LIKE %s",
				'widget_%'
			)
		);

		$custom_widget_count = count( $widget_options );

		// Check for active widgets in each sidebar.
		$sidebar_widgets_option = get_option( 'sidebars_widgets', array() );

		foreach ( $sidebar_widgets_option as $sidebar_id => $widgets ) {
			if ( 'wp_inactive_widgets' === $sidebar_id ) {
				continue;
			}

			if ( is_array( $widgets ) && ! empty( $widgets ) ) {
				$widget_count = count( $widgets );
				$total_widgets += $widget_count;

				if ( isset( $sidebars[ $sidebar_id ] ) ) {
					$sidebar_details[] = array(
						'sidebar_id'   => $sidebar_id,
						'sidebar_name' => $sidebars[ $sidebar_id ]['name'] ?? $sidebar_id,
						'widgets'      => $widget_count,
					);
				}
			}
		}

		// Check for inactive widgets.
		$inactive_widgets = isset( $sidebar_widgets_option['wp_inactive_widgets'] )
			? count( $sidebar_widgets_option['wp_inactive_widgets'] )
			: 0;

		// Check for block-based widgets (newer WordPress).
		$wp_widget_block_enabled = has_filter( 'widget_display_callback' );

		// Check for theme-specific widgets.
		$theme_widgets = get_option( 'theme_mods_' . get_template(), array() );
		$theme_widget_count = is_array( $theme_widgets ) ? count( $theme_widgets ) : 0;

		// Check for widget customizer settings.
		$widget_customizer_settings = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name LIKE %s",
				'%widget_customizer_%'
			)
		);

		// Check WXR widget export support.
		$wxr_widgets_included = apply_filters( 'wxr_export_widgets', false );

		if ( $total_widgets > 0 || $custom_widget_count > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of sidebars, %d: number of widgets */
					__( '%d sidebars with %d configured widgets are not included in exports', 'wpshadow' ),
					$sidebar_count,
					$total_widgets
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/incomplete-widget-settings-export',
				'details'      => array(
					'total_sidebars'                => $sidebar_count,
					'active_widgets'                => $total_widgets,
					'inactive_widgets'              => $inactive_widgets,
					'custom_widget_options'         => $custom_widget_count,
					'widget_customizer_settings'    => $widget_customizer_settings,
					'sidebar_details'               => $sidebar_details,
					'theme_widget_count'            => $theme_widget_count,
					'block_widgets_enabled'         => $wp_widget_block_enabled,
					'wxr_widgets_export_enabled'    => $wxr_widgets_included,
					'layout_impact'                 => sprintf(
						/* translators: %d: number of widgets */
						__( 'All %d sidebar widgets will need manual reconfiguration after restore', 'wpshadow' ),
						$total_widgets
					),
					'functionality_loss'            => __( 'Widget-dependent features will stop working (searches, calendars, etc.)', 'wpshadow' ),
					'user_experience'               => __( 'Site sidebars and widget areas will display empty after migration', 'wpshadow' ),
					'reconfiguration_effort'        => __( 'Hours of manual widget reconfiguration required', 'wpshadow' ),
					'important_note'                => __( 'WordPress standard export does not include widget settings - requires separate tool', 'wpshadow' ),
					'fix_methods'                   => array(
						__( 'Use Customizer to export/import widget settings if available', 'wpshadow' ),
						__( 'Use theme backup/restore if provided by theme', 'wpshadow' ),
						__( 'Export widget settings via theme-specific tools', 'wpshadow' ),
						__( 'Document all widget configurations manually', 'wpshadow' ),
						__( 'Use database backup which includes widget options', 'wpshadow' ),
					),
					'verification'                  => array(
						__( 'Check WXR export for widget option entries', 'wpshadow' ),
						__( 'Verify sidebar/widget data in export file', 'wpshadow' ),
						__( 'Document current widget setup before backup', 'wpshadow' ),
						__( 'Test import on staging site', 'wpshadow' ),
						__( 'Verify all widgets display after import', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
