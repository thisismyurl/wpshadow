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

if ( ! function_exists( 'wpshadow_render_findings' ) ) {
	/**
	 * Render Findings page (Kanban Board)
	 */
	function wpshadow_render_findings() {
		// Load the kanban board view
		if ( file_exists( WPSHADOW_PATH . 'includes/views/kanban-board.php' ) ) {
			require_once WPSHADOW_PATH . 'includes/views/kanban-board.php';
		} else {
			echo '<div class="wrap"><h1>Findings</h1><p class="wps-version-tag">v' . esc_html( WPSHADOW_VERSION ) . '</p><p>Loading findings...</p></div>';
		}
	}
}

// Legacy compatibility alias
if ( ! function_exists( 'wpshadow_render_action_items' ) ) {
	/**
	 * Legacy function name - redirects to wpshadow_render_findings()
	 *
	 * @deprecated Use wpshadow_render_findings() instead
	 */
	function wpshadow_render_action_items() {
		if ( function_exists( '_deprecated_function' ) ) {
			_deprecated_function( __FUNCTION__, '1.2601.2200', 'wpshadow_render_findings' );
		}
		wpshadow_render_findings();
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
		// Load card-based reports module if available
		$reports_module = WPSHADOW_PATH . 'includes/screens/class-reports-page-module.php';
		if ( file_exists( $reports_module ) ) {
			require_once $reports_module;
		}

		if ( function_exists( 'wpshadow_render_reports_page' ) ) {
			wpshadow_render_reports_page();
			return;
		}

		echo '<div class="wrap"><h1>Reports</h1><p class="wps-version-tag">v' . esc_html( WPSHADOW_VERSION ) . '</p><p>Site health reports and analytics.</p></div>';
	}
}

