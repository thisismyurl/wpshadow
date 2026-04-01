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

// Load feature availability helper functions
if ( file_exists( WPSHADOW_PATH . 'includes/ui/templates/functions-feature-availability.php' ) ) {
	require_once WPSHADOW_PATH . 'includes/ui/templates/functions-feature-availability.php';
}

if ( ! function_exists( 'wpshadow_render_findings' ) ) {
	/**
	 * Render Findings page (Kanban Board)
	 *
	 * @since 0.6093.1200
	 */
	function wpshadow_render_findings() {
		// Load the kanban board view
		if ( file_exists( WPSHADOW_PATH . 'includes/views/kanban-board.php' ) ) {
			require_once WPSHADOW_PATH . 'includes/views/kanban-board.php';
		} else {
			?>
			<div class="wrap wps-page-container">
				<?php
				wpshadow_render_page_header(
					__( 'Findings', 'wpshadow' ),
					__( 'Loading your findings...', 'wpshadow' ),
					'dashicons-grid-view'
				);
				?>
			</div>
			<?php
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
			_deprecated_function( __FUNCTION__, '0.6030.2200', 'wpshadow_render_findings' );
		}
		wpshadow_render_findings();
	}
}

if ( ! function_exists( 'wpshadow_render_guardian' ) ) {
	/**
	 * Render Guardian page (Diagnostics & Treatments)
	 *
	 * @since 0.6093.1200
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
			?>
			<div class="wrap wps-page-container">
				<?php
				wpshadow_render_page_header(
					__( 'WPShadow Guardian', 'wpshadow' ),
					__( 'Diagnostics and treatments system.', 'wpshadow' ),
					'dashicons-shield-alt'
				);
				?>
			</div>
			<?php
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

		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Reports', 'wpshadow' ),
				__( 'Site health reports and analytics.', 'wpshadow' ),
				'dashicons-chart-line'
			);
			?>
			<?php
			if ( function_exists( 'wpshadow_render_page_activities' ) ) {
				wpshadow_render_page_activities( 'reports', 10 );
			}
			?>
		</div>
		<?php
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

		// If import-export tab is requested, redirect to Utilities instead
		if ( 'import-export' === $tab ) {
			wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-utilities&tab=import-export' ) );
			exit;
		}

		// If a specific tab is requested, load and render the appropriate settings page
		if ( ! empty( $tab ) ) {
			$settings_pages = array(
				'general'           => 'WPShadow\\Admin\\Pages\\General_Settings_Page',
				'privacy'           => 'WPShadow\\Admin\\Pages\\Privacy_Settings_Page',
				'privacy-dashboard' => 'WPShadow\\Admin\\Privacy_Dashboard_Page',
				'notifications'     => 'WPShadow\\Admin\\Pages\\Notifications_Settings_Page',
				'advanced'          => 'WPShadow\\Admin\\Pages\\Advanced_Settings_Page',
			);

			// Check if the requested tab exists
			if ( isset( $settings_pages[ $tab ] ) ) {
				$class = $settings_pages[ $tab ];

				// Require the settings file if it exists
				if ( 'WPShadow\\Admin\\Privacy_Dashboard_Page' === $class ) {
					$file_path = WPSHADOW_PATH . 'includes/admin/class-privacy-dashboard-page.php';
				} else {
					$file_path = WPSHADOW_PATH . 'includes/admin/pages/class-' . str_replace( '_', '-', strtolower( str_replace( 'WPShadow\\Admin\\Pages\\', '', $class ) ) ) . '.php';
				}
				if ( file_exists( $file_path ) ) {
					require_once $file_path;
							if ( class_exists( $class ) ) {
								if ( method_exists( $class, 'render' ) ) {
									$class::render();
									return;
								}
								if ( method_exists( $class, 'render_page' ) ) {
									$class::render_page();
									return;
								}
						return;
					}
				}
			}

			// Fallback for unknown tabs
			?>
			<div class="wrap wps-page-container">
				<?php
				wpshadow_render_page_header(
					sprintf(
						/* translators: %s: settings tab name */
						__( '%s Settings', 'wpshadow' ),
						ucwords( str_replace( array( '_', '-' ), ' ', $tab ) )
					),
					'<a href="' . esc_url( admin_url( 'admin.php?page=wpshadow-settings' ) ) . '">&larr; ' . esc_html__( 'Back to Settings', 'wpshadow' ) . '</a>',
					'dashicons-admin-settings'
				);
				?>

				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--warning',
						'body'       => '<p>' . esc_html__( 'This settings section is not available. Please check the URL or select a different settings tab.', 'wpshadow' ) . '</p>',
					)
				);
				?>

				<?php
				if ( function_exists( 'wpshadow_render_page_activities' ) ) {
					wpshadow_render_page_activities( 'settings', 10 );
				}
				?>
			</div>
			<?php
			return;
		}

		// Show settings overview grid
		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'WPShadow Settings', 'wpshadow' ),
				__( 'Configure WPShadow plugin settings and preferences.', 'wpshadow' ),
				'dashicons-admin-settings'
			);
			?>

			<!-- Settings Grid -->
			<?php
			$settings_cards = array(
				array(
					'title'        => __( 'General Settings', 'wpshadow' ),
					'description'  => __( 'Configure general plugin behavior and preferences.', 'wpshadow' ),
					'url'          => admin_url( 'admin.php?page=wpshadow-settings&tab=general' ),
					'icon'         => 'dashicons-admin-generic',
					'action_label' => __( 'Configure', 'wpshadow' ),
				),

				array(
					'title'        => __( 'Privacy Dashboard', 'wpshadow' ),
					'description'  => __( 'Manage data export, deletion, and privacy preferences.', 'wpshadow' ),
					'url'          => admin_url( 'admin.php?page=wpshadow-settings&tab=privacy-dashboard' ),
					'icon'         => 'dashicons-lock',
					'action_label' => __( 'Open', 'wpshadow' ),
				),

				array(
					'title'        => __( 'Notifications', 'wpshadow' ),
					'description'  => __( 'Configure email alerts and notification preferences.', 'wpshadow' ),
					'url'          => admin_url( 'admin.php?page=wpshadow-settings&tab=notifications' ),
					'icon'         => 'dashicons-email',
					'action_label' => __( 'Configure', 'wpshadow' ),
				),
				array(
					'title'        => __( 'Advanced', 'wpshadow' ),
					'description'  => __( 'Advanced configuration options for power users.', 'wpshadow' ),
					'url'          => admin_url( 'admin.php?page=wpshadow-settings&tab=advanced' ),
					'icon'         => 'dashicons-admin-tools',
					'action_label' => __( 'Configure', 'wpshadow' ),
				),
			);
			?>
			<div class="wps-grid wps-grid-auto-320">
				<?php foreach ( $settings_cards as $card ) : ?>
					<?php
					wpshadow_render_card(
						array(
							'title'       => $card['title'],
							'title_url'   => $card['url'],
							'description' => $card['description'],
							'icon'        => $card['icon'],
							'actions'     => array(
								array(
									'label' => $card['action_label'],
									'url'   => $card['url'],
									'class' => 'wps-btn wps-btn--secondary',
									'icon'  => 'dashicons-arrow-right-alt',
								),
							),
						)
					);
					?>
				<?php endforeach; ?>
			</div>

			<!-- Recent Activity Section -->
			<?php
			if ( function_exists( 'wpshadow_render_page_activities' ) ) {
				wpshadow_render_page_activities( 'settings', 10 );
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

		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Scan Settings', 'wpshadow' ),
				__( 'Loading scan settings...', 'wpshadow' ),
				'dashicons-search'
			);
			?>
		</div>
		<?php
	}
}

