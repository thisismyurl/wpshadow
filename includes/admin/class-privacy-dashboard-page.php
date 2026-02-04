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
 * @since      1.6004.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Privacy\Consent_Preferences;
use WPShadow\Privacy\Privacy_Policy_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Dashboard Page Class
 *
 * Provides complete transparency about data collection, storage, and usage.
 * Users can view, export, and delete their data.
 *
 * @since 1.6004.0200
 */
class Privacy_Dashboard_Page {

	/**
	 * Initialize the privacy dashboard.
	 *
	 * @since 1.6004.0200
	 * @return void
	 */
	public static function init() {
		// Note: Menu registration moved to Gamification_UI::register_menu_pages()
		// to place Privacy under Achievements submenu
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
		add_action( 'wp_ajax_wpshadow_export_data', array( __CLASS__, 'handle_export_data' ) );
		add_action( 'wp_ajax_wpshadow_delete_data', array( __CLASS__, 'handle_delete_data' ) );
		add_action( 'wp_ajax_wpshadow_update_consent', array( __CLASS__, 'handle_update_consent' ) );
	}

	/**
	 * Add privacy dashboard menu page (now called by Gamification_UI).
	 *
	 * @since  1.6004.0200
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
	 * @since  1.6004.0200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( 'wpshadow_page_wpshadow-privacy' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'wpshadow-admin' );
		wp_enqueue_style(
			'wpshadow-privacy-dashboard',
			WPSHADOW_URL . 'assets/css/privacy-dashboard.css',
			array( 'wpshadow-admin' ),
			WPSHADOW_VERSION
		);

		wp_enqueue_script( 'wpshadow-admin' );
		wp_enqueue_script(
			'wpshadow-privacy-dashboard',
			WPSHADOW_URL . 'assets/js/privacy-dashboard.js',
			array( 'jquery', 'wpshadow-admin' ),
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
					'consent_error'  => __( 'Failed to save preferences. Please try again.', 'wpshadow' ),
					'exporting'      => __( 'Exporting...', 'wpshadow' ),
					'export_data'    => __( 'Export My Data', 'wpshadow' ),
					'export_error'   => __( 'Export failed. Please try again.', 'wpshadow' ),
					'deleting'       => __( 'Deleting...', 'wpshadow' ),
					'delete_confirm' => __( 'Are you sure? This will permanently delete all WPShadow data. This action cannot be undone.', 'wpshadow' ),
					'delete_success' => __( 'All data deleted successfully.', 'wpshadow' ),
					'delete_error'   => __( 'Deletion failed. Please try again.', 'wpshadow' ),
					'delete_data'    => __( 'Delete All Data', 'wpshadow' ),
				),
			)
		);
	}

	/**
	 * Render privacy dashboard page.
	 *
	 * @since  1.6004.0200
	 * @return void
	 */
	public static function render_page() {
		$user_id = get_current_user_id();
		$prefs   = Consent_Preferences::get_preferences( $user_id );

		?>
		<div class="wrap wpshadow-privacy wps-page-container">
			<div class="wps-page-header">
				<h1 class="wps-page-title">
					<span class="dashicons dashicons-shield-alt"></span>
					<?php esc_html_e( 'Privacy Dashboard', 'wpshadow' ); ?>
				</h1>
				<p class="wps-page-description">
					<?php esc_html_e( 'Complete transparency and control over your data. Your privacy, your rules.', 'wpshadow' ); ?>
				</p>
			</div>

			<!-- Privacy Score Card -->
			<div class="wps-card">
				<div class="wps-card-header">
					<h2><?php esc_html_e( 'Privacy Status', 'wpshadow' ); ?></h2>
				</div>
				<div class="wps-card-body">
					<?php self::render_privacy_score( $prefs ); ?>
				</div>
			</div>

			<!-- Consent Management -->
			<div class="wps-card">
				<div class="wps-card-header">
					<h2><?php esc_html_e( 'Data Collection Preferences', 'wpshadow' ); ?></h2>
				</div>
				<div class="wps-card-body">
					<?php self::render_consent_controls( $prefs ); ?>
				</div>
			</div>

			<!-- What We Collect -->
			<div class="wps-card">
				<div class="wps-card-header">
					<h2><?php esc_html_e( 'What We Collect', 'wpshadow' ); ?></h2>
				</div>
				<div class="wps-card-body">
					<?php self::render_data_collection_info(); ?>
				</div>
			</div>

			<!-- Your Data -->
			<div class="wps-card">
				<div class="wps-card-header">
					<h2><?php esc_html_e( 'Your Data', 'wpshadow' ); ?></h2>
				</div>
				<div class="wps-card-body">
					<?php self::render_user_data( $user_id ); ?>
				</div>
			</div>

			<!-- Data Management Actions -->
			<div class="wps-card">
				<div class="wps-card-header">
					<h2><?php esc_html_e( 'Data Management', 'wpshadow' ); ?></h2>
				</div>
				<div class="wps-card-body">
					<?php self::render_data_actions(); ?>
				</div>
			</div>

			<!-- Consent History -->
			<div class="wps-card">
				<div class="wps-card-header">
					<h2><?php esc_html_e( 'Consent History', 'wpshadow' ); ?></h2>
				</div>
				<div class="wps-card-body">
					<?php self::render_consent_history( $user_id ); ?>
				</div>
			</div>

			<!-- Third-Party Services -->
			<div class="wps-card">
				<div class="wps-card-header">
					<h2><?php esc_html_e( 'Third-Party Services', 'wpshadow' ); ?></h2>
				</div>
				<div class="wps-card-body">
					<?php self::render_third_party_services(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render privacy score section.
	 *
	 * @since  1.6004.0200
	 * @param  array $prefs User consent preferences.
	 * @return void
	 */
	private static function render_privacy_score( $prefs ) {
		$score = $prefs['anonymized_telemetry'] ? 95 : 100;
		$color = $score === 100 ? '#22c55e' : '#6366F1';

		?>
		<div class="wps-privacy-score-container">
			<div class="wps-privacy-score-badge">
				<div class="wps-privacy-score-circle" style="background: <?php echo esc_attr( $color ); ?>;">
					<span class="wps-privacy-score-number"><?php echo esc_html( $score ); ?></span>
				</div>
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
	 * @since  1.6004.0200
	 * @param  array $prefs User consent preferences.
	 * @return void
	 */
	private static function render_consent_controls( $prefs ) {
		?>
		<form id="wpshadow-consent-form" class="wps-consent-form">
			<?php wp_nonce_field( 'wpshadow_privacy_actions', 'wpshadow_consent_nonce' ); ?>

			<!-- Essential (Always On) -->
			<div class="wps-form-group wps-consent-required">
				<div class="wps-consent-group">
					<input type="checkbox" checked disabled class="wps-consent-checkbox" />
					<div class="wps-consent-content">
						<strong class="wps-consent-title">
							<?php esc_html_e( 'Essential Functions', 'wpshadow' ); ?>
						</strong>
						<span class="wps-consent-badge-required">
							<?php esc_html_e( 'Required', 'wpshadow' ); ?>
						</span>
						<p class="wps-consent-description">
							<?php esc_html_e( 'Required for basic plugin functionality. Cannot be disabled.', 'wpshadow' ); ?>
						</p>
						<ul class="wps-consent-feature-list">
							<li><?php esc_html_e( 'Settings and preferences storage', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Diagnostic scan results (local database)', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Activity and audit logging (local only)', 'wpshadow' ); ?></li>
						</ul>
					</div>
				</div>
			</div>

			<!-- Anonymous Telemetry (Opt-In) -->
			<div class="wps-form-group wps-consent-optional">
				<div class="wps-consent-group">
					<input
						type="checkbox"
						name="anonymized_telemetry"
						id="consent-telemetry"
						value="1"
						<?php checked( $prefs['anonymized_telemetry'] ); ?>
						class="wps-consent-checkbox"
					/>
					<div class="wps-consent-content">
						<label for="consent-telemetry" class="wps-consent-label">
							<strong class="wps-consent-title">
								<?php esc_html_e( 'Anonymous Usage Data', 'wpshadow' ); ?>
							</strong>
							<span class="wps-consent-badge-optional">
								<?php esc_html_e( 'Optional', 'wpshadow' ); ?>
							</span>
						</label>
						<p class="wps-consent-description">
							<?php esc_html_e( 'Help us improve WPShadow by sharing anonymous usage data.', 'wpshadow' ); ?>
						</p>
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
					</div>
				</div>
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
	 * @since  1.6004.0200
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
	 * @since  1.6004.0200
	 * @param  int $user_id User ID.
	 * @return void
	 */
	private static function render_user_data( $user_id ) {
		$data_points = array();

		// Count settings
		$settings_count = count( get_option( 'wpshadow_settings', array() ) );
		if ( $settings_count > 0 ) {
			$data_points[] = sprintf(
				/* translators: %d: number of settings */
				_n( '%d plugin setting', '%d plugin settings', $settings_count, 'wpshadow' ),
				$settings_count
			);
		}

		// Count user meta
		$user_meta = get_user_meta( $user_id );
		$wpshadow_meta = array_filter( $user_meta, function( $key ) {
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
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			$logs_count = \WPShadow\Core\Activity_Logger::count_user_activities( $user_id );
			if ( $logs_count > 0 ) {
				$data_points[] = sprintf(
					/* translators: %d: number of log entries */
					_n( '%d activity log entry', '%d activity log entries', $logs_count, 'wpshadow' ),
					$logs_count
				);
			}
		}

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
		</div>
		<?php
	}

	/**
	 * Render data management actions.
	 *
	 * @since  1.6004.0200
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
	 * @since  1.6004.0200
	 * @param  int $user_id User ID.
	 * @return void
	 */
	private static function render_consent_history( $user_id ) {
		// Get consent history from user meta
		$history = get_user_meta( $user_id, 'wpshadow_consent_history', true );

		if ( empty( $history ) || ! is_array( $history ) ) {
			$history = array();
		}

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
	 * @since  1.6004.0200
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
				<?php esc_html_e( 'If you enable optional Pro features in the future, some may use external services (cloud scanning, email notifications). We\'ll always ask for your consent first and explain exactly what data is shared.', 'wpshadow' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Handle data export AJAX request.
	 *
	 * @since  1.6004.0200
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
	 * @since  1.6004.0200
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
	 * @since  1.6004.0200
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
