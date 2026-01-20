<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpshadow_setup_dashboard_screen( string $hub_id = '', string $spoke_id = '' ): void { 
	$screen = get_current_screen();
	if ( ! $screen ) {
		return;
	}

	$context = WPSHADOW_Tab_Navigation::get_current_context();
	$tab     = $context['tab'] ?? 'dashboard';
	$hub     = $context['hub'] ?? '';
	$spoke   = $context['spoke'] ?? '';

	if ( 'dashboard' !== $tab ) {
		return;
	}

	$layout_context = 'core';
	if ( ! empty( $spoke ) && ! empty( $hub ) ) {
		$layout_context = $hub . '_' . $spoke;
	} elseif ( ! empty( $hub ) ) {
		$layout_context = $hub;
	}

	$network = is_network_admin();

	$screen->add_help_tab(
		array(
			'id'      => 'wpshadow_overview',
			'title'   => __( 'Overview', 'wpshadow' ),
			'content' => '<p>' . esc_html__( 'This dashboard provides a suite overview, active hubs, recent activity, and quick actions. Use Screen Options to show/hide cards and arrange them.', 'wpshadow' ) . '</p>',
		)
	);

	$screen->add_help_tab(
		array(
			'id'      => 'wpshadow_shortcuts',
			'title'   => __( 'Shortcuts', 'wpshadow' ),
			'content' => '<p>' . esc_html__( 'Drag cards to rearrange. Click the toggle arrow to hide/show cards. Use Quick Actions to jump to common tasks.', 'wpshadow' ) . '</p>',
		)
	);

	$screen->set_help_sidebar(
		'<p><strong>' . esc_html__( 'More Help', 'wpshadow' ) . '</strong></p>' .
		'<p><a href="https://wpshadow.com/help" target="_blank">' . esc_html__( 'Documentation', 'wpshadow' ) . '</a></p>'
	);

	add_screen_option(
		'layout_columns',
		array(
			'max'     => 2,
			'default' => 2,
		)
	);

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

	$hub_name = esc_html( ucfirst( str_replace( '-', ' ', $hub_id ) ) );

	switch ( $hub_id ) {
		case 'media':
			add_meta_box(
				'wpshadow_media_overview',
				__( 'Media Overview', 'wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_media_overview' ),
				$screen->id,
				'normal',
				'high'
			);
			add_meta_box(
				'wpshadow_media_activity',
				__( 'Media Activity', 'wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_media_activity' ),
				$screen->id,
				'normal',
				'default'
			);
			add_meta_box(
				'wpshadow_media_modules',
				$hub_name . ' ' . __( 'Modules', 'wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_modules' ),
				$screen->id,
				'normal',
				'low'
			);
			add_meta_box(
				'wpshadow_media_quick_actions',
				__( 'Media Quick Actions', 'wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_quick_actions' ),
				$screen->id,
				'side',
				'high'
			);
			break;

		case 'vault':

			break;

		default:

			add_meta_box(
				'wpshadow_' . sanitize_html_class( $hub_id ) . '_modules',
				$hub_name . ' ' . __( 'Modules', 'wpshadow' ),
				array( '\\WPShadow\\WPSHADOW_Dashboard_Widgets', 'render_metabox_modules' ),
				$screen->id,
				'normal',
				'default'
			);
			break;
	}

	add_screen_option(
		'layout_columns',
		array(
			'max'     => 2,
			'default' => 2,
		)
	);

	add_action(
		'admin_print_footer_scripts',
		static function () use ( $screen, $hub_id ): void {

			$state_key = 'wpshadow-' . $hub_id;
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
