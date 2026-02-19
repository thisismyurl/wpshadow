<?php
/**
 * Guardian Inactive Admin Notice
 *
 * Displays a dismissible admin notice when Guardian feature is inactive,
 * encouraging users to activate it for automated health monitoring.
 *
 * @package WPShadow
 * @subpackage Admin
 * @since 1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Admin;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guardian Inactive Admin Notice Class
 *
 * @since 1.6030.2148
 */
class Guardian_Inactive_Notice extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(
			'wpshadow_after_page_header'                         => 'display_notice',
			'wp_ajax_wpshadow_dismiss_guardian_notice'           => 'dismiss_notice',
			'wp_ajax_wpshadow_activate_guardian_from_notice'     => 'activate_guardian',
		);
	}

	/**
	 * Initialize the notice (deprecated)
	 *
	 * @deprecated 1.7035.1400 Use Guardian_Inactive_Notice::subscribe() instead
	 * @since      1.6030.2148
	 * @return     void
	 */
	public static function init(): void {
		self::subscribe();
	}

	/**
	 * Display the admin notice if Guardian is inactive
	 *
	 * @since 1.6030.2148
	 */
	public static function display_notice(): void {
		static $rendered = false;

		if ( $rendered ) {
			return;
		}

		// Only show on WPShadow pages
		if ( ! isset( $_GET['page'] ) || false === strpos( (string) $_GET['page'], 'wpshadow' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Only show to users who can manage options
		if (! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Don't show if Guardian is already enabled
		if ( get_option( 'wpshadow_guardian_enabled', false ) ) {
			return;
		}

		// Don't show if user has dismissed this notice
		$dismissed = get_user_meta( get_current_user_id(), 'wpshadow_guardian_notice_dismissed', true );
		if ( ! empty( $dismissed ) ) {
			return;
		}

		$rendered = true;

		$version = defined( 'WPSHADOW_VERSION' ) ? WPSHADOW_VERSION : '1.0.0';

		wp_enqueue_script(
			'wpshadow-guardian-inactive-notice',
			WPSHADOW_URL . 'assets/js/guardian-inactive-notice.js',
			array( 'jquery' ),
			$version,
			true
		);

		wp_localize_script(
			'wpshadow-guardian-inactive-notice',
			'wpsGuardianInactiveNotice',
			array(
				'dismissNonce'           => wp_create_nonce( 'wpshadow_dismiss_guardian_notice' ),
				'activatingText'         => __( 'Activating...', 'wpshadow' ),
				'activatedText'          => __( 'Guardian Activated!', 'wpshadow' ),
				'activateFailedText'     => __( 'Failed to activate Guardian', 'wpshadow' ),
				'activateErrorText'      => __( 'Error activating Guardian', 'wpshadow' ),
			)
		);

		// Display the notice
		?>
		<div class="notice notice-info is-dismissible" id="wpshadow-guardian-notice">
			<p>
				<strong><?php esc_html_e( 'WPShadow Guardian', 'wpshadow' ); ?></strong>
				<?php esc_html_e( 'Keep your site healthy automatically. Enable Guardian to run scheduled health checks and apply safe fixes without manual intervention.', 'wpshadow' ); ?>
			</p>
			<p>
				<button type="button" class="button button-primary" id="wpshadow-activate-guardian-btn" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_activate_guardian_from_notice' ) ); ?>">
					<?php esc_html_e( 'Activate Guardian', 'wpshadow' ); ?>
				</button>
				<button type="button" class="button" id="wpshadow-dismiss-guardian-notice">
					<?php esc_html_e( 'Dismiss', 'wpshadow' ); ?>
				</button>
			</p>
		</div>
		<?php
	}

	/**
	 * Handle AJAX dismiss request
	 *
	 * @since 1.6030.2148
	 */
	public static function dismiss_notice(): void {
		// Use Security_Validator for consistent security checks
		if ( ! \WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_dismiss_guardian_notice', '_wpnonce', false ) ||
			 ! \WPShadow\Core\Security_Validator::verify_capability( 'manage_options', false ) ) {
			wp_send_json_error( array( 'message' => \WPShadow\Core\Security_Validator::get_permission_error() ) );
		}

		update_user_meta( get_current_user_id(), 'wpshadow_guardian_notice_dismissed', true );

		wp_send_json_success( array( 'message' => __( 'Notice dismissed', 'wpshadow' ) ) );
	}

	/**
	 * Handle AJAX activate request
	 *
	 * Directly enables Guardian without requiring navigation to Guardian page.
	 *
	 * @since 1.6030.2148
	 */
	public static function activate_guardian(): void {
		// Use Security_Validator for consistent security checks
		if ( ! \WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_activate_guardian_from_notice', '_wpnonce', false ) ||
			 ! \WPShadow\Core\Security_Validator::verify_capability( 'manage_options', false ) ) {
			wp_send_json_error( array( 'message' => \WPShadow\Core\Security_Validator::get_permission_error() ) );
		}

		// Enable Guardian
		update_option( 'wpshadow_guardian_enabled', true );

		// Dismiss the notice automatically
		update_user_meta( get_current_user_id(), 'wpshadow_guardian_notice_dismissed', true );

		// Log the activation
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'guardian_enabled',
				'Guardian activated from admin notice',
				'guardian'
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'Guardian has been activated successfully!', 'wpshadow' ),
			)
		);
	}
}
