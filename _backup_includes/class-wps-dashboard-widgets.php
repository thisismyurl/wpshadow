<?php
/**
 * Dashboard widget system for tab-based interface.
 *
 * @package WPShadow
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Widgets Manager
 * Mimics WordPress Core dashboard functionality.
 */
class WPSHADOW_Dashboard_Widgets {
	/**
	 * Shared dashboard shell for core, hub, and spoke views.
	 *
	 * @param string $title            Page heading.
	 * @param array  $col1_callbacks   Callables to render in column 1.
	 * @param array  $col2_callbacks   Callables to render in column 2.
	 * @return void
	 */
	private static function render_dashboard_shell( string $title, array $col1_callbacks, array $col2_callbacks ): void {
		?>
		<div class="wrap wps-dashboard">
			<h1><?php echo esc_html( $title ); ?></h1>

			<style>
				.wps-dashboard { margin-top: 20px; }
				.wps-dashboard-widgets-wrap { max-width: 1800px; }
				.wps-dashboard-col-container { display: flex; flex-wrap: wrap; gap: 20px; }
				.wps-dashboard-col { flex: 1; min-width: 400px; }
			</style>

			<div class="wps-dashboard-widgets-wrap">
				<div class="wps-dashboard-col-container">
					<div id="wps-dashboard-col-1" class="wps-dashboard-col">
						<?php
						foreach ( $col1_callbacks as $callback ) {
							if ( is_callable( $callback ) ) {
								call_user_func( $callback ); }
						}
						?>
					</div>

					<div id="wps-dashboard-col-2" class="wps-dashboard-col">
						<?php
						foreach ( $col2_callbacks as $callback ) {
							if ( is_callable( $callback ) ) {
								call_user_func( $callback ); }
						}
						?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Core-level dashboard.
	 *
	 * @return void
	 */
	public static function render_core_dashboard(): void {
		self::render_dashboard_shell(
			esc_html__( 'Support Dashboard', 'wpshadow' ),
			array(
				array( __CLASS__, 'widget_suite_overview' ),
				array( __CLASS__, 'widget_active_hubs' ),
				array( __CLASS__, 'widget_performance_monitor' ),
				array( __CLASS__, 'widget_performance_history' ),
				array( __CLASS__, 'widget_performance_alerts' ),
				array( __CLASS__, 'widget_weekly_performance' ),
				array( __CLASS__, 'widget_database_stats_boxed' ),
			),
			array(
				array( __CLASS__, 'widget_dark_mode' ),
				array( __CLASS__, 'widget_tips_coach' ),
				array( __CLASS__, 'widget_quick_actions' ),
				array( __CLASS__, 'widget_performance_history_boxed' ),
			)
		);
	}

	/**
	 * Render Hub-level dashboard.
	 * Shows the same core dashboard content.
	 *
	 * @param string $hub_id Hub identifier.
	 * @return void
	 */
	public static function render_hub_dashboard( string $hub_id ): void {
		// Hub level displays core dashboard content.
		self::render_core_dashboard();
	}

	/**
	 * Render Spoke-level dashboard.
	 * Shows the same core dashboard content.
	 *
	 * @param string $hub_id Hub identifier.
	 * @param string $spoke_id Spoke identifier.
	 * @return void
	 */
	public static function render_spoke_dashboard( string $hub_id, string $spoke_id ): void {
		// Spoke level displays core dashboard content.
		self::render_core_dashboard();
	}

	/* ====== CORE WIDGETS ====== */

	public static function render_metabox_health(): void {
		self::widget_health();
	}

	public static function render_metabox_activity(): void {
		self::widget_activity();
	}

	public static function render_metabox_scheduled_tasks(): void {
		self::widget_scheduled_tasks();
	}

	public static function render_metabox_quick_actions(): void {
		self::widget_quick_actions();
	}

	public static function render_metabox_vault_status(): void {
		self::widget_vault_status();
	}

	public static function render_metabox_system_health(): void {
		self::widget_system_health();
	}

	public static function render_metabox_media_overview(): void {
		self::widget_media_overview();
	}

	public static function render_metabox_vault_overview(): void {
		self::widget_vault_overview();
	}

	public static function render_metabox_media_activity(): void {
		self::widget_activity( 'media' );
	}

	public static function render_metabox_vault_activity(): void {
		self::widget_activity( 'vault' );
	}

	public static function render_metabox_vault_health(): void {
		self::widget_hub_health( 'vault' );
	}

	public static function render_metabox_events_and_news(): void {
		self::widget_events_and_news();
	}

	public static function render_metabox_modules(): void {
		// TEMPORARILY DISABLED: Just returns early
		self::widget_modules();
	}

	public static function render_metabox_environment_status(): void {
		self::widget_environment_status();
	}

	public static function render_metabox_favicon_checker(): void {
		self::widget_favicon_checker();
	}

	public static function render_metabox_database_stats(): void {
		self::widget_database_stats();
	}

	public static function render_metabox_performance_history(): void {
		self::widget_performance_history();
	}

	private static function widget_health(): void {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Site_Health' ) ) {
			echo '<div class="wps-widget-content"><p><em>' . esc_html__( 'Health info isn\'t available right now.', 'wpshadow' ) . '</em></p></div>';
			return;
		}

		try {
			// Get the current module context (if viewing a module dashboard).
			$context     = WPSHADOW_Tab_Navigation::get_current_context();
			$module      = ! empty( $context['hub'] ) ? $context['hub'] : null;
			$module_name = '';

			// If we're on a module dashboard, get the module name.
			if ( ! empty( $module ) ) {
				$catalog     = \WPShadow\WPSHADOW_Module_Registry::get_catalog_with_status();
				$module_slug = str_contains( $module, '-wpshadow' ) ? $module : $module . '-wpshadow';
				if ( isset( $catalog[ $module_slug ] ) ) {
					$module_name = $catalog[ $module_slug ]['name'] ?? ucfirst( $module );
				}
			}

			// Get health results filtered by module (null means all checks).
			$health_data = \WPShadow\WPSHADOW_Site_Health::get_health_check_results( $module );

			// Render the health widget with module context.
			self::render_health_widget( $health_data, $module_name );
		} catch ( \Exception $e ) {
			echo '<div class="wps-widget-content"><p style="color: #d63638;"><strong>' . esc_html__( 'Looks like something didn\'t load:', 'wpshadow' ) . '</strong> ' . esc_html( $e->getMessage() ) . '</p></div>';

		}
	}

