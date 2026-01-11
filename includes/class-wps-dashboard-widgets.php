<?php
/**
 * Dashboard widget system for tab-based interface.
 *
 * @package wp_support_Support
 * @since 1.0.0
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Dashboard Widgets Manager
 * Mimics WordPress Core dashboard functionality.
 */
class WPS_Dashboard_Widgets {
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
						<?php foreach ( $col1_callbacks as $callback ) { if ( is_callable( $callback ) ) { call_user_func( $callback ); } } ?>
					</div>

					<div id="wps-dashboard-col-2" class="wps-dashboard-col">
						<?php foreach ( $col2_callbacks as $callback ) { if ( is_callable( $callback ) ) { call_user_func( $callback ); } } ?>
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
			esc_html__( 'Support Dashboard', 'plugin-wp-support-thisismyurl' ),
			array(
				array( __CLASS__, 'widget_suite_overview' ),
				array( __CLASS__, 'widget_active_hubs' ),
			),
			array(
				array( __CLASS__, 'widget_quick_actions' ),
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
		self::widget_modules();
	}

	private static function widget_health(): void {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Site_Health' ) ) {
			echo '<div class="wps-widget-content"><p><em>' . esc_html__( 'Health checks unavailable.', 'plugin-wp-support-thisismyurl' ) . '</em></p></div>';
			return;
		}

		try {
			// Get the current module context (if viewing a module dashboard).
			$context      = WPS_Tab_Navigation::get_current_context();
			$module       = ! empty( $context['hub'] ) ? $context['hub'] : null;
			$module_name  = '';

			// If we're on a module dashboard, get the module name.
			if ( ! empty( $module ) ) {
				$catalog     = \WPS\CoreSupport\WPS_Module_Registry::get_catalog_with_status();
				$module_slug = str_contains( $module, '-support-thisismyurl' ) ? $module : $module . '-support-thisismyurl';
				if ( isset( $catalog[ $module_slug ] ) ) {
					$module_name = $catalog[ $module_slug ]['name'] ?? ucfirst( $module );
				}
			}

			// Get health results filtered by module (null means all checks).
			$health_data = \WPS\CoreSupport\WPS_Site_Health::get_health_check_results( $module );

			// Render the health widget with module context.
			self::render_health_widget( $health_data, $module_name );
		} catch ( \Exception $e ) {
			echo '<div class="wps-widget-content"><p style="color: #d63638;"><strong>' . esc_html__( 'Error:', 'plugin-wp-support-thisismyurl' ) . '</strong> ' . esc_html( $e->getMessage() ) . '</p></div>';
			error_log( 'Health Widget Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine() );
		}
	}

