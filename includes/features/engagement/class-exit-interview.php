<?php
/**
 * Exit Interview Manager
 *
 * Manages exit interviews when users deactivate or uninstall the plugin.
 * Collects feedback, permission for contact, and optional diagnostic data.
 *
 * Philosophy:
 * - Commandment #1 (Helpful Neighbor) - Friendly, non-pushy feedback collection
 * - Commandment #10 (Beyond Pure) - Privacy first, explicit consent required
 * - CANON: Accessibility First - WCAG AA compliant modals
 *
 * @since   1.6030.2148
 * @package WPShadow\Engagement
 */

declare(strict_types=1);

namespace WPShadow\Engagement;

use WPShadow\Core\Error_Handler;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exit Interview Manager Class
 *
 * Handles exit interviews for plugin deactivation and uninstall.
 */
class Exit_Interview extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_footer-plugins.php' => 'render_deactivation_modal',
			'admin_enqueue_scripts'    => 'enqueue_assets',
		);
	}

	/**
	 * Initialize the exit interview system (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Exit_Interview::subscribe() instead
	 * @since      1.6030.2148
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Enqueue assets for exit interview
	 *
	 * @since  1.6030.2148
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		// Only load on plugins page
		if ( 'plugins.php' !== $hook ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-exit-interview',
			WPSHADOW_URL . 'assets/css/exit-interview.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-exit-interview',
			WPSHADOW_URL . 'assets/js/exit-interview.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		// Localize script with data
		wp_localize_script(
			'wpshadow-exit-interview',
			'wpshadowExitInterview',
			array(
				'nonce'          => wp_create_nonce( 'wpshadow_exit_interview' ),
				'plugin_slug'    => WPSHADOW_BASENAME,
				'site_url'       => get_site_url(),
				'site_name'      => get_bloginfo( 'name' ),
				'plugin_version' => WPSHADOW_VERSION,
				'wp_version'     => get_bloginfo( 'version' ),
				'php_version'    => PHP_VERSION,
				'active_plugins' => self::get_active_plugins_count(),
				'active_theme'   => wp_get_theme()->get( 'Name' ),
			)
		);
	}

	/**
	 * Render deactivation modal HTML
	 *
	 * @since  1.6030.2148
	 * @return void
	 */
	public static function render_deactivation_modal() {
		$plugin_name = 'WPShadow';
		?>
		<div id="wpshadow-exit-interview-modal" class="wpshadow-modal" role="dialog" aria-labelledby="wpshadow-exit-interview-title" aria-modal="true" style="display: none;">
			<div class="wpshadow-modal-overlay" aria-hidden="true"></div>
			<div class="wpshadow-modal-content">
				<div class="wpshadow-modal-header">
					<h2 id="wpshadow-exit-interview-title">
						<?php
						/* translators: %s: plugin name */
						echo esc_html( sprintf( __( 'Quick Feedback for %s', 'wpshadow' ), $plugin_name ) );
						?>
					</h2>
					<button type="button" class="wpshadow-modal-close" aria-label="<?php echo esc_attr__( 'Close dialog', 'wpshadow' ); ?>">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				
				<div class="wpshadow-modal-body">
					<p class="wpshadow-exit-interview-intro">
						<?php
						echo esc_html__( 'We\'re sorry to see you go! Your feedback helps us improve. This takes less than a minute.', 'wpshadow' );
						?>
					</p>

					<form id="wpshadow-exit-interview-form">
						<div class="wpshadow-form-group">
							<label for="wpshadow-exit-reason" class="wpshadow-form-label">
								<?php echo esc_html__( 'What\'s the main reason you\'re deactivating?', 'wpshadow' ); ?>
								<span class="required" aria-label="<?php echo esc_attr__( 'required', 'wpshadow' ); ?>">*</span>
							</label>
							<select id="wpshadow-exit-reason" name="reason" class="wpshadow-form-control" required aria-required="true">
								<option value=""><?php echo esc_html__( 'Select a reason...', 'wpshadow' ); ?></option>
								<option value="not_working"><?php echo esc_html__( 'The plugin isn\'t working as expected', 'wpshadow' ); ?></option>
								<option value="too_complex"><?php echo esc_html__( 'It\'s too complex for my needs', 'wpshadow' ); ?></option>
								<option value="found_better"><?php echo esc_html__( 'I found a better alternative', 'wpshadow' ); ?></option>
								<option value="temporary"><?php echo esc_html__( 'Temporary deactivation for testing', 'wpshadow' ); ?></option>
								<option value="performance"><?php echo esc_html__( 'Performance or compatibility issues', 'wpshadow' ); ?></option>
								<option value="missing_features"><?php echo esc_html__( 'Missing features I need', 'wpshadow' ); ?></option>
								<option value="switching_site"><?php echo esc_html__( 'Rebuilding or switching to a new site', 'wpshadow' ); ?></option>
								<option value="other"><?php echo esc_html__( 'Other reason', 'wpshadow' ); ?></option>
							</select>
						</div>

						<div class="wpshadow-form-group" id="wpshadow-exit-details-group" style="display: none;">
							<label for="wpshadow-exit-details" class="wpshadow-form-label">
								<?php echo esc_html__( 'Can you tell us more? (optional)', 'wpshadow' ); ?>
							</label>
							<textarea 
								id="wpshadow-exit-details" 
								name="details" 
								class="wpshadow-form-control" 
								rows="3"
								placeholder="<?php echo esc_attr__( 'Your feedback helps us improve...', 'wpshadow' ); ?>"
								aria-describedby="wpshadow-exit-details-help"
							></textarea>
							<p id="wpshadow-exit-details-help" class="wpshadow-help-text">
								<?php echo esc_html__( 'We genuinely want to understand and improve.', 'wpshadow' ); ?>
							</p>
						</div>

						<div class="wpshadow-form-group">
							<label class="wpshadow-checkbox-label">
								<input 
									type="checkbox" 
									id="wpshadow-exit-contact" 
									name="allow_contact" 
									value="1"
								/>
								<span>
									<?php echo esc_html__( 'You can contact me to learn more about my decision', 'wpshadow' ); ?>
								</span>
							</label>
							<p class="wpshadow-help-text">
								<?php
								printf(
									/* translators: %s: admin email */
									esc_html__( 'We\'ll use your site admin email: %s', 'wpshadow' ),
									'<strong>' . esc_html( get_option( 'admin_email' ) ) . '</strong>'
								);
								?>
							</p>
						</div>

						<div role="status" aria-live="polite" class="wpshadow-form-message" style="display: none;"></div>
					</form>
				</div>

				<div class="wpshadow-modal-footer">
					<button type="button" class="wpshadow-button wpshadow-button-secondary" id="wpshadow-exit-skip">
						<?php echo esc_html__( 'Skip & Deactivate', 'wpshadow' ); ?>
					</button>
					<button type="submit" form="wpshadow-exit-interview-form" class="wpshadow-button wpshadow-button-primary" id="wpshadow-exit-submit">
						<?php echo esc_html__( 'Submit & Deactivate', 'wpshadow' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Store deactivation flag to trigger modal
	 *
	 * Called from register_deactivation_hook.
	 *
	 * @since  1.6030.2148
	 * @return void
	 */
	public static function on_deactivation() {
		// Store timestamp for when plugin was deactivated
		update_option( 'wpshadow_last_deactivation', time() );
	}

	/**
	 * Save exit interview response
	 *
	 * @since  1.6030.2148
	 * @param  array $data Exit interview data.
	 * @return bool True on success, false on failure.
	 */
	public static function save_response( $data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wpshadow_exit_interviews';

		try {
			$result = $wpdb->insert(
				$table_name,
				array(
					'reason'         => sanitize_text_field( $data['reason'] ?? '' ),
					'details'        => sanitize_textarea_field( $data['details'] ?? '' ),
					'allow_contact'  => ! empty( $data['allow_contact'] ) ? 1 : 0,
					'contact_email'  => ! empty( $data['allow_contact'] ) ? sanitize_email( get_option( 'admin_email' ) ) : '',
					'site_url'       => esc_url_raw( get_site_url() ),
					'plugin_version' => WPSHADOW_VERSION,
					'wp_version'     => get_bloginfo( 'version' ),
					'php_version'    => PHP_VERSION,
					'created_at'     => current_time( 'mysql' ),
					'interview_type' => 'deactivation',
				),
				array( '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);

			if ( false === $result ) {
				Error_Handler::log_error(
					'Failed to save exit interview response',
					array(
						'error' => $wpdb->last_error,
					)
				);
				return false;
			}

			// Log the activity
			if ( class_exists( '\\WPShadow\\Core\\Activity_Logger' ) ) {
				\WPShadow\Core\Activity_Logger::log(
					'exit_interview_completed',
					'User completed exit interview on deactivation',
					'engagement',
					array(
						'reason'        => $data['reason'] ?? 'unknown',
						'allow_contact' => ! empty( $data['allow_contact'] ),
					)
				);
			}

			return true;
		} catch ( \Exception $e ) {
			Error_Handler::log_error( 'Exception saving exit interview: ' . $e->getMessage() );
			return false;
		}
	}

	/**
	 * Get count of active plugins (for context)
	 *
	 * @since  1.6030.2148
	 * @return int Number of active plugins.
	 */
	private static function get_active_plugins_count() {
		$active_plugins = get_option( 'active_plugins', array() );
		if ( is_multisite() ) {
			$network_plugins = get_site_option( 'active_sitewide_plugins', array() );
			$active_plugins  = array_merge( $active_plugins, array_keys( $network_plugins ) );
		}
		return count( array_unique( $active_plugins ) );
	}

	/**
	 * Create database table for exit interviews
	 *
	 * @since  1.6030.2148
	 * @return void
	 */
	public static function create_table() {
		global $wpdb;
		$table_name      = $wpdb->prefix . 'wpshadow_exit_interviews';
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			reason varchar(50) NOT NULL,
			details text,
			allow_contact tinyint(1) DEFAULT 0,
			contact_email varchar(255),
			site_url varchar(255),
			plugin_version varchar(20),
			wp_version varchar(20),
			php_version varchar(20),
			created_at datetime NOT NULL,
			interview_type varchar(20) DEFAULT 'deactivation',
			PRIMARY KEY  (id),
			KEY created_at (created_at),
			KEY interview_type (interview_type)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
