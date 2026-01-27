<?php
/**
 * Menu Callback Stubs
 *
 * Temporary stub functions for menu callbacks that don't have dedicated files yet.
 * These prevent fatal errors when menus are registered but the actual page isn't loaded.
 *
 * @package WPShadow
 * @subpackage Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'wpshadow_render_action_items' ) ) {
	/**
	 * Render Action Items page (Kanban Board)
	 */
	function wpshadow_render_action_items() {
		// Load the kanban board view
		if ( file_exists( WPSHADOW_PATH . 'includes/views/kanban-board.php' ) ) {
			require_once WPSHADOW_PATH . 'includes/views/kanban-board.php';
		} else {
			echo '<div class="wrap"><h1>Action Items</h1><p class="wps-version-tag">v' . esc_html( WPSHADOW_VERSION ) . '</p><p>Loading action items...</p></div>';
		}
	}
}

if ( ! function_exists( 'wpshadow_render_guardian' ) ) {
	/**
	 * Render Guardian page (Diagnostics & Treatments)
	 */
	function wpshadow_render_guardian() {
		// Load Guardian classes if not already loaded
		if ( ! class_exists( '\WPShadow\Guardian\Guardian_Manager' ) ) {
			require_once WPSHADOW_PATH . 'includes/guardian/class-guardian-manager.php';
		}
		if ( ! class_exists( '\WPShadow\Guardian\Guardian_Activity_Logger' ) ) {
			require_once WPSHADOW_PATH . 'includes/monitoring/class-guardian-activity-logger.php';
		}
		if ( ! class_exists( '\WPShadow\Guardian\Auto_Fix_Executor' ) ) {
			require_once WPSHADOW_PATH . 'includes/monitoring/recovery/class-auto-fix-executor.php';
		}
		if ( ! class_exists( '\WPShadow\Guardian\Recovery_System' ) ) {
			require_once WPSHADOW_PATH . 'includes/monitoring/recovery/class-recovery-system.php';
		}
		
		// Load Guardian Dashboard class if not already loaded
		if ( ! class_exists( '\WPShadow\Admin\Guardian_Dashboard' ) ) {
			require_once WPSHADOW_PATH . 'includes/admin/class-guardian-dashboard.php';
		}
		
		if ( class_exists( '\WPShadow\Admin\Guardian_Dashboard' ) ) {
			echo \WPShadow\Admin\Guardian_Dashboard::render();
		} else {
			echo '<div class="wrap"><h1>Guardian</h1><p class="wps-version-tag">v' . esc_html( WPSHADOW_VERSION ) . '</p><p>Diagnostics and treatments system.</p></div>';
		}
	}
}

if ( ! function_exists( 'wpshadow_render_reports' ) ) {
	/**
	 * Render Reports page
	 */
	function wpshadow_render_reports() {
		// Load Report Form class if not already loaded
		if ( ! class_exists( '\WPShadow\Admin\Report_Form' ) ) {
			require_once WPSHADOW_PATH . 'includes/screens/class-report-form.php';
		}
		
		if ( class_exists( '\WPShadow\Admin\Report_Form' ) ) {
			echo \WPShadow\Admin\Report_Form::render();
		} else {
			echo '<div class="wrap"><h1>Reports</h1><p class="wps-version-tag">v' . esc_html( WPSHADOW_VERSION ) . '</p><p>Site health reports and analytics.</p></div>';
		}
	}
}

