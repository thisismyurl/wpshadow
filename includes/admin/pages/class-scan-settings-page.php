<?php
/**
 * Scan Settings Page
 *
 * Admin UI to search, filter, paginate, and toggle diagnostics (and treatments if present).
 * Uses AJAX for scalable loading.
 *
 * @since   1.6030.2148
 * @package WPShadow\Admin
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the Scan Settings admin page.
 *
 * @since 1.6030.2148
 * @return void
 */
function wpshadow_render_scan_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
	}

	$nonce = wp_create_nonce( 'wpshadow_scan_settings' );
	?>
	<div class="wrap wps-page-container">
		<?php wpshadow_render_page_header(
			__( 'Scan Settings', 'wpshadow' ),
			__( 'Configure scan performance and manage which diagnostics and treatments are enabled.', 'wpshadow' )
		); ?>

		<!-- Performance Tuning -->
		<form method="post" action="options.php" class="wps-settings-form">
			<?php settings_fields( 'wpshadow_settings' ); ?>
			
			<?php
			wpshadow_render_card(
				array(
					'title'       => __( 'Scan Performance', 'wpshadow' ),
					'description' => __( 'Tune how scans run to match your server capabilities.', 'wpshadow' ),
					'icon'        => 'dashicons-performance',
					'body'        => function() {
						?>
						<div class="wps-form-group">
							<label for="wpshadow_scan_batch_size" class="wps-form-label">
								<?php esc_html_e( 'Diagnostics Per Batch', 'wpshadow' ); ?>
							</label>
							<div class="wps-input-group">
								<input 
									type="number" 
									id="wpshadow_scan_batch_size" 
									name="wpshadow_scan_batch_size" 
									value="<?php echo esc_attr( get_option( 'wpshadow_scan_batch_size', 10 ) ); ?>"
									min="1"
									max="100"
									step="1"
									class="wps-input wps-w-32"
								/>
								<span class="wps-input-addon"><?php esc_html_e( 'diagnostics', 'wpshadow' ); ?></span>
							</div>
							<p class="wps-form-description">
								<?php esc_html_e( 'Run this many diagnostics per batch. Lower values use less memory but take longer. Higher values are faster but use more memory.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="wpshadow_timeout_seconds" class="wps-form-label">
								<?php esc_html_e( 'Scan Timeout', 'wpshadow' ); ?>
							</label>
							<div class="wps-input-group">
								<input 
									type="number" 
									id="wpshadow_timeout_seconds" 
									name="wpshadow_timeout_seconds" 
									value="<?php echo esc_attr( get_option( 'wpshadow_timeout_seconds', 60 ) ); ?>"
									min="30"
									max="300"
									step="5"
									class="wps-input wps-w-32"
								/>
								<span class="wps-input-addon"><?php esc_html_e( 'seconds', 'wpshadow' ); ?></span>
							</div>
							<p class="wps-form-description">
								<?php esc_html_e( 'Maximum time to wait for a scan to complete. Increase on slower servers to avoid timeouts, decrease to fail faster.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label class="wps-toggle" for="wpshadow_parallel_scans">
								<input 
									type="checkbox" 
									id="wpshadow_parallel_scans" 
									name="wpshadow_parallel_scans" 
									value="1"
									<?php checked( get_option( 'wpshadow_parallel_scans', false ) ); ?>
								/>
								<span class="wps-toggle-slider"></span>
								<?php esc_html_e( 'Enable Parallel Scanning', 'wpshadow' ); ?>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Run multiple diagnostics at the same time (requires good server resources). Makes scans faster but uses more CPU and memory.', 'wpshadow' ); ?>
							</p>
						</div>
						<?php
					},
					'footer'      => function() {
						?>
						<?php submit_button( __( 'Save Performance Settings', 'wpshadow' ), 'primary', 'submit', false ); ?>
						<?php
					},
				)
			);
			?>
				</select>
			</div>

			<div id="wpshadow-diagnostics-list" role="region" aria-live="polite"></div>
			<div class="wpshadow-pagination">
				<button type="button" class="button" id="wpshadow-prev" aria-label="<?php echo esc_attr__( 'Previous page', 'wpshadow' ); ?>">&larr;</button>
				<span id="wpshadow-page">1</span>
				<button type="button" class="button" id="wpshadow-next" aria-label="<?php echo esc_attr__( 'Next page', 'wpshadow' ); ?>">&rarr;</button>
			</div>
		</section>
		<section aria-labelledby="treatments-heading">
			<h2 id="treatments-heading"><?php echo esc_html__( 'Treatments', 'wpshadow' ); ?></h2>
			<div class="wpshadow-controls">
				<label for="wpshadow-t-search"><?php echo esc_html__( 'Search', 'wpshadow' ); ?></label>
				<input type="search" id="wpshadow-t-search" placeholder="<?php echo esc_attr__( 'Search treatments...', 'wpshadow' ); ?>" />
			</div>

			<div id="wpshadow-treatments-list" role="region" aria-live="polite"></div>
			<div class="wpshadow-pagination">
				<button type="button" class="button" id="wpshadow-t-prev" aria-label="<?php echo esc_attr__( 'Previous page', 'wpshadow' ); ?>">&larr;</button>
				<span id="wpshadow-t-page">1</span>
				<button type="button" class="button" id="wpshadow-t-next" aria-label="<?php echo esc_attr__( 'Next page', 'wpshadow' ); ?>">&rarr;</button>
			</div>
		</section>
	</div>
	<script type="text/javascript">
	(function(){
		const ajaxurl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
		const nonce = '<?php echo esc_js( $nonce ); ?>';

		// Diagnostics state
		let page = 1;
		const perPage = 25;
		let currentFamily = '';
		let currentSearch = '';

		// Treatments state
		let tPage = 1;
		const tPerPage = 25;
		let tSearch = '';

		function renderList(items){
			const container = document.getElementById('wpshadow-diagnostics-list');
			container.innerHTML = '';
			if (!items || items.length === 0){
				container.innerHTML = '<p><?php echo esc_js( __( 'No diagnostics found.', 'wpshadow' ) ); ?></p>';
				return;
			}
			const frag = document.createDocumentFragment();
			items.forEach(function(item){
				const row = document.createElement('div');
				row.className = 'wpshadow-row';
				row.style.display = 'grid';
				row.style.gridTemplateColumns = '1fr auto';
				row.style.gap = '8px';
				const info = document.createElement('div');
				info.innerHTML = '<strong>' + escapeHtml(item.title || item.slug || item.class_name) + '</strong>' +
				(item.family ? ' <span class="wps-diagnostic-family">(' + escapeHtml(item.family) + ')</span>' : '') +
				(item.description ? '<div class="wps-diagnostic-description">' + escapeHtml(item.description) + '</div>' : '');
				const toggle = document.createElement('button');
				toggle.className = 'button';
				toggle.setAttribute('aria-label', '<?php echo esc_js( __( 'Toggle diagnostic', 'wpshadow' ) ); ?>');
				toggle.textContent = item.enabled ? '<?php echo esc_js( __( 'Disable', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Enable', 'wpshadow' ) ); ?>';
				toggle.addEventListener('click', function(){
					toggle.disabled = true;
					fetch(ajaxurl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams({
							action: 'wpshadow_toggle_diagnostic',
							nonce: nonce,
							class_name: item.class_name,
							enable: item.enabled ? '0' : '1'
						}).toString()
					}).then(r=>r.json()).then(function(resp){
						if (resp && resp.success){
							item.enabled = !!resp.data.enabled;
							toggle.textContent = item.enabled ? '<?php echo esc_js( __( 'Disable', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Enable', 'wpshadow' ) ); ?>';
						} else {
							WPShadowModal.alert({
								title: '<?php echo esc_js( __( 'Error', 'wpshadow' ) ); ?>',
								message: (resp && resp.data && resp.data.message) || '<?php echo esc_js( __( 'Operation failed', 'wpshadow' ) ); ?>',
								type: 'error'
							});
						}
					}).catch(function(){
					WPShadowModal.alert({
						title: '<?php echo esc_js( __( 'Network Error', 'wpshadow' ) ); ?>',
						message: '<?php echo esc_js( __( 'Network error', 'wpshadow' ) ); ?>',
						type: 'error'
					});
					}).finally(function(){ toggle.disabled = false; });
				});
				row.appendChild(info);
				row.appendChild(toggle);
				frag.appendChild(row);
			});
			container.appendChild(frag);
		}

		function renderTreatments(items){
			const container = document.getElementById('wpshadow-treatments-list');
			container.innerHTML = '';
			if (!items || items.length === 0){
				container.innerHTML = '<p><?php echo esc_js( __( 'No treatments found.', 'wpshadow' ) ); ?></p>';
				return;
			}
			const frag = document.createDocumentFragment();
			items.forEach(function(item){
				const row = document.createElement('div');
				row.className = 'wpshadow-row';
				row.style.display = 'grid';
				row.style.gridTemplateColumns = '1fr auto';
				row.style.gap = '8px';
				const info = document.createElement('div');
				info.innerHTML = '<strong>' + escapeHtml(item.label || item.class_name) + '</strong>' +
				'<div class="wps-treatment-class-name">' + escapeHtml(item.class_name) + '</div>';
				const toggle = document.createElement('button');
				toggle.className = 'button';
				toggle.setAttribute('aria-label', '<?php echo esc_js( __( 'Toggle treatment', 'wpshadow' ) ); ?>');
				toggle.textContent = item.enabled ? '<?php echo esc_js( __( 'Disable', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Enable', 'wpshadow' ) ); ?>';
				toggle.addEventListener('click', function(){
					toggle.disabled = true;
					fetch(ajaxurl, {
						method: 'POST',
						headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
						body: new URLSearchParams({
							action: 'wpshadow_toggle_treatment',
							nonce: nonce,
							class_name: item.class_name,
							enable: item.enabled ? '0' : '1'
						}).toString()
					}).then(r=>r.json()).then(function(resp){
						if (resp && resp.success){
							item.enabled = !!resp.data.enabled;
							toggle.textContent = item.enabled ? '<?php echo esc_js( __( 'Disable', 'wpshadow' ) ); ?>' : '<?php echo esc_js( __( 'Enable', 'wpshadow' ) ); ?>';
						} else {
							WPShadowModal.alert({
								title: '<?php echo esc_js( __( 'Error', 'wpshadow' ) ); ?>',
								message: (resp && resp.data && resp.data.message) || '<?php echo esc_js( __( 'Operation failed', 'wpshadow' ) ); ?>',
								type: 'error'
							});
						}
					}).catch(function(){
						WPShadowModal.alert({
							title: '<?php echo esc_js( __( 'Network Error', 'wpshadow' ) ); ?>',
							message: '<?php echo esc_js( __( 'Network error', 'wpshadow' ) ); ?>',
							type: 'error'
						});
					}).finally(function(){ toggle.disabled = false; });
				});
				row.appendChild(info);
				row.appendChild(toggle);
				frag.appendChild(row);
			});
			container.appendChild(frag);
		}

		function escapeHtml(s){
			return String(s).replace(/[&<>"']/g,function(c){return ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;','\'':'&#39;'})[c];});
		}

		function loadFamilies(){
			fetch(ajaxurl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: new URLSearchParams({
					action: 'wpshadow_list_diagnostics',
					nonce: nonce,
					page: 1,
					per_page: 1,
					get_families: '1'
				}).toString()
			}).then(r=>r.json()).then(function(resp){
				if (resp && resp.success && resp.data && resp.data.families){
					const select = document.getElementById('wpshadow-family');
					resp.data.families.forEach(function(f){
						const opt = document.createElement('option');
						opt.value = f;
						opt.textContent = f;
						select.appendChild(opt);
					});
				}
			});
		}

		function loadPage(){
			const params = new URLSearchParams({
				action: 'wpshadow_list_diagnostics',
				nonce: nonce,
				page: String(page),
				per_page: String(perPage)
			});
			if (currentFamily){ params.append('family', currentFamily); }
			if (currentSearch){ params.append('search', currentSearch); }
			fetch(ajaxurl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params.toString() })
				.then(r=>r.json()).then(function(resp){
					if (resp && resp.success){
						renderList(resp.data.items || []);
						document.getElementById('wpshadow-page').textContent = String(page);
					}
				});
		}

		function loadTreatmentsPage(){
			const params = new URLSearchParams({
				action: 'wpshadow_list_treatments',
				nonce: nonce,
				page: String(tPage),
				per_page: String(tPerPage)
			});
			if (tSearch){ params.append('search', tSearch); }
			fetch(ajaxurl, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: params.toString() })
				.then(r=>r.json()).then(function(resp){
					if (resp && resp.success){
						renderTreatments(resp.data.items || []);
						document.getElementById('wpshadow-t-page').textContent = String(tPage);
					}
				});
		}

		document.getElementById('wpshadow-prev').addEventListener('click', function(){ if (page>1){ page--; loadPage(); } });
		document.getElementById('wpshadow-next').addEventListener('click', function(){ page++; loadPage(); });
		document.getElementById('wpshadow-family').addEventListener('change', function(e){ currentFamily = e.target.value || ''; page = 1; loadPage(); });
		const searchEl = document.getElementById('wpshadow-search');
		let t;
		searchEl.addEventListener('input', function(){ clearTimeout(t); t = setTimeout(function(){ currentSearch = searchEl.value || ''; page = 1; loadPage(); }, 300); });

		const tSearchEl = document.getElementById('wpshadow-t-search');
		let tDebounce;
		tSearchEl.addEventListener('input', function(){ clearTimeout(tDebounce); tDebounce = setTimeout(function(){ tSearch = tSearchEl.value || ''; tPage = 1; loadTreatmentsPage(); }, 300); });
		document.getElementById('wpshadow-t-prev').addEventListener('click', function(){ if (tPage>1){ tPage--; loadTreatmentsPage(); } });
		document.getElementById('wpshadow-t-next').addEventListener('click', function(){ tPage++; loadTreatmentsPage(); });

		loadFamilies();
		loadPage();
		loadTreatmentsPage();
	})();
	</script>
	<?php
}
