<?php
/**
 * Dashboard renderer extracted from bootstrap.
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render dashboard (Core, Hub, or Spoke).
 *
 * @param string $hub_id Optional hub identifier for hub-level dashboards.
 * @param string $spoke_id Optional spoke identifier for spoke-level dashboards.
 * @return void
 */
function wp_support_render_dashboard( string $hub_id = '', string $spoke_id = '' ): void {
	if ( ! wps_can_access_dashboard() ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wp-support-thisismyurl' ) );
	}

	// Route to appropriate dashboard renderer.
	// All levels (core, hub, spoke) show the same core dashboard content.
	if ( ! empty( $spoke_id ) && ! empty( $hub_id ) ) {
		// Spoke-level displays core dashboard content.
	} elseif ( ! empty( $hub_id ) ) {
		// Hub-level displays core dashboard content.
	}

	// Core-level dashboard (shown for all levels).
	$catalog_modules = WPS_Module_Registry::get_catalog_with_status();
	$modules         = $catalog_modules;

	// Top stats derived from catalog with real activation state.
	$total_count     = count( $modules );
	$hub_modules     = array_filter(
		$modules,
		static function ( $m ) {
			return ( $m['type'] ?? '' ) === 'hub';
		}
	);
	$spoke_modules   = array_filter(
		$modules,
		static function ( $m ) {
			return ( $m['type'] ?? '' ) === 'spoke';
		}
	);
	$hubs_count      = count( $hub_modules );
	$spokes_count    = count( $spoke_modules );
	$available_count = count(
		array_filter(
			$modules,
			static function ( $m ) {
				return empty( $m['installed'] );
			}
		)
	);
	$updates_count   = count(
		array_filter(
			$modules,
			static function ( $m ) {
				return ! empty( $m['update_available'] );
			}
		)
	);
	$enabled_count   = count(
		array_filter(
			$modules,
			static function ( $m ) {
				$slug = $m['slug'] ?? null;
				if ( empty( $m['installed'] ) || ! $slug ) {
					return false;
				}
				$plugin = $slug . '/' . $slug . '.php';
				return is_plugin_active( $plugin ) || ( is_multisite() && is_plugin_active_for_network( $plugin ) );
			}
		)
	);

	$activity_logs     = WPS_Vault::get_logs( 0, 10 );
	$pending_uploads   = WPS_Vault::get_pending_contributor_uploads( 5 );
	$schedule_snapshot = WPS_Module_Registry::get_schedule_snapshot();
	$run_now_nonce     = wp_create_nonce( 'wps_run_task_now' );

	// Setup metaboxes for dashboard rendering.
	wp_support_setup_dashboard_screen( $hub_id, $spoke_id );
	$screen = get_current_screen();

	// Determine dashboard title based on context.
	$dashboard_title = __( 'Support Dashboard', 'plugin-wp-support-thisismyurl' );
	if ( ! empty( $spoke_id ) && ! empty( $hub_id ) ) {
		$dashboard_title = ucfirst( $spoke_id ) . ' ' . __( 'Dashboard', 'plugin-wp-support-thisismyurl' );
	} elseif ( ! empty( $hub_id ) ) {
		$dashboard_title = ucfirst( $hub_id ) . ' ' . __( 'Dashboard', 'plugin-wp-support-thisismyurl' );
	}

	// Render metabox-based dashboard.
	?>
	<div class="wrap">
		<div class="wps-dashboard-header" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
			<h1 style="margin: 0;"><?php echo esc_html( $dashboard_title ); ?></h1>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wp-support&WPS_tab=dashboard_settings' ) ); ?>" class="button button-secondary">
				<span class="dashicons dashicons-admin-generic" style="vertical-align: middle; margin-right: 4px;"></span>
				<?php esc_html_e( 'Dashboard Settings', 'plugin-wp-support-thisismyurl' ); ?>
			</a>
		</div>

		<div class="wps-dashboard-license-row">
			<div id="wps_license_widget" class="postbox" style="margin:0 0 16px 0;">
				<?php \WPS\CoreSupport\WPS_License_Widget::render_widget(); ?>
			</div>
		</div>

		<div id="dashboard-widgets" class="metabox-holder wps-dashboard-grid">
			<div id="postbox-container-1" class="postbox-container">
				<?php do_meta_boxes( $screen->id, 'normal', null ); ?>
			</div>
			<div id="postbox-container-2" class="postbox-container">
				<?php do_meta_boxes( $screen->id, 'side', null ); ?>
			</div>
		</div>
	</div>

	<style>
		.wps-dashboard-grid {
			display: grid;
			grid-template-columns: 2fr 1fr;
			grid-column-gap: 16px;
		}

		.wps-dashboard-grid #postbox-container-1,
		.wps-dashboard-grid #postbox-container-2 {
			width: 100%;
		}

		@media (max-width: 1024px) {
			.wps-dashboard-grid {
				grid-template-columns: 1fr;
			}
		}
	</style>
	</div>
	<?php
}