if ( ! function_exists( 'wpshadow_render_settings' ) ) {
	/**
	 * Render Settings page
	 */
	function wpshadow_render_settings() {
		// Check if a specific tab is requested (Issue #1685)
		$tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '';
		if ( 'backup' === $tab ) {
			$tab = 'vault-light';
		}

		// If vault-light tab is requested, redirect to Utilities instead
		if ( 'vault-light' === $tab ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-utilities&tab=vault-light' ) );
			exit;
		}

		// If a specific tab is requested, load and render the appropriate settings page
		if ( ! empty( $tab ) ) {
			$settings_pages = array(
				'general'       => 'WPShadow\Settings\General_Settings_Page',
				'privacy'       => 'WPShadow\Settings\Privacy_Settings_Page',
				'notifications' => 'WPShadow\Settings\Notifications_Settings_Page',
				'advanced'      => 'WPShadow\Settings\Advanced_Settings_Page',
			);

			// Check if the requested tab exists
			if ( isset( $settings_pages[ $tab ] ) ) {
				$class = $settings_pages[ $tab ];

				// Require the settings file if it exists
				$file_path = WPSHADOW_PATH . 'includes/settings/class-' . str_replace( '_', '-', strtolower( str_replace( 'WPShadow\\Settings\\', '', $class ) ) ) . '.php';
				if ( file_exists( $file_path ) ) {
					require_once $file_path;
					if ( class_exists( $class ) && method_exists( $class, 'render' ) ) {
						$class::render();
						return;
					}
				}
			}

			// Fallback for unknown tabs
			?>
			<div class="wps-page-container">
				<div class="wps-page-header">
					<h1 class="wps-page-title">
						<span class="dashicons dashicons-admin-settings"></span>
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: settings tab name */
								__( '%s Settings', 'wpshadow' ),
								ucwords( str_replace( array( '_', '-' ), ' ', $tab ) )
							)
						);
						?>
					</h1>
					<p class="wps-page-subtitle">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ); ?>">&larr; <?php esc_html_e( 'Back to Settings', 'wpshadow' ); ?></a>
					</p>
				</div>

				<div class="wps-card wps-card--warning">
					<div class="wps-card-body">
						<p><?php esc_html_e( 'This settings section is not available. Please check the URL or select a different settings tab.', 'wpshadow' ); ?></p>
					</div>
				</div>
			</div>
			<?php
			return;
		}

		// Show settings overview grid
		?>
		<div class="wps-page-container">
			<div class="wps-page-header">
				<h1 class="wps-page-title">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'WPShadow Settings', 'wpshadow' ); ?>
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
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=general' ) ); ?>" style="color: inherit; text-decoration: none;">
										<?php esc_html_e( 'General Settings', 'wpshadow' ); ?>
									</a>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Configure general plugin behavior and preferences.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=general' ) ); ?>" class="wps-btn wps-btn--secondary">
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
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-scan-settings' ) ); ?>" style="color: inherit; text-decoration: none;">
										<?php esc_html_e( 'Scan Settings', 'wpshadow' ); ?>
									</a>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Enable or disable specific diagnostic checks and treatments.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-scan-settings' ) ); ?>" class="wps-btn wps-btn--secondary">
							<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php esc_html_e( 'Configure', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

							<!-- Privacy Dashboard -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-lock wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-privacy' ) ); ?>" style="color: inherit; text-decoration: none;">
										<?php esc_html_e( 'Privacy Dashboard', 'wpshadow' ); ?>
									</a>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Manage data export, deletion, and privacy preferences.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-privacy' ) ); ?>" class="wps-btn wps-btn--secondary">
							<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php esc_html_e( 'Open', 'wpshadow' ); ?>
						</a>
					</div>
				</div>

				<!-- Privacy & Data Settings -->
				<div class="wps-card">
					<div class="wps-card-header wps-pb-3 wps-border-bottom">
						<div class="wps-flex wps-gap-3 wps-items-start">
							<span class="dashicons dashicons-admin-network wps-text-3xl wps-text-primary"></span>
							<div>
								<h3 class="wps-card-title wps-m-0">
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=privacy' ) ); ?>" style="color: inherit; text-decoration: none;">
										<?php esc_html_e( 'Privacy & Data Settings', 'wpshadow' ); ?>
									</a>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Configure data collection and anonymous reporting preferences.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=privacy' ) ); ?>" class="wps-btn wps-btn--secondary">
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
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=notifications' ) ); ?>" style="color: inherit; text-decoration: none;">
										<?php esc_html_e( 'Notifications', 'wpshadow' ); ?>
									</a>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Configure email alerts and notification preferences.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=notifications' ) ); ?>" class="wps-btn wps-btn--secondary">
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
									<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=advanced' ) ); ?>" style="color: inherit; text-decoration: none;">
										<?php esc_html_e( 'Advanced', 'wpshadow' ); ?>
									</a>
								</h3>
								<p class="wps-card-description wps-m-0">
									<?php esc_html_e( 'Advanced configuration options for power users.', 'wpshadow' ); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="wps-card-body">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-settings&tab=advanced' ) ); ?>" class="wps-btn wps-btn--secondary">
							<span class="dashicons dashicons-arrow-right-alt"></span>
							<?php esc_html_e( 'Configure', 'wpshadow' ); ?>
						</a>
					</div>
				</div>
			</div>

			<!-- Recent Activity Section -->
			<?php
			if ( function_exists( 'wpshadow_render_recent_activity' ) ) {
				wpshadow_render_recent_activity();
			}
			?>
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

// Load Utilities module (defines wpshadow_render_utilities if not already defined)
// Legacy: Also define wpshadow_render_tools for backward compatibility
if ( ! function_exists( 'wpshadow_render_utilities' ) ) {
	require_once WPSHADOW_PATH . 'includes/screens/class-utilities-page-module.php';
}

if ( ! function_exists( 'wpshadow_render_tools' ) ) {
	/**
	 * Legacy function name - redirects to wpshadow_render_utilities()
	 *
	 * @deprecated Use wpshadow_render_utilities() instead
	 */
	function wpshadow_render_tools() {
		if ( function_exists( '_deprecated_function' ) ) {
			_deprecated_function( __FUNCTION__, '1.2601.2200', 'wpshadow_render_utilities' );
		}
		wpshadow_render_utilities();
	}
}

// Load Help module (defines wpshadow_render_help if not already defined)
if ( ! function_exists( 'wpshadow_render_help' ) ) {
	require_once WPSHADOW_PATH . 'includes/screens/class-help-page-module.php';
}

// Load Reports module (defines wpshadow_render_reports if not already defined)
if ( ! function_exists( 'wpshadow_render_reports' ) ) {
	require_once WPSHADOW_PATH . 'includes/screens/class-reports-page-module.php';
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

