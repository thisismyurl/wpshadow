<?php
/**
 * Widget Groups Configuration
 *
 * Centralized definition of widget group metadata to avoid repetition across features.
 * Each widget group defines its label, description, icon, and default positioning.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.76000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Widget_Groups Class
 *
 * Provides centralized widget group configuration.
 */
final class WPSHADOW_Widget_Groups {

	/**
	 * Widget group definitions.
	 *
	 * @var array
	 */
	private static array $widget_groups = array();

	/**
	 * Get all widget group definitions.
	 *
	 * @return array Widget groups with metadata.
	 */
	public static function get_all(): array {
		if ( empty( self::$widget_groups ) ) {
			self::initialize();
		}

		return self::$widget_groups;
	}

	/**
	 * Get a specific widget group definition.
	 *
	 * @param string $group_id Widget group ID.
	 * @return array|null Widget group data or null if not found.
	 */
	public static function get( string $group_id ): ?array {
		$groups = self::get_all();
		return $groups[ $group_id ] ?? null;
	}

	/**
	 * Get widget group label.
	 *
	 * @param string $group_id Widget group ID.
	 * @return string Widget label.
	 */
	public static function get_label( string $group_id ): string {
		$group = self::get( $group_id );
		return $group['label'] ?? self::format_label( $group_id );
	}

	/**
	 * Get widget group description.
	 *
	 * @param string $group_id Widget group ID.
	 * @return string Widget description.
	 */
	public static function get_description( string $group_id ): string {
		$group = self::get( $group_id );
		return $group['description'] ?? '';
	}

	/**
	 * Get widget group icon.
	 *
	 * @param string $group_id Widget group ID.
	 * @return string Dashicon class.
	 */
	public static function get_icon( string $group_id ): string {
		$group = self::get( $group_id );
		return $group['icon'] ?? 'dashicons-admin-generic';
	}

	/**
	 * Get default dashboard location.
	 *
	 * @param string $group_id Widget group ID.
	 * @return string Dashboard location.
	 */
	public static function get_dashboard( string $group_id ): string {
		$group = self::get( $group_id );
		return $group['dashboard'] ?? 'overview';
	}

	/**
	 * Get default column.
	 *
	 * @param string $group_id Widget group ID.
	 * @return string Column location (left/right).
	 */
	public static function get_column( string $group_id ): string {
		$group = self::get( $group_id );
		return $group['column'] ?? 'left';
	}

	/**
	 * Get default priority.
	 *
	 * @param string $group_id Widget group ID.
	 * @return int Priority value.
	 */
	public static function get_priority( string $group_id ): int {
		$group = self::get( $group_id );
		return $group['priority'] ?? 50;
	}

