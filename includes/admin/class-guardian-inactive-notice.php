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
	}

	/**
	 * Display the admin notice if Guardian is inactive
	 *
	 * @since 1.2601.2148
	 */
	public static function display_notice(): void {
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

		// Display the notice
		?>
		<div class="notice notice-info is-dismissible" id="wpshadow-guardian-notice">
			<p>
				<strong><?php esc_html_e( 'WPShadow Guardian', 'wpshadow' ); ?></strong>
				<?php esc_html_e( 'Keep your site healthy automatically. Enable Guardian to run scheduled health checks and apply safe fixes without manual intervention.', 'wpshadow' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-guardian' ) ); ?>" class="button button-primary">
					<?php esc_html_e( 'Activate Guardian', 'wpshadow' ); ?>
				</a>
				<button type="button" class="button" id="wpshadow-dismiss-guardian-notice">
					<?php esc_html_e( 'Dismiss', 'wpshadow' ); ?>
				</button>
			</p>
		</div>

		<script>
		jQuery(document).ready(function($) {
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
		check_ajax_referer( 'wpshadow_dismiss_guardian_notice' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		update_user_meta( get_current_user_id(), 'wpshadow_guardian_notice_dismissed', true );

		wp_send_json_success( array( 'message' => __( 'Notice dismissed', 'wpshadow' ) ) );
	}
}
