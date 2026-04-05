<?php
/**
 * File Write Pending Notice
 *
 * Renders a dismissible admin notice when one or more file-write treatments
 * are pending. The notice links directly to the File Write Review page so
 * the admin can inspect the proposed changes, run a dry test, create a backup,
 * and safely apply the fix.
 *
 * The notice is dismissed per-user with a 24-hour cooldown stored in user meta.
 *
 * Philosophy: Commandment #1 (Helpful Neighbor) — surface actionable issues
 * without overwhelming; Commandment #8 (Inspire Confidence) — always explain
 * what will change before anything changes.
 *
 * @package WPShadow
 * @subpackage Admin
 * @since 0.6095
 */

namespace WPShadow\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the "file-write fixes pending" admin notice.
 */
class File_Write_Pending_Notice {

	/**
	 * User meta key for the dismiss-until timestamp.
	 */
	const DISMISSED_META_KEY = 'wpshadow_file_write_notice_dismissed_until';

	/**
	 * How long (in seconds) a dismissal lasts.
	 */
	const DISMISS_DURATION = DAY_IN_SECONDS;

	/**
	 * Register WordPress hooks.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_notices', [ __CLASS__, 'render' ] );
		add_action( 'wp_ajax_wpshadow_dismiss_file_write_notice', [ __CLASS__, 'handle_dismiss' ] );
	}

	/**
	 * Render the notice on WPShadow admin pages.
	 *
	 * Only shown to administrators with manage_options capability who have not
	 * recently dismissed the notice.
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

		// Check dismissal.
		$user_id        = get_current_user_id();
		$dismissed_until = (int) get_user_meta( $user_id, self::DISMISSED_META_KEY, true );
		if ( $dismissed_until > time() ) {
			return;
		}

		// Check for pending file-write treatments.
		$pending = File_Write_Registry::get_pending();
		if ( empty( $pending ) ) {
			return;
		}

		$count       = count( $pending );
		$review_url  = admin_url( 'admin.php?page=wpshadow-file-review' );
		$nonce       = wp_create_nonce( 'wpshadow_file_write_notice_nonce' );
		$dismiss_url = '#'; // handled via JS AJAX

		/* translators: %d: number of pending file-write fixes */
		$summary = sprintf(
			_n(
				'WPShadow has identified %d recommended change to a system file that requires your review before it can be applied.',
				'WPShadow has identified %d recommended changes to system files that require your review before they can be applied.',
				$count,
				'wpshadow'
			),
			$count
		);

		$file_labels = array_map( fn( $t ) => '<code>' . esc_html( $t['file_label'] ) . '</code>', $pending );
		$file_list   = implode( ', ', $file_labels );
		?>
		<div class="notice notice-warning is-dismissible wpshadow-file-write-notice"
		     data-nonce="<?php echo esc_attr( $nonce ); ?>">
			<p>
				<strong><?php esc_html_e( 'WPShadow — File Changes Pending', 'wpshadow' ); ?></strong>
			</p>
			<p>
				<?php echo esc_html( $summary ); ?>
				<?php
				/* translators: %s: comma-separated list of file labels */
				printf(
					esc_html__( 'Affected files: %s.', 'wpshadow' ),
					wp_kses( $file_list, [ 'code' => [] ] )
				);
				?>
			</p>
			<p>
				<a href="<?php echo esc_url( $review_url ); ?>" class="button button-primary">
					<?php esc_html_e( 'Review &amp; Apply Changes', 'wpshadow' ); ?>
				</a>
				&nbsp;
				<a href="<?php echo esc_url( $dismiss_url ); ?>"
				   class="wpshadow-dismiss-file-write-notice wpshadow-notice-muted-link"
				   data-nonce="<?php echo esc_attr( $nonce ); ?>">
					<?php esc_html_e( 'Remind me tomorrow', 'wpshadow' ); ?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * AJAX: dismiss the notice for DISMISS_DURATION seconds.
	 *
	 * @return void
	 */
	public static function handle_dismiss(): void {
		check_ajax_referer( 'wpshadow_file_write_notice_nonce', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ] );
		}

		$user_id = get_current_user_id();
		update_user_meta( $user_id, self::DISMISSED_META_KEY, time() + self::DISMISS_DURATION );

		wp_send_json_success( [ 'message' => __( 'Notice dismissed.', 'wpshadow' ) ] );
	}
}
