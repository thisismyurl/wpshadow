<?php
/**
 * Admin screen setup extracted from bootstrap.
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Setup Screen Options, Help tabs, and register dashboard meta boxes.
 * Accepts optional parameters for compatibility with direct calls.
 *
 * @param string $hub_id   Optional hub id (ignored).
 * @param string $spoke_id Optional spoke id (ignored).
 * @return void
 */
function wpshadow_setup_dashboard_screen( string $hub_id = '', string $spoke_id = '' ): void { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	// Register dashboard metaboxes for all levels (core, hub, spoke) on dashboard tab.
	$context = WPSHADOW_Tab_Navigation::get_current_context();
	$tab     = $context['tab'] ?? 'dashboard';
	$hub     = $context['hub'] ?? '';
	$spoke   = $context['spoke'] ?? '';

	// Only register metaboxes when on dashboard tab.
	if ( 'dashboard' !== $tab ) {
		return;
	}

	// Determine context string for layout manager.
	$layout_context = 'core';
	if ( ! empty( $spoke ) && ! empty( $hub ) ) {
		$layout_context = $hub . '_' . $spoke;
	} elseif ( ! empty( $hub ) ) {
		$layout_context = $hub;
	}

	$network = is_network_admin();

	// Add Help tabs.
	$screen->add_help_tab(
		array(
			'id'      => 'wpshadow_overview',
			'title'   => __( 'Overview', 'plugin-wpshadow' ),
			'content' => '<p>' . esc_html__( 'This dashboard provides a suite overview, active hubs, recent activity, and quick actions. Use Screen Options to show/hide cards and arrange them.', 'plugin-wpshadow' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'wpshadow_shortcuts',
			'title'   => __( 'Shortcuts', 'plugin-wpshadow' ),
			'content' => '<p>' . esc_html__( 'Drag cards to rearrange. Click the toggle arrow to hide/show cards. Use Quick Actions to jump to common tasks.', 'plugin-wpshadow' ) . '</p>',
		)
	);

	$screen->set_help_sidebar(
		'<p><strong>' . esc_html__( 'More Help', 'plugin-wpshadow' ) . '</strong></p>' .
		'<p><a href="https://wpshadow.com/plugin-wpshadow/" target="_blank" rel="noopener">' . esc_html__( 'Documentation', 'plugin-wpshadow' ) . '</a></p>'
	);

	// Enable Screen Options for number of columns (2 by default).
	add_screen_option(
		'layout_columns',
		array(
			'max'     => 2,
			'default' => 2,
		)
	);

	// Use dashboard layout manager to setup widgets with proper ordering.
	WPSHADOW_Dashboard_Layout::setup_dashboard_screen( $layout_context, $network );
}

/**
 * Setup Screen Options and register dashboard meta boxes for hub pages.
 *
 * @param string $hub_id Hub identifier.
 * @return void
 */
function wpshadow_setup_hub_dashboard_screen( string $hub_id ): void {
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}
	// Format hub display name.
	$hub_name = esc_html( ucfirst( str_replace( '-', ' ', $hub_id ) ) );

	// Register metaboxes based on hub type.
	switch ( $hub_id ) {
		case 'media':
			add_meta_box(
				'wpshadow_media_overview',
				__( 'Media Overview', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_media_overview' ),
				$screen->id,
				'normal',
				'high'
			);
			add_meta_box(
				'wpshadow_media_activity',
				__( 'Media Activity', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_media_activity' ),
				$screen->id,
				'normal',
				'default'
			);
			add_meta_box(
				'wpshadow_media_modules',
				$hub_name . ' ' . __( 'Modules', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_modules' ),
				$screen->id,
				'normal',
				'low'
			);
			add_meta_box(
				'wpshadow_media_quick_actions',
				__( 'Media Quick Actions', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_quick_actions' ),
				$screen->id,
				'side',
				'high'
			);
			break;

		case 'vault':
			add_meta_box(
				'wpshadow_vault_overview',
				__( 'Vault Overview', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_vault_overview' ),
				$screen->id,
				'normal',
				'high'
			);
			add_meta_box(
				'wpshadow_vault_activity',
				__( 'Vault Activity', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_vault_activity' ),
				$screen->id,
				'normal',
				'default'
			);
			add_meta_box(
				'wpshadow_vault_modules',
				$hub_name . ' ' . __( 'Modules', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_modules' ),
				$screen->id,
				'normal',
				'low'
			);
			add_meta_box(
				'wpshadow_vault_stats',
				__( 'Vault Status', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_vault_status' ),
				$screen->id,
				'side',
				'high'
			);
			add_meta_box(
				'wpshadow_vault_quick_actions',
				__( 'Vault Quick Actions', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_quick_actions' ),
				$screen->id,
				'side',
				'default'
			);
			add_meta_box(
				'wpshadow_vault_health',
				__( 'Vault Health', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_vault_health' ),
				$screen->id,
				'side',
				'low'
			);
			break;

		default:
			// Generic hub dashboard: always include Modules widget.
			add_meta_box(
				'wpshadow_' . sanitize_html_class( $hub_id ) . '_modules',
				$hub_name . ' ' . __( 'Modules', 'plugin-wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_modules' ),
				$screen->id,
				'normal',
				'default'
			);
			break;
	}

	// Enable Screen Options for number of columns.
	add_screen_option(
		'layout_columns',
		array(
			'max'     => 2,
			'default' => 2,
		)
	);

	// Initialize postboxes on this screen (drag/toggle).
	add_action(
		'admin_print_footer_scripts',
		static function () use ( $screen, $hub_id ): void {
			// Use hub-specific state key.
			$state_key = 'wp-support-' . $hub_id;
			?>
			<script>
			jQuery(document).ready(function($){
				if (typeof postboxes !== 'undefined') {
					postboxes.add_postbox_toggles('<?php echo esc_js( $state_key ); ?>');
				}
			});
			</script>
			<?php
		}
	);
}
