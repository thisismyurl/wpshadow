<?php
/**
 * Phase 4 Settings Page
 *
 * Admin page for configuring Phase 4 infrastructure features.
 *
 * @package    WPShadow
 * @subpackage Screens
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin\Pages;

use WPShadow\Reporting\Phase4_Initializer;
use WPShadow\Reporting\Report_Alert_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Phase4_Settings_Page Class
 *
 * Configuration interface for Phase 4 features.
 *
 * @since 1.6093.1200
 */
class Phase4_Settings_Page {

	/**
	 * Initialize settings page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'add_menu_page' ), 20 );
		add_action( 'admin_init', array( __CLASS__, 'handle_legacy_redirect' ) );
		add_action( 'admin_post_wpshadow_save_phase4_settings', array( __CLASS__, 'save_settings' ) );
	}

	/**
	 * Handle legacy redirect from old Advanced Features page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function handle_legacy_redirect() {
		if ( isset( $_GET['page'] ) && 'wpshadow-advanced-features' === $_GET['page'] ) {
			if ( current_user_can( 'manage_options' ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wpshadow-settings&tab=advanced' ) );
				exit;
			}
		}
	}

	/**
	 * Add menu page
	 *
	 * NOTE: Advanced Features moved to Settings > Advanced tab per bug #3874
	 * This function now only adds a redirect for backwards compatibility
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function add_menu_page() {
		// Advanced Features is now part of Settings > Advanced tab
		// No longer registering as separate submenu item
		// Redirect handled in handle_legacy_redirect()
	}

	/**
	 * Render settings page
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
		}

		$settings = Phase4_Initializer::get_integration_settings();
		$alerts = Report_Alert_Manager::get_alerts();

		?>
		<div class="wrap wps-page-container">
			<!-- Page Header -->
			<?php
			wpshadow_render_page_header(
				__( 'Advanced Features', 'wpshadow' ),
				__( 'Configure export, integration, analytics, and alerting features.', 'wpshadow' ),
				'dashicons-admin-generic'
			);
			?>

			<?php if ( isset( $_GET['updated'] ) ) : ?>
			<?php
			wpshadow_render_card(
				array(
					'card_class' => 'wps-card--success',
					'body'       => function() {
						?>
						<p><?php esc_html_e( 'Settings saved successfully.', 'wpshadow' ); ?></p>
						<?php
					},
				)
			);
			?>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wps-settings-form">
				<?php wp_nonce_field( 'wpshadow_phase4_settings', 'wpshadow_phase4_nonce' ); ?>
				<input type="hidden" name="action" value="wpshadow_save_phase4_settings" />

				<!-- Integration Settings -->
			<?php
			wpshadow_render_card(
				array(
					'title'       => __( 'Integration Settings', 'wpshadow' ),
					'description' => __( 'Connect WPShadow with external services for notifications and data export.', 'wpshadow' ),
					'icon'        => 'share',
					'body'        => function() use ( $settings ) {
						?>
						<!-- Slack Integration -->
						<div class="wps-form-group">
							<label for="slack_enabled" class="wps-form-label">
								<?php esc_html_e( 'Slack Integration', 'wpshadow' ); ?>
							</label>
							<label class="wps-toggle">
								<input type="checkbox"
									   name="slack_enabled"
									   id="slack_enabled"
									   value="1"
									   <?php checked( $settings['slack_enabled'] ); ?> />
								<span class="wps-toggle-slider"></span>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Send report summaries to Slack channels.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="slack_webhook" class="wps-form-label">
								<?php esc_html_e( 'Slack Webhook URL', 'wpshadow' ); ?>
							</label>
							<input type="url"
								   name="slack_webhook"
								   id="slack_webhook"
								   value="<?php echo esc_attr( $settings['slack_webhook'] ); ?>"
								   class="wps-input"
								   placeholder="https://hooks.slack.com/services/..." />
							<p class="wps-form-description">
								<?php
								printf(
									/* translators: %s: Slack webhook documentation URL */
									__( 'Get your webhook URL from <a href="%s" target="_blank">Slack</a>.', 'wpshadow' ),
									'https://api.slack.com/messaging/webhooks'
								);
								?>
							</p>
						</div>

						<!-- Microsoft Teams -->
						<div class="wps-form-group wps-mt-4">
							<label for="teams_enabled" class="wps-form-label">
								<?php esc_html_e( 'Microsoft Teams', 'wpshadow' ); ?>
							</label>
							<label class="wps-toggle">
								<input type="checkbox"
									   name="teams_enabled"
									   id="teams_enabled"
									   value="1"
									   <?php checked( $settings['teams_enabled'] ); ?> />
								<span class="wps-toggle-slider"></span>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Send notifications to Microsoft Teams.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="teams_webhook" class="wps-form-label">
								<?php esc_html_e( 'Teams Webhook URL', 'wpshadow' ); ?>
							</label>
							<input type="url"
								   name="teams_webhook"
								   id="teams_webhook"
								   value="<?php echo esc_attr( $settings['teams_webhook'] ); ?>"
								   class="wps-input"
								   placeholder="https://outlook.office.com/webhook/..." />
							<p class="wps-form-description">
								<?php esc_html_e( 'Configure incoming webhooks in your Teams channel settings.', 'wpshadow' ); ?>
							</p>
						</div>

						<!-- Custom Webhook -->
						<div class="wps-form-group wps-mt-4">
							<label for="webhook_enabled" class="wps-form-label">
								<?php esc_html_e( 'Custom Webhook', 'wpshadow' ); ?>
							</label>
							<label class="wps-toggle">
								<input type="checkbox"
									   name="webhook_enabled"
									   id="webhook_enabled"
									   value="1"
									   <?php checked( $settings['webhook_enabled'] ); ?> />
								<span class="wps-toggle-slider"></span>
							</label>
							<p class="wps-form-description">
								<?php esc_html_e( 'Send report data to any custom endpoint.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="webhook_url" class="wps-form-label">
								<?php esc_html_e( 'Webhook URL', 'wpshadow' ); ?>
							</label>
							<input type="url"
								   name="webhook_url"
								   id="webhook_url"
								   value="<?php echo esc_attr( $settings['webhook_url'] ); ?>"
								   class="wps-input"
								   placeholder="https://your-service.com/webhook" />
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="webhook_method" class="wps-form-label">
								<?php esc_html_e( 'HTTP Method', 'wpshadow' ); ?>
							</label>
							<select name="webhook_method" id="webhook_method" class="wps-input wps-w-32">
								<option value="POST" <?php selected( $settings['webhook_method'], 'POST' ); ?>>POST</option>
								<option value="GET" <?php selected( $settings['webhook_method'], 'GET' ); ?>>GET</option>
								<option value="PUT" <?php selected( $settings['webhook_method'], 'PUT' ); ?>>PUT</option>
							</select>
						</div>
						<?php
					},
				)
			);
			?>
				<!-- Alert Settings -->
			<?php
			wpshadow_render_card(
				array(
					'title'       => __( 'Alert Thresholds', 'wpshadow' ),
					'description' => __( 'Monitor configured alert thresholds and trigger conditions.', 'wpshadow' ),
					'icon'        => 'bell',
					'body'        => function() use ( $alerts ) {
						if ( empty( $alerts ) ) {
							?>
							<p><?php esc_html_e( 'No alerts configured yet.', 'wpshadow' ); ?></p>
							<?php
						} else {
							?>
							<table class="wps-table widefat">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Metric', 'wpshadow' ); ?></th>
										<th><?php esc_html_e( 'Condition', 'wpshadow' ); ?></th>
										<th><?php esc_html_e( 'Threshold', 'wpshadow' ); ?></th>
										<th><?php esc_html_e( 'Status', 'wpshadow' ); ?></th>
										<th><?php esc_html_e( 'Last Triggered', 'wpshadow' ); ?></th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ( $alerts as $alert_id => $alert ) : ?>
										<tr>
											<td><?php echo esc_html( $alert['metric'] ); ?></td>
											<td><?php echo esc_html( strtoupper( $alert['operator'] ) ); ?></td>
											<td><?php echo esc_html( (string) $alert['threshold'] ); ?></td>
											<td>
												<?php if ( $alert['enabled'] ) : ?>
													<span class="dashicons dashicons-yes-alt" style="color: #00a32a;"></span>
												<?php else : ?>
													<span class="dashicons dashicons-dismiss" style="color: #d63638;"></span>
												<?php endif; ?>
											</td>
											<td>
												<?php
												if ( $alert['last_triggered'] > 0 ) {
													echo esc_html( human_time_diff( $alert['last_triggered'], time() ) . ' ago' );
												} else {
													esc_html_e( 'Never', 'wpshadow' );
												}
												?>
											</td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
							<?php
						}
						?>
						<p class="wps-form-description wps-mt-4">
							<?php esc_html_e( 'Alerts can be configured via the WP-CLI or programmatically. UI management coming soon.', 'wpshadow' ); ?>
						</p>
						<?php
					},
				)
			);
			?>
				<!-- Export & Storage -->
			<?php
			wpshadow_render_card(
				array(
					'title'       => __( 'Export & Storage', 'wpshadow' ),
					'description' => __( 'Manage export retention policies and storage settings.', 'wpshadow' ),
					'icon'        => 'download',
					'body'        => function() {
						?>
						<div class="wps-form-group">
							<label class="wps-form-label"><?php esc_html_e( 'Export Retention', 'wpshadow' ); ?></label>
							<p><?php esc_html_e( 'Exported files are automatically deleted after 7 days.', 'wpshadow' ); ?></p>
							<p class="wps-form-description">
								<?php esc_html_e( 'Storage location: /wp-content/uploads/wpshadow-reports/', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label class="wps-form-label"><?php esc_html_e( 'Snapshot Retention', 'wpshadow' ); ?></label>
							<p><?php esc_html_e( 'Historical snapshots are kept for 90 days.', 'wpshadow' ); ?></p>
							<p class="wps-form-description">
								<?php esc_html_e( 'Older snapshots are automatically purged to save database space.', 'wpshadow' ); ?>
							</p>
						</div>
						<?php
					},
				)
			);
			?>
				<!-- Analytics & Benchmarking -->
			<?php
			wpshadow_render_card(
				array(
					'title'       => __( 'Analytics & Benchmarking', 'wpshadow' ),
					'description' => __( 'Configure site type and ROI calculation settings for better analytics.', 'wpshadow' ),
					'icon'        => 'chart-line',
					'body'        => function() {
						?>
						<div class="wps-form-group">
							<label for="site_type" class="wps-form-label">
								<?php esc_html_e( 'Site Type', 'wpshadow' ); ?>
							</label>
							<select name="site_type" id="site_type" class="wps-input wps-w-64">
								<option value="blog" <?php selected( get_option( 'wpshadow_site_type', 'business' ), 'blog' ); ?>>
									<?php esc_html_e( 'Blog/Content Site', 'wpshadow' ); ?>
								</option>
								<option value="ecommerce" <?php selected( get_option( 'wpshadow_site_type', 'business' ), 'ecommerce' ); ?>>
									<?php esc_html_e( 'E-Commerce', 'wpshadow' ); ?>
								</option>
								<option value="business" <?php selected( get_option( 'wpshadow_site_type', 'business' ), 'business' ); ?>>
									<?php esc_html_e( 'Business/Corporate', 'wpshadow' ); ?>
								</option>
							</select>
							<p class="wps-form-description">
								<?php esc_html_e( 'Used for benchmark comparisons against industry averages.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wps-form-group wps-mt-4">
							<label for="hourly_rate" class="wps-form-label">
								<?php esc_html_e( 'Hourly Rate', 'wpshadow' ); ?>
							</label>
							<div class="wps-input-group">
								<input type="number"
									   name="hourly_rate"
									   id="hourly_rate"
									   value="<?php echo esc_attr( get_option( 'wpshadow_hourly_rate', '100' ) ); ?>"
									   min="0"
									   step="1"
									   class="wps-input wps-w-32" />
								<span class="wps-input-addon">USD</span>
							</div>
							<p class="wps-form-description">
								<?php esc_html_e( 'Used for ROI calculations (labor cost savings).', 'wpshadow' ); ?>
							</p>
						</div>
						<?php
					},
				)
			);
			?>
				<!-- Action Buttons -->
				<?php
				wpshadow_render_card(
					array(
						'card_class' => 'wps-card--action',
						'body'       => function() {
							?>
							<?php submit_button( __( 'Save Settings', 'wpshadow' ), 'primary', 'submit', false ); ?>
							<?php
						},
					)
				);
				?>
			</form>

			<!-- Feature Documentation -->
			<?php
			wpshadow_render_card(
				array(
					'title'       => __( 'Available Features', 'wpshadow' ),
					'description' => __( 'Overview of Phase 4 advanced features available in WPShadow.', 'wpshadow' ),
					'icon'        => 'info',
					'body'        => function() {
						?>
						<div class="wps-grid wps-grid-cols-2 wps-gap-4">
							<div class="wps-info-box">
								<h4><?php esc_html_e( 'Export & Scheduling', 'wpshadow' ); ?></h4>
								<ul>
									<li><?php esc_html_e( 'PDF, CSV, Excel exports', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Scheduled report generation', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Email delivery', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Historical snapshots', 'wpshadow' ); ?></li>
								</ul>
							</div>

							<div class="wps-info-box">
								<h4><?php esc_html_e( 'Intelligence', 'wpshadow' ); ?></h4>
								<ul>
									<li><?php esc_html_e( 'Trend analysis', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Alert thresholds', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Report annotations', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Executive summaries', 'wpshadow' ); ?></li>
								</ul>
							</div>

							<div class="wps-info-box">
								<h4><?php esc_html_e( 'Integrations', 'wpshadow' ); ?></h4>
								<ul>
									<li><?php esc_html_e( 'Slack notifications', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Microsoft Teams', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Custom webhooks', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'REST API access', 'wpshadow' ); ?></li>
								</ul>
							</div>

							<div class="wps-info-box">
								<h4><?php esc_html_e( 'Advanced Analytics', 'wpshadow' ); ?></h4>
								<ul>
									<li><?php esc_html_e( 'ROI calculator', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Benchmark comparison', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'What-if scenarios', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Regression detection', 'wpshadow' ); ?></li>
								</ul>
							</div>
						</div>
						<?php
					},
				)
			);
			?>
		</div>
		<?php
	}

	/**
	 * Save settings
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions', 'wpshadow' ) );
		}

		if ( ! isset( $_POST['wpshadow_phase4_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpshadow_phase4_nonce'] ) ), 'wpshadow_phase4_settings' ) ) {
			wp_die( esc_html__( 'Security check failed', 'wpshadow' ) );
		}

		// Save integration settings
		$settings = array(
			'slack_enabled'   => isset( $_POST['slack_enabled'] ),
			'slack_webhook'   => isset( $_POST['slack_webhook'] ) ? esc_url_raw( wp_unslash( $_POST['slack_webhook'] ) ) : '',
			'teams_enabled'   => isset( $_POST['teams_enabled'] ),
			'teams_webhook'   => isset( $_POST['teams_webhook'] ) ? esc_url_raw( wp_unslash( $_POST['teams_webhook'] ) ) : '',
			'webhook_enabled' => isset( $_POST['webhook_enabled'] ),
			'webhook_url'     => isset( $_POST['webhook_url'] ) ? esc_url_raw( wp_unslash( $_POST['webhook_url'] ) ) : '',
			'webhook_method'  => isset( $_POST['webhook_method'] ) ? sanitize_key( wp_unslash( $_POST['webhook_method'] ) ) : 'POST',
		);

		Phase4_Initializer::save_integration_settings( $settings );

		// Save analytics settings
		if ( isset( $_POST['site_type'] ) ) {
			update_option( 'wpshadow_site_type', sanitize_key( wp_unslash( $_POST['site_type'] ) ) );
		}

		if ( isset( $_POST['hourly_rate'] ) ) {
			update_option( 'wpshadow_hourly_rate', absint( $_POST['hourly_rate'] ) );
		}

		// Redirect back with success message
		wp_safe_redirect( add_query_arg( 'updated', '1', admin_url( 'admin.php?page=wpshadow-advanced-features' ) ) );
		exit;
	}
}

// Initialize settings page
Phase4_Settings_Page::init();
