<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Widget_Groups {

	private static array $widget_groups = array();

	public static function get_all(): array {
		if ( empty( self::$widget_groups ) ) {
			self::initialize();
		}

		return self::$widget_groups;
	}

	public static function get( string $group_id ): ?array {
		$groups = self::get_all();
		return $groups[ $group_id ] ?? null;
	}

	public static function get_label( string $group_id ): string {
		$group = self::get( $group_id );
		return $group['label'] ?? self::format_label( $group_id );
	}

	public static function get_description( string $group_id ): string {
		$group = self::get( $group_id );
		return $group['description'] ?? '';
	}

	public static function get_icon( string $group_id ): string {
		$group = self::get( $group_id );
		return $group['icon'] ?? 'dashicons-admin-generic';
	}

	public static function get_dashboard( string $group_id ): string {
		$group = self::get( $group_id );
		return $group['dashboard'] ?? 'overview';
	}

	public static function get_column( string $group_id ): string {
		$group = self::get( $group_id );
		return $group['column'] ?? 'left';
	}

	public static function get_priority( string $group_id ): int {
		$group = self::get( $group_id );
		return $group['priority'] ?? 50;
	}

	private static function initialize(): void {
		self::$widget_groups = array(
			'security'           => array(
				'label'       => __( 'Security & Protection', 'wpshadow' ),
				'description' => __( 'Security hardening, authentication, and threat protection features', 'wpshadow' ),
				'icon'        => 'dashicons-shield',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 10,
			),
			'performance'        => array(
				'label'       => __( 'Performance Optimization', 'wpshadow' ),
				'description' => __( 'Speed optimization, caching, and resource management', 'wpshadow' ),
				'icon'        => 'dashicons-performance',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 20,
			),
			'accessibility'      => array(
				'label'       => __( 'UX & Accessibility', 'wpshadow' ),
				'description' => __( 'Improve user experience and accessibility standards', 'wpshadow' ),
				'icon'        => 'dashicons-universal-access',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 25,
			),
			'seo'                => array(
				'label'       => __( 'SEO & Social Media', 'wpshadow' ),
				'description' => __( 'SEO and social media optimization features', 'wpshadow' ),
				'icon'        => 'dashicons-search',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 30,
			),
			'server-diagnostics' => array(
				'label'       => __( 'Server Diagnostics', 'wpshadow' ),
				'description' => __( 'Server environment and configuration tools', 'wpshadow' ),
				'icon'        => 'dashicons-admin-tools',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 35,
			),
			'diagnostics'        => array(
				'label'       => __( 'Diagnostics & Monitoring', 'wpshadow' ),
				'description' => __( 'Health checks and monitoring features', 'wpshadow' ),
				'icon'        => 'dashicons-admin-tools',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 40,
			),
			'debugging'          => array(
				'label'       => __( 'Debugging & Testing', 'wpshadow' ),
				'description' => __( 'Development and troubleshooting tools', 'wpshadow' ),
				'icon'        => 'dashicons-editor-code',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 45,
			),
			'monitoring'         => array(
				'label'       => __( 'Monitoring & Alerts', 'wpshadow' ),
				'description' => __( 'Site monitoring and performance alerts', 'wpshadow' ),
				'icon'        => 'dashicons-visibility',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 50,
			),
			'reporting'          => array(
				'label'       => __( 'Analytics & Reporting', 'wpshadow' ),
				'description' => __( 'Performance metrics and reporting tools', 'wpshadow' ),
				'icon'        => 'dashicons-chart-line',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 55,
			),
			'analytics-features' => array(
				'label'       => __( 'Analytics & Recommendations', 'wpshadow' ),
				'description' => __( 'Intelligent site analysis and recommendation features', 'wpshadow' ),
				'icon'        => 'dashicons-chart-bar',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 57,
			),
			'media'              => array(
				'label'       => __( 'Media Optimization', 'wpshadow' ),
				'description' => __( 'Image and media optimization features', 'wpshadow' ),
				'icon'        => 'dashicons-format-image',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 60,
			),
			'image-optimization' => array(
				'label'       => __( 'Image Optimization', 'wpshadow' ),
				'description' => __( 'Image compression, lazy loading, and delivery optimization', 'wpshadow' ),
				'icon'        => 'dashicons-format-gallery',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 62,
			),
			'cleanup'            => array(
				'label'       => __( 'Code Cleanup', 'wpshadow' ),
				'description' => __( 'Clean up unnecessary code and optimize markup', 'wpshadow' ),
				'icon'        => 'dashicons-editor-removeformatting',
				'dashboard'   => 'overview',
				'column'      => 'left',
				'priority'    => 65,
			),
			'tools'              => array(
				'label'       => __( 'Maintenance Tools', 'wpshadow' ),
				'description' => __( 'Site maintenance and utility tools', 'wpshadow' ),
				'icon'        => 'dashicons-admin-tools',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 70,
			),
			'advanced'           => array(
				'label'       => __( 'Advanced Features', 'wpshadow' ),
				'description' => __( 'Advanced optimization and power-user tools', 'wpshadow' ),
				'icon'        => 'dashicons-admin-settings',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 75,
			),
			'advanced-features'  => array(
				'label'       => __( 'Advanced Features', 'wpshadow' ),
				'description' => __( 'Advanced site management and customization', 'wpshadow' ),
				'icon'        => 'dashicons-admin-generic',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 77,
			),
			'safety'             => array(
				'label'       => __( 'Safety & Recovery', 'wpshadow' ),
				'description' => __( 'Backup, rollback, and site protection features', 'wpshadow' ),
				'icon'        => 'dashicons-sos',
				'dashboard'   => 'overview',
				'column'      => 'right',
				'priority'    => 80,
			),
		);

		self::$widget_groups = apply_filters( 'wpshadow_widget_groups', self::$widget_groups );
	}

	private static function format_label( string $group_id ): string {
		return ucwords( str_replace( array( '-', '_' ), ' ', $group_id ) );
	}

	public static function exists( string $group_id ): bool {
		$groups = self::get_all();
		return isset( $groups[ $group_id ] );
	}

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
