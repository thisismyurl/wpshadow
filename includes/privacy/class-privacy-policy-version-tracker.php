<?php
/**
 * Privacy Policy Version Tracker
 *
 * Tracks changes to the privacy policy and notifies users.
 * Phase 6: Privacy & Consent Excellence
 *
 * @package    WPShadow
 * @subpackage Privacy
 * @since      1.2604.0200
 */

declare(strict_types=1);

namespace WPShadow\Privacy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Privacy Policy Version Tracker Class
 *
 * Monitors privacy policy changes and notifies users when updates occur.
 * Required for GDPR compliance - users must be notified of policy changes.
 *
 * @since 1.2604.0200
 */
class Privacy_Policy_Version_Tracker {

	/**
	 * Current policy version.
	 *
	 * Update this when the privacy policy changes.
	 *
	 * @since 1.2604.0200
	 * @var string
	 */
	const CURRENT_VERSION = '1.0.0';

	/**
	 * Policy effective date.
	 *
	 * @since 1.2604.0200
	 * @var string
	 */
	const EFFECTIVE_DATE = '2026-01-30';

	/**
	 * Initialize version tracker.
	 *
	 * @since 1.2604.0200
	 * @return void
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'check_policy_updates' ) );
		add_action( 'admin_notices', array( __CLASS__, 'show_update_notice' ) );
		add_action( 'wp_ajax_wpshadow_acknowledge_policy_update', array( __CLASS__, 'handle_acknowledgment' ) );
	}

	/**
	 * Check if users need to be notified of policy updates.
	 *
	 * @since  1.2604.0200
	 * @return void
	 */
	public static function check_policy_updates() {
		$user_id = get_current_user_id();

		if ( ! $user_id || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$last_acknowledged = get_user_meta( $user_id, 'wpshadow_policy_acknowledged_version', true );

		// If never acknowledged or different version, show notice
		if ( empty( $last_acknowledged ) || version_compare( $last_acknowledged, self::CURRENT_VERSION, '<' ) ) {
			set_transient( 'wpshadow_show_policy_notice_' . $user_id, true, WEEK_IN_SECONDS );
		}
	}

	/**
	 * Show policy update notice.
	 *
	 * Disabled per bug #3868 - alert removed from admin UI
	 * Privacy policy still accessible via Help menu
	 *
	 * @since  1.2604.0200
	 * @return void
	 */
	public static function show_update_notice() {
		// Alert disabled per bug #3868
		return;

		/* Original code commented out per bug #3868
		$user_id = get_current_user_id();

		if ( ! get_transient( 'wpshadow_show_policy_notice_' . $user_id ) ) {
			return;
		}

		$last_acknowledged = get_user_meta( $user_id, 'wpshadow_policy_acknowledged_version', true );
		$is_first_time     = empty( $last_acknowledged );

		?>
		<div class="notice notice-info wpshadow-policy-notice">
			<div style="display: flex; align-items: start; gap: 16px; padding: 12px 0;">
				<span class="dashicons dashicons-privacy" style="font-size: 40px; color: #6366F1; margin-top: 4px;"></span>
				<div style="flex: 1;">
					<h3 style="margin: 0 0 8px;">
						<?php
						if ( $is_first_time ) {
							esc_html_e( 'Welcome to WPShadow: Our Privacy Commitment', 'wpshadow' );
						} else {
							esc_html_e( 'Privacy Policy Updated', 'wpshadow' );
						}
						?>
					</h3>
					<?php if ( $is_first_time ) : ?>
						<p style="margin: 0 0 12px;">
							<?php esc_html_e( 'We believe in complete transparency about how we handle your data. Please take a moment to review our privacy policy.', 'wpshadow' ); ?>
						</p>
					<?php else : ?>
						<p style="margin: 0 0 12px;">
							<?php
							printf(
								esc_html__( 'Our privacy policy has been updated to version %1$s, effective %2$s. Please review the changes.', 'wpshadow' ),
								'<strong>' . esc_html( self::CURRENT_VERSION ) . '</strong>',
								'<strong>' . esc_html( wp_date( get_option( 'date_format' ), strtotime( self::EFFECTIVE_DATE ) ) ) . '</strong>'
							);
							?>
						</p>
					<?php endif; ?>

					<div style="margin: 12px 0;">
						<strong style="display: block; margin-bottom: 6px;">
							<?php esc_html_e( 'Key Points:', 'wpshadow' ); ?>
						</strong>
						<ul style="margin: 0; padding-left: 20px; list-style: disc;">
							<li><?php esc_html_e( 'All data stored locally by default', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'No personal information collected without consent', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Optional anonymous telemetry (you control this)', 'wpshadow' ); ?></li>
							<li><?php esc_html_e( 'Full data export and deletion tools available', 'wpshadow' ); ?></li>
						</ul>
					</div>

					<div style="display: flex; gap: 12px; margin-top: 16px;">
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow-privacy' ) ); ?>" class="button button-primary">
							<?php esc_html_e( 'View Privacy Dashboard', 'wpshadow' ); ?>
						</a>
						<a href="https://wpshadow.com/privacy/" target="_blank" rel="noopener" class="button button-secondary">
							<?php esc_html_e( 'Read Full Policy', 'wpshadow' ); ?>
						</a>
						<button type="button" class="button button-secondary wpshadow-acknowledge-policy" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_acknowledge_policy' ) ); ?>">
							<?php esc_html_e( 'I Understand', 'wpshadow' ); ?>
						</button>
					</div>
				</div>
			</div>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('.wpshadow-acknowledge-policy').on('click', function() {
				var button = $(this);
				button.prop('disabled', true);

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpshadow_acknowledge_policy_update',
						nonce: button.data('nonce')
					},
					success: function(response) {
						if (response.success) {
							$('.wpshadow-policy-notice').fadeOut(300, function() {
								$(this).remove();
							});
						}
					}
				});
			});
		});
		</script>
		*/
		<?php
	}

	/**
	 * Handle policy acknowledgment AJAX.
	 *
	 * @since  1.2604.0200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_acknowledgment() {
		check_ajax_referer( 'wpshadow_acknowledge_policy', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'wpshadow' ) ) );
		}

		$user_id = get_current_user_id();

		// Record acknowledgment
		update_user_meta( $user_id, 'wpshadow_policy_acknowledged_version', self::CURRENT_VERSION );
		update_user_meta( $user_id, 'wpshadow_policy_acknowledged_date', current_time( 'mysql' ) );

		// Clear notice
		delete_transient( 'wpshadow_show_policy_notice_' . $user_id );

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'privacy_policy_acknowledged',
				sprintf( 'User acknowledged privacy policy version %s', self::CURRENT_VERSION ),
				'',
				array( 'version' => self::CURRENT_VERSION )
			);
		}

		wp_send_json_success( array(
			'message' => __( 'Thank you for reviewing our privacy policy.', 'wpshadow' ),
		) );
	}

	/**
	 * Get policy version history.
	 *
	 * @since  1.2604.0200
	 * @return array Version history.
	 */
	public static function get_version_history() {
		return array(
			'1.0.0' => array(
				'date'    => '2026-01-30',
				'changes' => array(
					__( 'Initial privacy policy', 'wpshadow' ),
					__( 'Defined data collection practices', 'wpshadow' ),
					__( 'Added optional telemetry disclosure', 'wpshadow' ),
					__( 'Documented user rights (export, deletion)', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Check if user has acknowledged current policy version.
	 *
	 * @since  1.2604.0200
	 * @param  int $user_id User ID.
	 * @return bool True if acknowledged.
	 */
	public static function has_acknowledged_current( $user_id ) {
		$last_acknowledged = get_user_meta( $user_id, 'wpshadow_policy_acknowledged_version', true );
		return self::CURRENT_VERSION === $last_acknowledged;
	}

	/**
	 * Get policy effective date.
	 *
	 * @since  1.2604.0200
	 * @return string Formatted date.
	 */
	public static function get_effective_date() {
		return wp_date( get_option( 'date_format' ), strtotime( self::EFFECTIVE_DATE ) );
	}

	/**
	 * Get changelog for display.
	 *
	 * @since  1.2604.0200
	 * @return string HTML changelog.
	 */
	public static function get_changelog_html() {
		$history = self::get_version_history();
		$html    = '<div class="wpshadow-policy-changelog">';

		foreach ( $history as $version => $data ) {
			$date = wp_date( get_option( 'date_format' ), strtotime( $data['date'] ) );

			$html .= '<div class="wpshadow-policy-version" style="margin-bottom: 24px;">';
			$html .= '<h4 style="margin: 0 0 8px; color: #1e1e1e;">';
			$html .= sprintf(
				/* translators: 1: version number, 2: date */
				esc_html__( 'Version %1$s (%2$s)', 'wpshadow' ),
				esc_html( $version ),
				esc_html( $date )
			);
			$html .= '</h4>';

			$html .= '<ul style="margin: 0; padding-left: 20px; list-style: disc; color: #3c434a;">';
			foreach ( $data['changes'] as $change ) {
				$html .= '<li>' . esc_html( $change ) . '</li>';
			}
			$html .= '</ul>';
			$html .= '</div>';
		}

		$html .= '</div>';

		return $html;
	}
}