	/**
	 * Initialize widget group definitions.
	 *
	 * @return void
	 */
	private static function initialize(): void {
		self::$widget_groups = array(
			'security'           => array(
				'label'       => __( 'Security & Protection', 'plugin-wpshadow' ),
				'description' => __( 'Security hardening, authentication, and threat protection features', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-shield',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 10,
			),
			'performance'        => array(
				'label'       => __( 'Performance Optimization', 'plugin-wpshadow' ),
				'description' => __( 'Speed optimization, caching, and resource management', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-performance',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 20,
			),
			'accessibility'      => array(
				'label'       => __( 'UX & Accessibility', 'plugin-wpshadow' ),
				'description' => __( 'Improve user experience and accessibility standards', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-universal-access',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 25,
			),
			'seo'                => array(
				'label'       => __( 'SEO & Social Media', 'plugin-wpshadow' ),
				'description' => __( 'SEO and social media optimization features', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-search',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 30,
			),
			'server-diagnostics' => array(
				'label'       => __( 'Server Diagnostics', 'plugin-wpshadow' ),
				'description' => __( 'Server environment and configuration tools', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-admin-tools',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 35,
			),
			'diagnostics'        => array(
				'label'       => __( 'Diagnostics & Monitoring', 'plugin-wpshadow' ),
				'description' => __( 'Health checks and monitoring features', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-admin-tools',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 40,
			),
			'debugging'          => array(
				'label'       => __( 'Debugging & Testing', 'plugin-wpshadow' ),
				'description' => __( 'Development and troubleshooting tools', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-editor-code',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 45,
			),
			'monitoring'         => array(
				'label'       => __( 'Monitoring & Alerts', 'plugin-wpshadow' ),
				'description' => __( 'Site monitoring and performance alerts', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-visibility',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 50,
			),
			'reporting'          => array(
				'label'       => __( 'Analytics & Reporting', 'plugin-wpshadow' ),
				'description' => __( 'Performance metrics and reporting tools', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-chart-line',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 55,
			),
			'analytics-features' => array(
				'label'       => __( 'Analytics & Recommendations', 'plugin-wpshadow' ),
				'description' => __( 'Intelligent site analysis and recommendation features', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-chart-bar',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 57,
			),
			'media'              => array(
				'label'       => __( 'Media Optimization', 'plugin-wpshadow' ),
				'description' => __( 'Image and media optimization features', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-format-image',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 60,
			),
			'image-optimization' => array(
				'label'       => __( 'Image Optimization', 'plugin-wpshadow' ),
				'description' => __( 'Image compression, lazy loading, and delivery optimization', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-format-gallery',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 62,
			),
			'cleanup'            => array(
				'label'       => __( 'Code Cleanup', 'plugin-wpshadow' ),
				'description' => __( 'Clean up unnecessary code and optimize markup', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-editor-removeformatting',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 65,
			),
			'tools'              => array(
				'label'       => __( 'Maintenance Tools', 'plugin-wpshadow' ),
				'description' => __( 'Site maintenance and utility tools', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-admin-tools',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 70,
			),
			'advanced'           => array(
				'label'       => __( 'Advanced Features', 'plugin-wpshadow' ),
				'description' => __( 'Advanced optimization and power-user tools', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-admin-settings',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 75,
			),
			'advanced-features'  => array(
				'label'       => __( 'Advanced Features', 'plugin-wpshadow' ),
				'description' => __( 'Advanced site management and customization', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-admin-generic',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 77,
			),
			'safety'             => array(
				'label'       => __( 'Safety & Recovery', 'plugin-wpshadow' ),
				'description' => __( 'Backup, rollback, and site protection features', 'plugin-wpshadow' ),
				'icon'        => 'dashicons-sos',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 80,
			),
		);

		/**
		 * Filter widget group definitions.
		 *
		 * @param array $widget_groups Widget group definitions.
		 */
		self::$widget_groups = apply_filters( 'wpshadow_widget_groups', self::$widget_groups );
	}

	/**
	 * Format widget group ID to readable label.
	 *
	 * @param string $group_id Widget group ID.
	 * @return string Formatted label.
	 */
	private static function format_label( string $group_id ): string {
		return ucwords( str_replace( array( '-', '_' ), ' ', $group_id ) );
	}

	/**
	 * Check if widget group exists.
	 *
	 * @param string $group_id Widget group ID.
	 * @return bool True if exists.
	 */
	public static function exists( string $group_id ): bool {
		$groups = self::get_all();
		return isset( $groups[ $group_id ] );
	}

	/**
	 * Register a new widget group.
	 *
	 * @param string $group_id Widget group ID.
	 * @param array  $config   Widget group configuration.
	 * @return bool True if registered successfully.
	 */
	public static function register( string $group_id, array $config ): bool {
		if ( empty( self::$widget_groups ) ) {
			self::initialize();
		}

		if ( self::exists( $group_id ) ) {
			return false;
		}

		self::$widget_groups[ $group_id ] = wp_parse_args(
			$config,
			array(
				'label'       => self::format_label( $group_id ),
				'description' => '',
				'icon'        => 'dashicons-admin-generic',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 50,
			)
		);

		return true;
	}
}
