<?php
/**
 * Dashboard Layout Manager
 * Handles widget ordering, inheritance, and bulk operations.
 *
 * @package WPS_WP_SUPPORT_THISISMYURL
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages dashboard widget layout, ordering, and inheritance.
 */
class WPS_Dashboard_Layout {
	/**
	 * Default widget order for core dashboard.
	 * Matches WordPress core layout for familiarity.
	 *
	 * @return array<string, array<string>>
	 */
	public static function get_default_order(): array {
		return array(
			'normal' => array(
				'WPS_widget_activity',
				'WPS_widget_scheduled_tasks',
				'WPS_widget_modules',
			),
			'side'   => array(
				'WPS_widget_health',
				'WPS_widget_quick_actions',
				'WPS_widget_events_and_news',
			),
		);
	}

	/**
	 * Get the dashboard layout for a specific context.
	 *
	 * @param string $context Dashboard context (core, hub_id, hub_id_spoke_id).
	 * @param bool   $network Network admin context.
	 * @return array<string, array<string>>|null
	 */
	public static function get_layout( string $context, bool $network = false ): ?array {
		$option_name = self::get_layout_option_name( $context, $network );
		$layout      = get_option( $option_name );

		if ( is_array( $layout ) && ! empty( $layout ) ) {
			return $layout;
		}

		return null;
	}

	/**
	 * Save dashboard layout for a context.
	 *
	 * @param string $context Dashboard context.
	 * @param array  $layout  Layout configuration.
	 * @param bool   $network Network admin context.
	 * @return bool
	 */
	public static function save_layout( string $context, array $layout, bool $network = false ): bool {
		$option_name = self::get_layout_option_name( $context, $network );
		return update_option( $option_name, $layout );
	}

	/**
	 * Get or initialize layout for a dashboard context.
	 * Implements inheritance hierarchy: saved → parent → network → default.
	 *
	 * @param string $context Dashboard context.
	 * @param bool   $network Network admin context.
	 * @return array<string, array<string>>
	 */
	public static function get_or_initialize_layout( string $context, bool $network = false ): array {
		// Try saved layout first.
		$layout = self::get_layout( $context, $network );
		if ( null !== $layout ) {
			return $layout;
		}

		// Try to inherit from parent module.
		$parent_context = self::get_parent_context( $context );
		if ( $parent_context ) {
			$parent_layout = self::get_layout( $parent_context, $network );
			if ( null !== $parent_layout ) {
				// Save inherited layout for this context.
				self::save_layout( $context, $parent_layout, $network );
				return $parent_layout;
			}
		}

		// Try network default if in multisite and not network admin.
		if ( is_multisite() && ! $network ) {
			$network_layout = self::get_layout( $context, true );
			if ( null !== $network_layout ) {
				// Save network default for this site.
				self::save_layout( $context, $network_layout, false );
				return $network_layout;
			}
		}

		// Fall back to default order.
		$default = self::get_default_order();
		self::save_layout( $context, $default, $network );
		return $default;
	}

	/**
	 * Get parent context for a dashboard.
	 *
	 * @param string $context Current context (e.g., 'vault_webp').
	 * @return string|null Parent context or null if core level.
	 */
	private static function get_parent_context( string $context ): ?string {
		if ( 'core' === $context ) {
			return null;
		}

		// Check if it's a spoke (contains underscore).
		if ( str_contains( $context, '_' ) ) {
			// Return hub context (part before underscore).
			return substr( $context, 0, (int) strpos( $context, '_' ) );
		}

		// It's a hub, parent is core.
		return 'core';
	}