if ( ! function_exists( 'wpshadow_render_settings' ) ) {
	/**
	 * Render Settings page
	 */
	function wpshadow_render_settings() {
		?>
		<div class="wps-page-container">
			<!-- Page Header -->
			<div class="wps-page-header">
				<h1 class="wps-page-title">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'WPShadow Settings', 'wpshadow' ); ?>
					<small style="font-size: 14px; color: #666; margin-left: 12px;">v<?php echo esc_html( WPSHADOW_VERSION ); ?></small>
				</h1>
				<p class="wps-page-subtitle">
					<?php esc_html_e( 'Configure WPShadow plugin settings and preferences.', 'wpshadow' ); ?>
				</p>
			</div>

			<!-- Settings Grid -->
			<div class="wps-grid wps-grid-auto-320">
				<!-- General Settings -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-admin-generic wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<?php esc_html_e( 'General Settings', 'wpshadow' ); ?>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Configure general plugin behavior and preferences.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=general' ) ); ?>" class="wps-btn wps-btn--primary">
							<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php esc_html_e( 'Configure', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

				<!-- Scan Settings -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-search wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<?php esc_html_e( 'Scan Settings', 'wpshadow' ); ?>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Enable or disable specific diagnostic checks and treatments.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-scan-settings' ) ); ?>" class="wps-btn wps-btn--primary">
							<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php esc_html_e( 'Configure', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

				<!-- Privacy Settings -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-lock wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<?php esc_html_e( 'Privacy & Data', 'wpshadow' ); ?>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Manage data collection, anonymous reporting, and privacy preferences.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=privacy' ) ); ?>" class="wps-btn wps-btn--primary">
							<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php esc_html_e( 'Configure', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

				<!-- Notification Settings -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-email wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<?php esc_html_e( 'Notifications', 'wpshadow' ); ?>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Configure email alerts and notification preferences.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=notifications' ) ); ?>" class="wps-btn wps-btn--primary">
							<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php esc_html_e( 'Configure', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

				<!-- Backup & Recovery -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-backup wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<?php esc_html_e( 'Backup & Recovery', 'wpshadow' ); ?>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Configure automatic backups before applying treatments.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=backup' ) ); ?>" class="wps-btn wps-btn--primary">
							<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php esc_html_e( 'Configure', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

				<!-- Advanced Settings -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-admin-tools wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<?php esc_html_e( 'Advanced', 'wpshadow' ); ?>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Advanced configuration options for power users.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=advanced' ) ); ?>" class="wps-btn wps-btn--primary">
							<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php esc_html_e( 'Configure', 'wpshadow' ); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

if ( ! function_exists( 'wpshadow_render_scan_settings' ) ) {
	/**
	 * Render Scan Settings page (Diagnostics/Treatments toggles)
	 */
	function wpshadow_render_scan_settings() {
		// Load the scan settings page file
		if ( file_exists( WPSHADOW_PATH . 'includes/screens/class-scan-settings-page.php' ) ) {
			require_once WPSHADOW_PATH . 'includes/screens/class-scan-settings-page.php';
			if ( function_exists( '\WPShadow\Admin\wpshadow_render_scan_settings' ) ) {
				\WPShadow\Admin\wpshadow_render_scan_settings();
				return;
			}
		}

		echo '<div class="wrap"><h1>' . esc_html__( 'Scan Settings', 'wpshadow' ) . '</h1><p class="wps-version-tag">v' . esc_html( WPSHADOW_VERSION ) . '</p><p>' . esc_html__( 'Loading scan settings...', 'wpshadow' ) . '</p></div>';
	}
}

// Load Tools module (defines wpshadow_render_tools if not already defined)
if ( ! function_exists( 'wpshadow_render_tools' ) ) {
	require_once WPSHADOW_PATH . 'includes/screens/class-tools-page-module.php';
}

// Load Help module (defines wpshadow_render_help if not already defined)
if ( ! function_exists( 'wpshadow_render_help' ) ) {
	require_once WPSHADOW_PATH . 'includes/screens/class-help-page-module.php';
}

// Load Workflows module (defines wpshadow_render_workflow_builder if not already defined)
if ( ! function_exists( 'wpshadow_render_workflow_builder' ) ) {
	require_once WPSHADOW_PATH . 'includes/workflow/workflow-module.php';
}



if ( ! function_exists( 'wpshadow_render_visual_comparisons' ) ) {
	/**
	 * Render Visual Comparisons page
	 */
	function wpshadow_render_visual_comparisons() {
		echo '<div class="wrap"><h1>Visual Comparisons</h1><p class="wps-version-tag">v' . esc_html( WPSHADOW_VERSION ) . '</p><p>Visual regression testing coming soon.</p></div>';
	}
}

