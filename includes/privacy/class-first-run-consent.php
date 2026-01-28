<?php
/**
 * First-Run Consent Flow
 *
 * Displays first-time consent flow to new admins.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Privacy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * First-Run Consent Handler
 */
class First_Run_Consent {
	/**
	 * Check if user should see consent flow.
	 *
	 * @param int $user_id User ID.
	 * @return bool True if needs to show consent.
	 */
	public static function should_show_consent( $user_id ) {
		// Only show to admins
		if ( ! user_can( $user_id, 'manage_options' ) ) {
			return false;
		}

		// Don't show if already consented
		if ( Consent_Preferences::has_initial_consent( $user_id ) ) {
			return false;
		}

		// Don't show if they dismissed it recently (increasing delay)
		$dismissed = get_user_meta( (int) $user_id, 'wpshadow_consent_dismissed_until', true );
		if ( ! empty( $dismissed ) && time() < (int) $dismissed ) {
			return false;
		}

		return true;
	}

	/**
	 * Get next dismiss duration (increasing delay)
	 *
	 * @param int $user_id User ID.
	 * @return int Seconds until next show.
	 */
	public static function get_next_dismiss_duration( $user_id ) {
		$dismiss_count = (int) get_user_meta( $user_id, 'wpshadow_consent_dismiss_count', true );
		
		// Increasing delays: 1 day, 3 days, 1 week, 2 weeks, 1 month
		$delays = array(
			DAY_IN_SECONDS,       // 1 day
			3 * DAY_IN_SECONDS,   // 3 days
			WEEK_IN_SECONDS,      // 1 week
			2 * WEEK_IN_SECONDS,  // 2 weeks
			30 * DAY_IN_SECONDS,  // 1 month (then stays here)
		);
		
		$index = min( $dismiss_count, count( $delays ) - 1 );
		return $delays[ $index ];
	}

	/**
	 * Get consent flow HTML.
	 *
	 * @return string HTML.
	 */
	public static function get_consent_html() {
		return '
		<div id="wpshadow-consent-banner" class="wpshadow-consent-flow" style="position: relative; padding: 22px; border-radius: 10px; background: #fff; border: 1px solid #dcdcde; margin: 10px 0;">
			<button type="button" class="wpshadow-consent-dismiss" aria-label="' . esc_attr__( 'Dismiss privacy notice', 'wpshadow' ) . '" style="position: absolute; top: 10px; right: 10px; background: transparent; border: none; cursor: pointer; padding: 5px; color: #646970; font-size: 20px; line-height: 1;">
				<span class="dashicons dashicons-no-alt"></span>
			</button>
			<div class="wpshadow-consent-header">
				<h3 class="wps-mt-0">' . esc_html( __( 'Your Privacy Matters', 'wpshadow' ) ) . '</h3>
				<p class="wps-my-2">' .
					esc_html( __( 'WPShadow respects your privacy. Here\'s what we collect and how we use it.', 'wpshadow' ) ) .
				'</p>
			</div>

			<div class="wpshadow-consent-options" class="wps-m-20">
				<label class="wps-flex-items-center-m-12">
					<input type="checkbox" name="functional_cookies" checked disabled class="wps-mr-2 wps-cursor-not-allowed" />
					<span>
						<strong>' . esc_html( __( 'Essential Functions', 'wpshadow' ) ) . '</strong>
						<br />
						<small style="color: #999;">' .
							esc_html( __( 'Required for the plugin to work (always enabled)', 'wpshadow' ) ) .
						'</small>
					</span>
				</label>

				<label class="wps-flex-items-center-m-12">
					<input type="checkbox" name="error_reporting" checked disabled class="wps-mr-2 wps-cursor-not-allowed" />
					<span>
						<strong>' . esc_html( __( 'Error Reporting', 'wpshadow' ) ) . '</strong>
						<br />
						<small style="color: #999;">' .
							esc_html( __( 'Help us fix problems (no personal data)', 'wpshadow' ) ) .
						'</small>
					</span>
				</label>

				<label class="wps-flex-items-center-m-12">
					<input type="checkbox" name="anonymized_telemetry" class="wps-mr-2" />
					<span>
						<strong>' . esc_html( __( 'Anonymous Analytics', 'wpshadow' ) ) . '</strong>
						<br />
						<small style="color: #999;">' .
							esc_html( __( 'Show us which features help most (optional)', 'wpshadow' ) ) .
						'</small>
					</span>
				</label>
			</div>

			<div class="wps-m-15-p-12-rounded-4">
				' . wp_kses_post( __( '<strong>We never:</strong> Track IPs, collect passwords, or share data with third parties.', 'wpshadow' ) ) . '
			</div>

			<div class="wps-flex-gap-10">
				<button class="wpshadow-consent-dismiss" class="wps-p-10-rounded-4">' . esc_html( __( 'Not now', 'wpshadow' ) ) . '</button>
				<button class="wpshadow-consent-accept" class="wps-p-10-rounded-4">' . esc_html( __( 'Save preferences', 'wpshadow' ) ) . '</button>
			</div>
			<p class="wps-m-10">
				<a href="https://wpshadow.com/privacy/?utm_source=wpshadow&utm_medium=plugin&utm_campaign=consent" target="_blank" style="color: #2563eb; text-decoration: none; font-weight: 600;">
					' . esc_html( __( 'Read our privacy approach', 'wpshadow' ) ) . '
				</a>
			</p>
		</div>
		';
	}

	/**
	 * Record consent decision.
	 *
	 * @param int   $user_id User ID.
	 * @param array $preferences Chosen preferences.
	 * @return void
	 */
	public static function save_consent( $user_id, $preferences ) {
		// Determine decision type
		$decision = 'custom';
		if ( isset( $preferences['anonymized_telemetry'] ) && $preferences['anonymized_telemetry'] ) {
			$decision = 'accept_all';
		} else {
			$decision = 'essential_only';
		}

		// Save preferences
		Consent_Preferences::set_preferences( $user_id, $preferences );

		// Record in history
		Consent_Preferences::record_consent( $user_id, $decision, $preferences );
	}

	/**
	 * Dismiss consent prompt with increasing delay.
	 *
	 * @param int $user_id User ID.
	 * @return int Seconds until next show.
	 */
	public static function dismiss_consent( $user_id ) {
		// Get current dismiss count
		$dismiss_count = (int) get_user_meta( $user_id, 'wpshadow_consent_dismiss_count', true );
		
		// Increment dismiss count
		update_user_meta( $user_id, 'wpshadow_consent_dismiss_count', $dismiss_count + 1 );
		
		// Get next duration based on new count
		$duration = self::get_next_dismiss_duration( $user_id );
		$until    = time() + $duration;
		
		update_user_meta( (int) $user_id, 'wpshadow_consent_dismissed_until', $until );
		
		return $duration;
	}
}
