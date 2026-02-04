<?php
/**
 * First Activation Welcome Modal
 *
 * Displays a welcome modal on first plugin activation with clear privacy consent options.
 * Phase 6: Privacy & Consent Excellence
 *
 * @package    WPShadow
 * @subpackage Admin
 * @since      1.6004.0200
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Privacy\Consent_Preferences;
use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * First Activation Welcome Modal Class
 *
 * Shows a beautiful, informative welcome screen to new users with transparent
 * privacy choices. No dark patterns, no tricks - just honest conversation.
 *
 * @since 1.6004.0200
 */
class First_Activation_Welcome extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'admin_init'                             => 'check_first_activation',
			'admin_enqueue_scripts'                  => 'enqueue_assets',
			'admin_footer'                           => 'render_modal',
			'wp_ajax_wpshadow_complete_welcome'      => 'handle_complete_welcome',
		);
	}

	/**
	 * Initialize the welcome modal system (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use First_Activation_Welcome::subscribe() instead
	 * @since      1.6004.0200
	 * @return     void
	 */
	public static function init() {
		self::subscribe();
	}

	/**
	 * Check if this is first activation for this user.
	 *
	 * @since  1.6004.0200
	 * @return bool True if should show welcome.
	 */
	public static function should_show_welcome() {
		$user_id = get_current_user_id();

		if ( ! $user_id || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		// Check if user has completed welcome
		$completed = get_user_meta( $user_id, 'wpshadow_welcome_completed', true );
		if ( $completed ) {
			return false;
		}

		// Check if plugin was just activated
		$plugin_activated = \WPShadow\Core\Cache_Manager::get(
		'first_activation_' . $user_id,
		'wpshadow_activation'
	);
		if ( ! $plugin_activated ) {
			return false;
		}

		return true;
	}

	/**
	 * Set flag on plugin activation.
	 *
	 * Called from plugin bootstrap during activation.
	 *
	 * @since  1.6004.0200
	 * @return void
	 */
	public static function mark_first_activation() {
		$user_id = get_current_user_id();
		if ( $user_id ) {
			\WPShadow\Core\Cache_Manager::set(
				'first_activation_' . $user_id,
				true,
				HOUR_IN_SECONDS,
				'wpshadow_activation'
			);
		}
	}

	/**
	 * Check first activation status.
	 *
	 * @since  1.6004.0200
	 * @return void
	 */
	public static function check_first_activation() {
		if ( ! self::should_show_welcome() ) {
			return;
		}

		// Set a flag to show modal
		add_filter( 'admin_body_class', function( $classes ) {
			return $classes . ' wpshadow-first-activation';
		} );
	}

	/**
	 * Enqueue modal assets.
	 *
	 * @since  1.6004.0200
	 * @param  string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( $hook ) {
		if ( ! self::should_show_welcome() ) {
			return;
		}

		wp_enqueue_style( 'wp-components' );

		// Inline CSS for modal
		wp_add_inline_style( 'wp-components', self::get_modal_css() );

		// Inline JS for modal
		wp_add_inline_script( 'jquery', self::get_modal_js() );

		// Localize script
		wp_localize_script( 'jquery', 'wpshadowWelcome', array(
			'nonce'       => wp_create_nonce( 'wpshadow_complete_welcome' ),
			'ajax_url'    => admin_url( 'admin-ajax.php' ),
			'strings'     => array(
				'error' => __( 'Could not save preferences. Please try again.', 'wpshadow' ),
			),
		) );
	}

	/**
	 * Render the welcome modal HTML.
	 *
	 * @since  1.6004.0200
	 * @return void
	 */
	public static function render_modal() {
		if ( ! self::should_show_welcome() ) {
			return;
		}

		$user_id = get_current_user_id();
		$prefs   = Consent_Preferences::get_preferences( $user_id );

		?>
		<div id="wpshadow-welcome-modal" class="wpshadow-welcome-modal" role="dialog" aria-labelledby="wpshadow-welcome-title" aria-modal="true">
			<div class="wpshadow-welcome-overlay"></div>
			<div class="wpshadow-welcome-container">
				<div class="wpshadow-welcome-header">
					<div class="wpshadow-welcome-logo">
						<svg width="48" height="48" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
							<circle cx="24" cy="24" r="24" fill="#6366F1"/>
							<path d="M24 12L32 20L24 28L16 20L24 12Z" fill="white"/>
							<path d="M24 20L32 28L24 36L16 28L24 20Z" fill="white" opacity="0.7"/>
						</svg>
					</div>
					<h1 id="wpshadow-welcome-title"><?php esc_html_e( 'Welcome to WPShadow!', 'wpshadow' ); ?></h1>
					<p class="wpshadow-welcome-subtitle">
						<?php esc_html_e( 'Let\'s get you set up. First, let\'s talk about privacy.', 'wpshadow' ); ?>
					</p>
				</div>

				<div class="wpshadow-welcome-content">
					<div class="wpshadow-privacy-section">
						<h2><?php esc_html_e( 'Your Privacy, Your Choice', 'wpshadow' ); ?></h2>
						<p>
							<?php
							echo wp_kses_post(
								__( 'WPShadow <strong>respects your privacy</strong>. We believe in being completely transparent about what data we collect and why.', 'wpshadow' )
							);
							?>
						</p>

						<div class="wpshadow-consent-options">
							<!-- Essential Functions (Always On) -->
							<div class="wpshadow-consent-item wpshadow-consent-required">
								<div class="wpshadow-consent-header">
									<label>
										<input
											type="checkbox"
											checked
											disabled
											class="wpshadow-consent-checkbox"
										/>
										<strong><?php esc_html_e( 'Essential Functions', 'wpshadow' ); ?></strong>
										<span class="wpshadow-badge wpshadow-badge-required"><?php esc_html_e( 'Required', 'wpshadow' ); ?></span>
									</label>
								</div>
								<p class="wpshadow-consent-description">
									<?php esc_html_e( 'Basic plugin functionality. These are required for WPShadow to work.', 'wpshadow' ); ?>
								</p>
								<ul class="wpshadow-consent-details">
									<li><?php esc_html_e( 'Settings storage (in your database)', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Diagnostic results (stored locally)', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Activity logging (on your server)', 'wpshadow' ); ?></li>
								</ul>
							</div>

							<!-- Error Reporting (Always On) -->
							<div class="wpshadow-consent-item wpshadow-consent-required">
								<div class="wpshadow-consent-header">
									<label>
										<input
											type="checkbox"
											checked
											disabled
											class="wpshadow-consent-checkbox"
										/>
										<strong><?php esc_html_e( 'Error Reporting', 'wpshadow' ); ?></strong>
										<span class="wpshadow-badge wpshadow-badge-required"><?php esc_html_e( 'Required', 'wpshadow' ); ?></span>
									</label>
								</div>
								<p class="wpshadow-consent-description">
									<?php esc_html_e( 'Logs PHP errors and warnings to help us fix bugs.', 'wpshadow' ); ?>
								</p>
								<ul class="wpshadow-consent-details">
									<li><?php esc_html_e( 'Error messages and stack traces', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'PHP and WordPress versions', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Stored locally (never sent to our servers)', 'wpshadow' ); ?></li>
								</ul>
							</div>

							<!-- Anonymous Usage Data (Opt-In) -->
							<div class="wpshadow-consent-item wpshadow-consent-optional">
								<div class="wpshadow-consent-header">
									<label>
										<input
											type="checkbox"
											name="anonymized_telemetry"
											id="wpshadow-consent-telemetry"
											class="wpshadow-consent-checkbox"
											value="1"
											<?php checked( $prefs['anonymized_telemetry'] ); ?>
										/>
										<strong><?php esc_html_e( 'Anonymous Usage Data', 'wpshadow' ); ?></strong>
										<span class="wpshadow-badge wpshadow-badge-optional"><?php esc_html_e( 'Optional', 'wpshadow' ); ?></span>
									</label>
								</div>
								<p class="wpshadow-consent-description">
									<?php esc_html_e( 'Help us improve WPShadow by sharing anonymous usage data.', 'wpshadow' ); ?>
								</p>
								<ul class="wpshadow-consent-details">
									<li><?php esc_html_e( 'Which features you use most', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Diagnostic scan frequency', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Treatment success rates', 'wpshadow' ); ?></li>
									<li><?php esc_html_e( 'Plugin and WordPress versions', 'wpshadow' ); ?></li>
								</ul>
								<div class="wpshadow-consent-promise">
									<strong><?php esc_html_e( 'We never collect:', 'wpshadow' ); ?></strong>
									<ul>
										<li><?php esc_html_e( 'Your name, email, or personal info', 'wpshadow' ); ?></li>
										<li><?php esc_html_e( 'Your site URL or domain', 'wpshadow' ); ?></li>
										<li><?php esc_html_e( 'Your content or user data', 'wpshadow' ); ?></li>
										<li><?php esc_html_e( 'Anything that identifies you', 'wpshadow' ); ?></li>
									</ul>
								</div>
							</div>
						</div>

						<div class="wpshadow-privacy-footer">
							<p>
								<?php
								printf(
									wp_kses_post(
										/* translators: %s: link to privacy policy */
										__( 'You can change these preferences anytime in <strong>WPShadow → Privacy Settings</strong>. Read our complete <a href="%s" target="_blank" rel="noopener">Privacy Policy</a>.', 'wpshadow' )
									),
									esc_url( 'https://wpshadow.com/privacy/' )
								);
								?>
							</p>
						</div>
					</div>
				</div>

				<div class="wpshadow-welcome-footer">
					<button type="button" class="button button-secondary" id="wpshadow-welcome-skip">
						<?php esc_html_e( 'Skip for Now', 'wpshadow' ); ?>
					</button>
					<button type="button" class="button button-primary button-hero" id="wpshadow-welcome-continue">
						<?php esc_html_e( 'Continue to WPShadow', 'wpshadow' ); ?>
					</button>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get modal CSS.
	 *
	 * @since  1.6004.0200
	 * @return string CSS code.
	 */
	private static function get_modal_css() {
		return <<<CSS
.wpshadow-welcome-modal {
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	z-index: 999999;
	display: flex;
	align-items: center;
	justify-content: center;
}

.wpshadow-welcome-overlay {
	position: absolute;
	top: 0;
	left: 0;
	right: 0;
	bottom: 0;
	background: rgba(0, 0, 0, 0.7);
	backdrop-filter: blur(4px);
}

.wpshadow-welcome-container {
	position: relative;
	background: white;
	border-radius: 12px;
	box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
	max-width: 700px;
	max-height: 90vh;
	overflow: auto;
	animation: wpshadowWelcomeSlideIn 0.3s ease-out;
}

@keyframes wpshadowWelcomeSlideIn {
	from {
		opacity: 0;
		transform: translateY(30px);
	}
	to {
		opacity: 1;
		transform: translateY(0);
	}
}

.wpshadow-welcome-header {
	text-align: center;
	padding: 40px 40px 20px;
	background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 100%);
	color: white;
}

.wpshadow-welcome-logo {
	margin-bottom: 20px;
}

.wpshadow-welcome-header h1 {
	margin: 0 0 10px;
	font-size: 32px;
	font-weight: 700;
	color: white;
}

.wpshadow-welcome-subtitle {
	margin: 0;
	font-size: 16px;
	opacity: 0.95;
	color: white;
}

.wpshadow-welcome-content {
	padding: 30px 40px;
}

.wpshadow-privacy-section h2 {
	margin-top: 0;
	font-size: 24px;
	color: #1e1e1e;
}

.wpshadow-privacy-section > p {
	font-size: 15px;
	line-height: 1.6;
	color: #3c434a;
	margin-bottom: 24px;
}

.wpshadow-consent-options {
	display: flex;
	flex-direction: column;
	gap: 20px;
	margin-bottom: 24px;
}

.wpshadow-consent-item {
	border: 2px solid #e0e0e0;
	border-radius: 8px;
	padding: 20px;
	background: #fafafa;
	transition: all 0.2s ease;
}

.wpshadow-consent-item:hover {
	background: #f5f5f5;
	border-color: #6366F1;
}

.wpshadow-consent-required {
	background: #f0f9ff;
	border-color: #0ea5e9;
}

.wpshadow-consent-optional:has(input:checked) {
	background: #f0fdf4;
	border-color: #22c55e;
}

.wpshadow-consent-header {
	margin-bottom: 8px;
}

.wpshadow-consent-header label {
	display: flex;
	align-items: center;
	gap: 10px;
	cursor: pointer;
	font-size: 16px;
	margin: 0;
}

.wpshadow-consent-header label input[disabled] {
	cursor: not-allowed;
}

.wpshadow-consent-checkbox {
	width: 20px;
	height: 20px;
	margin: 0;
}

.wpshadow-badge {
	display: inline-block;
	padding: 2px 8px;
	border-radius: 4px;
	font-size: 11px;
	font-weight: 600;
	text-transform: uppercase;
	letter-spacing: 0.5px;
}

.wpshadow-badge-required {
	background: #0ea5e9;
	color: white;
}

.wpshadow-badge-optional {
	background: #d1d5db;
	color: #4b5563;
}

.wpshadow-consent-description {
	margin: 8px 0;
	font-size: 14px;
	color: #6b7280;
}

.wpshadow-consent-details {
	margin: 8px 0 0 30px;
	font-size: 13px;
	color: #6b7280;
	list-style: disc;
}

.wpshadow-consent-details li {
	margin: 4px 0;
}

.wpshadow-consent-promise {
	margin-top: 12px;
	padding: 12px;
	background: white;
	border-radius: 6px;
	border-left: 3px solid #22c55e;
}

.wpshadow-consent-promise strong {
	display: block;
	margin-bottom: 6px;
	color: #1e1e1e;
}

.wpshadow-consent-promise ul {
	margin: 4px 0 0 20px;
	font-size: 13px;
	color: #6b7280;
	list-style: disc;
}

.wpshadow-privacy-footer {
	padding: 16px;
	background: #f9fafb;
	border-radius: 8px;
	font-size: 13px;
	color: #6b7280;
}

.wpshadow-privacy-footer a {
	color: #6366F1;
	text-decoration: none;
	font-weight: 600;
}

.wpshadow-privacy-footer a:hover {
	text-decoration: underline;
}

.wpshadow-welcome-footer {
	display: flex;
	justify-content: space-between;
	align-items: center;
	padding: 20px 40px;
	border-top: 1px solid #e0e0e0;
	background: #fafafa;
}

.wpshadow-welcome-footer .button-hero {
	padding: 12px 24px;
	font-size: 16px;
}
CSS;
	}

	/**
	 * Get modal JavaScript.
	 *
	 * @since  1.6004.0200
	 * @return string JavaScript code.
	 */
	private static function get_modal_js() {
		return <<<JS
jQuery(document).ready(function($) {
	$('#wpshadow-welcome-continue, #wpshadow-welcome-skip').on('click', function(e) {
		e.preventDefault();

		var telemetry = $('#wpshadow-consent-telemetry').is(':checked');
		var skipped = $(this).attr('id') === 'wpshadow-welcome-skip';

		$.ajax({
			url: wpshadowWelcome.ajax_url,
			type: 'POST',
			data: {
				action: 'wpshadow_complete_welcome',
				nonce: wpshadowWelcome.nonce,
				anonymized_telemetry: telemetry ? '1' : '0',
				skipped: skipped ? '1' : '0'
			},
			success: function(response) {
				if (response.success) {
					$('#wpshadow-welcome-modal').fadeOut(300, function() {
						$(this).remove();
					});

					// Redirect to dashboard if not skipped
					if (!skipped && response.data.redirect) {
						window.location.href = response.data.redirect;
					}
				} else {
					alert(wpshadowWelcome.strings.error);
				}
			},
			error: function() {
				alert(wpshadowWelcome.strings.error);
			}
		});
	});

	// Prevent closing by clicking overlay
	$('.wpshadow-welcome-overlay').on('click', function(e) {
		e.preventDefault();
		// Optionally, show a message that they need to make a choice
	});
});
JS;
	}

	/**
	 * Handle welcome completion AJAX.
	 *
	 * @since  1.6004.0200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_complete_welcome() {
		// Use Security_Validator for consistent security checks
		if ( ! \WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_complete_welcome', 'nonce', false ) ||
			 ! \WPShadow\Core\Security_Validator::verify_capability( 'manage_options', false ) ) {
			wp_send_json_error( array( 'message' => \WPShadow\Core\Security_Validator::get_permission_error() ) );
		}

		$user_id = get_current_user_id();
		$skipped = isset( $_POST['skipped'] ) && '1' === $_POST['skipped'];

		// Save consent preferences
		$telemetry = isset( $_POST['anonymized_telemetry'] ) && '1' === $_POST['anonymized_telemetry'];

		Consent_Preferences::set_preferences( $user_id, array(
			'anonymized_telemetry' => $telemetry,
		) );

		// Mark welcome as completed
		update_user_meta( $user_id, 'wpshadow_welcome_completed', time() );
		update_user_meta( $user_id, 'wpshadow_welcome_skipped', $skipped );

		// Clear the activation flag
		\WPShadow\Core\Cache_Manager::delete(
			'first_activation_' . $user_id,
			'wpshadow_activation'
		);

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'welcome_completed',
				$skipped ? 'User skipped welcome flow' : 'User completed welcome flow',
				'',
				array(
					'telemetry_enabled' => $telemetry,
					'skipped'           => $skipped,
				)
			);
		}

		wp_send_json_success( array(
			'message'  => __( 'Preferences saved successfully', 'wpshadow' ),
			'redirect' => admin_url( 'admin.php?page=wpshadow' ),
		) );
	}
}
