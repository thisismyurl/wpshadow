<?php
/**
 * Dashboard renderer extracted from bootstrap.
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

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
function wpshadow_render_dashboard( string $hub_id = '', string $spoke_id = '' ): void {
	if ( ! WPSHADOW_can_access_dashboard() ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
	}

	// Route to appropriate dashboard renderer.
	// All levels (core, hub, spoke) show the same core dashboard content.
	if ( ! empty( $spoke_id ) && ! empty( $hub_id ) ) {
		// Spoke-level displays core dashboard content.
	} elseif ( ! empty( $hub_id ) ) {
		// Hub-level displays core dashboard content.
	}

	// TEMPORARILY DISABLED: Module Registry calls - modules are disabled.
	// $catalog_modules = WPSHADOW_Module_Registry::get_catalog_with_status();
	// Use empty array instead for now.
	$modules = array();

	// Top stats derived from catalog with real activation state.
	$total_count     = count( $modules );
	$hub_modules     = array();
	$spoke_modules   = array();
	$hubs_count      = 0;
	$spokes_count    = 0;
	$available_count = 0;
	$updates_count   = 0;
	$enabled_count   = 0;

	// NOTE: Vault functionality removed - belongs in module-vault-wpshadow or PRO plugin
	$activity_logs     = array();
	$pending_uploads   = array();
	// TEMPORARILY DISABLED: Module Registry schedule snapshot - modules are disabled.
	// $schedule_snapshot = WPSHADOW_Module_Registry::get_schedule_snapshot();
	$schedule_snapshot = array();
	$run_now_nonce     = wp_create_nonce( 'wpshadow_run_task_now' );

	// TEMPORARILY DISABLED: Dashboard metabox setup - modules are disabled.
	// wpshadow_setup_dashboard_screen( $hub_id, $spoke_id );
	// Use simple HTML rendering instead.

	// Determine dashboard title based on context.
	$dashboard_title = __( 'WPShadow Dashboard', 'plugin-wpshadow' );
	if ( ! empty( $spoke_id ) && ! empty( $hub_id ) ) {
		$dashboard_title = ucfirst( $spoke_id ) . ' ' . __( 'Dashboard', 'plugin-wpshadow' );
	} elseif ( ! empty( $hub_id ) ) {
		$dashboard_title = ucfirst( $hub_id ) . ' ' . __( 'Dashboard', 'plugin-wpshadow' );
	}

	// Render simplified dashboard (metaboxes disabled with module system).
	?>
	<div class="wrap">
		<style>
			.wps-dashboard-header {
				display: flex;
				align-items: center;
				justify-content: space-between;
				margin-bottom: 16px;
			}
			.wps-dashboard-header h1 {
				margin: 0;
			}
			.wps-dashboard-header .button .dashicons {
				vertical-align: middle;
				margin-right: 4px;
			}
			.wps-dashboard-notice {
				background: #fff8e5;
				border-left: 4px solid #ffb900;
				padding: 12px;
				margin-bottom: 20px;
			}
			.wps-dashboard-section {
				background: #fff;
				border: 1px solid #ccc;
				border-radius: 4px;
				padding: 20px;
				margin-bottom: 20px;
			}
			.wps-dashboard-section h2 {
				margin-top: 0;
			}
		</style>
		<div class="wps-dashboard-header">
			<h1><?php echo esc_html( $dashboard_title ); ?></h1>
			<?php if ( empty( $hub_id ) && empty( $spoke_id ) ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=' . WPSHADOW_Tab_Navigation::TAB_DASHBOARD_SETTINGS ) ); ?>" class="button button-secondary">
					<span class="dashicons dashicons-admin-generic"></span>
					<?php esc_html_e( 'Dashboard Settings', 'plugin-wpshadow' ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php
		// Render feature search component (Issues #447 & #448).
		WPSHADOW_Feature_Search::render_search_component();
		?>


		<div class="wps-dashboard-section">
			<h2><?php esc_html_e( 'Dashboard', 'plugin-wpshadow' ); ?></h2>
			<p><?php esc_html_e( 'Welcome to WPShadow Dashboard. Core diagnostic features are available.', 'plugin-wpshadow' ); ?></p>
		</div>
	</div>
	</div>
	<?php
}