// Load Utilities module (defines wpshadow_render_utilities if not already defined)
// Legacy: Also define wpshadow_render_tools for backward compatibility
if ( ! function_exists( 'wpshadow_render_utilities' ) ) {
	require_once WPSHADOW_PATH . 'includes/admin/pages/class-utilities-page-module.php';
}

if ( ! function_exists( 'wpshadow_render_tools' ) ) {
	/**
	 * Legacy function name - redirects to wpshadow_render_utilities()
	 *
	 * @deprecated Use wpshadow_render_utilities() instead
	 */
	function wpshadow_render_tools() {
		if ( function_exists( '_deprecated_function' ) ) {
			_deprecated_function( __FUNCTION__, '0.6030.2200', 'wpshadow_render_utilities' );
		}
		wpshadow_render_utilities();
	}
}

// Load Help module (defines wpshadow_render_help if not already defined)
if ( ! function_exists( 'wpshadow_render_help' ) ) {
	require_once WPSHADOW_PATH . 'includes/admin/pages/class-help-page-module.php';
}

// Load Reports module (defines wpshadow_render_reports if not already defined)
if ( ! function_exists( 'wpshadow_render_reports' ) ) {
	require_once WPSHADOW_PATH . 'includes/admin/pages/class-reports-page-module.php';
}

// Load Workflows module (defines wpshadow_render_workflow_builder if not already defined)
if ( ! function_exists( 'wpshadow_render_workflow_builder' ) ) {
	require_once WPSHADOW_PATH . 'includes/systems/workflow/workflow-module.php';
}



if ( ! function_exists( 'wpshadow_render_visual_comparisons' ) ) {
	/**
	 * Render Visual Comparisons page
	 */
	function wpshadow_render_visual_comparisons() {
		?>
		<div class="wrap wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Visual Comparisons', 'wpshadow' ),
				__( 'Visual regression testing coming soon.', 'wpshadow' ),
				'dashicons-images-alt2'
			);
			?>
		</div>
		<?php
	}
}