	private static function widget_activity( ?string $module_filter = null ): void {
		// Use WPS_Activity_Logger if available.
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Activity_Logger' ) ) {
			$events = \WPS\CoreSupport\WPS_Activity_Logger::get_events( 100 );

			// Filter events by module if specified.
			if ( ! empty( $module_filter ) ) {
				$events = array_filter(
					$events,
					function( $event ) use ( $module_filter ) {
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
						<label for="WPS_activity_type_filter" style="display: inline; margin-right: 8px;">
							<?php esc_html_e( 'Filter:', 'plugin-wp-support-thisismyurl' ); ?>
						</label>
						<select id="WPS_activity_type_filter" name="activity_type" style="padding: 4px 8px; font-size: 12px;">
							<option value="">- <?php esc_html_e( 'All Activity', 'plugin-wp-support-thisismyurl' ); ?> -</option>
							<option value="module_activated">📌 <?php esc_html_e( 'Module Activated', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="module_deactivated">📴 <?php esc_html_e( 'Module Deactivated', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="settings_changed">⚙️ <?php esc_html_e( 'Settings Changed', 'plugin-wp-support-thisismyurl' ); ?></option>
							<option value="error_logged">⚠️ <?php esc_html_e( 'Error', 'plugin-wp-support-thisismyurl' ); ?></option>
						</select>
						<noscript><input type="submit" value="<?php esc_attr_e( 'Filter', 'plugin-wp-support-thisismyurl' ); ?>" /></noscript>
					</form>
				</div>
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
			<div class="wps-widget-content">
				<p><em><?php esc_html_e( 'Activity log integration coming soon.', 'plugin-wp-support-thisismyurl' ); ?></em></p>
			</div>
			<?php
		}
	}

	private static function widget_scheduled_tasks(): void {
		// Hide widget entirely since there's no scheduled tasks functionality yet.
		return;
	}

	private static function widget_modules(): void {
		$context = WPS_Tab_Navigation::get_current_context();
		$catalog = \WPS\CoreSupport\WPS_Module_Registry::get_catalog_with_status();
		$catalog = self::discover_local_module_entries( $catalog );

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
		}
		else if ( 'hub' === $context['level'] && ! empty( $context['hub'] ) ) {
			// On hub: show both dependent hubs and spokes under that hub.
			$hub_id = $context['hub'];

			// First, collect dependent hubs (child hubs that require this hub).
			foreach ( $catalog as $module ) {
				if ( 'hub' !== ( $module['type'] ?? '' ) ) {
					continue;
				}
				$requires = (string) ( $module['requires_hub'] ?? '' );
				if ( empty( $requires ) ) {
					continue;
				}
				// Normalize requires_hub value to short hub id when comparing.
				$requires_short = str_contains( $requires, '-support-thisismyurl' ) ? explode( '-support-thisismyurl', $requires )[0] : $requires;
				if ( $requires_short === $hub_id ) {
					$next_level_modules[] = $module;
				}
			}

			// Then, collect spokes for this hub.
			$hub_prefix = $hub_id . '-';
			$spokes     = array_filter(
				$catalog,
				function( $m ) use ( $hub_prefix, $hub_id ) {
					if ( 'spoke' !== ( $m['type'] ?? '' ) ) {
						return false;
					}
					// Prefer explicit requires_hub when available.
					$requires = (string) ( $m['requires_hub'] ?? '' );
					if ( ! empty( $requires ) ) {
						$requires_short = str_contains( $requires, '-support-thisismyurl' ) ? explode( '-support-thisismyurl', $requires )[0] : $requires;
						return $requires_short === $hub_id;
					}
					// Fallback: slug prefix convention (e.g., media-*, image-*).
					return str_starts_with( (string) ( $m['slug'] ?? '' ), $hub_prefix );
				}
			);

			// Merge spokes into modules list.
			$next_level_modules = array_merge( $next_level_modules, $spokes );
		}

		// If there are no modules to render at this level, skip output entirely.
		if ( empty( $next_level_modules ) ) {
			return;
		}

		?>
		<style>
			.wps-collapse-toggle {
				cursor: pointer;
				user-select: none;
				transition: background 0.15s;
			}
			.wps-collapse-toggle:hover {
				background: #f0f0f0;
			}
			.wps-collapse-toggle .dashicons {
				transition: transform 0.2s;
			}
			.wps-collapse-toggle.collapsed .dashicons {
				transform: rotate(-90deg);
			}
			.wps-collapse-content {
				transition: max-height 0.3s ease-out, opacity 0.2s;
				overflow: hidden;
			}
			.wps-collapse-content.collapsed {
				max-height: 0 !important;
				opacity: 0;
			}
		</style>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				document.querySelectorAll('.wps-collapse-toggle').forEach(function(toggle) {
					toggle.addEventListener('click', function(e) {
						e.preventDefault();
						var target = document.getElementById(this.getAttribute('data-target'));
						if (target) {
							this.classList.toggle('collapsed');
							target.classList.toggle('collapsed');
						}
					});
				});
				// Toggle handling for dashboard module switches with persistence and submenu updates
				const ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
				const nonce = '<?php echo esc_js( wp_create_nonce( 'WPS_module_actions' ) ); ?>';
				const scope = '<?php echo ( is_multisite() && is_network_admin() ) ? 'network' : 'site'; ?>';
				const storagePrefix = 'wpsToggleState:' + scope + ':';
				const pendingPrefix = 'wpsTogglePending:' + scope + ':';
				function saveToggleState(slug, checked){ try{ localStorage.setItem(storagePrefix + slug, checked ? '1':'0'); }catch(e){} }
				function getToggleState(slug){ try{ return localStorage.getItem(storagePrefix + slug); }catch(e){ return null; } }
				function markPending(slug, target){ try{ localStorage.setItem(pendingPrefix + slug, target ? '1':'0'); }catch(e){} }
				function clearPending(slug){ try{ localStorage.removeItem(pendingPrefix + slug); }catch(e){} }
				function getPending(slug){ try{ return localStorage.getItem(pendingPrefix + slug); }catch(e){ return null; } }
				async function postAction(action, data){
					const form = new URLSearchParams({ action, nonce });
					Object.entries(data).forEach(([k,v])=>{ if (v != null) form.append(k,v); });
					const res = await fetch(ajaxUrl, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: form.toString() });
					const json = await res.json();
					if (!json || json.success !== true) { throw new Error((json && json.data && json.data.message) ? json.data.message : 'Unexpected error'); }
					return json.data || {};
				}
				document.addEventListener('change', async function(e){
					const input = e.target;
					if (!input.matches('.wps-toggle-switch input')) return;
					const slug = input.getAttribute('data-module');
					const turningOn = input.checked;
					// optimistic persist
					saveToggleState(slug, turningOn);
					markPending(slug, turningOn);
					// reflect submenu immediately for hubs
					const type = input.getAttribute('data-type') || 'hub';
					const name = input.getAttribute('data-module-name') || '';
					if (type === 'hub') { ensureSubmenuFromSlug(slug, name, turningOn); }
					const pluginBase = input.getAttribute('data-plugin-base') || '';
					const card = input.closest('div[style*="border"]');
					const progress = card ? card.querySelector('.wps-progress') : null;
					input.disabled = true; if (progress) progress.style.display = 'inline-flex';
					const isPlugin = input.getAttribute('data-is-plugin') === '1';
					let action = 'WPS_module_toggle';
					const payload = { slug };
					if (isPlugin) {
						if (turningOn) { action = (input.getAttribute('data-installed') === '1') ? 'WPS_module_activate' : 'WPS_module_install'; }
						else { action = 'WPS_module_deactivate'; }
						if (pluginBase) { payload.plugin_base = pluginBase; }
					} else {
						payload.enabled = turningOn ? 1 : 0;
					}
					try {
						const data = await postAction(action, payload);
						if (action === 'WPS_module_install') { input.setAttribute('data-installed','1'); }
						clearPending(slug);
					} catch (err) {
						input.checked = !turningOn;
						console.error(err);
						alert(err.message);
						// rollback optimistic save but keep pending for recovery
						saveToggleState(slug, !turningOn);
					} finally {
						input.disabled = false; if (progress) progress.style.display = 'none';
					}
				});

				// Restore saved states on load (and submenu entries)
				document.querySelectorAll('.wps-toggle-switch input[data-module]').forEach(function(input){
					const slug = input.getAttribute('data-module');
					const saved = getToggleState(slug);
					if (saved === '1' || saved === '0') {
						input.checked = (saved === '1');
						if ((input.getAttribute('data-type')||'hub') === 'hub'){
							const name = input.getAttribute('data-module-name') || '';
							ensureSubmenuFromSlug(slug, name, (saved === '1'));
						}
					}
				});

				// Sweep existing menu to hide any disabled hubs rendered server-side.
				(function sweepSubmenusFromStorage(){
					for (var i = 0; i < localStorage.length; i++){
						var key = localStorage.key(i);
						if (!key || key.indexOf(storagePrefix) !== 0) continue;
						var slug = key.substring(storagePrefix.length);
						var val = localStorage.getItem(key);
						if (val === '0'){
							ensureSubmenuFromSlug(slug, '', false);
						}
					}
				})();

				// Cross-tab sync (and submenu updates)
				window.addEventListener('storage', function(ev){
					if (!ev || typeof ev.key !== 'string') return;
					if (ev.key.startsWith(storagePrefix)){
						const slug = ev.key.substring(storagePrefix.length);
						const val = ev.newValue;
						const input = document.querySelector('.wps-toggle-switch input[data-module="' + slug + '"]');
						if (input && (val === '1' || val === '0')){
							input.checked = (val === '1');
							if ((input.getAttribute('data-type')||'hub') === 'hub'){
								const name = input.getAttribute('data-module-name') || '';
								ensureSubmenuFromSlug(slug, name, (val === '1'));
							}
						}
					}
				});

				// Utility: add/remove submenu entry for a hub from its slug
				function ensureSubmenuFromSlug(slug, name, enabled){
					var moduleId = (slug || '').replace(/-support-thisismyurl$/,'');
					var label = name || moduleId || 'Module';
					ensureSubmenu(moduleId, label, enabled);
				}

				function ensureSubmenu(moduleId, label, enabled){
					var top = document.getElementById('toplevel_page_wp-support');
					if (!top){
						var link = document.querySelector('#adminmenu a.menu-top[href*="page=wp-support"]');
						if (link) { top = link.closest('li'); }
					}
					if (!top) return;
					var submenu = top.querySelector('ul.wp-submenu-wrap') || top.querySelector('ul.wp-submenu');
					if (!submenu){
						// If submenu container is missing, rely on server-rendered menu; do not create here.
						return;
					}
					var target = 'page=wp-support&module=' + encodeURIComponent(moduleId);
					var anchors = submenu.querySelectorAll('a[href*="' + target + '"]');
					if (!anchors || anchors.length === 0){
						// Nothing to toggle; avoid creating to prevent duplicates.
						return;
					}
					anchors.forEach(function(anchor){
						var li = anchor.closest('li');
						if (!li) return;
						li.style.display = enabled ? '' : 'none';
						anchor.style.display = enabled ? '' : 'none';
						anchor.setAttribute('aria-hidden', enabled ? 'false' : 'true');
					});
				}

				// Retry pending periodically
				async function processPending(){
					document.querySelectorAll('.wps-toggle-switch input[data-module]').forEach(async function(input){
						const slug = input.getAttribute('data-module');
						const p = getPending(slug);
						if (p !== '1' && p !== '0') return;
						const targetOn = (p === '1');
						const pluginBase = input.getAttribute('data-plugin-base') || '';
						const isPlugin = input.getAttribute('data-is-plugin') === '1';
						let action = 'WPS_module_toggle';
						const payload = { slug };
						if (isPlugin){
							action = targetOn ? 'WPS_module_activate' : 'WPS_module_deactivate';
							if (pluginBase) { payload.plugin_base = pluginBase; }
						} else {
							payload.enabled = targetOn ? 1 : 0;
						}
						try{
							await postAction(action, payload);
							clearPending(slug);
							saveToggleState(slug, targetOn);
							input.checked = targetOn;
							if ((input.getAttribute('data-type')||'hub') === 'hub'){
								const name = input.getAttribute('data-module-name') || '';
								ensureSubmenuFromSlug(slug, name, targetOn);
							}
						}catch(e){ /* keep pending */ }
					});
				}
				setInterval(processPending, 5000);
			});
		</script>
		<div class="wps-widget-content">
			<?php if ( empty( $next_level_modules ) ) : ?>
				<p><?php esc_html_e( 'No modules available at this level.', 'plugin-wp-support-thisismyurl' ); ?></p>
			<?php else : ?>				<style>
				.wps-toggle-switch { display: inline-block; position: relative; width: 44px; height: 22px; }
				.wps-toggle-switch input { opacity: 0; width: 0; height: 0; }
				.wps-toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .3s; border-radius: 22px; }
				.wps-toggle-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; }
				input:checked + .wps-toggle-slider { background-color: #00a32a; }
				input:focus + .wps-toggle-slider { box-shadow: 0 0 1px #00a32a; }
				input:checked + .wps-toggle-slider:before { transform: translateX(22px); }
				input:disabled + .wps-toggle-slider { opacity: 0.5; cursor: not-allowed; }
				.wps-progress { display: none; align-items: center; gap: 8px; margin-left: 8px; }
				.wps-progress .bar { width: 60px; height: 6px; background: #e5e5e5; border-radius: 6px; overflow: hidden; }
				.wps-progress .bar .fill { width: 50%; height: 100%; background: linear-gradient(90deg, #2271b1, #00a32a); animation: wpsProgress 1s infinite alternate ease-in-out; }
				@keyframes wpsProgress { from { width: 30%; } to { width: 90%; } }
				</style>				<div class="wps-modules-list" style="margin: 0;">
					<?php foreach ( $next_level_modules as $module ) : ?>
						<?php
						$module_slug      = sanitize_key( $module['slug'] ?? '' );
						$module_type      = $module['type'] ?? '';
						$module_name      = esc_html( $module['name'] ?? '' );
						$module_version   = esc_html( $module['version'] ?? '?.?.?' );
						$is_installed     = ! empty( $module['installed'] );
						$is_enabled       = ! empty( $module['enabled'] );
						$update_available = ! empty( $module['update_available'] );

						// Build navigation URL (hub vs spoke) and icon.
						$icon_class = 'dashicons-networking';
						$module_url = WPS_Tab_Navigation::build_hub_url( $module_slug );
						if ( 'spoke' === $module_type && ! empty( $context['hub'] ) ) {
							$spoke_id  = str_starts_with( $module_slug, $context['hub'] . '-' ) ? substr( $module_slug, strlen( $context['hub'] ) + 1 ) : $module_slug;
							$module_url = WPS_Tab_Navigation::build_spoke_url( $context['hub'], $spoke_id );
							$icon_class = 'dashicons-hammer';
						}
						?>
						<div style="padding: 12px; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 10px; background: #fff;">
							<div style="display: flex; align-items: center; justify-content: space-between; gap: 12px;">
								<div style="flex: 1;">
									<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
										<span class="dashicons <?php echo esc_attr( $icon_class ); ?>" style="font-size: 20px; width: 20px; height: 20px; color: #2271b1;"></span>
										<strong style="font-size: 14px;">
											<a href="<?php echo esc_url( $module_url ); ?>" style="text-decoration: none; color: inherit;"><?php echo $module_name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
										</strong>
									</div>
									<div style="font-size: 12px; color: #666; margin-left: 28px;">
										<?php echo esc_html( $module['description'] ?? '' ); ?>
									</div>
								</div>
								<div style="display: flex; align-items: center; flex-shrink: 0;">
									<label class="wps-toggle-switch">
										<input type="checkbox" <?php checked( $is_enabled ); ?> data-module="<?php echo esc_attr( $module_slug ); ?>" data-module-name="<?php echo esc_attr( $module_name ); ?>" data-type="<?php echo esc_attr( 'spoke' === $module_type ? 'spoke' : 'hub' ); ?>" data-installed="<?php echo esc_attr( $is_installed ? '1' : '0' ); ?>" data-plugin-base="<?php echo esc_attr( $module['basename'] ?? '' ); ?>" data-is-plugin="<?php echo esc_attr( ( ! empty( $module['basename'] ?? '' ) || ! empty( $module['download_url'] ?? '' ) ) ? '1' : '0' ); ?>">
										<span class="wps-toggle-slider"></span>
									</label>
									<span class="wps-progress" aria-live="polite"><span class="spinner is-active" style="float:none"></span><span class="bar"><span class="fill"></span></span><span class="progress-label"><?php esc_html_e( 'Working…', 'plugin-wp-support-thisismyurl' ); ?></span></span>
								</div>
							</div>

							<?php
							// Show dependent hubs nested under this parent hub.
							if ( ! empty( $dependent_hubs[ $module_slug ] ) ) :
								$collapse_id        = 'wps-deps-' . $module_slug;
								$parent_can_support = $is_installed && $is_enabled; // Parent must be installed AND active.
								?>
								<div class="wps-collapse-toggle" data-target="<?php echo esc_attr( $collapse_id ); ?>" style="margin: 12px 0 0 0; padding: 8px 12px; background: #f0f0f0; border: 1px solid #e0e0e0; border-radius: 3px;">
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
								<div id="<?php echo esc_attr( $collapse_id ); ?>" class="wps-collapse-content" style="margin: 0; padding: 12px; background: #f9f9f9; border: 1px solid #e5e5e5; border-top: none; border-radius: 0 0 3px 3px;">
									<?php foreach ( $dependent_hubs[ $module_slug ] as $dep_module ) : ?>
										<?php
										$dep_slug      = sanitize_key( $dep_module['slug'] ?? '' );
										$dep_name      = esc_html( $dep_module['name'] ?? '' );
										$dep_installed = ! empty( $dep_module['installed'] );
										$dep_enabled   = ! empty( $dep_module['enabled'] );
										$dep_url       = WPS_Tab_Navigation::build_hub_url( $dep_slug );
										?>
										<div style="padding: 8px; display: flex; align-items: center; justify-content: space-between; gap: 8px; background: #fff; border: 1px solid #e0e0e0; border-radius: 2px; margin-bottom: 6px;">
											<div style="display: flex; align-items: center; gap: 8px; flex: 1;">
												<span class="dashicons dashicons-arrow-right-alt2" style="font-size: 16px; width: 16px; height: 16px; color: #999;"></span>
												<span style="font-size: 13px;">
													<a href="<?php echo esc_url( $dep_url ); ?>" style="text-decoration: none; color: inherit; font-weight: 500;"><?php echo $dep_name; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
												</span>
											</div>
											<div style="display: flex; align-items: center; flex-shrink: 0;">
												<label class="wps-toggle-switch">
													<input type="checkbox" <?php checked( $dep_enabled ); ?> data-module="<?php echo esc_attr( $dep_slug ); ?>" data-module-name="<?php echo esc_attr( $dep_name ); ?>" data-type="hub" data-installed="<?php echo esc_attr( $dep_installed ? '1' : '0' ); ?>" data-plugin-base="<?php echo esc_attr( $dep_module['basename'] ?? '' ); ?>" data-is-plugin="<?php echo esc_attr( ( ! empty( $dep_module['basename'] ?? '' ) || ! empty( $dep_module['download_url'] ?? '' ) ) ? '1' : '0' ); ?>">
													<span class="wps-toggle-slider"></span>
												</label>
												<span class="wps-progress" aria-live="polite"><span class="spinner is-active" style="float:none"></span><span class="bar"><span class="fill"></span></span><span class="progress-label"><?php esc_html_e( 'Working…', 'plugin-wp-support-thisismyurl' ); ?></span></span>
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
			.wps-module-card:hover {
				box-shadow: 0 2px 8px rgba(0,0,0,0.15);
			}
		</style>
		<?php
	}

	private static function widget_quick_actions(): void {
		$catalog         = \WPS\CoreSupport\WPS_Module_Registry::get_catalog_with_status();
		$inactive_count  = count( array_filter( $catalog, fn( $m ) => empty( $m['status']['active'] ) && ! empty( $m['status']['installed'] ) ) );
		$vault_path      = wp_upload_dir()['basedir'] . '/vault';
		$vault_exists    = is_dir( $vault_path );
		$vault_writable  = $vault_exists && wp_is_writable( $vault_path );
		$encryption_key  = wp_support_get_vault_key();
		$health_url      = admin_url( 'site-health.php?tab=debug' );
		?>
		<div class="wps-widget-content wps-quick-actions">
			<div class="wps-actions-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
				<!-- Settings Action -->
				<a href="<?php echo esc_url( WPS_Tab_Navigation::build_tab_url( 'settings' ) ); ?>" class="button button-primary" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
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

				<?php if ( empty( $encryption_key ) ) : ?>
					<!-- Setup Encryption Action -->
					<a href="<?php echo esc_url( WPS_Tab_Navigation::build_tab_url( 'settings' ) . '&section=encryption' ); ?>" class="button button-secondary" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
						<span class="dashicons dashicons-lock" style="margin-right: 5px; color: #d63638;"></span>
						<?php esc_html_e( 'Setup Encryption', 'plugin-wp-support-thisismyurl' ); ?>
					</a>
				<?php endif; ?>

				<!-- Get Help Action -->
				<a href="https://thisismyurl.com/?source=plugin-wp-support-thisismyurl" target="_blank" rel="noopener noreferrer" class="button" style="display: flex; align-items: center; justify-content: center; padding: 10px; text-align: center;">
					<span class="dashicons dashicons-editor-help" style="margin-right: 5px;"></span>
					<?php esc_html_e( 'Get Help', 'plugin-wp-support-thisismyurl' ); ?>
				</a>
			</div>
			<div id="wps-action-feedback" style="margin-top: 10px; display: none;"></div>
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
			'hub'   => trailingslashit( wp_support_PATH ) . 'modules/hubs',
			'spoke' => trailingslashit( wp_support_PATH ) . 'modules/spokes',
		);

		foreach ( $roots as $type => $root ) {
			if ( ! is_dir( $root ) ) {
				continue;
			}

			$items = @scandir( $root ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if ( false === $items ) {
				continue;
			}

			foreach ( $items as $item ) {
				if ( '.' === $item || '..' === $item ) {
					continue;
				}
				$module_dir = $root . '/' . $item;
				$entry_file = $module_dir . '/module.php';
				if ( ! is_dir( $module_dir ) || ! file_exists( $entry_file ) ) {
					continue;
				}

				$slug        = $item;
				$requires_hub = '';
				$name        = ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );

				$contents = @file_get_contents( $entry_file ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				if ( $contents ) {
					if ( preg_match( "/'slug'\s*=>\s*'([^']+)'/", $contents, $m ) ) {
						$slug = sanitize_key( $m[1] );
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
					'slug'          => $slug,
					'type'          => $type,
					'name'          => $name,
					'description'   => __( 'Discovered local module', 'plugin-wp-support-thisismyurl' ),
					'installed'     => true,
					'enabled'       => false,
					'update_available' => false,
					'basename'      => $item . '/module.php',
					'path'          => trailingslashit( $module_dir ),
					'requires_hub'  => $requires_hub,
				);
			}
		}

		// Merge placeholders from modules/missing-modules.json so the widget lists not-yet-downloaded modules.
		$missing_file = trailingslashit( wp_support_PATH ) . 'modules/missing-modules.json';
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

					$name        = sanitize_text_field( $entry['name'] ?? $slug );
					$description = sanitize_text_field( $entry['description'] ?? '' );
					$requires_hub = sanitize_key( $entry['requires_hub'] ?? '' );
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
		$vault_dirname = get_option( 'WPS_vault_dirname' );
		$vault_path    = ! empty( $vault_dirname ) ? $upload_dir['basedir'] . '/' . $vault_dirname : '';

		$vault_exists   = ! empty( $vault_path ) && is_dir( $vault_path );
		$vault_writable = $vault_exists && wp_is_writable( $vault_path );
		$encryption_key = wp_support_get_vault_key();
		$has_encryption = ! empty( $encryption_key );
		$key_source     = defined( 'WPS_VAULT_KEY' ) && WPS_VAULT_KEY ? 'wp-config.php' : 'Options';

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
		<div class="wps-widget-content wps-vault-status">
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
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Site_Health' ) ) {
			echo '<div class="wps-widget-content"><p><em>' . esc_html__( 'Health checks unavailable.', 'plugin-wp-support-thisismyurl' ) . '</em></p></div>';
			return;
		}

		// Get health results for core module (system-wide health).
		$health_data = \WPS\CoreSupport\WPS_Site_Health::get_health_check_results();
		self::render_health_widget( $health_data, '' );
	}

	/**
	 * Render hierarchical health widget.
	 *
	 * @param array  $health_data Health data array from get_health_check_results().
	 * @param string $module_name Optional module name for context. Empty string = system-wide health.
	 * @return void
	 */
	private static function render_health_widget( array $health_data, string $module_name = '' ): void {
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
		<div class="wps-widget-content">
			<div class="wps-events-feed">
				<p><em><?php esc_html_e( 'Loading latest events and news from Support Suite repositories...', 'plugin-wp-support-thisismyurl' ); ?></em></p>
				<ul class="wps-events-list" style="list-style: none; padding: 0; margin: 0;">
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
		<div class="wps-widget-content">
			<p><?php echo esc_html( sprintf( __( 'Managing %s processing and distribution.', 'plugin-wp-support-thisismyurl' ), strtoupper( $hub_id ) ) ); ?></p>
		</div>
		<?php
	}

	private static function widget_active_spokes( string $hub_id ): void {
		$catalog = \WPS\CoreSupport\WPS_Module_Registry::get_catalog_with_status();
		$spokes  = array_filter(
			$catalog,
			fn( $m ) => 'spoke' === ( $m['type'] ?? '' )
				&& ! empty( $m['status']['active'] )
				&& str_starts_with( $m['id'] ?? '', $hub_id )
		);
		?>
		<div class="wps-widget-content">
			<?php if ( empty( $spokes ) ) : ?>
				<p><?php esc_html_e( 'No spokes currently active for this hub.', 'plugin-wp-support-thisismyurl' ); ?></p>
			<?php else : ?>
				<ul class="wps-spoke-list">
					<?php foreach ( $spokes as $spoke ) : ?>
						<?php
						$spoke_id   = sanitize_key( str_replace( $hub_id . '-', '', $spoke['id'] ?? '' ) );
						$spoke_name = esc_html( $spoke['name'] ?? $spoke_id );
						$spoke_url  = WPS_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id );
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
			<p><em><?php esc_html_e( 'Processing stats coming soon.', 'plugin-wp-support-thisismyurl' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_hub_quick_actions( string $hub_id ): void {
		?>
		<div class="wps-widget-content">
			<p>
				<a href="<?php echo esc_url( WPS_Tab_Navigation::build_hub_url( $hub_id, 'settings' ) ); ?>" class="button button-primary">
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
		<div class="wps-widget-content">
			<p><?php echo esc_html( sprintf( __( 'Managing %s format support.', 'plugin-wp-support-thisismyurl' ), strtoupper( $spoke_id ) ) ); ?></p>
		</div>
		<?php
	}

	private static function widget_spoke_features( string $spoke_id ): void {
		?>
		<div class="wps-widget-content">
			<ul class="wps-features-list">
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Format Detection', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Conversion Support', 'plugin-wp-support-thisismyurl' ); ?></li>
				<li><span class="dashicons dashicons-yes"></span> <?php esc_html_e( 'Metadata Handling', 'plugin-wp-support-thisismyurl' ); ?></li>
			</ul>
		</div>
		<?php
	}

	private static function widget_spoke_stats( string $spoke_id ): void {
		?>
		<div class="wps-widget-content">
			<p><em><?php esc_html_e( 'Format-specific stats coming soon.', 'plugin-wp-support-thisismyurl' ); ?></em></p>
		</div>
		<?php
	}

	private static function widget_spoke_quick_actions( string $hub_id, string $spoke_id ): void {
		?>
		<div class="wps-widget-content">
			<p>
				<a href="<?php echo esc_url( WPS_Tab_Navigation::build_spoke_url( $hub_id, $spoke_id, 'settings' ) ); ?>" class="button button-primary">
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Spoke Settings', 'plugin-wp-support-thisismyurl' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	private static function widget_media_overview(): void {
		?>
		<div class="wps-widget-content">
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
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Site_Health' ) ) {
			echo '<div class="wps-widget-content"><p><em>' . esc_html__( 'Health checks unavailable.', 'plugin-wp-support-thisismyurl' ) . '</em></p></div>';
			return;
		}

		// Get hierarchical health data.
		$health_hierarchy = \WPS\CoreSupport\WPS_Site_Health::get_hierarchical_health( $hub_id );
		$self_health = $health_hierarchy['self'] ?? array();
		$dependents = $health_hierarchy['dependents'] ?? array();
		?>
		<div class="wps-widget-content">
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
		$upload_dir    = wp_upload_dir();
		$vault_dirname = get_option( 'WPS_vault_dirname' );
		$vault_dir     = ! empty( $vault_dirname ) ? $upload_dir['basedir'] . '/' . $vault_dirname : '';
		$vault_exists  = ! empty( $vault_dir ) && is_dir( $vault_dir );
		$vault_writable = $vault_exists && wp_is_writable( $vault_dir );
		?>
		<div class="wps-widget-content">
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
				<a href="#" class="wps-metabox-toggle" aria-label="<?php esc_attr_e( 'Toggle panel', 'plugin-wp-support-thisismyurl' ); ?>">
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
		self::render_custom_metabox( 'WPS_quick_actions', __( 'WP Support Quick Actions', 'plugin-wp-support-thisismyurl' ), array( __CLASS__, 'widget_quick_actions' ) );
	}

	public static function render_metabox_modules_custom(): void {
		self::render_custom_metabox( 'WPS_modules', __( 'WP Support Modules', 'plugin-wp-support-thisismyurl' ), array( __CLASS__, 'widget_modules' ) );
	}

	public static function render_metabox_activity_custom(): void {
		self::render_custom_metabox( 'WPS_activity', __( 'WP Support Activity', 'plugin-wp-support-thisismyurl' ), array( __CLASS__, 'widget_activity' ) );
	}

	public static function render_metabox_events_and_news_custom(): void {
		self::render_custom_metabox( 'WPS_events_and_news', __( 'Events & News', 'plugin-wp-support-thisismyurl' ), array( __CLASS__, 'widget_events_and_news' ) );
	}

	public static function render_metabox_vault_status_custom(): void {
		self::render_custom_metabox( 'WPS_vault_status', __( 'Vault Status', 'plugin-wp-support-thisismyurl' ), array( __CLASS__, 'widget_vault_status' ) );
	}

}

/* @changelog */



