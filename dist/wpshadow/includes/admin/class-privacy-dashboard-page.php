<?php
/**
 * Privacy Dashboard Page
 *
 * Complete privacy control center showing what data we collect,
 * consent history, and data management tools.
 * Phase 6: Privacy & Consent Excellence
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Privacy\Consent_Preferences;
use WPShadow\Privacy\Privacy_Policy_Manager;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Dashboard Page Class
 *
 * Provides complete transparency about data collection, storage, and usage.
 * Users can view, export, and delete their data.
 *
 * @since 0.6093.1200
 */
class Privacy_Dashboard_Page extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since 0.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_enqueue_scripts'                 => 'enqueue_assets',
			'wp_ajax_wpshadow_export_data'          => 'handle_export_data',
			'wp_ajax_wpshadow_delete_data'          => 'handle_delete_data',
			'wp_ajax_wpshadow_update_consent'       => 'handle_update_consent',
		);
	}

	/**
	 * Initialize the privacy dashboard (deprecated)
	 *
	 * @deprecated1.0 Use Privacy_Dashboard_Page::subscribe() instead
	 * @since 0.6093.1200
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Add privacy dashboard menu page (now called by Gamification_UI).
	 *
	 * @since 0.6093.1200
	 * @deprecated Menu registration moved to Gamification_UI::register_menu_pages()
	 * @return void
	 */
	public static function add_menu_page() {
		// Menu registration moved to Gamification_UI for proper hierarchy
		// Kept for backward compatibility if called directly
	}

	/**
	 * Enqueue dashboard assets.
	 *
	 * @since 0.6093.1200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'wpshadow_page_wpshadow-privacy' !== $hook && ! self::is_settings_privacy_tab() ) {
			return;
		}

		wp_enqueue_style( 'wpshadow-admin-pages' );
		wp_enqueue_style(
			'wpshadow-privacy-dashboard',
			WPSHADOW_URL . 'assets/css/privacy-dashboard.css',
			array( 'wpshadow-admin-pages' ),
			WPSHADOW_VERSION
		);

		wp_enqueue_script( 'wpshadow-admin-pages' );
		wp_enqueue_script(
			'wpshadow-privacy-dashboard',
			WPSHADOW_URL . 'assets/js/privacy-dashboard.js',
			array( 'jquery', 'wpshadow-admin-pages' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-privacy-dashboard',
			'wpshadowPrivacy',
			array(
				'nonce'   => wp_create_nonce( 'wpshadow_privacy_actions' ),
				'strings' => array(
					'confirm_delete' => __( 'Are you sure you want to delete all your WPShadow data? This action cannot be undone.', 'wpshadow' ),
					'export_started' => __( 'Preparing your data export...', 'wpshadow' ),
					'consent_saved'  => __( 'Privacy preferences updated successfully', 'wpshadow' ),
					'consent_error'  => __( 'Couldn\'t save preferences right now. Please try again in a moment.', 'wpshadow' ),
					'exporting'      => __( 'Exporting...', 'wpshadow' ),
					'export_data'    => __( 'Export My Data', 'wpshadow' ),
					'export_error'   => __( 'Couldn\'t create export right now. Please try again in a moment.', 'wpshadow' ),
					'deleting'       => __( 'Deleting...', 'wpshadow' ),
					'delete_confirm' => __( 'Are you sure? This will permanently delete all WPShadow data. This action cannot be undone.', 'wpshadow' ),
					'delete_success' => __( 'All data deleted successfully.', 'wpshadow' ),
					'delete_error'   => __( 'Couldn\'t complete deletion right now. Please try again in a moment.', 'wpshadow' ),
					'delete_data'    => __( 'Delete All Data', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Check if the current page is the Settings privacy dashboard tab.
	 *
	 * @since 0.6093.1200
	 * @return bool True when on the Settings privacy dashboard tab.
	 */
	private static function is_settings_privacy_tab(): bool {
		if ( ! isset( $_GET['page'], $_GET['tab'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return false;
		}

		$page = sanitize_key( wp_unslash( $_GET['page'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$tab  = sanitize_key( wp_unslash( $_GET['tab'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		return 'wpshadow-settings' === $page && 'privacy-dashboard' === $tab;
	}

	/**
	 * Render privacy dashboard page.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	public static function render_page() {
		$user_id = get_current_user_id();
		$prefs   = Consent_Preferences::get_preferences( $user_id );

		?>
		<div class="wrap wpshadow-privacy wps-page-container">
			<?php
			wpshadow_render_page_header(
				__( 'Privacy Dashboard', 'wpshadow' ),
				__( 'Complete transparency and control over your data. Your privacy, your rules.', 'wpshadow' ),
				'dashicons-shield-alt'
			);
			?>

			<style>
				.wps-privacy-dashboard-layout {
					display: flex;
					gap: 24px;
					align-items: stretch;
					flex-wrap: wrap;
				}

				.wps-privacy-dashboard-main {
					flex: 2 1 520px;
					min-width: 0;
				}

				.wps-privacy-dashboard-side {
					flex: 1 1 320px;
					min-width: 0;
				}

				.wps-consent-stack {
					display: flex;
					flex-direction: column;
					gap: 16px;
					width: 100%;
				}

				.wps-consent-stack > .wps-card {
					width: 100%;
					max-width: 100%;
					box-sizing: border-box;
				}

				.wps-consent-form {
					max-width: 100%;
					width: 100%;
					overflow: hidden;
				}

				.wps-privacy-score-gauge {
					text-align: center;
					margin-bottom: 16px;
				}

				.wps-privacy-score-gauge svg {
					display: block;
					margin: 0 auto;
				}

				.wps-data-actions-grid {
					display: grid;
					grid-template-columns: repeat(2, minmax(0, 1fr));
					gap: 20px;
					align-items: stretch;
				}

				@media (max-width: 900px) {
					.wps-data-actions-grid {
						grid-template-columns: 1fr;
					}
				}

				.wps-data-report {
					margin-top: 24px;
				}

				.wps-data-report h4 {
					margin-bottom: 8px;
				}

				.wps-data-report h5 {
					margin: 20px 0 8px;
				}

				.wps-data-report .wps-table {
					width: 100%;
				}

				.wps-pre-wrap {
					white-space: pre-wrap;
					word-break: break-word;
					font-size: 12px;
					line-height:1.0;
					background: #f9fafb;
					border: 1px solid #e5e7eb;
					border-radius: 8px;
					padding: 12px;
					margin: 0;
				}

				.wps-data-report-raw summary {
					cursor: pointer;
					font-weight: 600;
					margin-bottom: 8px;
				}
			</style>

			<div class="wps-privacy-dashboard-layout">
				<div class="wps-privacy-dashboard-main">
					<!-- Consent Management -->
					<?php
					wpshadow_render_card(
						array(
							'title'     => __( 'Data Collection Preferences', 'wpshadow' ),
							'title_tag' => 'h2',
							'icon'      => 'dashicons-forms',
							'body'      => function() use ( $prefs ) {
								self::render_consent_controls( $prefs );
							},
						)
					);
					?>

					<!-- Data Management Actions -->
					<?php
					wpshadow_render_card(
						array(
							'title'     => __( 'Data Management', 'wpshadow' ),
							'title_tag' => 'h2',
							'icon'      => 'dashicons-database',
							'body'      => function() {
								self::render_data_actions();
							},
						)
					);
					?>

					<!-- Consent History -->
					<?php
					wpshadow_render_card(
						array(
							'title'     => __( 'Consent History', 'wpshadow' ),
							'title_tag' => 'h2',
							'icon'      => 'dashicons-clock',
							'body'      => function() use ( $user_id ) {
								self::render_consent_history( $user_id );
							},
						)
					);
					?>

					<!-- Third-Party Services -->
					<?php
					wpshadow_render_card(
						array(
							'title'     => __( 'Third-Party Services', 'wpshadow' ),
							'title_tag' => 'h2',
							'icon'      => 'dashicons-admin-links',
							'body'      => function() {
								self::render_third_party_services();
							},
						)
					);
					?>
				</div>

				<div class="wps-privacy-dashboard-side">
					<!-- Privacy Score Card -->
					<?php
					wpshadow_render_card(
						array(
							'title'     => __( 'Privacy Status', 'wpshadow' ),
							'title_tag' => 'h2',
							'icon'      => 'dashicons-shield-alt',
							'body'      => function() use ( $prefs ) {
								self::render_privacy_score( $prefs );
							},
						)
					);
					?>

					<!-- What We Collect -->
					<?php
					wpshadow_render_card(
						array(
							'title'     => __( 'What We Collect', 'wpshadow' ),
							'title_tag' => 'h2',
							'icon'      => 'dashicons-search',
							'body'      => function() {
								self::render_data_collection_info();
							},
						)
					);
					?>

					<!-- Your Data -->
					<?php
					wpshadow_render_card(
						array(
							'title'     => __( 'Your Data', 'wpshadow' ),
							'title_tag' => 'h2',
							'icon'      => 'dashicons-archive',
							'body'      => function() use ( $user_id ) {
								self::render_user_data( $user_id );
							},
						)
					);
					?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render privacy score section.
	 *
	 * @since 0.6093.1200
	 * @param  array $prefs User consent preferences.
	 * @return void
	 */
	private static function render_privacy_score( $prefs ) {
		$score = $prefs['anonymized_telemetry'] ? 95 : 100;
		$color = $score === 100 ? '#22c55e' : '#6366F1';

		?>
		<?php
		$status_label = 100 === $score ? __( 'Excellent', 'wpshadow' ) : __( 'High', 'wpshadow' );
		$radius       = 85;
		$dash_total   = 2 * M_PI * $radius;
		$dash_value   = ( $score / 100 ) * $dash_total;
		$dash_gap     = max( 0, $dash_total - $dash_value );
		?>
		<div class="wps-privacy-score-container">
			<div class="wps-privacy-score-gauge">
				<svg width="200" height="200" viewBox="0 0 200 200" class="wps-health-gauge-svg" aria-labelledby="privacy-score-title" role="img">
					<title id="privacy-score-title">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %d: privacy score percentage */
								__( 'Privacy score: %d%%', 'wpshadow' ),
								$score
							)
						);
						?>
					</title>
					<circle cx="100" cy="100" r="95" fill="none" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="2" opacity="0.2" />
					<circle cx="100" cy="100" r="85" fill="none" stroke="#e0e0e0" stroke-width="16" />
					<circle cx="100" cy="100" r="85" fill="none" stroke="<?php echo esc_attr( $color ); ?>" stroke-width="16"
						stroke-dasharray="<?php echo esc_attr( sprintf( '%.2f %.2f', $dash_value, $dash_gap ) ); ?>"
						stroke-linecap="round" transform="rotate(-90 100 100)"
						class="wps-gauge-progress" />
				<text x="100" y="110" text-anchor="middle" font-size="48" font-weight="bold" fill="<?php echo esc_attr( $color ); ?>"><?php echo esc_html( $score ); ?>%</text>
				<text x="100" y="135" text-anchor="middle" font-size="16" fill="#666"><?php echo esc_html( $status_label ); ?></text>
				</svg>
				<p class="wps-privacy-score-label">
					<?php esc_html_e( 'Privacy Score', 'wpshadow' ); ?>
				</p>
			</div>
			<div class="wps-privacy-score-details">
				<h3>
					<?php
					if ( 100 === $score ) {
						esc_html_e( 'Maximum Privacy Protection', 'wpshadow' );
					} else {
						esc_html_e( 'High Privacy Protection', 'wpshadow' );
					}
					?>
				</h3>
				<ul class="wps-privacy-score-list">
					<li><?php esc_html_e( 'No personal information collected', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'All data stored locally on your server', 'wpshadow' ); ?></li>
					<li><?php esc_html_e( 'No tracking cookies or external beacons', 'wpshadow' ); ?></li>
					<?php if ( ! $prefs['anonymized_telemetry'] ) : ?>
						<li><?php esc_html_e( 'Anonymous usage data: Disabled', 'wpshadow' ); ?></li>
					<?php else : ?>
						<li><?php esc_html_e( 'Anonymous usage data: Enabled (fully anonymized)', 'wpshadow' ); ?></li>
					<?php endif; ?>
				</ul>
				<?php if ( $prefs['consented_at'] ) : ?>
					<p class="wps-privacy-score-note">
						<?php
						printf(
							/* translators: %s: formatted date */
							esc_html__( 'Last updated: %s', 'wpshadow' ),
							esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $prefs['consented_at'] ) ) )
						);
						?>
					</p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render consent controls.
	 *
	 * @since 0.6093.1200
	 * @param  array $prefs User consent preferences.
	 * @return void
	 */
	private static function render_consent_controls( $prefs ) {
		?>
		<form id="wpshadow-consent-form" class="wps-consent-form">
			<?php wp_nonce_field( 'wpshadow_privacy_actions', 'wpshadow_consent_nonce' ); ?>

			<div class="wps-consent-stack">
				<?php
				// Essential Functions card.
				wpshadow_render_card(
					array(
						'title'       => __( 'Essential Functions', 'wpshadow' ),
						'description' => __( 'Required for basic plugin functionality. Always on.', 'wpshadow' ),
						'icon'        => 'dashicons-shield',
						'badge'       => array(
							'label' => __( 'Required', 'wpshadow' ),
							'class' => 'wps-badge wps-badge--success',
						),
						'body'        => function() {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'consent-essential',
									'label'       => __( 'Always enabled', 'wpshadow' ),
									'helper_text' => __( 'Core features like diagnostics and settings storage.', 'wpshadow' ),
									'checked'     => true,
									'disabled'    => true,
								)
							);
							?>
							<ul class="wps-consent-feature-list">
								<li><?php esc_html_e( 'Settings and preferences storage', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Diagnostic scan results (local database)', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Activity and audit logging (local only)', 'wpshadow' ); ?></li>
							</ul>
							<?php
						},
					)
				);

				// Anonymous Usage Data card.
				wpshadow_render_card(
					array(
						'title'       => __( 'Anonymous Usage Data', 'wpshadow' ),
						'description' => __( 'Share anonymous usage data to help us improve WPShadow.', 'wpshadow' ),
						'icon'        => 'dashicons-chart-bar',
						'badge'       => array(
							'label' => __( 'Optional', 'wpshadow' ),
							'class' => 'wps-badge wps-badge--info',
						),
						'body'        => function() use ( $prefs ) {
							// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Form_Controls outputs escaped HTML.
							echo \WPShadow\Helpers\Form_Controls::toggle_switch(
								array(
									'id'          => 'consent-telemetry',
									'name'        => 'anonymized_telemetry',
									'label'       => __( 'Share anonymous usage data', 'wpshadow' ),
									'helper_text' => __( 'This is fully anonymized and never includes personal data.', 'wpshadow' ),
									'checked'     => $prefs['anonymized_telemetry'],
								)
							);
							?>
							<ul class="wps-consent-feature-list">
								<li><?php esc_html_e( 'Which features you use (no personal info)', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Diagnostic scan frequency and results', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'Treatment success/failure rates', 'wpshadow' ); ?></li>
								<li><?php esc_html_e( 'WordPress and PHP versions (for compatibility)', 'wpshadow' ); ?></li>
							</ul>
							<div class="wps-consent-benefit-box">
								<strong class="wps-consent-benefit-title">
									<?php esc_html_e( 'We never collect:', 'wpshadow' ); ?>
								</strong>
								<ul class="wps-consent-benefit-list">
									<li><?php esc_html_e( 'Your name, email, or personal information', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Your site URL, domain name, or content', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'User data, post content, or comments', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Anything that could identify you or your site', 'wpshadow' ); ?></li>
								</ul>
							</div>
							<?php
						},
					)
				);
				?>
			</div>

			<div class="wps-consent-footer">
				<button type="submit" class="button button-primary button-large">
					<?php esc_html_e( 'Save Privacy Preferences', 'wpshadow' ); ?>
				</button>
				<p class="wps-consent-footer-note">
					<?php esc_html_e( 'Changes take effect immediately. You can update these preferences anytime.', 'wpshadow' ); ?>
				</p>
			</div>
		</form>
		<?php
	}

	/**
	 * Render data collection information.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	private static function render_data_collection_info() {
		?>
		<div class="wps-data-collection-grid">
			<!-- Settings Data -->
			<div class="wps-data-collection-card wps-data-collection-card-config">
				<h4>
					<span class="dashicons dashicons-admin-settings"></span>
					<?php esc_html_e( 'Settings & Preferences', 'wpshadow' ); ?>
				</h4>
				<p>
					<?php esc_html_e( 'Plugin configuration, user preferences, and consent choices. Stored in your WordPress database.', 'wpshadow' ); ?>
				</p>
			</div>

			<!-- Diagnostic Results -->
			<div class="wps-data-collection-card wps-data-collection-card-diagnostic">
				<h4>
					<span class="dashicons dashicons-search"></span>
					<?php esc_html_e( 'Diagnostic Scan Results', 'wpshadow' ); ?>
				</h4>
				<p>
					<?php esc_html_e( 'Security, performance, and SEO findings. Cached locally for faster dashboard loading.', 'wpshadow' ); ?>
				</p>
			</div>

			<!-- Activity Logs -->
			<div class="wps-data-collection-card wps-data-collection-card-activity">
				<h4>
					<span class="dashicons dashicons-list-view"></span>
					<?php esc_html_e( 'Activity Logs', 'wpshadow' ); ?>
				</h4>
				<p>
					<?php esc_html_e( 'Audit trail of treatments applied and settings changed. Never leaves your server.', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

		<div class="wps-privacy-commitment">
			<h4>
				<span class="dashicons dashicons-shield"></span>
				<?php esc_html_e( 'Data Storage', 'wpshadow' ); ?>
			</h4>
			<p>
				<?php esc_html_e( 'All data is stored locally in your WordPress database. No external servers, no cloud storage, no third-party services (unless you enable optional telemetry).', 'wpshadow' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Render user data summary.
	 *
	 * @since 0.6093.1200
	 * @param  int $user_id User ID.
	 * @return void
	 */
	private static function render_user_data( $user_id ) {
		$data_points = array();
		$settings    = get_option( 'wpshadow_settings', array() );

		// Count settings
		$settings_count = count( $settings );
		if ( $settings_count > 0 ) {
			$data_points[] = sprintf(
				/* translators: %d: number of settings */
				_n( '%d plugin setting', '%d plugin settings', $settings_count, 'wpshadow' ),
				$settings_count
			);
		}

		// Count user meta
		$user_meta    = get_user_meta( $user_id );
		$wpshadow_meta = array_filter( $user_meta, function ( $key ) {
			return 0 === strpos( $key, 'wpshadow_' );
		}, ARRAY_FILTER_USE_KEY );

		if ( ! empty( $wpshadow_meta ) ) {
			$data_points[] = sprintf(
				/* translators: %d: number of preferences */
				_n( '%d user preference', '%d user preferences', count( $wpshadow_meta ), 'wpshadow' ),
				count( $wpshadow_meta )
			);
		}

		// Count activity logs (if Activity_Logger exists)
		$activity_logs = array();
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			$result = \WPShadow\Core\Activity_Logger::get_activities(
				array( 'user_id' => $user_id ),
				500,
				0  // offset
			);
			$activity_logs = isset( $result['activities'] ) ? $result['activities'] : array();
			$logs_count    = isset( $result['total'] ) ? $result['total'] : count( $activity_logs );
			if ( $logs_count > 0 ) {
				$data_points[] = sprintf(
					/* translators: %d: number of log entries */
					_n( '%d activity log entry', '%d activity log entries', $logs_count, 'wpshadow' ),
					$logs_count
				);
			}
		}

		$consent = Consent_Preferences::get_preferences( $user_id );
		$format_value = function ( $value ) {
			if ( is_bool( $value ) ) {
				return $value ? __( 'Yes', 'wpshadow' ) : __( 'No', 'wpshadow' );
			}
			if ( is_array( $value ) || is_object( $value ) ) {
				return wp_json_encode( $value, JSON_PRETTY_PRINT );
			}
			if ( '' === $value || null === $value ) {
				return __( 'None', 'wpshadow' );
			}
			return (string) $value;
		};

		$report_data = array(
			'generated_at'  => current_time( 'mysql' ),
			'user'          => array(
				'id'           => $user_id,
				'display_name' => wp_get_current_user()->display_name,
				'email'        => wp_get_current_user()->user_email,
			),
			'consent'       => $consent,
			'settings'      => $settings,
			'user_meta'     => $wpshadow_meta,
			'activity_logs' => $activity_logs,
		);

		?>
		<div class="wps-data-management-container">
			<p class="wps-data-management-intro">
				<?php esc_html_e( 'Here\'s what WPShadow has stored about you:', 'wpshadow' ); ?>
			</p>

			<?php if ( ! empty( $data_points ) ) : ?>
				<ul class="wps-data-management-list">
					<?php foreach ( $data_points as $point ) : ?>
						<li><?php echo esc_html( $point ); ?></li>
					<?php endforeach; ?>
				</ul>
			<?php else : ?>
				<p class="wps-data-management-note">
					<?php esc_html_e( 'No data stored yet. Start using WPShadow to see your data summary.', 'wpshadow' ); ?>
				</p>
			<?php endif; ?>

			<p class="wps-data-management-footer">
				<?php esc_html_e( 'This is everything WPShadow knows about you. No hidden data, no secrets.', 'wpshadow' ); ?>
			</p>

			<div class="wps-data-report">
				<h4><?php esc_html_e( 'Your Data Report', 'wpshadow' ); ?></h4>
				<p class="wps-data-management-note">
					<?php esc_html_e( 'Open the full privacy report to see a complete, well-formatted breakdown of everything WPShadow stores about you.', 'wpshadow' ); ?>
				</p>
				<a class="wps-btn wps-btn--secondary wps-mb-3" href="<?php echo esc_url( add_query_arg( array( 'page' => 'wpshadow-reports', 'report' => 'user-privacy-report', 'user_id' => $user_id ), admin_url( 'admin.php' ) ) ); ?>">
					<span class="dashicons dashicons-privacy"></span>
					<?php esc_html_e( 'Open Full Privacy Report', 'wpshadow' ); ?>
				</a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render data management actions.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	private static function render_data_actions() {
		?>
		<div class="wps-data-actions-grid">
			<!-- Export Data -->
			<div class="wps-data-action-card wps-data-action-card-export">
				<h3>
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Export Your Data', 'wpshadow' ); ?>
				</h3>
				<p>
					<?php esc_html_e( 'Download a complete copy of all your WPShadow data in JSON format.', 'wpshadow' ); ?>
				</p>
				<button type="button" class="button button-primary" id="wpshadow-export-data-btn">
					<?php esc_html_e( 'Export Data', 'wpshadow' ); ?>
				</button>
			</div>

			<!-- Delete Data -->
			<div class="wps-data-action-card wps-data-action-card-delete">
				<h3>
					<span class="dashicons dashicons-trash"></span>
					<?php esc_html_e( 'Delete Your Data', 'wpshadow' ); ?>
				</h3>
				<p>
					<?php esc_html_e( 'Permanently delete all your WPShadow data. This action cannot be undone.', 'wpshadow' ); ?>
				</p>
				<button type="button" class="button button-danger" id="wpshadow-delete-data-btn">
					<?php esc_html_e( 'Delete All Data', 'wpshadow' ); ?>
				</button>
			</div>
		</div>
		<?php
	}

	/**
	 * Render consent history.
	 *
	 * @since 0.6093.1200
	 * @param  int $user_id User ID.
	 * @return void
	 */
	private static function render_consent_history( $user_id ) {
		// Get consent history from user meta
		$history = get_user_meta( $user_id, 'wpshadow_consent_history', true );

		if ( empty( $history ) || ! is_array( $history ) ) {
			$history = array();
		}

		// Discard any malformed entries (entries must be arrays with expected keys).
		$history = array_values(
			array_filter(
				$history,
				function ( $entry ) {
					return is_array( $entry ) && isset( $entry['timestamp'], $entry['action'] );
				}
			)
		);

		// Add current consent as most recent
		$prefs = Consent_Preferences::get_preferences( $user_id );
		if ( $prefs['consented_at'] ) {
			array_unshift( $history, array(
				'timestamp' => $prefs['consented_at'],
				'telemetry' => $prefs['anonymized_telemetry'],
				'action'    => 'updated',
			) );
		}

		?>
		<?php if ( ! empty( $history ) ) : ?>
			<table class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Action', 'wpshadow' ); ?></th>
						<th><?php esc_html_e( 'Anonymous Telemetry', 'wpshadow' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( array_slice( $history, 0, 10 ) as $entry ) : ?>
						<tr>
							<td><?php echo esc_html( wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry['timestamp'] ) ) ); ?></td>
							<td><?php echo esc_html( ucfirst( $entry['action'] ) ); ?></td>
							<td>
								<?php if ( $entry['telemetry'] ) : ?>
									<span class="wps-activity-status-enabled">✓ <?php esc_html_e( 'Enabled', 'wpshadow' ); ?></span>
								<?php else : ?>
									<span class="wps-activity-status-disabled">○ <?php esc_html_e( 'Disabled', 'wpshadow' ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php else : ?>
			<p class="wps-activity-log-empty">
				<?php esc_html_e( 'No consent history available yet.', 'wpshadow' ); ?>
			</p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render third-party services disclosure.
	 *
	 * @since 0.6093.1200
	 * @return void
	 */
	private static function render_third_party_services() {
		?>
		<p class="wps-external-services-intro">
			<?php esc_html_e( 'WPShadow may contact these external services:', 'wpshadow' ); ?>
		</p>

		<div class="wps-external-services-grid">
			<div class="wps-external-service-card">
				<strong>wpshadow.com</strong>
				<p>
					<?php esc_html_e( 'Knowledge base articles and training videos (when you click links)', 'wpshadow' ); ?>
				</p>
			</div>

			<div class="wps-external-service-card">
				<strong><?php esc_html_e( 'WordPress.org', 'wpshadow' ); ?></strong>
				<p>
					<?php esc_html_e( 'Plugin updates and version checks (standard WordPress functionality)', 'wpshadow' ); ?>
				</p>
			</div>
		</div>

		<div class="wps-external-services-note">
			<p>
				<strong><?php esc_html_e( 'Future Pro Features:', 'wpshadow' ); ?></strong>
				<?php esc_html_e( 'If you enable optional Pro features in the future, some may use WPShadow Cloud services to access your WPShadow server for cloud scanning or email notifications. We\'ll always ask for your consent first and explain exactly what data is shared.', 'wpshadow' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Handle data export AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending file.
	 */
	public static function handle_export_data() {
		// Use Security_Validator for consistent security checks
		\WPShadow\Core\Security_Validator::verify_request( 'wpshadow_privacy_actions', 'manage_options', 'nonce' );

		$user_id = get_current_user_id();

		// Gather all WPShadow data for this user
		$export_data = array(
			'export_date'    => current_time( 'mysql' ),
			'user_id'        => $user_id,
			'user_email'     => wp_get_current_user()->user_email,
			'plugin_version' => WPSHADOW_VERSION,
			'settings'       => get_option( 'wpshadow_settings', array() ),
			'user_meta'      => array(),
			'consent'        => Consent_Preferences::get_preferences( $user_id ),
		);

		// Get all user meta with wpshadow_ prefix
		$all_user_meta = get_user_meta( $user_id );
		foreach ( $all_user_meta as $key => $value ) {
			if ( 0 === strpos( $key, 'wpshadow_' ) ) {
				$export_data['user_meta'][ $key ] = $value[0];
			}
		}

		// Get activity logs if available
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			$export_data['activity_logs'] = \WPShadow\Core\Activity_Logger::get_user_activities( $user_id );
		}

		// Convert to JSON
		$json = wp_json_encode( $export_data, JSON_PRETTY_PRINT );

		// Send as download
		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename="wpshadow-data-export-' . date( 'Y-m-d' ) . '.json"' );
		header( 'Content-Length: ' . strlen( $json ) );

		echo $json; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		exit;
	}

	/**
	 * Handle data deletion AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_delete_data() {
		// Use Security_Validator for consistent security checks
		if ( ! \WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_privacy_actions', 'nonce', false ) ||
			 ! \WPShadow\Core\Security_Validator::verify_capability( 'manage_options', false ) ) {
			wp_send_json_error( array( 'message' => \WPShadow\Core\Security_Validator::get_permission_error() ) );
		}

		$user_id = get_current_user_id();

		// Delete all user meta with wpshadow_ prefix
		$all_user_meta = get_user_meta( $user_id );
		foreach ( $all_user_meta as $key => $value ) {
			if ( 0 === strpos( $key, 'wpshadow_' ) ) {
				delete_user_meta( $user_id, $key );
			}
		}

		// Delete activity logs if available
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::delete_user_activities( $user_id );
		}

		wp_send_json_success( array(
			'message' => __( 'All your WPShadow data has been permanently deleted.', 'wpshadow' ),
		) );
	}

	/**
	 * Handle consent update AJAX request.
	 *
	 * @since 0.6093.1200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_update_consent() {
		// Use Security_Validator for consistent security checks
		if ( ! \WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_privacy_actions', 'nonce', false ) ||
			 ! \WPShadow\Core\Security_Validator::verify_capability( 'manage_options', false ) ) {
			wp_send_json_error( array( 'message' => \WPShadow\Core\Security_Validator::get_permission_error() ) );
		}

		$user_id = get_current_user_id();
		$telemetry = isset( $_POST['anonymized_telemetry'] ) && '1' === $_POST['anonymized_telemetry'];

		Consent_Preferences::set_preferences( $user_id, array(
			'anonymized_telemetry' => $telemetry,
		) );

		// Add to consent history
		$history   = get_user_meta( $user_id, 'wpshadow_consent_history', true ) ?: array();
		$history[] = array(
			'timestamp' => current_time( 'mysql' ),
			'telemetry' => $telemetry,
			'action'    => 'updated',
		);
		update_user_meta( $user_id, 'wpshadow_consent_history', array_slice( $history, -50 ) ); // Keep last 50

		wp_send_json_success( array(
			'message' => __( 'Privacy preferences updated successfully', 'wpshadow' ),
		) );
	}
}
