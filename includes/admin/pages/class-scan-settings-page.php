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

	wp_enqueue_style(
		'wpshadow-scan-settings-page',
		WPSHADOW_URL . 'assets/css/scan-settings-page.css',
		array(),
		WPSHADOW_VERSION
	);

	wp_enqueue_script(
		'wpshadow-scan-settings-page',
		WPSHADOW_URL . 'assets/js/scan-settings-page.js',
		array(),
		WPSHADOW_VERSION,
		true
	);

	\WPShadow\Core\Admin_Asset_Registry::localize_with_ajax_nonce(
		'wpshadow-scan-settings-page',
		'wpshadowScanSettings',
		'wpshadow_scan_settings',
		array(
			'strings'  => array(
				'no_diagnostics'   => __( 'No diagnostics found.', 'wpshadow' ),
				'no_treatments'    => __( 'No treatments found.', 'wpshadow' ),
				'toggle_diagnostic'=> __( 'Toggle diagnostic', 'wpshadow' ),
				'toggle_treatment' => __( 'Toggle treatment', 'wpshadow' ),
				'disable'          => __( 'Disable', 'wpshadow' ),
				'enable'           => __( 'Enable', 'wpshadow' ),
				'error_title'      => __( 'Error', 'wpshadow' ),
				'operation_failed' => __( 'Operation failed', 'wpshadow' ),
				'network_title'    => __( 'Network Error', 'wpshadow' ),
				'network_error'    => __( 'Network error', 'wpshadow' ),
			),
		),
		'nonce',
		'ajax_url'
	);
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

		<!-- Recent Activity Section -->
		<?php
		if ( function_exists( 'wpshadow_render_page_activities' ) ) {
			wpshadow_render_page_activities( 'settings', 10 );
		}
		?>
	</div>

	<?php
}