	/**
	 * Apply layout to child dashboards or all dashboards.
	 *
	 * @param string $source_context Source dashboard context.
	 * @param array  $layout         Layout to apply.
	 * @param string $scope          Scope: 'children' or 'all'.
	 * @param bool   $network        Network admin context.
	 * @return array{success: int, failed: int, targets: array<string>}
	 */
	public static function apply_layout_to_scope( string $source_context, array $layout, string $scope, bool $network = false ): array {
		$targets = self::get_target_contexts( $source_context, $scope, $network );
		$success = 0;
		$failed  = 0;

		foreach ( $targets as $target_context ) {
			// Don't overwrite if target has custom modifications.
			$target_layout = self::get_layout( $target_context, $network );
			if ( null !== $target_layout ) {
				// Check if user wants to overwrite (should be confirmed in UI).
				// For now, skip contexts with existing customizations.
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

	/**
	 * Get target contexts based on scope.
	 *
	 * @param string $source_context Source dashboard context.
	 * @param string $scope          Scope: 'children' or 'all'.
	 * @param bool   $network        Network admin context.
	 * @return array<string>
	 */
	private static function get_target_contexts( string $source_context, string $scope, bool $network ): array {
		$catalog = WPS_Module_Registry::get_catalog_with_status();
		$targets = array();

		if ( 'all' === $scope ) {
			// Apply to all active modules.
			foreach ( $catalog as $module ) {
				if ( ! empty( $module['status']['active'] ) ) {
					$module_id = sanitize_key( str_replace( '-support-thisismyurl', '', $module['id'] ?? '' ) );
					if ( $module_id !== $source_context ) {
						$targets[] = $module_id;
					}
				}
			}
		} elseif ( 'children' === $scope ) {
			// Apply only to child modules.
			foreach ( $catalog as $module ) {
				if ( empty( $module['status']['active'] ) ) {
					continue;
				}

				$module_id = sanitize_key( str_replace( '-support-thisismyurl', '', $module['id'] ?? '' ) );

				// For core, children are all hubs.
				if ( 'core' === $source_context && 'hub' === ( $module['type'] ?? '' ) ) {
					$targets[] = $module_id;
					continue;
				}

				// For hubs, children are spokes that start with hub ID.
				if ( 'spoke' === ( $module['type'] ?? '' ) && str_starts_with( $module_id, $source_context . '_' ) ) {
					$targets[] = $module_id;
				}
			}
		}

		return $targets;
	}

	/**
	 * Get option name for layout storage.
	 *
	 * @param string $context Dashboard context.
	 * @param bool   $network Network admin context.
	 * @return string
	 */
	private static function get_layout_option_name( string $context, bool $network ): string {
		$prefix = $network ? 'WPS_network_' : 'WPS_';
		return $prefix . 'dashboard_layout_' . sanitize_key( $context );
	}

	/**
	 * Handle module activation and inherit parent layout.
	 * Hook: after module activation.
	 *
	 * @param string $module_id Module identifier.
	 * @param bool   $network   Network activation.
	 * @return void
	 */
	public static function on_module_activated( string $module_id, bool $network = false ): void {
		$context        = sanitize_key( str_replace( '-support-thisismyurl', '', $module_id ) );
		$parent_context = self::get_parent_context( $context );

		if ( ! $parent_context ) {
			// No parent, use default.
			$layout = self::get_default_order();
			self::save_layout( $context, $layout, $network );
			return;
		}

		// Inherit from parent.
		$parent_layout = self::get_layout( $parent_context, $network );
		if ( null === $parent_layout ) {
			// Parent has no saved layout, try to initialize it.
			$parent_layout = self::get_or_initialize_layout( $parent_context, $network );
		}

		// Save inherited layout.
		self::save_layout( $context, $parent_layout, $network );

		// Log activity.
		if ( class_exists( 'WPS\CoreSupport\WPS_Activity_Logger' ) ) {
			WPS_Activity_Logger::log(
				'dashboard',
				sprintf(
					__( 'Dashboard layout inherited from %1$s for module %2$s', 'plugin-wp-support-thisismyurl' ),
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

	/**
	 * Setup dashboard metaboxes with proper ordering.
	 *
	 * @param string $context Dashboard context.
	 * @param bool   $network Network admin context.
	 * @return void
	 */
	public static function setup_dashboard_screen( string $context = 'core', bool $network = false ): void {
		$screen = get_current_screen();
		if ( ! $screen ) {
			return;
		}

		// Determine module name for prepending to widget titles.
		$module_name = '';
		if ( 'core' !== $context ) {
			// Extract module name from context (e.g., 'media', 'vault').
			$module_parts = explode( '-', $context );
			$module_id    = $module_parts[0];

			// Get the proper module name from catalog.
			$catalog     = \WPS\CoreSupport\WPS_Module_Registry::get_catalog_with_status();
			$module_slug = str_contains( $module_id, '-support-thisismyurl' ) ? $module_id : $module_id . '-support-thisismyurl';
			if ( isset( $catalog[ $module_slug ] ) ) {
				$module_name = $catalog[ $module_slug ]['name'] ?? ucfirst( $module_id );
			} else {
				$module_name = ucfirst( $module_id );
			}
		}

		// Get layout order for this context.
		$layout = self::get_or_initialize_layout( $context, $network );

		// Register dashboard widgets in order.
		// Normal column.
		if ( ! empty( $layout['normal'] ) ) {
			foreach ( $layout['normal'] as $widget_id ) {
				self::register_widget_by_id( $widget_id, $screen->id, 'normal', $module_name );
			}
		}

		// Side column.
		if ( ! empty( $layout['side'] ) ) {
			foreach ( $layout['side'] as $widget_id ) {
				self::register_widget_by_id( $widget_id, $screen->id, 'side', $module_name );
			}
		}

		// Enqueue scripts for drag/drop and bulk apply prompt.
		wp_enqueue_script( 'postbox' );
		wp_enqueue_script( 'dashboard' );
		wp_enqueue_style( 'dashboard' );

		// Enqueue custom dashboard layout script.
		wp_enqueue_script(
			'wps-dashboard-layout',
			plugin_dir_url( __DIR__ ) . 'assets/js/dashboard-layout.js',
			array( 'jquery', 'postbox', 'jquery-ui-sortable' ),
			'1.0.0',
			true
		);

		wp_localize_script(
			'wps-dashboard-layout',
			'WPS_dashboard_layout',
			array(
				'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
				'nonce'         => wp_create_nonce( 'WPS_dashboard_layout' ),
				'context'       => $context,
				'network'       => $network ? '1' : '0',
				'applyPrompt'   => __( 'Apply this layout to:', 'plugin-wp-support-thisismyurl' ),
				'applyChildren' => __( 'Child dashboards only', 'plugin-wp-support-thisismyurl' ),
				'applyAll'      => __( 'All dashboards', 'plugin-wp-support-thisismyurl' ),
				'cancel'        => __( 'Cancel', 'plugin-wp-support-thisismyurl' ),
				'apply'         => __( 'Apply', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}

	/**
	 * Register a widget by ID.
	 *
	 * @param string $widget_id   Widget identifier.
	 * @param string $screen_id   Screen identifier.
	 * @param string $context     Context (normal or side).
	 * @param string $module_name Optional module name to prepend to title.
	 * @return void
	 */
	private static function register_widget_by_id( string $widget_id, string $screen_id, string $context, string $module_name = '' ): void {
		$widgets = self::get_available_widgets();

		if ( ! isset( $widgets[ $widget_id ] ) ) {
			return;
		}

		$widget = $widgets[ $widget_id ];

		// Prepend module name to title if provided.
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

	/**
	 * Get available dashboard widgets.
	 *
	 * @return array<string, array{title: string, callback: callable}>
	 */
	private static function get_available_widgets(): array {
		return array(
			'WPS_widget_activity'        => array(
				'title'    => __( 'Activity', 'plugin-wp-support-thisismyurl' ),
				'callback' => array( 'WPS\CoreSupport\WPS_Dashboard_Widgets', 'render_metabox_activity' ),
			),
			'WPS_widget_scheduled_tasks' => array(
				'title'    => __( 'Scheduled Tasks', 'plugin-wp-support-thisismyurl' ),
				'callback' => array( 'WPS\CoreSupport\WPS_Dashboard_Widgets', 'render_metabox_scheduled_tasks' ),
			),
			'WPS_widget_modules'         => array(
				'title'    => __( 'Modules', 'plugin-wp-support-thisismyurl' ),
				'callback' => array( 'WPS\CoreSupport\WPS_Dashboard_Widgets', 'render_metabox_modules' ),
			),
			'WPS_widget_health'          => array(
				'title'    => __( 'Health', 'plugin-wp-support-thisismyurl' ),
				'callback' => array( 'WPS\CoreSupport\WPS_Dashboard_Widgets', 'render_metabox_health' ),
			),
			'WPS_widget_quick_actions'   => array(
				'title'    => __( 'Quick Actions', 'plugin-wp-support-thisismyurl' ),
				'callback' => array( 'WPS\CoreSupport\WPS_Dashboard_Widgets', 'render_metabox_quick_actions' ),
			),
			'WPS_widget_events_and_news' => array(
				'title'    => __( 'Events and News', 'plugin-wp-support-thisismyurl' ),
				'callback' => array( 'WPS\CoreSupport\WPS_Dashboard_Widgets', 'render_metabox_events_and_news' ),
			),
			'WPS_widget_system_health'   => array(
				'title'    => __( 'System Health', 'plugin-wp-support-thisismyurl' ),
				'callback' => array( 'WPS\CoreSupport\WPS_Dashboard_Widgets', 'render_metabox_system_health' ),
			),
			'WPS_widget_vault_status'    => array(
				'title'    => __( 'Vault Status', 'plugin-wp-support-thisismyurl' ),
				'callback' => array( 'WPS\CoreSupport\WPS_Dashboard_Widgets', 'render_metabox_vault_status' ),
			),
			'WPS_widget_media_overview'  => array(
				'title'    => __( 'Media Overview', 'plugin-wp-support-thisismyurl' ),
				'callback' => array( 'WPS\CoreSupport\WPS_Dashboard_Widgets', 'render_metabox_media_overview' ),
			),
			'WPS_widget_vault_overview'  => array(
				'title'    => __( 'Vault Overview', 'plugin-wp-support-thisismyurl' ),
				'callback' => array( 'WPS\CoreSupport\WPS_Dashboard_Widgets', 'render_metabox_vault_overview' ),
			),
		);
	}

	/**
	 * Handle AJAX save dashboard layout.
	 *
	 * @return void
	 */
	public static function ajax_save_layout(): void {
		check_ajax_referer( 'WPS_dashboard_layout', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$context = sanitize_text_field( wp_unslash( $_POST['context'] ?? 'core' ) );
		$network = isset( $_POST['network'] ) && '1' === $_POST['network'];
		$layout  = json_decode( wp_unslash( $_POST['layout'] ?? '{}' ), true );

		if ( ! is_array( $layout ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid layout data.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		if ( self::save_layout( $context, $layout, $network ) ) {
			wp_send_json_success( array( 'message' => __( 'Layout saved.', 'plugin-wp-support-thisismyurl' ) ) );
		} else {
			wp_send_json_error( array( 'message' => __( 'Failed to save layout.', 'plugin-wp-support-thisismyurl' ) ) );
		}
	}

	/**
	 * Handle AJAX apply layout to scope.
	 *
	 * @return void
	 */
	public static function ajax_apply_layout(): void {
		check_ajax_referer( 'WPS_dashboard_layout', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_network_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$context = sanitize_text_field( wp_unslash( $_POST['context'] ?? 'core' ) );
		$scope   = sanitize_text_field( wp_unslash( $_POST['scope'] ?? 'children' ) );
		$network = isset( $_POST['network'] ) && '1' === $_POST['network'];
		$layout  = json_decode( wp_unslash( $_POST['layout'] ?? '{}' ), true );

		if ( ! is_array( $layout ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid layout data.', 'plugin-wp-support-thisismyurl' ) ) );
		}

		$result = self::apply_layout_to_scope( $context, $layout, $scope, $network );

		wp_send_json_success(
			array(
				'message' => sprintf(
					__( 'Applied to %1$d dashboards. %2$d skipped.', 'plugin-wp-support-thisismyurl' ),
					$result['success'],
					$result['failed']
				),
				'result'  => $result,
			)
		);
	}
}
