<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Dashboard_Layout {

	public static function get_default_order(): array {
		return array(
			'normal' => array(
				'wpshadow_widget_activity',
				'wpshadow_widget_scheduled_tasks',
				'wpshadow_widget_modules',
			),
			'side'   => array(
				'wpshadow_widget_health',
				'wpshadow_widget_environment_status',
				'wpshadow_widget_database_stats',
				'wpshadow_widget_performance_history',
				'wpshadow_widget_quick_actions',
				'wpshadow_widget_events_and_news',
			),
		);
	}

	public static function get_features_default_order(): array {
		return array(
			'normal' => array(
				'wpshadow_features_list',
				'wpshadow_feature_settings',
				'wpshadow_features_scheduled_activity',
				'wpshadow_features_activity_history',
			),
			'side'   => array(
				'wpshadow_features_quick_links',
				'wpshadow_features_info',
				'wpshadow_features_system_health',
			),
		);
	}

	public static function get_layout( string $context, bool $network = false ): ?array {
		$option_name = self::get_layout_option_name( $context, $network );
		$layout      = get_option( $option_name );

		if ( is_array( $layout ) && ! empty( $layout ) ) {
			return $layout;
		}

		return null;
	}

	public static function save_layout( string $context, array $layout, bool $network = false ): bool {
		$option_name = self::get_layout_option_name( $context, $network );
		return update_option( $option_name, $layout );
	}

	public static function get_or_initialize_layout( string $context, bool $network = false ): array {

		$layout = self::get_layout( $context, $network );
		if ( null !== $layout ) {
			return $layout;
		}

		$parent_context = self::get_parent_context( $context );
		if ( $parent_context ) {
			$parent_layout = self::get_layout( $parent_context, $network );
			if ( null !== $parent_layout ) {

				self::save_layout( $context, $parent_layout, $network );
				return $parent_layout;
			}
		}

		if ( is_multisite() && ! $network ) {
			$network_layout = self::get_layout( $context, true );
			if ( null !== $network_layout ) {

				self::save_layout( $context, $network_layout, false );
				return $network_layout;
			}
		}

		$default = self::get_default_order();
		self::save_layout( $context, $default, $network );
		return $default;
	}

	private static function get_parent_context( string $context ): ?string {
		if ( 'core' === $context ) {
			return null;
		}

		if ( str_contains( $context, '_' ) ) {

			return substr( $context, 0, (int) strpos( $context, '_' ) );
		}

		return 'core';
	}

	public static function apply_layout_to_scope( string $source_context, array $layout, string $scope, bool $network = false ): array {

		if ( 'this' === $scope ) {
			return array(
				'success' => 0,
				'failed'  => 0,
				'targets' => array(),
			);
		}

		$targets = self::get_target_contexts( $source_context, $scope, $network );
		$success = 0;
		$failed  = 0;

		foreach ( $targets as $target_context ) {

			$target_layout = self::get_layout( $target_context, $network );
			if ( null !== $target_layout ) {

				++$failed;
				continue;
			}

			if ( self::save_layout( $target_context, $layout, $network ) ) {
				++$success;
			} else {
				++$failed;
			}
		}

		return array(
			'success' => $success,
			'failed'  => $failed,
			'targets' => $targets,
		);
	}

	private static function get_target_contexts( string $source_context, string $scope, bool $network ): array {

		if ( 'this' === $scope ) {
			return array();
		}

		$catalog = WPSHADOW_Module_Registry::get_catalog_with_status();
		$targets = array();

		if ( 'all' === $scope ) {

			foreach ( $catalog as $module ) {
				if ( ! empty( $module['status']['active'] ) ) {
					$module_id = sanitize_key( str_replace( '-wpshadow', '', $module['id'] ?? '' ) );
					if ( $module_id !== $source_context ) {
						$targets[] = $module_id;
					}
				}
			}
		} elseif ( 'children' === $scope ) {

			foreach ( $catalog as $module ) {
				if ( empty( $module['status']['active'] ) ) {
					continue;
				}

				$module_id = sanitize_key( str_replace( '-wpshadow', '', $module['id'] ?? '' ) );

				if ( 'core' === $source_context && 'hub' === ( $module['type'] ?? '' ) ) {
					$targets[] = $module_id;
					continue;
				}

				if ( 'spoke' === ( $module['type'] ?? '' ) && str_starts_with( $module_id, $source_context . '_' ) ) {
					$targets[] = $module_id;
				}
			}
		}

		return $targets;
	}

	private static function get_layout_option_name( string $context, bool $network ): string {
		$prefix = $network ? 'wpshadow_network_' : 'wpshadow_';
		return $prefix . 'dashboard_layout_' . sanitize_key( $context );
	}

	public static function on_module_activated( string $module_id, bool $network = false ): void {
		$context        = sanitize_key( str_replace( '-wpshadow', '', $module_id ) );
		$parent_context = self::get_parent_context( $context );

		if ( ! $parent_context ) {

			$layout = self::get_default_order();
			self::save_layout( $context, $layout, $network );
			return;
		}

		$parent_layout = self::get_layout( $parent_context, $network );
		if ( null === $parent_layout ) {

			$parent_layout = self::get_or_initialize_layout( $parent_context, $network );
		}

		self::save_layout( $context, $parent_layout, $network );

		if ( class_exists( 'WPShadow\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log(
				'dashboard',
				sprintf(
					__( 'Dashboard layout inherited from %1$s for module %2$s', 'wpshadow' ),
					$parent_context,
					$context
				),
				array(
					'module_id' => $module_id,
					'parent'    => $parent_context,
					'network'   => $network,
				)
			);
		}
	}

	public static function setup_dashboard_screen( string $context = 'core', bool $network = false ): void {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		$module_name = '';
		if ( 'core' !== $context ) {

			$module_parts = explode( '-', $context );
			$module_id    = $module_parts[0];

			$catalog     = \WPShadow\WPSHADOW_Module_Registry::get_catalog_with_status();
			$module_slug = str_contains( $module_id, '-wpshadow' ) ? $module_id : $module_id . '-wpshadow';
			if ( isset( $catalog[ $module_slug ] ) ) {
				$module_name = $catalog[ $module_slug ]['name'] ?? ucfirst( $module_id );
			} else {
				$module_name = ucfirst( $module_id );
			}
		}

		$layout = self::get_or_initialize_layout( $context, $network );

		if ( ! empty( $layout['normal'] ) ) {
			foreach ( $layout['normal'] as $widget_id ) {
				self::register_widget_by_id( $widget_id, $screen->id, 'normal', $module_name );
			}
		}

		if ( ! empty( $layout['side'] ) ) {
			foreach ( $layout['side'] as $widget_id ) {
				self::register_widget_by_id( $widget_id, $screen->id, 'side', $module_name );
			}
		}

		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'dashboard' );
		wp_enqueue_style( 'dashboard' );

		wp_enqueue_script(
			'wps-dashboard-layout',
			plugin_dir_url( __DIR__ ) . 'assets/js/dashboard-layout.js',
			array( 'jquery', 'postbox', 'jquery-ui-sortable' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'wps-dashboard-layout',
			'wpshadow_dashboard_layout',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'wpshadow_dashboard_layout' ),
				'moduleNonce'    => wp_create_nonce( 'wpshadow_module_actions' ),
				'context'        => $context,
				'network'        => $network ? '1' : '0',
				'applyPrompt'    => __( 'Apply this layout to:', 'wpshadow' ),
				'applyThis'      => __( 'This dashboard only', 'wpshadow' ),
				'applyChildren'  => __( 'Child dashboards only', 'wpshadow' ),
				'applyAll'       => __( 'All dashboards', 'wpshadow' ),
				'cancel'         => __( 'Cancel', 'wpshadow' ),
				'apply'          => __( 'Apply', 'wpshadow' ),
				'refreshSuccess' => __( 'Your stats are up to date.', 'wpshadow' ),
				'refreshError'   => __( 'Stats didn\'t update. Try again?', 'wpshadow' ),
			)
		);
	}

	private static function register_widget_by_id( string $widget_id, string $screen_id, string $context, string $module_name = '' ): void {
		$widgets = self::get_available_widgets();

		if ( ! isset( $widgets[ $widget_id ] ) ) {
			return;
		}

		$widget = $widgets[ $widget_id ];

		$title = $widget['title'];
		if ( ! empty( $module_name ) ) {
			$title = $module_name . ' ' . $title;
		}

		add_meta_box(
			$widget_id,
			$title,
			$widget['callback'],
			$screen_id,
			$context,
			'default'
		);
	}

	private static function get_available_widgets(): array {
		return array(
			'wpshadow_widget_activity'           => array(
				'title'    => __( 'Activity', 'wpshadow' ),
				'callback' => array( 'WPShadow\CoreSupport\WPSHADOW_Dashboard_Widgets', 'render_metabox_activity' ),
			),
			'wpshadow_widget_scheduled_tasks'    => array(
				'title'    => __( 'Scheduled Tasks', 'wpshadow' ),
				'callback' => array( 'WPShadow\CoreSupport\WPSHADOW_Dashboard_Widgets', 'render_metabox_scheduled_tasks' ),
			),

			'wpshadow_widget_health'             => array(
				'title'    => __( 'Health', 'wpshadow' ),
				'callback' => array( 'WPShadow\CoreSupport\WPSHADOW_Dashboard_Widgets', 'render_metabox_health' ),
			),
			'wpshadow_widget_quick_actions'      => array(
				'title'    => __( 'Quick Actions', 'wpshadow' ),
				'callback' => array( 'WPShadow\CoreSupport\WPSHADOW_Dashboard_Widgets', 'render_metabox_quick_actions' ),
			),
			'wpshadow_widget_events_and_news'    => array(
				'title'    => __( 'Events and News', 'wpshadow' ),
				'callback' => array( 'WPShadow\CoreSupport\WPSHADOW_Dashboard_Widgets', 'render_metabox_events_and_news' ),
			),
			'wpshadow_widget_system_health'      => array(
				'title'    => __( 'System Health', 'wpshadow' ),
				'callback' => array( 'WPShadow\CoreSupport\WPSHADOW_Dashboard_Widgets', 'render_metabox_system_health' ),
			),
			'wpshadow_widget_vault_status'       => array(
				'title'    => __( 'Vault Status', 'wpshadow' ),
				'callback' => array( 'WPShadow\CoreSupport\WPSHADOW_Dashboard_Widgets', 'render_metabox_vault_status' ),
			),
			'wpshadow_widget_database_stats'     => array(
				'title'    => __( 'Database Statistics', 'wpshadow' ),
				'callback' => array( 'WPShadow\CoreSupport\WPSHADOW_Dashboard_Widgets', 'render_metabox_database_stats' ),
			),
			'wpshadow_widget_performance_history' => array(
				'title'    => __( 'Historical Performance', 'wpshadow' ),
				'callback' => array( 'WPShadow\CoreSupport\WPSHADOW_Dashboard_Widgets', 'render_metabox_performance_history' ),
			),
			'wpshadow_widget_media_overview'     => array(
				'title'    => __( 'Media Overview', 'wpshadow' ),
				'callback' => array( 'WPShadow\CoreSupport\WPSHADOW_Dashboard_Widgets', 'render_metabox_media_overview' ),
			),
			'wpshadow_widget_vault_overview'     => array(
				'title'    => __( 'Vault Overview', 'wpshadow' ),
				'callback' => array( 'WPShadow\WPSHADOW_Dashboard_Widgets', 'render_metabox_vault_overview' ),
			),

		);
	}

	public static function ajax_save_layout(): void {
		check_ajax_referer( 'wpshadow_dashboard_layout', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$context = sanitize_text_field( wp_unslash( $_POST['context'] ?? 'core' ) );
		$network = isset( $_POST['network'] ) && '1' === $_POST['network'];
		$layout  = json_decode( wp_unslash( $_POST['layout'] ?? '{}' ), true );

		if ( ! is_array( $layout ) ) {
			wp_send_json_error( array( 'message' => __( 'That layout data isn\'t valid.', 'wpshadow' ) ) );
		}

		if ( self::save_layout( $context, $layout, $network ) ) {
			wp_send_json_success( array( 'message' => __( 'Layout saved.', 'wpshadow' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Layout didn\'t save. Try again?', 'wpshadow' ) ) );
		}
	}

	public static function ajax_apply_layout(): void {
		check_ajax_referer( 'wpshadow_dashboard_layout', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$context = sanitize_text_field( wp_unslash( $_POST['context'] ?? 'core' ) );
		$scope   = sanitize_text_field( wp_unslash( $_POST['scope'] ?? 'children' ) );
		$network = isset( $_POST['network'] ) && '1' === $_POST['network'];
		$layout  = json_decode( wp_unslash( $_POST['layout'] ?? '{}' ), true );

		if ( ! is_array( $layout ) ) {
			wp_send_json_error( array( 'message' => __( 'That layout data isn\'t valid.', 'wpshadow' ) ) );
		}

		$result = self::apply_layout_to_scope( $context, $layout, $scope, $network );

		wp_send_json_success(
			array(
				'message' => sprintf(
					__( 'Applied to %1$d dashboards. %2$d skipped.', 'wpshadow' ),
					$result['success'],
					$result['failed']
				),
				'result'  => $result,
			)
		);
	}
}
