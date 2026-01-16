<?php
/**
 * Modules view
 *
 * Renders the modules table with hubs and spokes, including install/activate/update actions.
 * Assumes controller passes `$hub_modules` and `$spoke_modules` arrays and computes activation states.
 *
 * @package core-wpshadow
 */

defined( 'ABSPATH' ) || exit;
?>
<style>
.wps-toggle-switch {
	display: inline-block;
	position: relative;
	width: 50px;
	height: 24px;
}
.wps-toggle-switch input {
	opacity: 0;
	width: 0;
	height: 0;
}
.wps-toggle-slider {
	position: absolute;
	cursor: pointer;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background-color: #ccc;
	transition: .3s;
	border-radius: 24px;
}
.wps-toggle-slider:before {
	position: absolute;
	content: "";
	height: 18px;
	width: 18px;
	left: 3px;
	bottom: 3px;
	background-color: white;
	transition: .3s;
	border-radius: 50%;
}
input:checked + .wps-toggle-slider {
	background-color: #00a32a;
}
input:focus + .wps-toggle-slider {
	box-shadow: 0 0 1px #00a32a;
}
input:checked + .wps-toggle-slider:before {
	transform: translateX(26px);
}
input:disabled + .wps-toggle-slider {
	opacity: 0.5;
	cursor: not-allowed;
}
/* Progress bar */
.wps-progress { display: none; align-items: center; gap: 8px; margin-left: 8px; }
.wps-progress .bar { width: 80px; height: 6px; background: #e5e5e5; border-radius: 6px; overflow: hidden; }
.wps-progress .bar .fill { width: 50%; height: 100%; background: linear-gradient(90deg, #2271b1, #00a32a); animation: wpsProgress 1s infinite alternate ease-in-out; }
@keyframes wpsProgress { from { width: 30%; } to { width: 90%; } }
</style>
<div class="wrap wps-modules-view" id="wps-modules-main" role="main">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Modules', 'plugin-wpshadow' ); ?></h1>
	<span class="dashicons dashicons-editor-help" aria-label="<?php esc_attr_e( 'Modules help', 'plugin-wpshadow' ); ?>" title="<?php esc_attr_e( 'Install or update modules from the catalog; activate/deactivate per site or network. Network Active items can only be deactivated from Network Admin.', 'plugin-wpshadow' ); ?>">
		<span class="screen-reader-text"><?php esc_html_e( 'Install or update modules from the catalog; activate/deactivate per site or network. Network Active items can only be deactivated from Network Admin.', 'plugin-wpshadow' ); ?></span>
	</span>
	<?php $override_allowed = class_exists( 'wpshadow_Vault' ) ? WPSHADOW_Vault::site_override_allowed() : true; ?>
	<div class="wps-dashboard-stats">
		<?php
		// Fallback counts when controller variables are missing.
		$total_fallback   = (int) ( ( isset( $hub_modules ) ? count( $hub_modules ) : 0 ) + ( isset( $spoke_modules ) ? count( $spoke_modules ) : 0 ) );
		$updates_fallback = 0;
		if ( ! empty( $hub_modules ) && is_array( $hub_modules ) ) {
			foreach ( $hub_modules as $m ) {
				if ( ! empty( $m['update_available'] ) ) {
					++$updates_fallback;
				}
			}
		}
		if ( ! empty( $spoke_modules ) && is_array( $spoke_modules ) ) {
			foreach ( $spoke_modules as $m ) {
				if ( ! empty( $m['update_available'] ) ) {
					++$updates_fallback;
				}
			}
		}
		?>
		<div class="wps-stat-card" role="group" aria-label="<?php esc_attr_e( 'Total modules', 'plugin-wpshadow' ); ?>">
			<div class="wps-stat-icon">
				<span class="dashicons dashicons-admin-plugins"></span>
			</div>
			<div class="wps-stat-content">
				<div class="wps-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $total_count ?? $total_fallback ) ) ); ?></div>
				<div class="wps-stat-label"><?php esc_html_e( 'Total', 'plugin-wpshadow' ); ?></div>
			</div>
		</div>

			<script>
			(function(){
				const ajaxUrl = '<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>';
				const nonce = '<?php echo esc_js( wp_create_nonce( 'wpshadow_module_actions' ) ); ?>';
				const scope = '<?php echo ( is_multisite() && is_network_admin() ) ? 'network' : 'site'; ?>';

				const storagePrefix = 'wpsToggleState:' + scope + ':';
				function saveToggleState(slug, checked){
					try{ localStorage.setItem(storagePrefix + slug, checked ? '1' : '0'); }catch(e){ /* no-op */ }
				}
				function getToggleState(slug){
					try{ return localStorage.getItem(storagePrefix + slug); }catch(e){ return null; }
				}

				// Pending queue for recovery across tabs
				const pendingPrefix = 'wpsTogglePending:' + scope + ':';
				function markPending(slug, target){
					try{ localStorage.setItem(pendingPrefix + slug, target ? '1' : '0'); }catch(e){ /* no-op */ }
				}
				function clearPending(slug){
					try{ localStorage.removeItem(pendingPrefix + slug); }catch(e){ /* no-op */ }
				}
				function getPending(slug){
					try{ return localStorage.getItem(pendingPrefix + slug); }catch(e){ return null; }
				}

				function setWorking(toggle, working) {
					const row = toggle.closest('tr');
					const progress = row ? row.querySelector('.wps-progress') : null;
					toggle.disabled = working;
					if (progress) progress.style.display = working ? 'inline-flex' : 'none';
				}

				async function postAction(action, data){
					const form = new URLSearchParams();
					form.append('action', action);
					form.append('nonce', nonce);
					Object.entries(data).forEach(([k,v])=>{ if (v != null) form.append(k,v); });
					const res = await fetch(ajaxUrl, { method: 'POST', headers: { 'Content-Type':'application/x-www-form-urlencoded' }, body: form.toString() });
					const json = await res.json();
					if (!json || json.success !== true) {
						const msg = (json && json.data && json.data.message) ? json.data.message : 'Unexpected error';
						throw new Error(msg);
					}
					return json.data;
				}

				function handleToggleChange(e){
					const input = e.target;
					if (!input || !input.matches('.wps-toggle-switch input')) return;
					const slug = input.getAttribute('data-module');
					const turningOn = input.checked;
					// Optimistic: persist immediately
					saveToggleState(slug, turningOn);
					markPending(slug, turningOn);
					// Reflect submenu immediately for hubs
					const type = input.getAttribute('data-type') || 'hub';
					const name = input.getAttribute('data-module-name') || '';
					if (type === 'hub') { ensureSubmenuFromSlug(slug, name, turningOn); }
					const pluginBase = input.getAttribute('data-plugin-base') || '';
					const pluginExists = input.getAttribute('data-plugin-exists') === '1';
					const downloadable = input.getAttribute('data-downloadable') === '1';
					setWorking(input, true);
					let action = 'wpshadow_module_toggle';
					const payload = { slug };
					if (turningOn) {
						if (downloadable && !pluginExists) {
							action = 'wpshadow_module_install';
						} else if (pluginExists) {
							action = 'wpshadow_module_activate';
							payload.plugin_base = pluginBase;
						} else {
							payload.enabled = 1;
						}
					} else {
						if (pluginExists) {
							action = 'wpshadow_module_deactivate';
							payload.plugin_base = pluginBase;
						} else {
							payload.enabled = 0;
						}
					}
					postAction(action, payload)
						.then((data)=>{
							input.setAttribute('data-installed','1');
							if (data && data.plugin_base) {
								input.setAttribute('data-plugin-exists','1');
								input.setAttribute('data-plugin-base', data.plugin_base);
							}
							clearPending(slug);
							// No reload to avoid flicker; UI and submenu are handled client-side.
						})
						.catch(err=>{
							// revert toggle on failure
							input.checked = !turningOn;
							saveToggleState(slug, !turningOn);
							// keep pending so another tab or retry can recover
							console.error(err);
							window.alert(err.message);
						})
						.finally(()=> setWorking(input, false));
				}

				document.addEventListener('change', handleToggleChange, true);

				// Cross-tab sync: reflect changes from other tabs
				window.addEventListener('storage', (ev)=>{
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

				// Periodically process pending queue (retry server updates)
				async function processPending(){
					const toggles = document.querySelectorAll('.wps-toggle-switch input[data-module]');
					for (const input of toggles){
						const slug = input.getAttribute('data-module');
						const pending = getPending(slug);
						if (pending !== '1' && pending !== '0') continue;
						const targetOn = (pending === '1');
						const pluginBase = input.getAttribute('data-plugin-base') || '';
						const pluginExists = input.getAttribute('data-plugin-exists') === '1';
						const downloadable = input.getAttribute('data-downloadable') === '1';
						let action = 'wpshadow_module_toggle';
						const payload = { slug };
						if (targetOn){
							if (downloadable && !pluginExists){ action = 'wpshadow_module_install'; }
							else if (pluginExists){ action = 'wpshadow_module_activate'; payload.plugin_base = pluginBase; }
							else { payload.enabled = 1; }
						} else {
							if (pluginExists){ action = 'wpshadow_module_deactivate'; payload.plugin_base = pluginBase; }
							else { payload.enabled = 0; }
						}

						try{
							const res = await postAction(action, payload);
							clearPending(slug);
							saveToggleState(slug, targetOn);
							// reflect UI state
							input.checked = targetOn;
							if ((input.getAttribute('data-type')||'hub') === 'hub'){
								const name = input.getAttribute('data-module-name') || '';
								ensureSubmenuFromSlug(slug, name, targetOn);
							}
						}catch(err){
							// keep pending; will retry next interval
						}
					}
				}
				setInterval(processPending, 5000);

				// Restore saved toggle states on load.
				function restoreSavedStates(){
					const toggles = document.querySelectorAll('.wps-toggle-switch input[data-module]');
					toggles.forEach((input)=>{
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
				}

				// Sweep existing menu to hide any disabled hubs rendered server-side.
				function sweepSubmenusFromStorage(){
					for (let i = 0; i < localStorage.length; i++){
						const key = localStorage.key(i);
						if (!key || !key.startsWith(storagePrefix)) continue;
						const slug = key.substring(storagePrefix.length);
						const val = localStorage.getItem(key);
						if (val === '0'){
							ensureSubmenuFromSlug(slug, '', false);
						}
					}
				}

				// Utility: add/remove submenu entry for a hub from its slug
				function ensureSubmenuFromSlug(slug, name, enabled){
					const moduleId = (slug || '').replace(/-wpshadow$/,'');
					const label = name || moduleId || 'Module';
					ensureSubmenu(moduleId, label, enabled);
				}

				function ensureSubmenu(moduleId, label, enabled){
					var top = document.getElementById('toplevel_page_wpshadow');
					if (!top){
						var link = document.querySelector('#adminmenu a.menu-top[href*="page=wpshadow"]');
						if (link) { top = link.closest('li'); }
					}
					if (!top) return;
					var submenu = top.querySelector('ul.wp-submenu-wrap') || top.querySelector('ul.wp-submenu');
					if (!submenu){
						// If submenu container is missing, defer to server-rendered menu; avoid creating.
						return;
					}
					var target = 'page=wpshadow&module=' + encodeURIComponent(moduleId);
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

				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', function(){ restoreSavedStates(); sweepSubmenusFromStorage(); });
				} else {
					restoreSavedStates();
					sweepSubmenusFromStorage();
				}
			})();
			</script>

		<div class="wps-stat-card" role="group" aria-label="<?php esc_attr_e( 'Enabled modules', 'plugin-wpshadow' ); ?>">
			<div class="wps-stat-icon wps-stat-enabled">
				<span class="dashicons dashicons-yes-alt"></span>
			</div>
			<div class="wps-stat-content">
				<div class="wps-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $enabled_count ?? 0 ) ) ); ?></div>
				<div class="wps-stat-label"><?php esc_html_e( 'Enabled', 'plugin-wpshadow' ); ?></div>
			</div>
		</div>

		<div class="wps-stat-card" role="group" aria-label="<?php esc_attr_e( 'Available modules', 'plugin-wpshadow' ); ?>">
			<div class="wps-stat-icon wps-stat-available">
				<span class="dashicons dashicons-plus-alt"></span>
			</div>
			<div class="wps-stat-content">
				<div class="wps-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $available_count ?? 0 ) ) ); ?></div>
				<div class="wps-stat-label"><?php esc_html_e( 'Available', 'plugin-wpshadow' ); ?></div>
			</div>
		</div>

		<div class="wps-stat-card" role="group" aria-label="<?php esc_attr_e( 'Updates available', 'plugin-wpshadow' ); ?>">
			<div class="wps-stat-icon wps-stat-update">
				<span class="dashicons dashicons-update"></span>
			</div>
			<div class="wps-stat-content">
				<div class="wps-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $updates_count ?? $updates_fallback ) ) ); ?></div>
				<div class="wps-stat-label"><?php esc_html_e( 'Updates', 'plugin-wpshadow' ); ?></div>
			</div>
		</div>

		<div class="wps-stat-card" role="group" aria-label="<?php esc_attr_e( 'Hubs', 'plugin-wpshadow' ); ?>">
			<div class="wps-stat-icon wps-stat-hub">
				<span class="dashicons dashicons-networking"></span>
			</div>
			<div class="wps-stat-content">
				<div class="wps-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $hubs_count ?? ( isset( $hub_modules ) ? count( $hub_modules ) : 0 ) ) ) ); ?></div>
				<div class="wps-stat-label"><?php esc_html_e( 'Hubs', 'plugin-wpshadow' ); ?></div>
			</div>
		</div>

		<div class="wps-stat-card" role="group" aria-label="<?php esc_attr_e( 'Spokes', 'plugin-wpshadow' ); ?>">
			<div class="wps-stat-icon wps-stat-spoke">
				<span class="dashicons dashicons-admin-tools"></span>
			</div>
			<div class="wps-stat-content">
				<div class="wps-stat-value"><?php echo esc_html( number_format_i18n( (int) ( $spokes_count ?? ( isset( $spoke_modules ) ? count( $spoke_modules ) : 0 ) ) ) ); ?></div>
				<div class="wps-stat-label"><?php esc_html_e( 'Spokes', 'plugin-wpshadow' ); ?></div>
			</div>
		</div>
	</div>

	<?php
	// Group spokes under Image Support hub by convention.
	$groups = array();
	foreach ( $hub_modules as $hub ) {
		$groups[ $hub['slug'] ] = array(
			'hub'    => $hub,
			'spokes' => array(),
		);
	}
	foreach ( $spoke_modules as $spoke ) {
		$parent = 'image-wpshadow';
		if ( isset( $groups[ $parent ] ) ) {
			$groups[ $parent ]['spokes'][] = $spoke;
		} else {
			// Fallback: attach to first hub if Image Support missing.
			$first = array_key_first( $groups );
			if ( $first ) {
				$groups[ $first ]['spokes'][] = $spoke;
			}
		}
	}
	?>

	<div class="wps-modules-grid">
		<?php if ( empty( $groups ) ) : ?>
			<div class="wps-no-modules">
				<span class="dashicons dashicons-info"></span>
				<p><?php esc_html_e( 'No modules found.', 'plugin-wpshadow' ); ?></p>
			</div>
		<?php else : ?>
			<div class="wps-table-responsive">
				<table class="widefat fixed striped wps-modules-table">
					<caption class="screen-reader-text"><?php esc_html_e( 'Module catalog with status, version, and author information.', 'plugin-wpshadow' ); ?></caption>
					<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Module', 'plugin-wpshadow' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Requires', 'plugin-wpshadow' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Version', 'plugin-wpshadow' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Last Updated', 'plugin-wpshadow' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Author', 'plugin-wpshadow' ); ?></th>			<th scope="col"><?php esc_html_e( 'Active', 'plugin-wpshadow' ); ?></th>					</tr>
					</thead>
					<tbody>
				<?php foreach ( $groups as $group ) : ?>
					<?php
					$module = $group['hub'];
					$slug   = $module['slug'];
					// Real-time plugin state detection.
					$plugin_base       = $module['basename'] ?? $slug . '/' . $slug . '.php';
					$plugin_base_path  = WP_PLUGIN_DIR . '/' . ltrim( $plugin_base, '/\\' );
					$plugin_exists     = file_exists( $plugin_base_path );
					$module_file_path  = (string) ( $module['file'] ?? '' );
					$module_file_found = ! empty( $module_file_path ) && file_exists( $module_file_path );
					$installed         = ! empty( $module['installed'] ) || $plugin_exists || $module_file_found;
					$is_network_active = is_multisite() && is_plugin_active_for_network( $plugin_base );
					$registry_enabled  = ! empty( $module['enabled'] );
					$is_enabled        = $plugin_exists ? ( is_plugin_active( $plugin_base ) || $is_network_active ) : $registry_enabled;
					$update_available  = ! empty( $module['update_available'] );
					$type_class        = 'wps-type-hub';
					$status_class      = $installed ? ( $is_enabled ? 'wps-module-enabled' : 'wps-module-disabled' ) : 'wps-module-available';
					?>
					<tr class="wps-module-card <?php echo esc_attr( $type_class . ' ' . $status_class ); ?>" data-type="hub" data-group="<?php echo esc_attr( $slug ); ?>" data-status="<?php echo esc_attr( $installed ? ( $update_available ? 'update' : 'installed' ) : 'available' ); ?>">
						<td class="wps-module-name">
							<?php if ( $module['slug'] !== 'plugin-wpshadow' ) : ?>
								<button type="button" class="button-link wps-hub-toggle" data-group="<?php echo esc_attr( $module['slug'] ); ?>" aria-expanded="true" aria-label="<?php echo esc_attr( sprintf( __( 'Toggle %s spokes', 'plugin-wpshadow' ), $module['name'] ) ); ?>">
									<span class="dashicons dashicons-arrow-down-alt2"></span>
								</button>
							<?php endif; ?>
							<strong><a href="<?php echo esc_url( $module['uri'] ?? '#' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $module['name'] ); ?></a></strong>
							<br><span class="description"><?php echo esc_html( $module['description'] ); ?></span>
						</td>
						<td>
							<?php
							$requires = ( $module['slug'] === 'plugin-wpshadow' ) ? 'None' : 'Core ' . ( $module['requires_core'] ?? '' );
							echo esc_html( $requires === 'None' ? '-' : $requires );
							?>
						</td>
						<td>
							<?php echo esc_html( $module['version'] ); ?>
							<?php if ( $update_available && ! empty( $module['download_url'] ) ) : ?>
								<br><small><a href="#" class="wps-btn-update" data-slug="<?php echo esc_attr( $module['slug'] ); ?>"><?php esc_html_e( 'Update', 'plugin-wpshadow' ); ?></a></small>
							<?php endif; ?>
						</td>
						<td>
							<?php
							// Get last modified time of module files.
							$module_path = WPSHADOW_PATH . 'modules/hubs/' . basename( $slug );
							if ( ! file_exists( $module_path ) ) {
								$module_path = WP_PLUGIN_DIR . '/' . $slug;
							}
							$module_file = '';
							if ( file_exists( $module_path ) ) {
								$module_file = $module_path . '/module.php';
								if ( ! file_exists( $module_file ) ) {
									$module_file = $module_path . '/' . basename( $slug ) . '.php';
								}
							}
							if ( $module_file && file_exists( $module_file ) ) {
								$last_modified = filemtime( $module_file );
								echo esc_html( human_time_diff( $last_modified, time() ) . ' ago' );
								echo '<br><small>' . esc_html( date_i18n( get_option( 'date_format' ), $last_modified ) ) . '</small>';
							} else {
								echo '<span class="description">' . esc_html__( 'Not installed', 'plugin-wpshadow' ) . '</span>';
							}
							?>
						</td>
						<td>
							<?php
							$author_github = 'https://github.com/thisismyurl';
							if ( ! empty( $module['author_uri'] ) && strpos( $module['author_uri'], 'github.com' ) !== false ) {
								$author_github = $module['author_uri'];
							}
							?>
							<a href="<?php echo esc_url( $author_github ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $module['author'] ); ?></a>
						</td>
					<td>
						<label class="wps-toggle-switch">
							<input type="checkbox" <?php checked( $is_enabled ); ?> data-module="<?php echo esc_attr( $slug ); ?>" data-module-name="<?php echo esc_attr( $module['name'] ?? '' ); ?>" data-type="hub" data-installed="<?php echo esc_attr( $installed ? '1' : '0' ); ?>" data-plugin-base="<?php echo esc_attr( $module['basename'] ?? $plugin_base ); ?>" data-plugin-exists="<?php echo esc_attr( $plugin_exists ? '1' : '0' ); ?>" data-downloadable="<?php echo esc_attr( ! empty( $module['download_url'] ) ? '1' : '0' ); ?>">
							<span class="wps-toggle-slider"></span>
						</label>
						<span class="wps-progress" aria-live="polite"><span class="spinner is-active" style="float:none"></span><span class="bar"><span class="fill"></span></span><span class="progress-label"><?php esc_html_e( 'Working…', 'plugin-wpshadow' ); ?></span></span>
					</td>
				</tr>
					<?php foreach ( $group['spokes'] as $module ) : ?>
							<?php
							$slug = $module['slug'];
							// Real-time plugin state detection.
							$plugin_base       = $module['basename'] ?? $slug . '/' . $slug . '.php';
							$plugin_base_path  = WP_PLUGIN_DIR . '/' . ltrim( $plugin_base, '/\\' );
							$plugin_exists     = file_exists( $plugin_base_path );
							$module_file_path  = (string) ( $module['file'] ?? '' );
							$module_file_found = ! empty( $module_file_path ) && file_exists( $module_file_path );
							$installed         = ! empty( $module['installed'] ) || $plugin_exists || $module_file_found;
							$is_network_active = is_multisite() && is_plugin_active_for_network( $plugin_base );
							$registry_enabled  = ! empty( $module['enabled'] );
							$is_enabled        = $plugin_exists ? ( is_plugin_active( $plugin_base ) || $is_network_active ) : $registry_enabled;
							$update_available  = ! empty( $module['update_available'] );
							$type_class        = 'wps-type-spoke';
							$status_class      = $installed ? ( $is_enabled ? 'wps-module-enabled' : 'wps-module-disabled' ) : 'wps-module-available';
							?>
							<tr class="wps-module-card wps-child-module <?php echo esc_attr( $type_class . ' ' . $status_class ); ?>" data-type="spoke" data-parent="<?php echo esc_attr( $group['hub']['slug'] ?? '' ); ?>" data-status="<?php echo esc_attr( $installed ? ( $update_available ? 'update' : 'installed' ) : 'available' ); ?>">
								<td class="wps-module-name">
									<span class="wps-indent">&#8212; </span>
									<a href="<?php echo esc_url( $module['uri'] ?? '#' ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $module['name'] ); ?></a>
									<br><span class="description"><?php echo esc_html( $module['description'] ); ?></span>
								</td>
								<td>
									<?php
									$requires = $group['hub']['name'] ?? 'Unknown';
									echo esc_html( $requires === 'None' ? '-' : $requires );
									?>
								</td>
								<td>
									<?php echo esc_html( $module['version'] ); ?>
									<?php if ( $update_available && ! empty( $module['download_url'] ) ) : ?>
										<br><small><a href="#" class="wps-btn-update" data-slug="<?php echo esc_attr( $module['slug'] ); ?>"><?php esc_html_e( 'Update', 'plugin-wpshadow' ); ?></a></small>
									<?php endif; ?>
								</td>
								<td>
									<?php
									// Get last modified time of module files
									$module_dir_hint = '';
									if ( ! empty( $module['basename'] ) ) {
										$module_dir_hint = dirname( $module['basename'] );
										$module_dir_hint = ( '.' === $module_dir_hint ) ? '' : trim( $module_dir_hint, '/\\' );
									}
									$module_path = WPSHADOW_PATH . 'modules/spokes/' . ( ! empty( $module_dir_hint ) ? basename( $module_dir_hint ) : basename( $slug ) );
									if ( ! file_exists( $module_path ) ) {
										$module_path = WP_PLUGIN_DIR . '/' . ( ! empty( $module_dir_hint ) ? $module_dir_hint : $slug );
									}
									$module_file = '';
									if ( file_exists( $module_path ) ) {
										$module_file = $module_path . '/module.php';
										if ( ! file_exists( $module_file ) ) {
											$module_file = $module_path . '/' . basename( $slug ) . '.php';
										}
									}
									if ( $module_file && file_exists( $module_file ) ) {
										$last_modified = filemtime( $module_file );
										echo esc_html( human_time_diff( $last_modified, time() ) . ' ago' );
										echo '<br><small>' . esc_html( date_i18n( get_option( 'date_format' ), $last_modified ) ) . '</small>';
									} else {
										echo '<span class="description">' . esc_html__( 'Not installed', 'plugin-wpshadow' ) . '</span>';
									}
									?>
								</td>
								<td>
									<?php
									$author_github = 'https://github.com/thisismyurl';
									if ( ! empty( $module['author_uri'] ) && strpos( $module['author_uri'], 'github.com' ) !== false ) {
										$author_github = $module['author_uri'];
									}
									?>
									<a href="<?php echo esc_url( $author_github ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $module['author'] ); ?></a>
								</td>
								<td>
									<label class="wps-toggle-switch">
										<input type="checkbox" <?php checked( $is_enabled ); ?> data-module="<?php echo esc_attr( $slug ); ?>" data-module-name="<?php echo esc_attr( $module['name'] ?? '' ); ?>" data-type="spoke" data-installed="<?php echo esc_attr( $installed ? '1' : '0' ); ?>" data-plugin-base="<?php echo esc_attr( $module['basename'] ?? $plugin_base ); ?>" data-plugin-exists="<?php echo esc_attr( $plugin_exists ? '1' : '0' ); ?>" data-downloadable="<?php echo esc_attr( ! empty( $module['download_url'] ) ? '1' : '0' ); ?>">
										<span class="wps-toggle-slider"></span>
									</label>
									<span class="wps-progress" aria-live="polite"><span class="spinner is-active" style="float:none"></span><span class="bar"><span class="fill"></span></span><span class="progress-label"><?php esc_html_e( 'Working…', 'plugin-wpshadow' ); ?></span></span>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</div>
</div>


