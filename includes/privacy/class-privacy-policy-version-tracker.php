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
	}

	/**
	 * Handle policy acknowledgment AJAX.
	 *
	 * @since  1.2604.0200
	 * @return void Dies after sending JSON response.
	 */
	public static function handle_acknowledgment() {
		\WPShadow\Core\Security_Validator::verify_request( 'wpshadow_acknowledge_policy', 'manage_options', 'nonce' );

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