	private static function widget_activity( ?string $module_filter = null ): void {
		// Use WPSHADOW_Activity_Logger if available.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
			$events = \WPShadow\WPSHADOW_Activity_Logger::get_events( 100 );

			// Filter events by module if specified.
			if ( ! empty( $module_filter ) ) {
				$events = array_filter(
					$events,
					function ( $event ) use ( $module_filter ) {
						$source = $event['module_source'] ?? ( $event['metadata']['module'] ?? null );
						return $source === $module_filter;
					}
				);
				// Limit to 5 after filtering.
				$events = array_slice( $events, 0, 5 );
			} else {
				// If no filter, just get the first 5.
				$events = array_slice( $events, 0, 5 );
			}

			if ( empty( $events ) ) {
				// No events to display - return early to hide widget completely.
				return;
			}

			?>
			<div class="wps-widget-content">
				<div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #eee;">
					<form method="get" style="display: inline;">
						<label for="wpshadow_activity_type_filter" style="display: inline; margin-right: 8px;">
						<?php esc_html_e( 'Filter:', 'wpshadow' ); ?>
						</label>
						<select id="wpshadow_activity_type_filter" name="activity_type" style="padding: 4px 8px; font-size: 12px;">
							<option value="">- <?php esc_html_e( 'All Activity', 'wpshadow' ); ?> -</option>
							<option value="module_activated">📌 <?php esc_html_e( 'Module Activated', 'wpshadow' ); ?></option>
							<option value="module_deactivated">📴 <?php esc_html_e( 'Module Deactivated', 'wpshadow' ); ?></option>
							<option value="settings_changed">⚙️ <?php esc_html_e( 'Settings Changed', 'wpshadow' ); ?></option>
							<option value="error_logged">⚠️ <?php esc_html_e( 'Error', 'wpshadow' ); ?></option>
						</select>
						<noscript><input type="submit" value="<?php esc_attr_e( 'Filter', 'wpshadow' ); ?>" /></noscript>
					</form>
				</div>
				<ul style="list-style: none; padding: 0; margin: 0;">
				<?php foreach ( $events as $event ) : ?>
						<?php
						$description = esc_html( $event['description'] );
						$timestamp   = human_time_diff( $event['timestamp'] ) . ' ' . __( 'ago', 'wpshadow' );
						$user        = get_userdata( $event['user_id'] );
						$username    = $user ? $user->display_name : __( 'System', 'wpshadow' );
						?>
						<li style="padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
							<strong><?php echo esc_html( $description ); ?></strong>
							<br />
							<small style="color: #666;"><?php echo esc_html( $timestamp ); ?> • <?php echo esc_html( $username ); ?></small>
						</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php
		} else {
			?>
			<div class="wps-widget-content">
				<p><em><?php esc_html_e( 'Activity log integration coming soon.', 'wpshadow' ); ?></em></p>
			</div>
			<?php
		}
	}

	private static function widget_scheduled_tasks(): void {
		// Get all scheduled cron events
		$cron_array = _get_cron_array();
		$wpshadow_tasks = array();

		if ( $cron_array ) {
			foreach ( $cron_array as $timestamp => $cron_jobs ) {
				foreach ( $cron_jobs as $hook => $jobs ) {
					// Only show wpshadow-related tasks
					if ( strpos( $hook, 'wpshadow_' ) === 0 ) {
						foreach ( $jobs as $key => $job ) {
							$wpshadow_tasks[] = array(
								'hook' => $hook,
								'timestamp' => $timestamp,
								'schedule' => isset( $job['schedule'] ) ? $job['schedule'] : 'once',
								'args' => isset( $job['args'] ) ? $job['args'] : array(),
								'key' => $key,
							);
						}
					}
				}
			}
		}

		// Check for paused tasks
		$paused_tasks = get_option( 'wpshadow_paused_tasks', array() );

		// Get available schedules for display names
		$schedules = wp_get_schedules();
		?>
		<div class="wps-widget-content">
			<?php if ( empty( $wpshadow_tasks ) && empty( $paused_tasks ) ) : ?>
				<p><em><?php esc_html_e( 'No scheduled tasks found.', 'wpshadow' ); ?></em></p>
			<?php else : ?>
				<table class="widefat wps-scheduled-tasks-table" style="margin-top: 0;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Task', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Next Run', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Frequency', 'wpshadow' ); ?></th>
							<th><?php esc_html_e( 'Actions', 'wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $wpshadow_tasks as $task ) : ?>
							<?php
							$task_name = str_replace( array( 'wpshadow_', '_' ), array( '', ' ' ), $task['hook'] );
							$task_name = ucwords( $task_name );
							$next_run = $task['timestamp'];
							$time_until = human_time_diff( time(), $next_run );
							$is_past = $next_run < time();
							$schedule_label = isset( $schedules[ $task['schedule'] ] ) ? $schedules[ $task['schedule'] ]['display'] : ucfirst( $task['schedule'] );
							?>
							<tr>
								<td>
									<strong><?php echo esc_html( $task_name ); ?></strong>
									<br><code style="font-size: 11px; color: #666;"><?php echo esc_html( $task['hook'] ); ?></code>
								</td>
								<td>
									<?php if ( $is_past ) : ?>
										<span style="color: #d63638;">
											<?php echo esc_html( sprintf( __( '%s ago (missed)', 'wpshadow' ), $time_until ) ); ?>
										</span>
									<?php else : ?>
										<?php echo esc_html( sprintf( __( 'In %s', 'wpshadow' ), $time_until ) ); ?>
									<?php endif; ?>
									<br><small style="color: #666;"><?php echo esc_html( wp_date( 'Y-m-d H:i:s', $next_run ) ); ?></small>
								</td>
								<td><?php echo esc_html( $schedule_label ); ?></td>
								<td>
									<button type="button" class="button button-small wps-pause-task" data-hook="<?php echo esc_attr( $task['hook'] ); ?>" data-timestamp="<?php echo esc_attr( $task['timestamp'] ); ?>" data-key="<?php echo esc_attr( md5( serialize( $task['args'] ) ) ); ?>">
										<?php esc_html_e( 'Pause', 'wpshadow' ); ?>
									</button>
									<button type="button" class="button button-small button-link-delete wps-remove-task" data-hook="<?php echo esc_attr( $task['hook'] ); ?>" data-timestamp="<?php echo esc_attr( $task['timestamp'] ); ?>" data-key="<?php echo esc_attr( md5( serialize( $task['args'] ) ) ); ?>">
										<?php esc_html_e( 'Remove', 'wpshadow' ); ?>
									</button>
								</td>
							</tr>
						<?php endforeach; ?>

						<?php if ( ! empty( $paused_tasks ) ) : ?>
							<?php foreach ( $paused_tasks as $paused_hook => $paused_data ) : ?>
								<?php
								$task_name = str_replace( array( 'wpshadow_', '_' ), array( '', ' ' ), $paused_hook );
								$task_name = ucwords( $task_name );
								$schedule_label = isset( $schedules[ $paused_data['schedule'] ] ) ? $schedules[ $paused_data['schedule'] ]['display'] : ucfirst( $paused_data['schedule'] );
								?>
								<tr style="background-color: #f0f0f1;">
									<td>
										<strong><?php echo esc_html( $task_name ); ?></strong>
										<br><code style="font-size: 11px; color: #666;"><?php echo esc_html( $paused_hook ); ?></code>
										<br><span class="dashicons dashicons-controls-pause" style="color: #d63638; font-size: 14px;"></span> <em style="color: #d63638;"><?php esc_html_e( 'Paused', 'wpshadow' ); ?></em>
									</td>
									<td colspan="2">
										<?php
										/* translators: %s: formatted date and time */
										echo esc_html( sprintf( __( 'Paused on %s', 'wpshadow' ), wp_date( 'Y-m-d H:i:s', $paused_data['paused_at'] ) ) );
										?>
									</td>
									<td>
										<button type="button" class="button button-small wps-resume-task" data-hook="<?php echo esc_attr( $paused_hook ); ?>">
											<?php esc_html_e( 'Resume', 'wpshadow' ); ?>
										</button>
										<button type="button" class="button button-small button-link-delete wps-remove-paused-task" data-hook="<?php echo esc_attr( $paused_hook ); ?>">
											<?php esc_html_e( 'Delete', 'wpshadow' ); ?>
										</button>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>

				<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
					<p style="margin: 0 0 10px 0; color: #666;">
						<strong><?php esc_html_e( 'Cron Method:', 'wpshadow' ); ?></strong>
						<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
							<span style="color: #d63638;"><?php esc_html_e( 'System Cron (WP-Cron disabled)', 'wpshadow' ); ?></span>
						<?php else : ?>
							<span style="color: #00a32a;"><?php esc_html_e( 'WP-Cron (WordPress internal)', 'wpshadow' ); ?></span>
						<?php endif; ?>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-features&feature=cron-test' ) ); ?>" style="margin-left: 10px;">
							<?php esc_html_e( 'Test Cron →', 'wpshadow' ); ?>
						</a>
					</p>
				</div>
			<?php endif; ?>
		</div>

		<style>
			.wps-scheduled-tasks-table { border-collapse: collapse; }
			.wps-scheduled-tasks-table th { font-weight: 600; background: #f9f9f9; padding: 8px 10px; }
			.wps-scheduled-tasks-table td { padding: 10px; border-bottom: 1px solid #e5e5e5; vertical-align: middle; }
			.wps-scheduled-tasks-table tr:last-child td { border-bottom: none; }
			.wps-scheduled-tasks-table .button-small { padding: 2px 8px; font-size: 12px; height: auto; line-height: 1.5; }
		</style>
		<?php
	}

	private static function widget_modules(): void {
		// TEMPORARILY DISABLED: Modules system is temporarily disabled
		return;
	}

	private static function widget_quick_actions(): void {
		// TEMPORARILY DISABLED: Module catalog check
		// $catalog        = \WPShadow\WPSHADOW_Module_Registry::get_catalog_with_status();
		// $inactive_count = count( array_filter( $catalog, fn( $m ) => empty( $m['status']['active'] ) && ! empty( $m['status']['installed'] ) ) );
		$inactive_count = 0; // Modules disabled temporarily
		$vault_path     = wp_upload_dir()['basedir'] . '/vault';
		$vault_exists   = is_dir( $vault_path );
		$vault_writable = $vault_exists && wp_is_writable( $vault_path );
		$encryption_key = wpshadow_get_vault_key();
		$health_url     = admin_url( 'site-health.php?tab=debug' );
		?>
		<div class="wps-widget-content wps-quick-actions">
			<div class="wps-actions-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
				<!-- Site Health Action -->
				<a href="<?php echo esc_url( $health_url ); ?>" class="button" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
					<span class="dashicons dashicons-heart" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Site Health', 'wpshadow' ); ?>
				</a>

				<?php if ( $inactive_count > 0 ) : ?>
					<!-- Activate Modules Action -->
					<a href="<?php echo esc_url( admin_url( 'plugins.php?s=wpshadow' ) ); ?>" class="button" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
						<span class="dashicons dashicons-update" style="margin-right: 5px;"></span>
						<?php echo esc_html( sprintf( __( 'Activate %d Module(s)', 'wpshadow' ), $inactive_count ) ); ?>
					</a>
				<?php endif; ?>

				<?php if ( empty( $encryption_key ) ) : ?>
					<!-- Setup Encryption Action -->
					<a href="<?php echo esc_url( WPSHADOW_Tab_Navigation::build_tab_url( 'dashboard_settings' ) . '&section=encryption' ); ?>" class="button button-secondary" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
						<span class="dashicons dashicons-lock" style="margin-right: 5px; color: #d63638;"></span>
						<?php esc_html_e( 'Setup Encryption', 'wpshadow' ); ?>
					</a>
				<?php endif; ?>

				<!-- Get Help Action -->
				<a href="https://wpshadow.com/?source=plugin-wpshadow" target="_blank" rel="noopener noreferrer" class="button" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
					<span class="dashicons dashicons-editor-help" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Get Help', 'wpshadow' ); ?>
				</a>
			</div>
			
			<!-- Feature Quick Links -->
			<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
				<h4 style="margin: 0 0 10px 0; font-size: 13px; color: #666;">
					<?php esc_html_e( 'Feature Quick Links:', 'wpshadow' ); ?>
				</h4>
				<div style="display: flex; flex-direction: column; gap: 5px; max-height: 200px; overflow-y: auto;">
					<?php
					$features = WPSHADOW_Feature_Registry::get_all_features();
					$feature_links = array();
					
					foreach ( $features as $feature ) {
						if ( ! method_exists( $feature, 'has_details_page' ) || ! $feature->has_details_page() ) {
							continue;
						}
						
						$feature_links[] = array(
							'id' => $feature->get_id(),
							'name' => $feature->get_name(),
							'icon' => $feature->get_icon(),
							'url' => \WPShadow\CoreSupport\WPSHADOW_Feature_Details_Page::get_feature_url( $feature->get_id() ),
						);
					}
					
					// Sort by name
					usort( $feature_links, function( $a, $b ) {
						return strcmp( $a['name'], $b['name'] );
					});
					
					if ( ! empty( $feature_links ) ) {
						foreach ( $feature_links as $link ) {
							?>
							<a href="<?php echo esc_url( $link['url'] ); ?>" 
							   style="display: flex; align-items: center; gap: 5px; padding: 5px; text-decoration: none; color: #2271b1; font-size: 12px; border-radius: 3px;"
							   onmouseover="this.style.backgroundColor='#f0f0f1'"
							   onmouseout="this.style.backgroundColor='transparent'">
								<span class="dashicons <?php echo esc_attr( $link['icon'] ); ?>" style="font-size: 16px; width: 16px; height: 16px;"></span>
								<span><?php echo esc_html( $link['name'] ); ?></span>
							</a>
							<?php
						}
					} else {
						?>
						<p style="margin: 0; font-size: 12px; color: #666; font-style: italic;">
							<?php esc_html_e( 'No feature details pages available.', 'wpshadow' ); ?>
						</p>
						<?php
					}
					?>
				</div>
			</div>
			
			<!-- Configure Dashboard Text Link -->
			<div style="margin-top: 15px; text-align: center;">
				<a href="<?php echo esc_url( WPSHADOW_Tab_Navigation::build_tab_url( 'dashboard_settings' ) ); ?>" style="color: #2271b1; text-decoration: none; font-size: 13px;">
					<span class="dashicons dashicons-admin-generic" style="font-size: 14px; vertical-align: middle;"></span>
					<?php esc_html_e( 'Dashboard Settings', 'wpshadow' ); ?>
				</a>
			</div>
			
			<div id="wps-action-feedback" style="margin-top: 10px; display: none;"></div>
		</div>
		<?php
	}

	/**
	 * Dark Mode Widget - Control appearance of WPShadow admin pages.
	 *
	 * @return void
	 */
	private static function widget_dark_mode(): void {
		$dark_mode_feature = \WPShadow\CoreSupport\WPSHADOW_Feature_Registry::get_feature_object( 'dark-mode' );

		if ( ! $dark_mode_feature ) {
			return;
		}

		$feature_state = \WPShadow\CoreSupport\WPSHADOW_Feature_Registry::get_feature( 'dark-mode' );
		if ( empty( $feature_state['enabled'] ) ) {
			return;
		}

		if ( method_exists( $dark_mode_feature, 'render_dashboard_widget' ) ) {
			$dark_mode_feature->render_dashboard_widget();
		}
	}

	/**
	 * Tips Coach Widget - Contextual next-best-action cards.
	 *
	 * @return void
	 */
	private static function widget_tips_coach(): void {
		if ( ! class_exists( '\\WPShadow\\Features\\WPSHADOW_Feature_Tips_Coach' ) ) {
			return;
		}

		// Render the Tips Coach widget
		?>
		<div class="postbox">
			<div class="postbox-header">
				<h2 class="hndle"><?php esc_html_e( 'Tips Coach', 'wpshadow' ); ?></h2>
			</div>
			<div class="inside">
				<?php \WPShadow\Features\WPSHADOW_Feature_Tips_Coach::render_widget(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Widget: Weekly Performance Report
	 *
	 * @return void
	 */
	private static function widget_weekly_performance(): void {
		if ( ! class_exists( '\\WPShadow\\Features\\WPSHADOW_Feature_Weekly_Performance_Report' ) ) {
			return;
		}

		$metrics = \WPShadow\Features\WPSHADOW_Feature_Weekly_Performance_Report::get_current_week_metrics();

		$uptime_percentage = 0;
		if ( $metrics['uptime_checks'] > 0 ) {
			$uptime_percentage = ( $metrics['uptime_success'] / $metrics['uptime_checks'] ) * 100;
		}

		$time_saved_hours = round( $metrics['time_saved_seconds'] / 3600, 2 );
		$data_saved_mb    = round( $metrics['data_saved_mb'], 2 );

		?>
		<div class="postbox">
			<div class="postbox-header">
				<h2 class="hndle"><?php esc_html_e( '📊 This Week\'s Performance', 'wpshadow' ); ?></h2>
			</div>
			<div class="inside">
				<div class="wps-widget-content">
					<style>
						.wps-perf-metrics {
							display: grid;
							grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
							gap: 15px;
							margin-bottom: 15px;
						}
						.wps-perf-metric {
							text-align: center;
							padding: 15px;
							background: #f8f9fa;
							border-radius: 6px;
							border: 1px solid #e9ecef;
						}
						.wps-perf-metric .value {
							font-size: 28px;
							font-weight: 700;
							color: #667eea;
							display: block;
							margin: 5px 0;
						}
						.wps-perf-metric .label {
							font-size: 12px;
							color: #6c757d;
							text-transform: uppercase;
							letter-spacing: 0.5px;
						}
						.wps-perf-highlight {
							padding: 12px;
							background: #e8f4f8;
							border-left: 4px solid #0073aa;
							margin-bottom: 10px;
							border-radius: 3px;
							font-size: 13px;
						}
						.wps-perf-highlight strong {
							color: #0073aa;
						}
					</style>
					
					<div class="wps-perf-metrics">
						<div class="wps-perf-metric">
							<span class="value"><?php echo esc_html( $time_saved_hours ); ?></span>
							<span class="label"><?php esc_html_e( 'Hours Saved', 'wpshadow' ); ?></span>
						</div>
						<div class="wps-perf-metric">
							<span class="value"><?php echo esc_html( $data_saved_mb ); ?></span>
							<span class="label"><?php esc_html_e( 'MB Saved', 'wpshadow' ); ?></span>
						</div>
						<div class="wps-perf-metric">
							<span class="value"><?php echo esc_html( $metrics['issues_fixed'] ); ?></span>
							<span class="label"><?php esc_html_e( 'Issues Fixed', 'wpshadow' ); ?></span>
						</div>
						<div class="wps-perf-metric">
							<span class="value"><?php echo esc_html( number_format( $uptime_percentage, 1 ) ); ?>%</span>
							<span class="label"><?php esc_html_e( 'Uptime', 'wpshadow' ); ?></span>
						</div>
					</div>

					<?php if ( $time_saved_hours > 0 ) : ?>
					<div class="wps-perf-highlight">
						<?php
						// translators: %s: hours saved this week.
						echo wp_kses_post( sprintf( __( '<strong>You saved %s hours this week!</strong> That\'s time you can spend on what matters most.', 'wpshadow' ), $time_saved_hours ) );
						?>
					</div>
					<?php endif; ?>

					<?php if ( $data_saved_mb > 0 ) : ?>
					<div class="wps-perf-highlight">
						<?php
						// translators: %s: MB of data saved this week.
						echo wp_kses_post( sprintf( __( '<strong>You saved %s MB of data this week!</strong>', 'wpshadow' ), $data_saved_mb ) );
						?>
					</div>
					<?php endif; ?>

					<?php if ( $metrics['cpu_cycles_saved'] > 0 ) : ?>
					<div class="wps-perf-highlight">
						<?php
						// translators: %s: CPU cycles saved this week.
						echo wp_kses_post( sprintf( __( '<strong>You saved %s CPU cycles this week!</strong>', 'wpshadow' ), number_format( $metrics['cpu_cycles_saved'] ) ) );
						?>
					</div>
					<?php endif; ?>

					<p style="text-align: center; margin-top: 15px;">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-performance-reports' ) ); ?>" class="button button-primary">
							<?php esc_html_e( 'View Full Report', 'wpshadow' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Performance Monitor widget for real-time performance metrics.
	 *
	 * @return void
	 */
	private static function widget_performance_monitor(): void {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Performance_Monitor' ) ) {
			return;
		}

		$metrics         = \WPShadow\WPSHADOW_Performance_Monitor::get_current_metrics();
		$score_data      = \WPShadow\WPSHADOW_Performance_Monitor::calculate_performance_score();
		$recommendations = \WPShadow\WPSHADOW_Performance_Monitor::get_recommendations();

		?>
		<div class="postbox">
			<div class="postbox-header">
				<h2 class="hndle"><?php esc_html_e( '⚡ Performance Overview', 'wpshadow' ); ?></h2>
			</div>
			<div class="inside">
				<div class="wps-widget-content">
					<style>
						.wps-performance-score {
							text-align: center;
							margin-bottom: 20px;
							padding: 20px;
							background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
							border-radius: 8px;
							color: #fff;
						}
						.wps-performance-score .score {
							font-size: 48px;
							font-weight: 700;
							display: block;
							margin: 10px 0;
						}
						.wps-performance-score .grade {
							font-size: 24px;
							opacity: 0.9;
						}
						.wps-current-metrics {
							display: grid;
							grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
							gap: 12px;
							margin-bottom: 15px;
						}
						.wps-metric-item {
							padding: 12px;
							background: #f8f9fa;
							border-radius: 4px;
							border: 1px solid #e9ecef;
						}
						.wps-metric-item .metric-label {
							font-size: 11px;
							color: #6c757d;
							text-transform: uppercase;
							letter-spacing: 0.5px;
							display: block;
							margin-bottom: 5px;
						}
						.wps-metric-item .metric-value {
							font-size: 18px;
							font-weight: 600;
							color: #333;
							display: block;
						}
						.wps-recommendations {
							margin-top: 15px;
						}
						.wps-recommendation {
							padding: 10px 12px;
							margin-bottom: 8px;
							border-radius: 4px;
							font-size: 13px;
						}
						.wps-recommendation.warning {
							background: #fff3cd;
							border-left: 4px solid #ffc107;
							color: #856404;
						}
						.wps-recommendation.critical {
							background: #f8d7da;
							border-left: 4px solid #dc3545;
							color: #721c24;
						}
						.wps-recommendation.info {
							background: #d1ecf1;
							border-left: 4px solid #17a2b8;
							color: #0c5460;
						}
						.wps-recommendation strong {
							display: block;
							margin-bottom: 3px;
						}
					</style>

					<!-- Performance Score -->
					<div class="wps-performance-score" style="background: <?php echo esc_attr( $score_data['color'] ); ?>;">
						<div class="score-label" style="font-size: 14px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9;">
							<?php esc_html_e( 'Performance Score', 'wpshadow' ); ?>
						</div>
						<span class="score"><?php echo esc_html( $score_data['score'] ); ?></span>
						<span class="grade"><?php echo esc_html( $score_data['grade'] ); ?></span>
					</div>

					<!-- Current Metrics -->
					<div class="wps-current-metrics">
						<div class="wps-metric-item">
							<span class="metric-label"><?php esc_html_e( 'Queries', 'wpshadow' ); ?></span>
							<span class="metric-value"><?php echo esc_html( $metrics['query_count'] ?? 0 ); ?></span>
						</div>
						<div class="wps-metric-item">
							<span class="metric-label"><?php esc_html_e( 'Load Time', 'wpshadow' ); ?></span>
							<span class="metric-value"><?php echo esc_html( number_format( (float) ( $metrics['load_time'] ?? 0 ), 3 ) ); ?>s</span>
						</div>
						<div class="wps-metric-item">
							<span class="metric-label"><?php esc_html_e( 'Memory', 'wpshadow' ); ?></span>
							<span class="metric-value"><?php echo esc_html( $metrics['memory_mb'] ?? 0 ); ?> MB</span>
						</div>
						<div class="wps-metric-item">
							<span class="metric-label"><?php esc_html_e( 'Database', 'wpshadow' ); ?></span>
							<span class="metric-value"><?php echo esc_html( $metrics['db_size'] ?? 0 ); ?> MB</span>
						</div>
					</div>

					<!-- Recommendations -->
					<?php if ( ! empty( $recommendations ) ) : ?>
					<div class="wps-recommendations">
						<h4 style="margin: 0 0 10px 0; font-size: 13px; font-weight: 600; color: #333;">
							<?php esc_html_e( '💡 Optimization Recommendations', 'wpshadow' ); ?>
						</h4>
						<?php foreach ( array_slice( $recommendations, 0, 3 ) as $rec ) : ?>
							<div class="wps-recommendation <?php echo esc_attr( $rec['type'] ); ?>">
								<strong><?php echo esc_html( $rec['title'] ); ?></strong>
								<span><?php echo esc_html( $rec['description'] ); ?></span>
							</div>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>

					<!-- Quick Actions -->
					<p style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=features' ) ); ?>" class="button button-primary">
							<?php esc_html_e( 'Performance Features', 'wpshadow' ); ?>
						</a>
					</p>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Performance Alerts Widget
	 * Displays recent performance alerts and warnings.
	 *
	 * @return void
	 */
	private static function widget_performance_alerts(): void {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Performance_Monitor' ) ) {
			return;
		}

		// Get recent alerts.
		$alerts = get_transient( 'wpshadow_performance_alerts' );
		if ( empty( $alerts ) || ! is_array( $alerts ) ) {
			// No alerts - don't display widget.
			return;
		}

		// Get most recent 5 alerts.
		$recent_alerts = array_slice( array_reverse( $alerts ), 0, 5 );

		?>
		<div class="postbox">
			<div class="postbox-header">
				<h2 class="hndle"><?php esc_html_e( '🔔 Performance Alerts', 'wpshadow' ); ?></h2>
			</div>
			<div class="inside">
				<div class="wps-widget-content">
					<style>
						.wps-alert-item {
							padding: 10px 12px;
							margin-bottom: 8px;
							border-radius: 4px;
							font-size: 13px;
							background: #fff3cd;
							border-left: 4px solid #ffc107;
							color: #856404;
						}
						.wps-alert-item strong {
							display: block;
							margin-bottom: 3px;
							text-transform: capitalize;
						}
						.wps-alert-item small {
							color: #666;
							font-size: 11px;
						}
						.wps-no-alerts {
							text-align: center;
							padding: 20px;
							color: #28a745;
						}
					</style>

					<?php if ( ! empty( $recent_alerts ) ) : ?>
						<ul style="list-style: none; padding: 0; margin: 0;">
							<?php foreach ( $recent_alerts as $alert ) : ?>
								<li class="wps-alert-item">
									<strong><?php echo esc_html( ucfirst( $alert['type'] ?? 'alert' ) ); ?></strong>
									<span><?php echo esc_html( $alert['message'] ?? '' ); ?></span>
									<br />
									<small>
										<?php
										/* translators: %s: time ago */
										echo esc_html( sprintf( __( '%s ago', 'wpshadow' ), human_time_diff( $alert['timestamp'] ?? time() ) ) );
										?>
									</small>
								</li>
							<?php endforeach; ?>
						</ul>

						<p style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e9ecef;">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=features' ) ); ?>" class="button">
								<?php esc_html_e( 'Configure Alert Settings', 'wpshadow' ); ?>
							</a>
						</p>
					<?php else : ?>
						<div class="wps-no-alerts">
							<p><?php esc_html_e( '✅ No alerts triggered recently. Your site is performing well!', 'wpshadow' ); ?></p>
						</div>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Discover locally downloaded hubs/spokes and append to catalog for UI rendering.
	 *
	 * @param array $catalog Existing catalog with status.
	 * @return array
	 */
	private static function discover_local_module_entries( array $catalog ): array {
		$catalog_by_slug = array();
		foreach ( $catalog as $entry ) {
			if ( isset( $entry['slug'] ) ) {
				$catalog_by_slug[ $entry['slug'] ] = $entry;
			}
		}

		$roots = array(
			'hub'   => trailingslashit( WPSHADOW_PATH ) . 'modules/hubs',
			'spoke' => trailingslashit( WPSHADOW_PATH ) . 'modules/spokes',
		);

		foreach ( $roots as $type => $root ) {
			if ( ! is_dir( $root ) ) {
				continue;
			}

		// Safely scan directory with proper error handling.
		$items = @scandir( $root ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- Directory traversal requires error suppression for reliability
			foreach ( $items as $item ) {
				if ( '.' === $item || '..' === $item ) {
					continue;
				}
				$module_dir = $root . '/' . $item;
				$entry_file = $module_dir . '/module.php';
				if ( ! is_dir( $module_dir ) || ! is_file( $entry_file ) ) {
					continue;
				}

				$slug         = $item;
				$requires_hub = '';
				$name         = ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );

			// Safely read file with proper error handling instead of silencing errors.
			$contents = '';
			if ( file_exists( $entry_file ) && is_readable( $entry_file ) ) {
				$contents = file_get_contents( $entry_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			}
					if ( preg_match( "/'name'\s*=>\s*__\(\s*'([^']+)'/", $contents, $m ) ) {
						$name = sanitize_text_field( $m[1] );
					}
					if ( preg_match( "/'requires_hub'\s*=>\s*'([^']+)'/", $contents, $m ) ) {
						$requires_hub = sanitize_key( $m[1] );
					}
				}

				if ( isset( $catalog_by_slug[ $slug ] ) ) {
					continue;
				}

				$catalog_by_slug[ $slug ] = array(
					'slug'             => $slug,
					'type'             => $type,
					'name'             => $name,
					'description'      => __( 'Found a new module', 'wpshadow' ),
					'installed'        => true,
					'enabled'          => false,
					'update_available' => false,
					'basename'         => $item . '/module.php',
					'path'             => trailingslashit( $module_dir ),
					'requires_hub'     => $requires_hub,
				);
			}
		}

		// Merge placeholders from modules/missing-modules.json so the widget lists not-yet-downloaded modules.
		$missing_file = trailingslashit( WPSHADOW_PATH ) . 'modules/missing-modules.json';
		if ( file_exists( $missing_file ) && is_readable( $missing_file ) ) {
			$missing_json = file_get_contents( $missing_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$missing_data = json_decode( (string) $missing_json, true );

			if ( is_array( $missing_data ) ) {
				foreach ( $missing_data as $entry ) {
					$slug = sanitize_key( $entry['slug'] ?? '' );
					$type = sanitize_key( $entry['type'] ?? '' );

					if ( empty( $slug ) || isset( $catalog_by_slug[ $slug ] ) ) {
						continue;
					}

					$name           = sanitize_text_field( $entry['name'] ?? $slug );
					$description    = sanitize_text_field( $entry['description'] ?? '' );
					$requires_hub   = sanitize_key( $entry['requires_hub'] ?? '' );
					$requires_spoke = sanitize_key( $entry['requires_spoke'] ?? '' );

					$catalog_by_slug[ $slug ] = array_merge(
						$entry,
						array(
							'slug'             => $slug,
							'type'             => $type,
							'name'             => $name,
							'description'      => $description,
							'installed'        => false,
							'enabled'          => false,
							'update_available' => false,
							'basename'         => '',
							'path'             => '',
							'requires_hub'     => $requires_hub,
							'requires_spoke'   => $requires_spoke,
							'download_url'     => esc_url_raw( $entry['download_url'] ?? '' ),
						)
					);
				}
			}
		}

		return array_values( $catalog_by_slug );
	}

	private static function widget_vault_status(): void {
		$upload_dir    = wp_upload_dir();
		$vault_dirname = get_option( 'wpshadow_vault_dirname' );
		$vault_path    = ! empty( $vault_dirname ) ? $upload_dir['basedir'] . '/' . $vault_dirname : '';

		$vault_exists   = ! empty( $vault_path ) && is_dir( $vault_path );
		$vault_writable = $vault_exists && wp_is_writable( $vault_path );
		$encryption_key = wpshadow_get_vault_key();
		$has_encryption = ! empty( $encryption_key );
		$key_source     = defined( 'wpshadow_VAULT_KEY' ) && WPSHADOW_VAULT_KEY ? 'wp-config.php' : 'Options';

		// Calculate vault size if it exists.
		$vault_size = 0;
		$file_count = 0;
		if ( $vault_exists ) {
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $vault_path, \RecursiveDirectoryIterator::SKIP_DOTS )
			);
			foreach ( $iterator as $file ) {
				if ( $file->isFile() ) {
					$vault_size += $file->getSize();
					++$file_count;
				}
			}
		}

		$vault_size_formatted = size_format( $vault_size, 2 );

		// Determine overall vault health status.
		$status_issues = array();
		if ( ! $vault_exists ) {
			$status_issues[] = __( 'We can\'t find your vault folder', 'wpshadow' );
		} elseif ( ! $vault_writable ) {
			$status_issues[] = __( 'We can\'t save to your vault folder', 'wpshadow' );
		}
		if ( ! $has_encryption ) {
			$status_issues[] = __( 'Encryption isn\'t set up yet', 'wpshadow' );
		}

		$is_healthy = empty( $status_issues );
		?>
		<div class="wps-widget-content wps-vault-status">
			<!-- Overall Status Badge -->
			<div style="text-align: center; padding: 15px 0; border-bottom: 1px solid #e5e5e5; margin-bottom: 15px;">
				<?php if ( $is_healthy ) : ?>
					<span class="dashicons dashicons-yes-alt" style="font-size: 48px; width: 48px; height: 48px; color: #00a32a;"></span>
					<div style="margin-top: 8px;">
						<strong style="color: #00a32a; font-size: 16px;"><?php esc_html_e( 'Vault Operational', 'wpshadow' ); ?></strong>
					</div>
				<?php else : ?>
					<span class="dashicons dashicons-warning" style="font-size: 48px; width: 48px; height: 48px; color: #d63638;"></span>
					<div style="margin-top: 8px;">
						<strong style="color: #d63638; font-size: 16px;"><?php esc_html_e( 'Vault Needs Attention', 'wpshadow' ); ?></strong>
					</div>
				<?php endif; ?>
			</div>

			<!-- Vault Stats Grid -->
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
				<div style="text-align: center; padding: 10px; background: #f6f7f7; border-radius: 4px;">
					<div style="font-size: 24px; font-weight: bold; color: #2271b1;"><?php echo esc_html( $file_count ); ?></div>
					<div style="font-size: 12px; color: #666; margin-top: 4px;"><?php esc_html_e( 'Files Stored', 'wpshadow' ); ?></div>
				</div>
				<div style="text-align: center; padding: 10px; background: #f6f7f7; border-radius: 4px;">
					<div style="font-size: 24px; font-weight: bold; color: #2271b1;"><?php echo esc_html( $vault_size_formatted ); ?></div>
					<div style="font-size: 12px; color: #666; margin-top: 4px;"><?php esc_html_e( 'Total Size', 'wpshadow' ); ?></div>
				</div>
			</div>

			<!-- Configuration Details -->
			<div style="font-size: 13px;">
				<div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
					<span><?php esc_html_e( 'Directory', 'wpshadow' ); ?>:</span>
					<strong><?php echo $vault_exists ? '<span style="color: #00a32a;">✓</span>' : '<span style="color: #d63638;">✗</span>'; ?></strong>
				</div>
				<div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
					<span><?php esc_html_e( 'Writable', 'wpshadow' ); ?>:</span>
					<strong><?php echo $vault_writable ? '<span style="color: #00a32a;">✓</span>' : '<span style="color: #d63638;">✗</span>'; ?></strong>
				</div>
				<div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
					<span><?php esc_html_e( 'Encryption', 'wpshadow' ); ?>:</span>
					<strong><?php echo $has_encryption ? '<span style="color: #00a32a;">✓</span>' : '<span style="color: #d63638;">✗</span>'; ?></strong>
				</div>
				<?php if ( $has_encryption ) : ?>
					<div style="display: flex; justify-content: space-between; padding: 8px 0;">
						<span><?php esc_html_e( 'Key Source', 'wpshadow' ); ?>:</span>
						<strong><?php echo esc_html( $key_source ); ?></strong>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $status_issues ) ) : ?>
				<!-- Issues List -->
				<div style="margin-top: 15px; padding: 10px; background: #fcf0f1; border-left: 4px solid #d63638; border-radius: 2px;">
					<strong style="color: #d63638;"><?php esc_html_e( 'Issues Found:', 'wpshadow' ); ?></strong>
					<ul style="margin: 8px 0 0 20px; color: #d63638;">
						<?php foreach ( $status_issues as $issue ) : ?>
							<li><?php echo esc_html( $issue ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render health widget HTML (public for AJAX refresh).
	 *
	 * @param array  $health_data Health data array from get_health_check_results().
	 * @param string $module_name Optional module name for context.
	 * @return void
	 */
	public static function render_health_widget_html( array $health_data, string $module_name = '' ): void {
		self::render_health_widget( $health_data, $module_name );
	}

	/**
	 * Render events widget HTML (public for AJAX refresh).
	 *
	 * @param array $active_repos Active repository data.
	 * @return void
	 */
	public static function render_events_widget_html( array $active_repos ): void {
		?>
		<div class="wps-events-feed">
			<?php if ( empty( $active_repos ) ) : ?>
				<p><em><?php esc_html_e( 'No active modules. Activate modules to see their updates.', 'wpshadow' ); ?></em></p>
			<?php else : ?>
				<p><em><?php esc_html_e( 'Showing events for active modules:', 'wpshadow' ); ?></em></p>
				<ul class="wps-events-list" style="list-style: none; padding: 0; margin: 0;">
					<?php foreach ( $active_repos as $repo_data ) : ?>
						<li style="padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
							<strong><?php echo esc_html( $repo_data['name'] ); ?></strong>
							<br />
							<small>
								<a href="<?php echo esc_url( 'https://github.com/thisismyurl/' . $repo_data['repo'] . '/releases' ); ?>" target="_blank" rel="noopener">
									<?php esc_html_e( 'Latest releases', 'wpshadow' ); ?>
								</a>
								|
								<a href="<?php echo esc_url( 'https://github.com/thisismyurl/' . $repo_data['repo'] . '/issues' ); ?>" target="_blank" rel="noopener">
									<?php esc_html_e( 'Issues', 'wpshadow' ); ?>
								</a>
							</small>
						</li>
					<?php endforeach; ?>
				</ul>
				<p style="margin-top: 12px; text-align: center;">
					<a href="https://github.com/thisismyurl?tab=repositories&q=plugin-" target="_blank" rel="noopener">
						<?php esc_html_e( 'View all repositories →', 'wpshadow' ); ?>
					</a>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}

	private static function widget_system_health(): void {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Site_Health' ) ) {
			echo '<div class="wps-widget-content"><p><em>' . esc_html__( 'Health info isn\'t available right now.', 'wpshadow' ) . '</em></p></div>';
			return;
		}

		// Get health results (automatically filters by active modules).
		$health_data = \WPShadow\WPSHADOW_Site_Health::get_health_check_results();
		?>
		<div id="wps-health-widget-container">
			<?php self::render_health_widget( $health_data, '' ); ?>
		</div>
		<?php
	}

	/**
	 * Render hierarchical health widget.
	 *
	 * @param array  $health_data Health data array from get_health_check_results().
	 * @param string $module_name Optional module name for context. Empty string = system-wide health.
	 * @return void
	 */
	private static function render_health_widget( array $health_data, string $module_name = '' ): void {
		$score          = $health_data['score'] ?? 0;
		$status         = $health_data['status'] ?? 'good';
		$results        = $health_data['results'] ?? array();
		$counts         = $health_data['counts'] ?? array(
			'good'     => 0,
			'warning'  => 0,
			'critical' => 0,
		);
		$good_count     = $counts['good'] ?? 0;
		$warning_count  = $counts['warning'] ?? 0;
		$critical_count = $counts['critical'] ?? 0;

		// Color coding.
		$health_color = 'critical' === $status ? '#d63638' : ( 'recommended' === $status ? '#dba617' : '#00a32a' );
		$health_label = 'critical' === $status ? __( 'Needs Attention', 'wpshadow' ) : ( 'recommended' === $status ? __( 'Could Be Better', 'wpshadow' ) : __( 'Looking Good', 'wpshadow' ) );
		?>
		<div class="wps-widget-content wps-system-health">
			<!-- Health Score Badge -->
			<div style="text-align: center; padding: 15px 0; border-bottom: 1px solid #e5e5e5; margin-bottom: 15px;">
				<div style="width: 80px; height: 80px; margin: 0 auto; border-radius: 50%; border: 6px solid <?php echo esc_attr( $health_color ); ?>; display: flex; align-items: center; justify-content: center;">
					<div>
						<div style="font-size: 24px; font-weight: bold; color: <?php echo esc_attr( $health_color ); ?>;"><?php echo esc_html( $score ); ?>%</div>
					</div>
				</div>
				<div style="margin-top: 10px;">
					<strong style="color: <?php echo esc_attr( $health_color ); ?>; font-size: 16px;"><?php echo esc_html( $health_label ); ?></strong>
				</div>
			</div>

			<!-- Test Results Summary -->
			<div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 15px;">
				<div style="text-align: center; padding: 10px; background: #f6f7f7; border-radius: 4px;">
					<div style="font-size: 20px; font-weight: bold; color: #00a32a;"><?php echo esc_html( $good_count ); ?></div>
					<div style="font-size: 11px; color: #666; margin-top: 2px;"><?php esc_html_e( 'Passed', 'wpshadow' ); ?></div>
				</div>
				<div style="text-align: center; padding: 10px; background: #f6f7f7; border-radius: 4px;">
					<div style="font-size: 20px; font-weight: bold; color: #dba617;"><?php echo esc_html( $warning_count ); ?></div>
					<div style="font-size: 11px; color: #666; margin-top: 2px;"><?php esc_html_e( 'To Review', 'wpshadow' ); ?></div>
				</div>
				<div style="text-align: center; padding: 10px; background: #f6f7f7; border-radius: 4px;">
					<div style="font-size: 20px; font-weight: bold; color: #d63638;"><?php echo esc_html( $critical_count ); ?></div>
					<div style="font-size: 11px; color: #666; margin-top: 2px;"><?php esc_html_e( 'Needs Attention', 'wpshadow' ); ?></div>
				</div>
			</div>

			<!-- Individual Test Results -->
			<div style="font-size: 13px;">
				<?php foreach ( $results as $test_id => $result ) : ?>
					<?php
					$icon_map  = array(
						'good'        => array( 'dashicons-yes-alt', '#00a32a' ),
						'recommended' => array( 'dashicons-warning', '#dba617' ),
						'critical'    => array( 'dashicons-dismiss', '#d63638' ),
					);
					$icon_data = $icon_map[ $result['status'] ] ?? array( 'dashicons-marker', '#666' );
					?>
					<div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
						<span><?php echo esc_html( $result['label'] ); ?></span>
						<span class="dashicons <?php echo esc_attr( $icon_data[0] ); ?>" style="color: <?php echo esc_attr( $icon_data[1] ); ?>; width: 20px; height: 20px; font-size: 20px;"></span>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- View Full Report Link -->
			<div style="text-align: center; margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
				<a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>" class="button">
					<?php esc_html_e( 'View Full Site Health Report →', 'wpshadow' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	private static function widget_events_and_news(): void {
		// Get active module repos for filtering.
		$active_repos = array();
		$catalog      = \WPShadow\WPSHADOW_Module_Registry::get_catalog_with_status();
		foreach ( $catalog as $module ) {
			$slug = $module['slug'] ?? '';
			if ( ! empty( $slug ) && \WPShadow\WPSHADOW_Module_Registry::is_enabled( $slug ) ) {
				$repo           = 'plugin-' . $slug;
				$active_repos[] = array(
					'slug' => $slug,
					'repo' => $repo,
					'name' => $module['name'] ?? ucfirst( str_replace( '-', ' ', $slug ) ),
				);
			}
		}
		?>
		<div class="wps-widget-content" id="wps-events-news-container">
			<?php self::render_events_widget_html( $active_repos ); ?>
		</div>
		<?php
	}

	/* ====== HUB WIDGETS ====== */

	private static function widget_hub_overview( string $hub_id ): void {
		?>
		<div class="wps-widget-content">
			<p><?php echo esc_html( sprintf( __( 'Managing %s processing and distribution.', 'wpshadow' ), strtoupper( $hub_id ) ) ); ?></p>
		</div>
		<?php
	}

	private static function widget_active_spokes( string $hub_id ): void {
		$catalog = \WPShadow\WPSHADOW_Module_Registry::get_catalog_with_status();
		$spokes  = array_filter(
			$catalog,
			fn( $m ) => 'spoke' === ( $m['type'] ?? '' )
				&& ! empty( $m['status']['active'] )
				&& str_starts_with( $m['id'] ?? '', $hub_id )
		);
		?>
		<div class="wps-widget-content">
			<?php if ( empty( $spokes ) ) : ?>
				<p><?php esc_html_e( 'No spokes currently active for this hub.', 'wpshadow' ); ?></p>
			<?php else : ?>
				<ul class="wps-spoke-list">
					<?php foreach ( $spokes as $spoke ) : ?>
						<?php
						$spoke_id   = sanitize_key( str_replace( $hub_id . '-', '', $spoke['id'] ?? '' ) );
						$spoke_name = esc_html( $spoke['name'] ?? $spoke_id );
						$spoke_url  = WPSHADOW_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id );
						?>
						<li>
							<a href="<?php echo esc_url( $spoke_url ); ?>">
								<span class="dashicons dashicons-hammer"></span>
								<?php echo $spoke_name; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
		<?php
	}

	private static function widget_hub_stats( string $hub_id ): void {
		?>
		<div class="wps-widget-content">
			<p><em><?php esc_html_e( 'Processing stats coming soon.', 'wpshadow' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_hub_quick_actions( string $hub_id ): void {
		?>
		<div class="wps-widget-content">
			<p>
				<a href="<?php echo esc_url( WPSHADOW_Tab_Navigation::build_hub_url( $hub_id, 'settings' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Hub Settings', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/* ====== SPOKE WIDGETS ====== */

	private static function widget_spoke_overview( string $hub_id, string $spoke_id ): void {
		?>
		<div class="wps-widget-content">
			<p><?php echo esc_html( sprintf( __( 'Managing %s format support.', 'wpshadow' ), strtoupper( $spoke_id ) ) ); ?></p>
		</div>
		<?php
	}

	private static function widget_spoke_features( string $spoke_id ): void {
		?>
		<div class="wps-widget-content">
			<ul class="wps-features-list">
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Format Detection', 'wpshadow' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Conversion Support', 'wpshadow' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Metadata Handling', 'wpshadow' ); ?></li>
			</ul>
		</div>
		<?php
	}

	private static function widget_spoke_stats( string $spoke_id ): void {
		?>
		<div class="wps-widget-content">
			<p><em><?php esc_html_e( 'Format-specific stats coming soon.', 'wpshadow' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_spoke_quick_actions( string $hub_id, string $spoke_id ): void {
		?>
		<div class="wps-widget-content">
			<p>
				<a href="<?php echo esc_url( WPSHADOW_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id, 'settings' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Spoke Settings', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	private static function widget_media_overview(): void {
		?>
		<div class="wps-widget-content">
			<p><?php esc_html_e( 'Media Hub provides centralized media processing and management capabilities.', 'wpshadow' ); ?></p>
			<ul style="list-style: none; padding: 0;">
				<li><span class="dashicons dashicons-admin-media"></span> <?php esc_html_e( 'Batch processing for media files', 'wpshadow' ); ?></li>
				<li><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Media optimization policies', 'wpshadow' ); ?></li>
				<li><span class="dashicons dashicons-networking"></span> <?php esc_html_e( 'Multi-format support coordination', 'wpshadow' ); ?></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render hub health with dependents.
	 *
	 * @param string $hub_id Hub identifier.
	 * @return void
	 */
	private static function widget_hub_health( string $hub_id ): void {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Site_Health' ) ) {
			echo '<div class="wps-widget-content"><p><em>' . esc_html__( 'Health checks unavailable.', 'wpshadow' ) . '</em></p></div>';
			return;
		}

		// Get hierarchical health data.
		$health_hierarchy = \WPShadow\WPSHADOW_Site_Health::get_hierarchical_health( $hub_id );
		$self_health      = $health_hierarchy['self'] ?? array();
		$dependents       = $health_hierarchy['dependents'] ?? array();
		?>
		<div class="wps-widget-content">
			<!-- Self Health -->
			<div style="margin-bottom: 20px;">
				<h4 style="margin: 0 0 10px 0;"><?php esc_html_e( 'Hub Health', 'wpshadow' ); ?></h4>
				<?php self::render_health_widget( $self_health ); ?>
			</div>

			<?php if ( ! empty( $dependents ) ) : ?>
				<!-- Dependent Health -->
				<div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e5e5;">
					<h4 style="margin: 0 0 10px 0;"><?php esc_html_e( 'Dependent Modules Health', 'wpshadow' ); ?></h4>
					<?php foreach ( $dependents as $dep_id => $dep_data ) : ?>
						<div style="margin-bottom: 15px; padding: 10px; background: #f9f9f9; border-radius: 3px;">
							<h5 style="margin: 0 0 8px 0; color: #2271b1;">
								<?php echo esc_html( $dep_data['name'] ?? ucfirst( $dep_id ) ); ?>
							</h5>
							<?php self::render_compact_health_widget( $dep_data['health'] ?? array() ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Render compact health widget (for dependents).
	 *
	 * @param array $health_data Health data.
	 * @return void
	 */
	private static function render_compact_health_widget( array $health_data ): void {
		$score   = $health_data['score'] ?? 0;
		$status  = $health_data['status'] ?? 'good';
		$counts  = $health_data['counts'] ?? array(
			'good'     => 0,
			'warning'  => 0,
			'critical' => 0,
		);
		$results = $health_data['results'] ?? array();

		$health_color = 'critical' === $status ? '#d63638' : ( 'recommended' === $status ? '#dba617' : '#00a32a' );
		$health_label = 'critical' === $status ? __( 'Critical', 'wpshadow' ) : ( 'recommended' === $status ? __( 'Warning', 'wpshadow' ) : __( 'Good', 'wpshadow' ) );
		?>
		<div style="display: flex; align-items: center; justify-content: space-between; gap: 15px;">
			<div style="flex: 0 0 60px;">
				<div style="width: 60px; height: 60px; border-radius: 50%; border: 4px solid <?php echo esc_attr( $health_color ); ?>; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold; color: <?php echo esc_attr( $health_color ); ?>;">
					<?php echo esc_html( $score ); ?>%
				</div>
			</div>
			<div style="flex: 1;">
				<div style="font-weight: 600; color: <?php echo esc_attr( $health_color ); ?>; margin-bottom: 6px;">
					<?php echo esc_html( $health_label ); ?>
				</div>
				<div style="display: flex; gap: 10px; font-size: 12px; color: #666;">
					<span><strong><?php echo esc_html( $counts['good'] ?? 0 ); ?></strong> <?php esc_html_e( 'Passed', 'wpshadow' ); ?></span>
					<?php if ( ( $counts['warning'] ?? 0 ) > 0 ) : ?>
						<span><strong style="color: #dba617;"><?php echo esc_html( $counts['warning'] ); ?></strong> <?php esc_html_e( 'To Review', 'wpshadow' ); ?></span>
					<?php endif; ?>
					<?php if ( ( $counts['critical'] ?? 0 ) > 0 ) : ?>
						<span><strong style="color: #d63638;"><?php echo esc_html( $counts['critical'] ); ?></strong> <?php esc_html_e( 'Needs Attention', 'wpshadow' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	private static function widget_vault_overview(): void {
		$upload_dir     = wp_upload_dir();
		$vault_dirname  = get_option( 'wpshadow_vault_dirname' );
		$vault_dir      = ! empty( $vault_dirname ) ? $upload_dir['basedir'] . '/' . $vault_dirname : '';
		$vault_exists   = ! empty( $vault_dir ) && is_dir( $vault_dir );
		$vault_writable = $vault_exists && wp_is_writable( $vault_dir );
		?>
		<div class="wps-widget-content">
			<p><?php esc_html_e( 'The Vault securely stores original media files with SHA-256 verification and automatic recovery.', 'wpshadow' ); ?></p>
			<ul style="list-style: none; padding: 0;">
				<li>
					<span class="dashicons dashicons-<?php echo $vault_writable ? 'yes' : 'no'; ?>" style="color: <?php echo $vault_writable ? '#00a32a' : '#d63638'; ?>;"></span>
					<?php echo $vault_writable ? esc_html__( 'Vault directory is writable', 'wpshadow' ) : esc_html__( 'Vault directory not writable', 'wpshadow' ); ?>
				</li>
				<li><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Optional encryption support', 'wpshadow' ); ?></li>
				<li><span class="dashicons dashicons-image-rotate"></span> <?php esc_html_e( 'Automatic rehydration on 404', 'wpshadow' ); ?></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Render metabox with custom drag-and-drop wrapper.
	 *
	 * @param string $id Metabox ID.
	 * @param string $title Metabox title.
	 * @param callable $callback Content render callback.
	 * @return void
	 */
	private static function render_custom_metabox( string $id, string $title, callable $callback ): void {
		?>
		<div class="wps-metabox" data-metabox-id="<?php echo esc_attr( $id ); ?>">
			<div class="wps-metabox-header">
				<div class="wps-metabox-handle"><?php echo esc_html( $title ); ?></div>
				<a href="#" class="wps-metabox-toggle" aria-label="<?php esc_attr_e( 'Toggle panel', 'wpshadow' ); ?>">
					<span class="dashicons dashicons-arrow-down-alt2"></span>
				</a>
			</div>
			<div class="wps-metabox-content">
				<?php call_user_func( $callback ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Custom render methods for drag-and-drop dashboard.
	 */
	public static function render_metabox_quick_actions_custom(): void {
		self::render_custom_metabox( 'wpshadow_quick_actions', __( 'WPShadow Quick Actions', 'wpshadow' ), array( __CLASS__, 'widget_quick_actions' ) );
	}

	public static function render_metabox_modules_custom(): void {
		self::render_custom_metabox( 'wpshadow_modules', __( 'WPShadow Modules', 'wpshadow' ), array( __CLASS__, 'widget_modules' ) );
	}

	public static function render_metabox_activity_custom(): void {
		self::render_custom_metabox( 'wpshadow_activity', __( 'WPShadow Activity', 'wpshadow' ), array( __CLASS__, 'widget_activity' ) );
	}

	public static function render_metabox_events_and_news_custom(): void {
		self::render_custom_metabox( 'wpshadow_events_and_news', __( 'Events & News', 'wpshadow' ), array( __CLASS__, 'widget_events_and_news' ) );
	}

	public static function render_metabox_vault_status_custom(): void {
		self::render_custom_metabox( 'wpshadow_vault_status', __( 'Vault Status', 'wpshadow' ), array( __CLASS__, 'widget_vault_status' ) );
	}

	/**
	 * Widget: Environment Status
	 * Shows current server environment status and resource usage.
	 *
	 * @return void
	 */
	private static function widget_environment_status(): void {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Environment_Checker' ) || ! class_exists( '\\WPShadow\\WPSHADOW_Server_Limits' ) ) {
			?>
			<div class="wps-widget-content">
				<p><em><?php esc_html_e( 'Environment checker unavailable.', 'wpshadow' ); ?></em></p>
			</div>
			<?php
			return;
		}

		$env_status      = \WPShadow\WPSHADOW_Environment_Checker::get_environment_status();
		$resource_status = \WPShadow\WPSHADOW_Server_Limits::get_resource_status();

		// Determine overall status icon and message.
		$status_icon    = '✓';
		$status_color   = '#46b450';
		$status_message = __( 'Environment is optimal', 'wpshadow' );

		if ( ! $env_status['is_compatible'] ) {
			$status_icon    = '✗';
			$status_color   = '#d63638';
			$status_message = __( 'Environment is incompatible', 'wpshadow' );
		} elseif ( $env_status['has_constraints'] || 'warning' === $resource_status['level'] ) {
			$status_icon    = '⚠';
			$status_color   = '#dba617';
			$status_message = __( 'Resource constraints detected', 'wpshadow' );
		} elseif ( 'critical' === $resource_status['level'] ) {
			$status_icon    = '✗';
			$status_color   = '#d63638';
			$status_message = __( 'Your site is working hard right now', 'wpshadow' );
		}

		?>
		<div class="wps-widget-content">
			<!-- Overall Status -->
			<div style="display: flex; align-items: center; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px; border-left: 4px solid <?php echo esc_attr( $status_color ); ?>;">
				<div style="font-size: 32px; margin-right: 15px; line-height: 1;"><?php echo esc_html( $status_icon ); ?></div>
				<div>
					<div style="font-size: 16px; font-weight: 600; color: <?php echo esc_attr( $status_color ); ?>;">
						<?php echo esc_html( $status_message ); ?>
					</div>
					<div style="font-size: 13px; color: #666; margin-top: 4px;">
						<?php
						if ( $env_status['is_compatible'] && ! $env_status['has_constraints'] ) {
							esc_html_e( 'All systems operational', 'wpshadow' );
						} elseif ( $env_status['has_constraints'] ) {
							esc_html_e( 'Operations will be batched automatically', 'wpshadow' );
						} else {
							esc_html_e( 'Heavy operations disabled', 'wpshadow' );
						}
						?>
					</div>
				</div>
			</div>

			<!-- Environment Details -->
			<div style="margin-bottom: 20px;">
				<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #23282d;"><?php esc_html_e( 'Environment', 'wpshadow' ); ?></h4>
				<table style="width: 100%; font-size: 13px;">
					<tr>
						<td style="padding: 6px 0; color: #666;"><?php esc_html_e( 'PHP Version:', 'wpshadow' ); ?></td>
						<td style="padding: 6px 0; text-align: right; font-weight: 500;">
							<?php echo esc_html( $env_status['php_version']['current'] ); ?>
							<?php if ( ! $env_status['php_version']['meets_requirement'] ) : ?>
								<span style="color: #d63638;">⚠</span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td style="padding: 6px 0; color: #666;"><?php esc_html_e( 'WordPress Version:', 'wpshadow' ); ?></td>
						<td style="padding: 6px 0; text-align: right; font-weight: 500;">
							<?php echo esc_html( $env_status['wp_version']['current'] ); ?>
							<?php if ( ! $env_status['wp_version']['meets_requirement'] ) : ?>
								<span style="color: #d63638;">⚠</span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td style="padding: 6px 0; color: #666;"><?php esc_html_e( 'Memory Limit:', 'wpshadow' ); ?></td>
						<td style="padding: 6px 0; text-align: right; font-weight: 500;">
							<?php echo esc_html( $env_status['memory_limit']['current'] ); ?>
							<?php if ( 'critical' === $env_status['memory_limit']['level'] ) : ?>
								<span style="color: #d63638;">⚠</span>
							<?php elseif ( 'warning' === $env_status['memory_limit']['level'] ) : ?>
								<span style="color: #dba617;">⚠</span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<td style="padding: 6px 0; color: #666;"><?php esc_html_e( 'Execution Time:', 'wpshadow' ); ?></td>
						<td style="padding: 6px 0; text-align: right; font-weight: 500;">
							<?php
							echo 0 === $env_status['execution_time']['current']
								? esc_html__( 'Unlimited', 'wpshadow' )
								: esc_html( $env_status['execution_time']['current'] . 's' );
							?>
							<?php if ( 'critical' === $env_status['execution_time']['level'] ) : ?>
								<span style="color: #d63638;">⚠</span>
							<?php elseif ( 'warning' === $env_status['execution_time']['level'] ) : ?>
								<span style="color: #dba617;">⚠</span>
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>

			<!-- Resource Usage -->
			<div style="margin-bottom: 20px;">
				<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #23282d;"><?php esc_html_e( 'Current Usage', 'wpshadow' ); ?></h4>
				
				<!-- Memory Usage Bar -->
				<div style="margin-bottom: 12px;">
					<div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 12px;">
						<span style="color: #666;"><?php esc_html_e( 'Memory', 'wpshadow' ); ?></span>
						<span style="font-weight: 500;"><?php echo esc_html( number_format( $resource_status['memory']['usage_percentage'], 1 ) ); ?>%</span>
					</div>
					<div style="height: 8px; background: #e5e5e5; border-radius: 4px; overflow: hidden;">
						<?php
						$memory_bar_color = '#46b450';
						if ( $resource_status['memory']['usage_percentage'] >= 90 ) {
							$memory_bar_color = '#d63638';
						} elseif ( $resource_status['memory']['usage_percentage'] >= 80 ) {
							$memory_bar_color = '#dba617';
						}
						?>
						<div style="width: <?php echo esc_attr( min( 100, $resource_status['memory']['usage_percentage'] ) ); ?>%; height: 100%; background: <?php echo esc_attr( $memory_bar_color ); ?>; transition: width 0.3s ease;"></div>
					</div>
					<div style="font-size: 11px; color: #666; margin-top: 2px;">
						<?php echo esc_html( \WPShadow\WPSHADOW_Environment_Checker::format_bytes( $resource_status['memory']['current_usage'] ) ); ?> / <?php echo esc_html( $resource_status['memory']['limit'] ); ?>
					</div>
				</div>

				<!-- Time Usage Bar (if not unlimited) -->
				<?php if ( 0 !== $resource_status['time']['max_execution_time'] ) : ?>
					<div style="margin-bottom: 12px;">
						<div style="display: flex; justify-content: space-between; margin-bottom: 4px; font-size: 12px;">
							<span style="color: #666;"><?php esc_html_e( 'Execution Time', 'wpshadow' ); ?></span>
							<span style="font-weight: 500;"><?php echo esc_html( number_format( $resource_status['time']['usage_percentage'], 1 ) ); ?>%</span>
						</div>
						<div style="height: 8px; background: #e5e5e5; border-radius: 4px; overflow: hidden;">
							<?php
							$time_bar_color = '#46b450';
							if ( $resource_status['time']['usage_percentage'] >= 85 ) {
								$time_bar_color = '#d63638';
							} elseif ( $resource_status['time']['usage_percentage'] >= 75 ) {
								$time_bar_color = '#dba617';
							}
							?>
							<div style="width: <?php echo esc_attr( min( 100, $resource_status['time']['usage_percentage'] ) ); ?>%; height: 100%; background: <?php echo esc_attr( $time_bar_color ); ?>; transition: width 0.3s ease;"></div>
						</div>
						<div style="font-size: 11px; color: #666; margin-top: 2px;">
							<?php echo esc_html( number_format( $resource_status['time']['elapsed'], 1 ) ); ?>s / <?php echo esc_html( $resource_status['time']['max_execution_time'] ); ?>s
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- PHP Extensions -->
			<?php if ( ! empty( $env_status['extensions']['required_missing'] ) || ! empty( $env_status['extensions']['recommended_missing'] ) ) : ?>
				<div style="margin-bottom: 15px;">
					<h4 style="margin: 0 0 10px 0; font-size: 14px; color: #23282d;"><?php esc_html_e( 'Extensions', 'wpshadow' ); ?></h4>
					<?php if ( ! empty( $env_status['extensions']['required_missing'] ) ) : ?>
						<div style="padding: 8px 10px; background: #fff3cd; border-left: 3px solid #d63638; font-size: 12px; margin-bottom: 8px;">
							<strong><?php esc_html_e( 'Missing required:', 'wpshadow' ); ?></strong>
							<?php echo esc_html( implode( ', ', $env_status['extensions']['required_missing'] ) ); ?>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $env_status['extensions']['recommended_missing'] ) ) : ?>
						<div style="padding: 8px 10px; background: #f8f9fa; border-left: 3px solid #dba617; font-size: 12px;">
							<strong><?php esc_html_e( 'Missing recommended:', 'wpshadow' ); ?></strong>
							<?php echo esc_html( implode( ', ', $env_status['extensions']['recommended_missing'] ) ); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<!-- Actions -->
			<div style="padding-top: 15px; border-top: 1px solid #e5e5e5;">
				<a href="<?php echo esc_url( admin_url( 'site-health.php' ) ); ?>" class="button button-secondary" style="margin-right: 8px;">
					<?php esc_html_e( 'Site Health', 'wpshadow' ); ?>
				</a>
				<?php if ( $resource_status['should_batch'] ) : ?>
					<span style="font-size: 12px; color: #666;">
						<?php
						printf(
							/* translators: %d: Batch size */
							esc_html__( 'Batching enabled (%d items/batch)', 'wpshadow' ),
							\WPShadow\WPSHADOW_Server_Limits::get_batch_size()
						);
						?>
					</span>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render database statistics widget.
	 *
	 * @return void
	 */
	private static function widget_database_stats(): void {
		// Get cached or fresh database statistics.
		$stats = self::get_database_statistics();

		if ( empty( $stats ) ) {
			?>
			<div class="wps-widget-content">
				<p><em><?php esc_html_e( 'Unable to retrieve database statistics.', 'wpshadow' ); ?></em></p>
			</div>
			<?php
			return;
		}

		?>
		<div class="wps-widget-content" id="wps-database-stats-container">
			<!-- Total Database Size -->
			<div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
				<div style="font-size: 32px; font-weight: 700; color: #2271b1; margin-bottom: 5px;">
					<?php echo esc_html( size_format( $stats['total_size'], 2 ) ); ?>
				</div>
				<div style="font-size: 13px; color: #666;">
					<?php esc_html_e( 'Total Database Size', 'wpshadow' ); ?>
				</div>
			</div>

			<!-- Quick Stats Grid -->
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px;">
				<div style="padding: 10px; background: #fff; border: 1px solid #e5e5e5; border-radius: 4px;">
					<div style="font-size: 20px; font-weight: 600; color: #2271b1;"><?php echo esc_html( $stats['table_count'] ); ?></div>
					<div style="font-size: 12px; color: #666;"><?php esc_html_e( 'Tables', 'wpshadow' ); ?></div>
				</div>
				<div style="padding: 10px; background: #fff; border: 1px solid #e5e5e5; border-radius: 4px;">
					<div style="font-size: 20px; font-weight: 600; color: <?php echo $stats['expired_transients'] > 100 ? '#d63638' : '#2271b1'; ?>;">
						<?php echo esc_html( number_format_i18n( $stats['expired_transients'] ) ); ?>
					</div>
					<div style="font-size: 12px; color: #666;"><?php esc_html_e( 'Expired Transients', 'wpshadow' ); ?></div>
				</div>
				<div style="padding: 10px; background: #fff; border: 1px solid #e5e5e5; border-radius: 4px;">
					<div style="font-size: 20px; font-weight: 600; color: <?php echo $stats['revisions'] > 500 ? '#dba617' : '#2271b1'; ?>;">
						<?php echo esc_html( number_format_i18n( $stats['revisions'] ) ); ?>
					</div>
					<div style="font-size: 12px; color: #666;"><?php esc_html_e( 'Post Revisions', 'wpshadow' ); ?></div>
				</div>
				<div style="padding: 10px; background: #fff; border: 1px solid #e5e5e5; border-radius: 4px;">
					<div style="font-size: 20px; font-weight: 600; color: #2271b1;"><?php echo esc_html( number_format_i18n( $stats['autodrafts'] ) ); ?></div>
					<div style="font-size: 12px; color: #666;"><?php esc_html_e( 'Auto-Drafts', 'wpshadow' ); ?></div>
				</div>
			</div>

			<!-- Largest Tables -->
			<div style="margin-bottom: 20px;">
				<h4 style="margin: 0 0 10px 0; font-size: 14px;"><?php esc_html_e( 'Largest Tables', 'wpshadow' ); ?></h4>
				<table style="width: 100%; font-size: 13px;">
					<thead>
						<tr>
							<th style="padding: 6px 0; color: #666; font-weight: 600; text-align: left;"><?php esc_html_e( 'Table', 'wpshadow' ); ?></th>
							<th style="padding: 6px 0; color: #666; font-weight: 600; text-align: right;"><?php esc_html_e( 'Size', 'wpshadow' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $stats['largest_tables'] as $table ) : ?>
							<tr>
								<td style="padding: 6px 0; color: #666;"><?php echo esc_html( $table['name'] ); ?></td>
								<td style="padding: 6px 0; text-align: right; font-weight: 500;">
									<?php echo esc_html( size_format( $table['size'], 2 ) ); ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>

			<!-- Optimization Opportunities -->
			<?php if ( ! empty( $stats['recommendations'] ) ) : ?>
				<div style="margin-bottom: 15px;">
					<h4 style="margin: 0 0 10px 0; font-size: 14px;"><?php esc_html_e( 'Optimization Opportunities', 'wpshadow' ); ?></h4>
					<ul style="list-style: none; margin: 0; padding: 0;">
						<?php foreach ( $stats['recommendations'] as $rec ) : ?>
							<li style="padding: 10px; margin-bottom: 8px; background: #fff3cd; border-left: 4px solid #dba617; border-radius: 2px;">
								<div style="display: flex; align-items: center; gap: 8px;">
									<span class="dashicons dashicons-info" style="color: #dba617; flex-shrink: 0;"></span>
									<div style="flex: 1;">
										<div style="font-size: 13px; margin-bottom: 4px;"><?php echo esc_html( $rec['message'] ); ?></div>
										<a href="<?php echo esc_url( $rec['action_url'] ); ?>" class="button button-small" style="font-size: 11px; padding: 2px 8px; height: auto;">
											<?php echo esc_html( $rec['action_label'] ); ?>
										</a>
									</div>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<!-- Refresh Button -->
			<div style="text-align: center; padding-top: 10px; border-top: 1px solid #e5e5e5;">
				<button type="button" class="button button-small wps-refresh-database-stats" style="font-size: 12px;">
					<span class="dashicons dashicons-update" style="font-size: 14px; vertical-align: middle;"></span>
					<?php esc_html_e( 'Refresh', 'wpshadow' ); ?>
				</button>
				<span class="wps-refresh-spinner" style="display: none; margin-left: 8px;">
					<span class="spinner is-active" style="float: none; margin: 0;"></span>
				</span>
			</div>
		</div>
		<?php
	}

	/**
	 * Get database statistics (cached).
	 *
	 * @return array<string, mixed>
	 */
	private static function get_database_statistics(): array {
		// Check cache first.
		$cached = get_transient( 'wpshadow_database_stats' );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;

		$stats = array();

		try {
			// Total database size.
			$result = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT SUM(data_length + index_length) as size 
					 FROM information_schema.TABLES 
					 WHERE table_schema = %s',
					DB_NAME
				)
			);
			$stats['total_size'] = ! empty( $result[0]->size ) ? (int) $result[0]->size : 0;

			// Table count.
			$stats['table_count'] = (int) $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM information_schema.TABLES WHERE table_schema = %s',
					DB_NAME
				)
			);

			// Largest tables (top 5).
			$largest_tables = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT table_name as name, (data_length + index_length) as size 
					 FROM information_schema.TABLES 
					 WHERE table_schema = %s 
					 ORDER BY size DESC 
					 LIMIT 5',
					DB_NAME
				),
				ARRAY_A
			);

			$stats['largest_tables'] = array();
			if ( ! empty( $largest_tables ) ) {
				foreach ( $largest_tables as $table ) {
					$stats['largest_tables'][] = array(
						'name' => $table['name'],
						'size' => (int) $table['size'],
					);
				}
			}

			// Expired transients.
			$stats['expired_transients'] = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->options} 
					 WHERE option_name LIKE %s 
					 AND option_value < UNIX_TIMESTAMP()",
					$wpdb->esc_like( '_transient_timeout_' ) . '%'
				)
			);

			// Post revisions.
			$stats['revisions'] = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
					'revision'
				)
			);

			// Auto-drafts.
			$stats['autodrafts'] = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = %s",
					'auto-draft'
				)
			);

			// Build recommendations.
			$stats['recommendations'] = array();

			if ( $stats['expired_transients'] > 100 ) {
				$stats['recommendations'][] = array(
					'message'      => sprintf(
						/* translators: %d: number of expired transients */
						__( 'Clean %d expired transients to save space', 'wpshadow' ),
						$stats['expired_transients']
					),
					'action_label' => __( 'Clean Now', 'wpshadow' ),
					'action_url'   => admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=dashboard_settings&action=clean_transients' ),
				);
			}

			if ( $stats['revisions'] > 500 ) {
				$stats['recommendations'][] = array(
					'message'      => sprintf(
						/* translators: %d: number of post revisions */
						__( '%d post revisions can be cleaned', 'wpshadow' ),
						$stats['revisions']
					),
					'action_label' => __( 'Manage Revisions', 'wpshadow' ),
					'action_url'   => admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=dashboard_settings#revisions' ),
				);
			}

			if ( $stats['autodrafts'] > 50 ) {
				$stats['recommendations'][] = array(
					'message'      => sprintf(
						/* translators: %d: number of auto-drafts */
						__( '%d auto-drafts can be removed', 'wpshadow' ),
						$stats['autodrafts']
					),
					'action_label' => __( 'Clean Auto-Drafts', 'wpshadow' ),
					'action_url'   => admin_url( 'admin.php?page=wpshadow&WPSHADOW_tab=dashboard_settings#autodrafts' ),
				);
			}

			// Cache for 1 hour.
			set_transient( 'wpshadow_database_stats', $stats, HOUR_IN_SECONDS );

			return $stats;
		} catch ( \Exception $e ) {
			// Log error but don't expose to user.
			error_log( 'WPS Database Stats Error: ' . $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			return array();
		}
	}

	/**
	 * Render historical performance widget.
	 *
	 * @return void
	 */
	private static function widget_performance_history(): void {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Performance_Monitor' ) ) {
			?>
			<div class="wps-widget-content">
				<p><em><?php esc_html_e( 'Performance monitoring unavailable.', 'wpshadow' ); ?></em></p>
			</div>
			<?php
			return;
		}

		// Get time range from request (default: 7 days).
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display, no state change.
		$days = isset( $_GET['perf_days'] ) ? absint( $_GET['perf_days'] ) : 7;
		if ( ! in_array( $days, array( 7, 30, 90 ), true ) ) {
			$days = 7;
		}

		// Get historical metrics for selected time range.
		$history = \WPShadow\WPSHADOW_Performance_Monitor::get_performance_history( $days );
		
		if ( empty( $history ) ) {
			?>
			<div class="wps-widget-content">
				<p><em><?php esc_html_e( 'No performance history available yet. Check back after collecting some data.', 'wpshadow' ); ?></em></p>
			</div>
			<?php
			return;
		}

		// Prepare chart data.
		$dates         = array();
		$scores        = array();
		$query_counts  = array();
		$load_times    = array();
		$memory_usages = array();
		
		foreach ( $history as $entry ) {
			$dates[]         = $entry['date'] ?? date_i18n( 'M j', $entry['timestamp'] );
			$scores[]        = $entry['score'] ?? 0;
			$query_counts[]  = $entry['query_count'] ?? 0;
			$load_times[]    = round( ( $entry['load_time'] ?? 0 ) * 1000, 2 ); // Convert to ms.
			$memory_usages[] = round( $entry['memory_mb'] ?? 0, 2 );
		}

		$chart_id = 'wps-performance-chart-' . wp_rand();
		?>
		<div class="wps-widget-content wps-performance-history">
			<!-- Time Range Selector -->
			<div class="wps-widget-controls" style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
				<label for="wps-history-range-<?php echo esc_attr( $chart_id ); ?>" style="font-weight: 600; font-size: 13px; color: #666;">
					<?php esc_html_e( 'Time Range:', 'wpshadow' ); ?>
				</label>
				<select id="wps-history-range-<?php echo esc_attr( $chart_id ); ?>" class="wps-history-range-selector" style="padding: 4px 8px; font-size: 13px;">
					<option value="7" <?php selected( $days, 7 ); ?>><?php esc_html_e( 'Last 7 days', 'wpshadow' ); ?></option>
					<option value="30" <?php selected( $days, 30 ); ?>><?php esc_html_e( 'Last 30 days', 'wpshadow' ); ?></option>
					<option value="90" <?php selected( $days, 90 ); ?>><?php esc_html_e( 'Last 90 days', 'wpshadow' ); ?></option>
				</select>
			</div>
			
			<!-- Chart Canvas -->
			<canvas id="<?php echo esc_attr( $chart_id ); ?>" style="max-height: 250px;"></canvas>
			
			<!-- Summary Stats -->
			<?php
			// Calculate averages once to avoid redundant computations.
			$avg_score   = ! empty( $scores ) ? round( array_sum( $scores ) / count( $scores ) ) : 0;
			$avg_queries = ! empty( $query_counts ) ? round( array_sum( $query_counts ) / count( $query_counts ) ) : 0;
			$avg_load    = ! empty( $load_times ) ? round( array_sum( $load_times ) / count( $load_times ) ) : 0;
			$avg_memory  = ! empty( $memory_usages ) ? round( array_sum( $memory_usages ) / count( $memory_usages ), 1 ) : 0;
			?>
			<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
				<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); gap: 10px;">
					<div style="text-align: center;">
						<div style="font-size: 11px; color: #666; margin-bottom: 3px; text-transform: uppercase;"><?php esc_html_e( 'Avg Score', 'wpshadow' ); ?></div>
						<div style="font-size: 20px; font-weight: 600; color: #2271b1;">
							<?php echo esc_html( $avg_score ); ?>
						</div>
					</div>
					<div style="text-align: center;">
						<div style="font-size: 11px; color: #666; margin-bottom: 3px; text-transform: uppercase;"><?php esc_html_e( 'Avg Queries', 'wpshadow' ); ?></div>
						<div style="font-size: 20px; font-weight: 600; color: #0969da;">
							<?php echo esc_html( $avg_queries ); ?>
						</div>
					</div>
					<div style="text-align: center;">
						<div style="font-size: 11px; color: #666; margin-bottom: 3px; text-transform: uppercase;"><?php esc_html_e( 'Avg Load', 'wpshadow' ); ?></div>
						<div style="font-size: 20px; font-weight: 600; color: #1a7f37;">
							<?php echo esc_html( $avg_load ); ?><span style="font-size: 12px; font-weight: 400;">ms</span>
						</div>
					</div>
					<div style="text-align: center;">
						<div style="font-size: 11px; color: #666; margin-bottom: 3px; text-transform: uppercase;"><?php esc_html_e( 'Avg Memory', 'wpshadow' ); ?></div>
						<div style="font-size: 20px; font-weight: 600; color: #8250df;">
							<?php echo esc_html( $avg_memory ); ?><span style="font-size: 12px; font-weight: 400;">MB</span>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script>
		(function($) {
			$(document).ready(function() {
				if (typeof Chart === 'undefined') {
					console.warn('Chart.js not loaded - performance history chart unavailable');
					return;
				}

				var ctx = document.getElementById('<?php echo esc_js( $chart_id ); ?>').getContext('2d');
				var chart = new Chart(ctx, {
					type: 'line',
					data: {
						labels: <?php echo wp_json_encode( $dates ); ?>,
						datasets: [{
							label: '<?php echo esc_js( __( 'Query Count', 'wpshadow' ) ); ?>',
							data: <?php echo wp_json_encode( $query_counts ); ?>,
							borderColor: '#0969da',
							backgroundColor: 'rgba(9, 105, 218, 0.1)',
							tension: 0.4,
							fill: true,
							yAxisID: 'y-queries'
						}, {
							label: '<?php echo esc_js( __( 'Load Time (ms)', 'wpshadow' ) ); ?>',
							data: <?php echo wp_json_encode( $load_times ); ?>,
							borderColor: '#1a7f37',
							backgroundColor: 'rgba(26, 127, 55, 0.1)',
							tension: 0.4,
							fill: true,
							yAxisID: 'y-time'
						}]
					},
					options: {
						responsive: true,
						maintainAspectRatio: false,
						interaction: {
							mode: 'index',
							intersect: false
						},
						plugins: {
							legend: {
								display: true,
								position: 'top',
								labels: {
									boxWidth: 12,
									padding: 10,
									font: {
										size: 11
									}
								}
							},
							tooltip: {
								callbacks: {
									label: function(context) {
										var label = context.dataset.label || '';
										if (label) {
											label += ': ';
										}
										label += context.parsed.y;
										if (context.dataset.yAxisID === 'y-time') {
											label += ' ms';
										}
										return label;
									}
								}
							}
						},
						scales: {
							'y-queries': {
								type: 'linear',
								display: true,
								position: 'left',
								title: {
									display: true,
									text: '<?php echo esc_js( __( 'Queries', 'wpshadow' ) ); ?>',
									font: {
										size: 11
									}
								},
								beginAtZero: true
							},
							'y-time': {
								type: 'linear',
								display: true,
								position: 'right',
								title: {
									display: true,
									text: '<?php echo esc_js( __( 'Time (ms)', 'wpshadow' ) ); ?>',
									font: {
										size: 11
									}
								},
								beginAtZero: true,
								grid: {
									drawOnChartArea: false
								}
							}
						}
					}
				});

				// Handle time range selector change.
				$('#wps-history-range-<?php echo esc_js( $chart_id ); ?>').on('change', function() {
					var days = $(this).val();
					// Safely construct URL using WordPress admin URL.
					var url = new URL(window.location.href);
					url.searchParams.set('perf_days', days);
					window.location.href = url.toString();
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * Database Stats Widget (boxed version for dashboard).
	 *
	 * @return void
	 */
	private static function widget_database_stats_boxed(): void {
		?>
		<div class="postbox">
			<div class="postbox-header">
				<h2 class="hndle"><?php esc_html_e( '🗄️ Database Statistics', 'wpshadow' ); ?></h2>
			</div>
			<div class="inside">
				<?php self::widget_database_stats(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Performance History Widget (boxed version for dashboard).
	 *
	 * @return void
	 */
	private static function widget_performance_history_boxed(): void {
		?>
		<div class="postbox">
			<div class="postbox-header">
				<h2 class="hndle"><?php esc_html_e( '📈 Performance History', 'wpshadow' ); ?></h2>
			</div>
			<div class="inside">
				<?php self::widget_performance_history(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get database statistics (public method for AJAX).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_database_statistics_for_ajax(): array {
		return self::get_database_statistics();
	}

	/**
	 * Render the Favicon & Touch Icon Checker widget.
	 *
	 * @return void
	 */
	private static function widget_favicon_checker(): void {
		// Get the feature instance and render its widget.
		$feature = WPSHADOW_Feature_Registry::get_feature( 'wpshadow_favicon_checker' );
		if ( $feature && method_exists( $feature, 'render_widget' ) ) {
			$feature->render_widget();
		} else {
			echo '<div class="wps-widget-content"><p><em>' . esc_html__( 'Favicon checker unavailable.', 'wpshadow' ) . '</em></p></div>';
		}
	}
}

/* @changelog */



