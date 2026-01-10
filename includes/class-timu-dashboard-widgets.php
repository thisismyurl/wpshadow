<?php
/**
 * Dashboard widget system for tab-based interface.
 *
 * @package TIMU_Core_Support
 * @since 1.0.0
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Widgets Manager
 * Mimics WordPress Core dashboard functionality.
 */
class TIMU_Dashboard_Widgets {
	/**
	 * Render Core-level dashboard.
	 *
	 * @return void
	 */
	public static function render_core_dashboard(): void {
		?>
		<div class="wrap timu-dashboard">
			<h1><?php echo esc_html__( 'Support Dashboard', 'plugin-wp-support-thisismyurl' ); ?></h1>

			<div class="timu-dashboard-widgets-wrap">
				<div class="timu-dashboard-col-container">
					<div id="timu-dashboard-col-1" class="timu-dashboard-col">
						<?php self::widget_suite_overview(); ?>
						<?php self::widget_active_hubs(); ?>
					</div>

					<div id="timu-dashboard-col-2" class="timu-dashboard-col">
						<?php self::widget_recent_activity(); ?>
						<?php self::widget_quick_actions(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Hub-level dashboard.
	 *
	 * @param string $hub_id Hub identifier.
	 * @return void
	 */
	public static function render_hub_dashboard( string $hub_id ): void {
		// Check if hub module has its own dashboard renderer.
		$slug_map = array(
			'media' => 'TIMU\MediaSupport',
			'vault' => 'TIMU\VaultSupport',
		);

		$namespace = $slug_map[ $hub_id ] ?? null;

		if ( $namespace && function_exists( $namespace . '\\render_dashboard' ) ) {
			call_user_func( $namespace . '\\render_dashboard' );
			return;
		}

		// Fall back to generic hub dashboard.
		$hub_name = ucfirst( $hub_id );
		?>
		<div class="wrap timu-dashboard">
			<h1><?php echo esc_html( sprintf( __( '%s Hub Dashboard', 'plugin-wp-support-thisismyurl' ), $hub_name ) ); ?></h1>

			<div class="timu-dashboard-widgets-wrap">
				<div class="timu-dashboard-col-container">
					<div id="timu-dashboard-col-1" class="timu-dashboard-col">
						<?php self::widget_hub_overview( $hub_id ); ?>
						<?php self::widget_active_spokes( $hub_id ); ?>
					</div>

					<div id="timu-dashboard-col-2" class="timu-dashboard-col">
						<?php self::widget_hub_stats( $hub_id ); ?>
						<?php self::widget_hub_quick_actions( $hub_id ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Spoke-level dashboard.
	 *
	 * @param string $hub_id Hub identifier.
	 * @param string $spoke_id Spoke identifier.
	 * @return void
	 */
	public static function render_spoke_dashboard( string $hub_id, string $spoke_id ): void {
		$spoke_name = strtoupper( $spoke_id );
		?>
		<div class="wrap timu-dashboard">
			<h1><?php echo esc_html( sprintf( __( '%s Support Dashboard', 'plugin-wp-support-thisismyurl' ), $spoke_name ) ); ?></h1>

			<div class="timu-dashboard-widgets-wrap">
				<div class="timu-dashboard-col-container">
					<div id="timu-dashboard-col-1" class="timu-dashboard-col">
						<?php self::widget_spoke_overview( $hub_id, $spoke_id ); ?>
						<?php self::widget_spoke_features( $spoke_id ); ?>
					</div>

					<div id="timu-dashboard-col-2" class="timu-dashboard-col">
						<?php self::widget_spoke_stats( $spoke_id ); ?>
						<?php self::widget_spoke_quick_actions( $hub_id, $spoke_id ); ?>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/* ====== CORE WIDGETS ====== */

	public static function render_metabox_health(): void {
		self::widget_health();
	}

	public static function render_metabox_at_a_glance(): void {
		self::widget_at_a_glance();
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

	public static function render_metabox_media_health(): void {
		self::widget_hub_health( 'media' );
	}

	public static function render_metabox_vault_health(): void {
		self::widget_hub_health( 'vault' );
	}

	public static function render_metabox_events_and_news(): void {
		self::widget_events_and_news();
	}

	public static function render_metabox_modules(): void {
		self::widget_modules();
	}

	private static function widget_health(): void {
		$catalog  = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();
		$total    = count( $catalog );
		$active   = count( array_filter( $catalog, fn( $m ) => ! empty( $m['status']['active'] ) ) );
		$inactive = $total - $active;
		$health   = 100;

		// Deduct points for inactive modules.
		if ( $inactive > 0 ) {
			$health = max( 50, 100 - ( $inactive * 10 ) );
		}

		// Color code based on health.
		if ( $health >= 90 ) {
			$color_class = 'timu-health-good';
			$label       = __( 'Good', 'plugin-wp-support-thisismyurl' );
		} elseif ( $health >= 70 ) {
			$color_class = 'timu-health-warning';
			$label       = __( 'Warning', 'plugin-wp-support-thisismyurl' );
		} else {
			$color_class = 'timu-health-critical';
			$label       = __( 'Critical', 'plugin-wp-support-thisismyurl' );
		}
		?>
		<div class="timu-widget-content">
			<div class="timu-health-status <?php echo esc_attr( $color_class ); ?>">
				<div class="timu-health-score"><?php echo esc_html( $health ); ?>%</div>
				<div class="timu-health-label"><?php echo esc_html( $label ); ?></div>
			</div>
			<ul class="timu-health-checks">
					<li><span class="dashicons dashicons-yes"></span> <?php echo esc_html( sprintf( __( '%d Active Modules', 'plugin-wp-support-thisismyurl' ), $active ) ); ?></li>
				<?php if ( $inactive > 0 ) : ?>
					<li class="warning"><span class="dashicons dashicons-warning"></span> <?php echo esc_html( sprintf( __( '%d Inactive Modules', 'plugin-wp-support-thisismyurl' ), $inactive ) ); ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<?php
	}

	private static function widget_at_a_glance(): void {
		$catalog = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();
		$total   = count( $catalog );
		$active  = count( array_filter( $catalog, fn( $m ) => ! empty( $m['status']['active'] ) ) );
		$hubs    = count( array_filter( $catalog, fn( $m ) => 'hub' === ( $m['type'] ?? '' ) ) );
		$spokes  = count( array_filter( $catalog, fn( $m ) => 'spoke' === ( $m['type'] ?? '' ) ) );
		?>
		<div class="timu-widget-content">
			<ul class="timu-stats-list">
					<li><span class="dashicons dashicons-admin-plugins"></span> <?php echo esc_html( sprintf( __( '%d Total Modules', 'plugin-wp-support-thisismyurl' ), $total ) ); ?></li>
					<li><span class="dashicons dashicons-yes"></span> <?php echo esc_html( sprintf( __( '%d Active', 'plugin-wp-support-thisismyurl' ), $active ) ); ?></li>
					<li><span class="dashicons dashicons-networking"></span> <?php echo esc_html( sprintf( __( '%d Hubs', 'plugin-wp-support-thisismyurl' ), $hubs ) ); ?></li>
				<li><span class="dashicons dashicons-hammer"></span> <?php echo esc_html( sprintf( __( '%d Spokes', 'plugin-wp-support-thisismyurl' ), $spokes ) ); ?></li>
			</ul>
		</div>
		<?php
	}

	private static function widget_activity( ?string $module_filter = null ): void {
		// Use TIMU_Activity_Logger if available.
		if ( class_exists( '\\TIMU\\CoreSupport\\TIMU_Activity_Logger' ) ) {
			$events = \TIMU\CoreSupport\TIMU_Activity_Logger::get_events( 100 );

			// Filter events by module if specified.
			if ( ! empty( $module_filter ) ) {
				$events = array_filter(
					$events,
					function( $event ) use ( $module_filter ) {
						$module = $event['metadata']['module'] ?? null;
						return $module === $module_filter;
					}
				);
				// Limit to 5 after filtering.
				$events = array_slice( $events, 0, 5 );
			} else {
				// If no filter, just get the first 5.
				$events = array_slice( $events, 0, 5 );
			}

			if ( empty( $events ) ) {
				?>
				<div class="timu-widget-content">
					<p><em><?php esc_html_e( 'No recent activity.', 'plugin-wp-support-thisismyurl' ); ?></em></p>
				</div>
				<?php
				return;
			}

			?>
			<div class="timu-widget-content">
				<ul style="list-style: none; padding: 0; margin: 0;">
					<?php foreach ( $events as $event ) : ?>
						<?php
						$description = esc_html( $event['description'] );
						$timestamp   = human_time_diff( $event['timestamp'] ) . ' ' . __( 'ago', 'plugin-wp-support-thisismyurl' );
						$user        = get_userdata( $event['user_id'] );
						$username    = $user ? $user->display_name : __( 'System', 'plugin-wp-support-thisismyurl' );
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
			<div class="timu-widget-content">
				<p><em><?php esc_html_e( 'Activity log integration coming soon.', 'plugin-wp-support-thisismyurl' ); ?></em></p>
			</div>
			<?php
		}
	}

	private static function widget_scheduled_tasks(): void {
		?>
		<div class="timu-widget-content">
			<p><em><?php esc_html_e( 'No scheduled tasks configured.', 'plugin-wp-support-thisismyurl' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_modules(): void {
		$context = TIMU_Tab_Navigation::get_current_context();
		$catalog = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();

		// Determine which modules to show based on current level.
		$next_level_modules = array();
		$dependent_hubs     = array(); // Hubs that depend on other hubs.

		if ( 'core' === $context['level'] ) {
			// On core: show top-level hubs (those without requires_hub dependency).
			foreach ( $catalog as $module ) {
				if ( 'hub' !== ( $module['type'] ?? '' ) ) {
					continue;
				}
				if ( ! empty( $module['requires_hub'] ) ) {
					// This is a dependent hub - store it for nested display.
					$parent_hub = $module['requires_hub'];
					if ( ! isset( $dependent_hubs[ $parent_hub ] ) ) {
						$dependent_hubs[ $parent_hub ] = array();
					}
					$dependent_hubs[ $parent_hub ][] = $module;
				} else {
					// This is a top-level hub.
					$next_level_modules[] = $module;
				}
			}
		} elseif ( 'hub' === $context['level'] && ! empty( $context['hub'] ) ) {
			// On hub (e.g., image): show all spokes under that hub.
			$hub_prefix         = $context['hub'] . '-';
			$next_level_modules = array_filter(
				$catalog,
				fn( $m ) => 'spoke' === ( $m['type'] ?? '' )
					&& str_starts_with( $m['slug'] ?? '', $hub_prefix )
			);
		}

		?>
		<style>
			.timu-collapse-toggle {
				cursor: pointer;
				user-select: none;
				transition: background 0.15s;
			}
			.timu-collapse-toggle:hover {
				background: #f0f0f0;
			}
			.timu-collapse-toggle .dashicons {
				transition: transform 0.2s;
			}
			.timu-collapse-toggle.collapsed .dashicons {
				transform: rotate(-90deg);
			}
			.timu-collapse-content {
				transition: max-height 0.3s ease-out, opacity 0.2s;
				overflow: hidden;
			}
			.timu-collapse-content.collapsed {
				max-height: 0 !important;
				opacity: 0;
			}
		</style>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				document.querySelectorAll('.timu-collapse-toggle').forEach(function(toggle) {
					toggle.addEventListener('click', function(e) {
						e.preventDefault();
						var target = document.getElementById(this.getAttribute('data-target'));
						if (target) {
							this.classList.toggle('collapsed');
							target.classList.toggle('collapsed');
						}
					});
				});
				// Toggle handling for dashboard module switches
				const ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
				const nonce = '<?php echo esc_js( wp_create_nonce( 'timu_module_actions' ) ); ?>';
				document.addEventListener('change', async function(e){
					const input = e.target;
					if (!input.matches('.timu-toggle-switch input')) return;
					const slug = input.getAttribute('data-module');
					const installed = input.getAttribute('data-installed') === '1';
					const turningOn = input.checked;
					const pluginBase = input.getAttribute('data-plugin-base') || '';
					const card = input.closest('div[style*="border"]');
					const progress = card ? card.querySelector('.timu-progress') : null;
					input.disabled = true; if (progress) progress.style.display = 'inline-flex';
					const canUsePluginAPI = !!pluginBase;
					const action = canUsePluginAPI ? (turningOn ? (installed ? 'timu_module_activate' : 'timu_module_install') : 'timu_module_deactivate') : 'timu_module_toggle';
					const form = new URLSearchParams({ action, nonce, slug });
					if (canUsePluginAPI) { form.append('plugin_base', pluginBase); } else { form.append('enabled', turningOn ? 1 : 0); }
					try {
						const res = await fetch(ajaxUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: form.toString() });
						const json = await res.json();
						if (!json.success) throw new Error(json?.data?.message || 'Unexpected error');
						input.setAttribute('data-installed','1');
					} catch (err) {
						input.checked = !turningOn;
						console.error(err);
						alert(err.message);
					} finally {
						input.disabled = false; if (progress) progress.style.display = 'none';
					}
				});
			});
		</script>
		<div class="timu-widget-content">
			<?php if ( empty( $next_level_modules ) ) : ?>
				<p><?php esc_html_e( 'No modules available at this level.', 'plugin-wp-support-thisismyurl' ); ?></p>
			<?php else : ?>				<style>
				.timu-toggle-switch { display: inline-block; position: relative; width: 44px; height: 22px; }
				.timu-toggle-switch input { opacity: 0; width: 0; height: 0; }
				.timu-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .3s; border-radius: 22px; }
				.timu-toggle-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
				input:checked + .timu-toggle-slider { background-color: #00a32a; }
				input:focus + .timu-toggle-slider { box-shadow: 0 0 1px #00a32a; }
				input:checked + .timu-toggle-slider:before { transform: translateX(22px); }
				input:disabled + .timu-toggle-slider { opacity: 0.5; cursor: not-allowed; }
				.timu-progress { display: none; align-items: center; gap: 8px; margin-left: 8px; }
				.timu-progress .bar { width: 60px; height: 6px; background: #e5e5e5; border-radius: 6px; overflow: hidden; }
				.timu-progress .bar .fill { width: 50%; height: 100%; background: linear-gradient(90deg, #2271b1, #00a32a); animation: timuProgress 1s infinite alternate ease-in-out; }
				@keyframes timuProgress { from { width: 30%; } to { width: 90%; } }
				</style>				<div class="timu-modules-list" style="margin: 0;">
					<?php foreach ( $next_level_modules as $module ) : ?>
						<?php
						$module_slug      = sanitize_key( $module['slug'] ?? '' );
						$module_name      = esc_html( $module['name'] ?? '' );
						$module_version   = esc_html( $module['version'] ?? '?.?.?' );
						$is_installed     = ! empty( $module['installed'] );
						$is_enabled       = ! empty( $module['enabled'] );
						$update_available = ! empty( $module['update_available'] );

						// Build navigation URL.
						$module_url = TIMU_Tab_Navigation::build_hub_url( $module_slug );
						?>
						<div style="padding: 12px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; background: #fff;">
							<div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
								<div style="flex: 1;">
									<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
										<span class="dashicons dashicons-networking" style="font-size: 20px; width: 20px; height: 20px; color: #2271b1;"></span>
										<strong style="font-size: 14px;">
											<a href="<?php echo esc_url( $module_url ); ?>" style="text-decoration: none; color: inherit;"><?php echo $module_name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
										</strong>
									</div>
									<div style="font-size: 12px; color: #666; margin-left: 28px;">
										<?php echo esc_html( $module['description'] ?? '' ); ?>
									</div>
								</div>
								<div style="display: flex; align-items: center; flex-shrink: 0;">
									<label class="timu-toggle-switch">
										<input type="checkbox" <?php checked( $is_enabled ); ?> data-module="<?php echo esc_attr( $module_slug ); ?>" data-type="hub" data-installed="<?php echo esc_attr( $is_installed ? '1' : '0' ); ?>" data-plugin-base="<?php echo esc_attr( $module['basename'] ?? '' ); ?>">
										<span class="timu-toggle-slider"></span>
									</label>
									<span class="timu-progress" aria-live="polite"><span class="spinner is-active" style="float:none"></span><span class="bar"><span class="fill"></span></span><span class="progress-label"><?php esc_html_e( 'Working…', 'plugin-wp-support-thisismyurl' ); ?></span></span>
								</div>
							</div>

							<?php
							// Show dependent hubs nested under this parent hub.
							if ( ! empty( $dependent_hubs[ $module_slug ] ) ) :
								$collapse_id        = 'timu-deps-' . $module_slug;
								$parent_can_support = $is_installed && $is_enabled; // Parent must be installed AND active.
								?>
								<div class="timu-collapse-toggle" data-target="<?php echo esc_attr( $collapse_id ); ?>" style="margin: 12px 0 0 0; padding: 8px 12px; background: #f0f0f0; border: 1px solid #e0e0e0; border-radius: 3px;">
									<div style="display: flex; align-items: center; gap: 6px;">
										<span class="dashicons dashicons-arrow-down-alt2" style="font-size: 16px; width: 16px; height: 16px; color: #666;"></span>
										<span style="font-size: 11px; text-transform: uppercase; color: #666; font-weight: 600; letter-spacing: 0.5px;">
											<?php
											/* translators: %d: number of dependent modules */
											echo esc_html( sprintf( _n( '%d Dependent Module', '%d Dependent Modules', count( $dependent_hubs[ $module_slug ] ), 'plugin-wp-support-thisismyurl' ), count( $dependent_hubs[ $module_slug ] ) ) );
											?>
										</span>
										<?php if ( ! $parent_can_support ) : ?>
											<span style="font-size: 10px; padding: 2px 6px; background: #999; color: #fff; border-radius: 2px;">
												<?php esc_html_e( 'Parent Required', 'plugin-wp-support-thisismyurl' ); ?>
											</span>
										<?php endif; ?>
									</div>
								</div>
								<div id="<?php echo esc_attr( $collapse_id ); ?>" class="timu-collapse-content" style="margin: 0; padding: 12px; background: #f9f9f9; border: 1px solid #e5e5e5; border-top: none; border-radius: 0 0 3px 3px;">
									<?php foreach ( $dependent_hubs[ $module_slug ] as $dep_module ) : ?>
										<?php
										$dep_slug             = sanitize_key( $dep_module['slug'] ?? '' );
										$dep_name             = esc_html( $dep_module['name'] ?? '' );
										$dep_installed        = ! empty( $dep_module['installed'] );
										$dep_enabled          = ! empty( $dep_module['enabled'] );
										$dep_url              = TIMU_Tab_Navigation::build_hub_url( $dep_slug );
										?>
										<div style="padding: 8px; display: flex; align-items: center; justify-content: space-between; gap: 8px; background: #fff; border: 1px solid #e0e0e0; border-radius: 2px; margin-bottom: 6px;">
											<div style="display: flex; align-items: center; gap: 8px; flex: 1;">
												<span class="dashicons dashicons-arrow-right-alt2" style="font-size: 16px; width: 16px; height: 16px; color: #999;"></span>
												<span style="font-size: 13px;">
													<a href="<?php echo esc_url( $dep_url ); ?>" style="text-decoration: none; color: inherit; font-weight: 500;"><?php echo $dep_name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
												</span>
											</div>
											<div style="display: flex; align-items: center; flex-shrink: 0;">
												<label class="timu-toggle-switch">
													<input type="checkbox" <?php checked( $dep_enabled ); ?> data-module="<?php echo esc_attr( $dep_slug ); ?>" data-type="hub" data-installed="<?php echo esc_attr( $dep_installed ? '1' : '0' ); ?>" data-plugin-base="<?php echo esc_attr( $dep_module['basename'] ?? '' ); ?>">
													<span class="timu-toggle-slider"></span>
												</label>
												<span class="timu-progress" aria-live="polite"><span class="spinner is-active" style="float:none"></span><span class="bar"><span class="fill"></span></span><span class="progress-label"><?php esc_html_e( 'Working…', 'plugin-wp-support-thisismyurl' ); ?></span></span>
											</div>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<style>
			.timu-module-card:hover {
				box-shadow: 0 2px 8px rgba(0,0,0,0.15);
			}
		</style>
		<?php
	}

	private static function widget_quick_actions(): void {
		$catalog         = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();
		$inactive_count  = count( array_filter( $catalog, fn( $m ) => empty( $m['status']['active'] ) && ! empty( $m['status']['installed'] ) ) );
		$vault_path      = wp_upload_dir()['basedir'] . '/vault';
		$vault_exists    = is_dir( $vault_path );
		$vault_writable  = $vault_exists && wp_is_writable( $vault_path );
		$encryption_key  = timu_core_get_vault_key();
		$health_url      = admin_url( 'site-health.php?tab=debug' );
		?>
		<div class="timu-widget-content timu-quick-actions">
			<div class="timu-actions-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
				<!-- Settings Action -->
				<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_tab_url( 'settings' ) ); ?>" class="button button-primary" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
					<span class="dashicons dashicons-admin-settings" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Settings', 'plugin-wp-support-thisismyurl' ); ?>
				</a>

				<!-- Site Health Action -->
				<a href="<?php echo esc_url( $health_url ); ?>" class="button" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
					<span class="dashicons dashicons-heart" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Site Health', 'plugin-wp-support-thisismyurl' ); ?>
				</a>

				<?php if ( $inactive_count > 0 ) : ?>
					<!-- Activate Modules Action -->
					<a href="<?php echo esc_url( admin_url( 'plugins.php?s=thisismyurl' ) ); ?>" class="button" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
						<span class="dashicons dashicons-update" style="margin-right: 5px;"></span>
						<?php echo esc_html( sprintf( __( 'Activate %d Module(s)', 'plugin-wp-support-thisismyurl' ), $inactive_count ) ); ?>
					</a>
				<?php endif; ?>

				<?php if ( ! $vault_exists || ! $vault_writable ) : ?>
					<!-- Fix Vault Action -->
					<button type="button" class="button button-secondary timu-fix-vault" data-action="fix_vault" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
						<span class="dashicons dashicons-warning" style="margin-right: 5px; color: #d63638;"></span>
						<?php esc_html_e( 'Fix Vault', 'plugin-wp-support-thisismyurl' ); ?>
					</button>
				<?php endif; ?>

				<?php if ( empty( $encryption_key ) ) : ?>
					<!-- Setup Encryption Action -->
					<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_tab_url( 'settings' ) . '&section=encryption' ); ?>" class="button button-secondary" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
						<span class="dashicons dashicons-lock" style="margin-right: 5px; color: #d63638;"></span>
						<?php esc_html_e( 'Setup Encryption', 'plugin-wp-support-thisismyurl' ); ?>
					</a>
				<?php endif; ?>

				<!-- Diagnostics Action -->
				<button type="button" class="button timu-run-diagnostics" data-action="run_diagnostics" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
					<span class="dashicons dashicons-search" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Run Diagnostics', 'plugin-wp-support-thisismyurl' ); ?>
				</button>

				<!-- Tools Action -->
				<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_tab_url( 'tools' ) ); ?>" class="button" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
					<span class="dashicons dashicons-admin-tools" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Tools', 'plugin-wp-support-thisismyurl' ); ?>
				</a>
			</div>
			<div id="timu-action-feedback" style="margin-top: 10px; display: none;"></div>
		</div>
		<?php
	}

	private static function widget_vault_status(): void {
		$vault_path      = wp_upload_dir()['basedir'] . '/vault';
		$vault_exists    = is_dir( $vault_path );
		$vault_writable  = $vault_exists && wp_is_writable( $vault_path );
		$encryption_key  = timu_core_get_vault_key();
		$has_encryption  = ! empty( $encryption_key );
		$key_source      = defined( 'TIMU_VAULT_KEY' ) && TIMU_VAULT_KEY ? 'wp-config.php' : 'Options';

		// Calculate vault size if it exists.
		$vault_size = 0;
		$file_count = 0;
		if ( $vault_exists ) {
			$iterator   = new \RecursiveIteratorIterator(
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
			$status_issues[] = __( 'Vault directory missing', 'plugin-wp-support-thisismyurl' );
		} elseif ( ! $vault_writable ) {
			$status_issues[] = __( 'Vault not writable', 'plugin-wp-support-thisismyurl' );
		}
		if ( ! $has_encryption ) {
			$status_issues[] = __( 'Encryption not configured', 'plugin-wp-support-thisismyurl' );
		}

		$is_healthy = empty( $status_issues );
		?>
		<div class="timu-widget-content timu-vault-status">
			<!-- Overall Status Badge -->
			<div style="text-align: center; padding: 15px 0; border-bottom: 1px solid #e5e5e5; margin-bottom: 15px;">
				<?php if ( $is_healthy ) : ?>
					<span class="dashicons dashicons-yes-alt" style="font-size: 48px; width: 48px; height: 48px; color: #00a32a;"></span>
					<div style="margin-top: 8px;">
						<strong style="color: #00a32a; font-size: 16px;"><?php esc_html_e( 'Vault Operational', 'plugin-wp-support-thisismyurl' ); ?></strong>
					</div>
				<?php else : ?>
					<span class="dashicons dashicons-warning" style="font-size: 48px; width: 48px; height: 48px; color: #d63638;"></span>
					<div style="margin-top: 8px;">
						<strong style="color: #d63638; font-size: 16px;"><?php esc_html_e( 'Vault Needs Attention', 'plugin-wp-support-thisismyurl' ); ?></strong>
					</div>
				<?php endif; ?>
			</div>

			<!-- Vault Stats Grid -->
			<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
				<div style="text-align: center; padding: 10px; background: #f6f7f7; border-radius: 4px;">
					<div style="font-size: 24px; font-weight: bold; color: #2271b1;"><?php echo esc_html( $file_count ); ?></div>
					<div style="font-size: 12px; color: #666; margin-top: 4px;"><?php esc_html_e( 'Files Stored', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
				<div style="text-align: center; padding: 10px; background: #f6f7f7; border-radius: 4px;">
					<div style="font-size: 24px; font-weight: bold; color: #2271b1;"><?php echo esc_html( $vault_size_formatted ); ?></div>
					<div style="font-size: 12px; color: #666; margin-top: 4px;"><?php esc_html_e( 'Total Size', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
			</div>

			<!-- Configuration Details -->
			<div style="font-size: 13px;">
				<div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
					<span><?php esc_html_e( 'Directory', 'plugin-wp-support-thisismyurl' ); ?>:</span>
					<strong><?php echo $vault_exists ? '<span style="color: #00a32a;">✓</span>' : '<span style="color: #d63638;">✗</span>'; ?></strong>
				</div>
				<div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
					<span><?php esc_html_e( 'Writable', 'plugin-wp-support-thisismyurl' ); ?>:</span>
					<strong><?php echo $vault_writable ? '<span style="color: #00a32a;">✓</span>' : '<span style="color: #d63638;">✗</span>'; ?></strong>
				</div>
				<div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
					<span><?php esc_html_e( 'Encryption', 'plugin-wp-support-thisismyurl' ); ?>:</span>
					<strong><?php echo $has_encryption ? '<span style="color: #00a32a;">✓</span>' : '<span style="color: #d63638;">✗</span>'; ?></strong>
				</div>
				<?php if ( $has_encryption ) : ?>
					<div style="display: flex; justify-content: space-between; padding: 8px 0;">
						<span><?php esc_html_e( 'Key Source', 'plugin-wp-support-thisismyurl' ); ?>:</span>
						<strong><?php echo esc_html( $key_source ); ?></strong>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $status_issues ) ) : ?>
				<!-- Issues List -->
				<div style="margin-top: 15px; padding: 10px; background: #fcf0f1; border-left: 4px solid #d63638; border-radius: 2px;">
					<strong style="color: #d63638;"><?php esc_html_e( 'Issues Found:', 'plugin-wp-support-thisismyurl' ); ?></strong>
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

	private static function widget_system_health(): void {
		if ( ! class_exists( '\\TIMU\\CoreSupport\\TIMU_Site_Health' ) ) {
			echo '<div class="timu-widget-content"><p><em>' . esc_html__( 'Health checks unavailable.', 'plugin-wp-support-thisismyurl' ) . '</em></p></div>';
			return;
		}

		// Get health results for core module.
		$health_data = \TIMU\CoreSupport\TIMU_Site_Health::get_health_check_results();
		self::render_health_widget( $health_data );
	}

	/**
	 * Render hierarchical health widget.
	 *
	 * @param array $health_data Health data array from get_health_check_results().
	 * @return void
	 */
	private static function render_health_widget( array $health_data ): void {
		$score        = $health_data['score'] ?? 0;
		$status       = $health_data['status'] ?? 'good';
		$results      = $health_data['results'] ?? array();
		$counts       = $health_data['counts'] ?? array( 'good' => 0, 'warning' => 0, 'critical' => 0 );
		$good_count   = $counts['good'] ?? 0;
		$warning_count = $counts['warning'] ?? 0;
		$critical_count = $counts['critical'] ?? 0;

		// Color coding.
		$health_color = 'critical' === $status ? '#d63638' : ( 'recommended' === $status ? '#dba617' : '#00a32a' );
		$health_label = 'critical' === $status ? __( 'Critical', 'plugin-wp-support-thisismyurl' ) : ( 'recommended' === $status ? __( 'Warning', 'plugin-wp-support-thisismyurl' ) : __( 'Good', 'plugin-wp-support-thisismyurl' ) );
		?>
		<div class="timu-widget-content timu-system-health">
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
					<div style="font-size: 11px; color: #666; margin-top: 2px;"><?php esc_html_e( 'Passed', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
				<div style="text-align: center; padding: 10px; background: #f6f7f7; border-radius: 4px;">
					<div style="font-size: 20px; font-weight: bold; color: #dba617;"><?php echo esc_html( $warning_count ); ?></div>
					<div style="font-size: 11px; color: #666; margin-top: 2px;"><?php esc_html_e( 'Warnings', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
				<div style="text-align: center; padding: 10px; background: #f6f7f7; border-radius: 4px;">
					<div style="font-size: 20px; font-weight: bold; color: #d63638;"><?php echo esc_html( $critical_count ); ?></div>
					<div style="font-size: 11px; color: #666; margin-top: 2px;"><?php esc_html_e( 'Critical', 'plugin-wp-support-thisismyurl' ); ?></div>
				</div>
			</div>

			<!-- Individual Test Results -->
			<div style="font-size: 13px;">
				<?php foreach ( $results as $test_id => $result ) : ?>
					<?php
					$icon_map = array(
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
					<?php esc_html_e( 'View Full Site Health Report →', 'plugin-wp-support-thisismyurl' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	private static function widget_events_and_news(): void {
		?>
		<div class="timu-widget-content">
			<div class="timu-events-feed">
				<p><em><?php esc_html_e( 'Loading latest events and news from Support Suite repositories...', 'plugin-wp-support-thisismyurl' ); ?></em></p>
				<ul class="timu-events-list" style="list-style: none; padding: 0; margin: 0;">
					<li style="padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
						<strong><?php esc_html_e( 'Release Updates', 'plugin-wp-support-thisismyurl' ); ?></strong>
						<br />
						<small><?php esc_html_e( 'Latest plugin releases and updates', 'plugin-wp-support-thisismyurl' ); ?></small>
					</li>
					<li style="padding: 8px 0; border-bottom: 1px solid #e5e5e5;">
						<strong><?php esc_html_e( 'GitHub Issues', 'plugin-wp-support-thisismyurl' ); ?></strong>
						<br />
						<small><?php esc_html_e( 'Recent discussions and feature requests', 'plugin-wp-support-thisismyurl' ); ?></small>
					</li>
					<li style="padding: 8px 0;">
						<strong><?php esc_html_e( 'Suite Announcements', 'plugin-wp-support-thisismyurl' ); ?></strong>
						<br />
						<small><?php esc_html_e( 'Important suite-wide updates', 'plugin-wp-support-thisismyurl' ); ?></small>
					</li>
				</ul>
				<p style="margin-top: 12px; text-align: center;">
					<a href="https://github.com/thisismyurl?tab=repositories" target="_blank" rel="noopener"><?php esc_html_e( 'Visit GitHub →', 'plugin-wp-support-thisismyurl' ); ?></a>
				</p>
			</div>
		</div>
		<?php
	}

	/* ====== HUB WIDGETS ====== */

	private static function widget_hub_overview( string $hub_id ): void {
		?>
		<div class="timu-widget-content">
			<p><?php echo esc_html( sprintf( __( 'Managing %s processing and distribution.', 'plugin-wp-support-thisismyurl' ), strtoupper( $hub_id ) ) ); ?></p>
		</div>
		<?php
	}

	private static function widget_active_spokes( string $hub_id ): void {
		$catalog = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();
		$spokes  = array_filter(
			$catalog,
			fn( $m ) => 'spoke' === ( $m['type'] ?? '' )
				&& ! empty( $m['status']['active'] )
				&& str_starts_with( $m['id'] ?? '', $hub_id )
		);
		?>
		<div class="timu-widget-content">
			<?php if ( empty( $spokes ) ) : ?>
				<p><?php esc_html_e( 'No spokes currently active for this hub.', 'plugin-wp-support-thisismyurl' ); ?></p>
			<?php else : ?>
				<ul class="timu-spoke-list">
					<?php foreach ( $spokes as $spoke ) : ?>
						<?php
						$spoke_id   = sanitize_key( str_replace( $hub_id . '-', '', $spoke['id'] ?? '' ) );
						$spoke_name = esc_html( $spoke['name'] ?? $spoke_id );
						$spoke_url  = TIMU_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id );
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
		<div class="timu-widget-content">
			<p><em><?php esc_html_e( 'Processing stats coming soon.', 'plugin-wp-support-thisismyurl' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_hub_quick_actions( string $hub_id ): void {
		?>
		<div class="timu-widget-content">
			<p>
				<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_hub_url( $hub_id, 'settings' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Hub Settings', 'plugin-wp-support-thisismyurl' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/* ====== SPOKE WIDGETS ====== */

	private static function widget_spoke_overview( string $hub_id, string $spoke_id ): void {
		?>
		<div class="timu-widget-content">
			<p><?php echo esc_html( sprintf( __( 'Managing %s format support.', 'plugin-wp-support-thisismyurl' ), strtoupper( $spoke_id ) ) ); ?></p>
		</div>
		<?php
	}

	private static function widget_spoke_features( string $spoke_id ): void {
		?>
		<div class="timu-widget-content">
			<ul class="timu-features-list">
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Format Detection', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Conversion Support', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Metadata Handling', 'plugin-wp-support-thisismyurl' ); ?></li>
			</ul>
		</div>
		<?php
	}

	private static function widget_spoke_stats( string $spoke_id ): void {
		?>
		<div class="timu-widget-content">
			<p><em><?php esc_html_e( 'Format-specific stats coming soon.', 'plugin-wp-support-thisismyurl' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_spoke_quick_actions( string $hub_id, string $spoke_id ): void {
		?>
		<div class="timu-widget-content">
			<p>
				<a href="<?php echo esc_url( TIMU_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id, 'settings' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Spoke Settings', 'plugin-wp-support-thisismyurl' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	private static function widget_media_overview(): void {
		?>
		<div class="timu-widget-content">
			<p><?php esc_html_e( 'Media Hub provides centralized media processing and management capabilities.', 'plugin-wp-support-thisismyurl' ); ?></p>
			<ul style="list-style: none; padding: 0;">
				<li><span class="dashicons dashicons-admin-media"></span> <?php esc_html_e( 'Batch processing for media files', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Media optimization policies', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-networking"></span> <?php esc_html_e( 'Multi-format support coordination', 'plugin-wp-support-thisismyurl' ); ?></li>
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
		if ( ! class_exists( '\\TIMU\\CoreSupport\\TIMU_Site_Health' ) ) {
			echo '<div class="timu-widget-content"><p><em>' . esc_html__( 'Health checks unavailable.', 'plugin-wp-support-thisismyurl' ) . '</em></p></div>';
			return;
		}

		// Get hierarchical health data.
		$health_hierarchy = \TIMU\CoreSupport\TIMU_Site_Health::get_hierarchical_health( $hub_id );
		$self_health = $health_hierarchy['self'] ?? array();
		$dependents = $health_hierarchy['dependents'] ?? array();
		?>
		<div class="timu-widget-content">
			<!-- Self Health -->
			<div style="margin-bottom: 20px;">
				<h4 style="margin: 0 0 10px 0;"><?php esc_html_e( 'Hub Health', 'plugin-wp-support-thisismyurl' ); ?></h4>
				<?php self::render_health_widget( $self_health ); ?>
			</div>

			<?php if ( ! empty( $dependents ) ) : ?>
				<!-- Dependent Health -->
				<div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e5e5e5;">
					<h4 style="margin: 0 0 10px 0;"><?php esc_html_e( 'Dependent Modules Health', 'plugin-wp-support-thisismyurl' ); ?></h4>
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
		$score = $health_data['score'] ?? 0;
		$status = $health_data['status'] ?? 'good';
		$counts = $health_data['counts'] ?? array( 'good' => 0, 'warning' => 0, 'critical' => 0 );
		$results = $health_data['results'] ?? array();

		$health_color = 'critical' === $status ? '#d63638' : ( 'recommended' === $status ? '#dba617' : '#00a32a' );
		$health_label = 'critical' === $status ? __( 'Critical', 'plugin-wp-support-thisismyurl' ) : ( 'recommended' === $status ? __( 'Warning', 'plugin-wp-support-thisismyurl' ) : __( 'Good', 'plugin-wp-support-thisismyurl' ) );
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
					<span><strong><?php echo esc_html( $counts['good'] ?? 0 ); ?></strong> <?php esc_html_e( 'Passed', 'plugin-wp-support-thisismyurl' ); ?></span>
					<?php if ( ( $counts['warning'] ?? 0 ) > 0 ) : ?>
						<span><strong style="color: #dba617;"><?php echo esc_html( $counts['warning'] ); ?></strong> <?php esc_html_e( 'Warnings', 'plugin-wp-support-thisismyurl' ); ?></span>
					<?php endif; ?>
					<?php if ( ( $counts['critical'] ?? 0 ) > 0 ) : ?>
						<span><strong style="color: #d63638;"><?php echo esc_html( $counts['critical'] ); ?></strong> <?php esc_html_e( 'Critical', 'plugin-wp-support-thisismyurl' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	private static function widget_vault_overview(): void {
		$vault_dir = wp_upload_dir()['basedir'] . '/.vault_' . get_option( 'timu_vault_dirname', 'default' );
		$vault_exists = is_dir( $vault_dir );
		$vault_writable = $vault_exists && wp_is_writable( $vault_dir );
		?>
		<div class="timu-widget-content">
			<p><?php esc_html_e( 'The Vault securely stores original media files with SHA-256 verification and automatic recovery.', 'plugin-wp-support-thisismyurl' ); ?></p>
			<ul style="list-style: none; padding: 0;">
				<li>
					<span class="dashicons dashicons-<?php echo $vault_writable ? 'yes' : 'no'; ?>" style="color: <?php echo $vault_writable ? '#00a32a' : '#d63638'; ?>;"></span>
					<?php echo $vault_writable ? esc_html__( 'Vault directory is writable', 'plugin-wp-support-thisismyurl' ) : esc_html__( 'Vault directory not writable', 'plugin-wp-support-thisismyurl' ); ?>
				</li>
				<li><span class="dashicons dashicons-lock"></span> <?php esc_html_e( 'Optional encryption support', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-image-rotate"></span> <?php esc_html_e( 'Automatic rehydration on 404', 'plugin-wp-support-thisismyurl' ); ?></li>
			</ul>
		</div>
		<?php
	}
}

/* @changelog Added TIMU_Dashboard_Widgets for tab-based dashboard rendering */
