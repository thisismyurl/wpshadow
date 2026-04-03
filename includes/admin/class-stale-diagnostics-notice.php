<?php
/**
 * Stale Diagnostics Notice
 *
 * Renders a dismissible admin notice when the WPShadow diagnostic suite has
 * not been run in more than 24 hours. Gives the administrator a one-click path
 * to open the Guardian page and run all tests.
 *
 * The notice is dismissed per-user with a 24-hour cooldown stored in user meta.
 *
 * Philosophy: Commandment #1 (Helpful Neighbor) — surface actionable issues
 * without overwhelming; Commandment #8 (Inspire Confidence) — always explain
 * the site's current health state.
 *
 * @package WPShadow
 * @subpackage Admin
 * @since 0.6096.1000
 */

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the "diagnostics are stale" admin notice.
 */
class Stale_Diagnostics_Notice {

	/**
	 * User meta key for the dismiss-until timestamp.
	 */
	const DISMISSED_META_KEY = 'wpshadow_stale_diagnostics_dismissed_until';

	/**
	 * How long (in seconds) a dismissal lasts.
	 */
	const DISMISS_DURATION = DAY_IN_SECONDS;

	/**
	 * How long (in seconds) before diagnostics are considered stale.
	 */
	const STALE_THRESHOLD = DAY_IN_SECONDS;

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_notices', [ __CLASS__, 'render' ] );
		add_action( 'wp_ajax_wpshadow_dismiss_stale_diagnostics_notice', [ __CLASS__, 'handle_dismiss' ] );
	}

	/**
	 * Render the notice on WPShadow admin pages.
	 *
	 * Only shown to administrators with manage_options capability who have not
	 * recently dismissed the notice, and only when diagnostics are stale.
	 *
	 * @return void
	 */
	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Limit to WPShadow admin pages to avoid noise on unrelated screens.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || false === strpos( (string) $screen->id, 'wpshadow' ) ) {
			return;
		}

		// Check dismissal cooldown.
		$user_id         = get_current_user_id();
		$dismissed_until = (int) get_user_meta( $user_id, self::DISMISSED_META_KEY, true );
		if ( $dismissed_until > time() ) {
			return;
		}

		// Check staleness.
		$last_run = (int) get_option( 'wpshadow_last_quick_checks', 0 );
		if ( $last_run > 0 && ( time() - $last_run ) < self::STALE_THRESHOLD ) {
			return;
		}

		$guardian_url = admin_url( 'admin.php?page=wpshadow-guardian' );
		$nonce        = wp_create_nonce( 'wpshadow_stale_diagnostics_nonce' );

		if ( $last_run === 0 ) {
			$message = __( 'WPShadow diagnostics have never been run. Run them now to get a complete picture of your site\'s health.', 'wpshadow' );
		} else {
			$time_ago = human_time_diff( $last_run, time() );
			/* translators: %s: human-readable time since last run, e.g. "2 hours" */
			$message = sprintf(
				__( 'WPShadow diagnostics haven\'t run in %s. Run them now to keep your site health report current.', 'wpshadow' ),
				$time_ago
			);
		}
		?>
		<div class="notice notice-warning is-dismissible wpshadow-stale-diagnostics-notice"
		     data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<p>
				<strong><?php esc_html_e( 'WPShadow — Diagnostics Overdue', 'wpshadow' ); ?></strong>
			</p>
			<p><?php echo esc_html( $message ); ?></p>
			<p>
				<a href="<?php echo esc_url( $guardian_url ); ?>" class="button button-primary">
					<?php esc_html_e( 'Run Diagnostics Now', 'wpshadow' ); ?>
				</a>
				&nbsp;
				<a href="#"
				   class="wpshadow-dismiss-stale-diagnostics-notice"
				   style="text-decoration:none;color:#666;"
				   data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'Remind me tomorrow', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
		<script>
		(function($) {
			$(document).on('click', '.wpshadow-dismiss-stale-diagnostics-notice, .wpshadow-stale-diagnostics-notice .notice-dismiss', function(e) {
				e.preventDefault();
				var nonce = $('.wpshadow-stale-diagnostics-notice').data('nonce');
				$.post(ajaxurl, {
					action: 'wpshadow_dismiss_stale_diagnostics_notice',
					nonce: nonce
				});
				$('.wpshadow-stale-diagnostics-notice').fadeOut(300);
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * AJAX: dismiss the notice for DISMISS_DURATION seconds.
	 *
	 * @return void
	 */
	public static function handle_dismiss(): void {
		check_ajax_referer( 'wpshadow_stale_diagnostics_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ] );
		}

		$user_id = get_current_user_id();
		update_user_meta( $user_id, self::DISMISSED_META_KEY, time() + self::DISMISS_DURATION );

		wp_send_json_success( [ 'message' => __( 'Notice dismissed.', 'wpshadow' ) ] );
	}
}
