<?php
/**
 * Guardian Inactive Admin Notice
 *
 * Displays a dismissible admin notice when Guardian feature is inactive,
 * encouraging users to activate it for automated health monitoring.
 *
 * @package WPShadow
 * @subpackage Admin
 * @since 1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Guardian Inactive Admin Notice Class
 *
 * @since 1.2601.2148
 */
class Guardian_Inactive_Notice {

	/**
	 * Initialize the notice
	 *
	 * @since 1.2601.2148
	 */
	public static function init(): void {
		add_action( 'admin_notices', array( __CLASS__, 'display_notice' ) );
		add_action( 'wp_ajax_wpshadow_dismiss_guardian_notice', array( __CLASS__, 'dismiss_notice' ) );
		add_action( 'wp_ajax_wpshadow_activate_guardian_from_notice', array( __CLASS__, 'activate_guardian' ) );
	}

	/**
	 * Display the admin notice if Guardian is inactive
	 *
	 * @since 1.2601.2148
	 */
	public static function display_notice(): void {
		// Check if another notice has already been shown (only one at a time)
		$shown_notice = get_transient( 'wpshadow_active_notice_' . get_current_user_id() );
		if ( ! empty( $shown_notice ) ) {
			return;
		}

		// Only show on WPShadow pages
		if ( ! isset( $_GET['page'] ) || strpos( (string) $_GET['page'], 'wpshadow' ) === false ) {
			return;
		}

		// Only show to users who can manage options
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check if Guardian_Manager is available
		if ( ! class_exists( 'WPShadow\Guardian\Guardian_Manager' ) ) {
			return; // Guardian module not loaded
		}

		// Don't show if Guardian is already enabled
		if ( \WPShadow\Guardian\Guardian_Manager::is_enabled() ) {
			return;
		}

		// Don't show if user has dismissed this notice
		$dismissed = get_user_meta( get_current_user_id(), 'wpshadow_guardian_notice_dismissed', true );
		if ( ! empty( $dismissed ) ) {
			return;
		}

		// Mark this notice as active (blocks other notices)
		set_transient( 'wpshadow_active_notice_' . get_current_user_id(), 'guardian', HOUR_IN_SECONDS );

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

		<script>
		jQuery(document).ready(function($) {
			// Handle direct Guardian activation
			$('#wpshadow-guardian-notice').on('click', '#wpshadow-activate-guardian-btn', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var originalText = $btn.text();

				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Activating...', 'wpshadow' ) ); ?>');

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_activate_guardian_from_notice',
						nonce: $btn.attr('data-nonce')
					},
					success: function(response) {
						if (response.success) {
							$btn.text('<?php echo esc_js( __( 'Guardian Activated!', 'wpshadow' ) ); ?>').addClass('disabled');
							setTimeout(function() {
								$('#wpshadow-guardian-notice').fadeOut(300, function() {
									$(this).remove();
								});
							}, 1500);
						} else {
							$btn.prop('disabled', false).text(originalText);
							alert(response.data.message || '<?php echo esc_js( __( 'Failed to activate Guardian', 'wpshadow' ) ); ?>');
						}
					},
					error: function() {
						$btn.prop('disabled', false).text(originalText);
						alert('<?php echo esc_js( __( 'Error activating Guardian', 'wpshadow' ) ); ?>');
					}
				});
			});

			// Handle dismiss
			$('#wpshadow-guardian-notice').on('click', '.notice-dismiss, #wpshadow-dismiss-guardian-notice', function(e) {
				if (e.target.id === 'wpshadow-dismiss-guardian-notice') {
					e.preventDefault();
				}

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_dismiss_guardian_notice',
						nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_dismiss_guardian_notice' ) ); ?>'
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Handle AJAX dismiss request
	 *
	 * @since 1.2601.2148
	 */
	public static function dismiss_notice(): void {
		// Use Security_Validator for consistent security checks
		if ( ! \WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_dismiss_guardian_notice', '_wpnonce', false ) ||
			 ! \WPShadow\Core\Security_Validator::verify_capability( 'manage_options', false ) ) {
			wp_send_json_error( array( 'message' => \WPShadow\Core\Security_Validator::get_permission_error() ) );
		}

		update_user_meta( get_current_user_id(), 'wpshadow_guardian_notice_dismissed', true );

		// Clear the active notice transient so other notices can show
		delete_transient( 'wpshadow_active_notice_' . get_current_user_id() );

		wp_send_json_success( array( 'message' => __( 'Notice dismissed', 'wpshadow' ) ) );
	}

	/**
	 * Handle AJAX activate request
	 *
	 * Directly enables Guardian without requiring navigation to Guardian page.
	 *
	 * @since 1.2601.2148
	 */
	public static function activate_guardian(): void {
		// Use Security_Validator for consistent security checks
		if ( ! \WPShadow\Core\Security_Validator::verify_nonce( 'wpshadow_activate_guardian_from_notice', '_wpnonce', false ) ||
			 ! \WPShadow\Core\Security_Validator::verify_capability( 'manage_options', false ) ) {
			wp_send_json_error( array( 'message' => \WPShadow\Core\Security_Validator::get_permission_error() ) );
		}

		// Verify Guardian Manager is available
		if ( ! class_exists( 'WPShadow\Guardian\Guardian_Manager' ) ) {
			wp_send_json_error( array( 'message' => __( 'Guardian module not available', 'wpshadow' ) ) );
		}

		// Enable Guardian with default settings
		\WPShadow\Core\Cache_Manager::set(
			'guardian_first_activation',
			true,
			3600,
			'wpshadow_notices'
		);
		$success = \WPShadow\Guardian\Guardian_Manager::update_settings(
			array(
				'enabled' => true,
			)
		);

		if ( $success ) {
			// Dismiss the notice automatically
			update_user_meta( get_current_user_id(), 'wpshadow_guardian_notice_dismissed', true );

			wp_send_json_success(
				array(
					'message' => __( 'Guardian has been activated successfully!', 'wpshadow' ),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __( 'Failed to activate Guardian. Please try again.', 'wpshadow' ),
				)
			);
		}
	}
}
