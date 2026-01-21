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

		// Don't show if they dismissed it recently
		$dismissed = get_user_meta( (int) $user_id, 'wpshadow_consent_dismissed_until', true );
		if ( ! empty( $dismissed ) && time() < (int) $dismissed ) {
			return false;
		}

		return true;
	}

	/**
	 * Get consent flow HTML.
	 *
	 * @return string HTML.
	 */
	public static function get_consent_html() {
		return '
		<div id="wpshadow-consent-banner" class="wpshadow-consent-flow" style="
			position: fixed;
			bottom: 0;
			right: 0;
			width: 420px;
			background: white;
			border: 1px solid #ddd;
			border-radius: 10px 10px 0 0;
			box-shadow: 0 -6px 18px rgba(0,0,0,0.18);
			padding: 22px;
			z-index: 99999;
		">
			<div class="wpshadow-consent-header">
				<h3 style="margin-top: 0;">' . esc_html( __( 'Your Privacy Matters', 'wpshadow' ) ) . '</h3>
				<p style="margin: 10px 0; color: #666;">' . 
					esc_html( __( 'WPShadow respects your privacy. Here\'s what we collect and how we use it.', 'wpshadow' ) ) . 
				'</p>
			</div>

			<div class="wpshadow-consent-options" style="margin: 20px 0;">
				<label style="display: flex; align-items: center; margin: 12px 0; cursor: pointer;">
					<input type="checkbox" name="functional_cookies" checked disabled style="margin-right: 10px; cursor: not-allowed;" />
					<span>
						<strong>' . esc_html( __( 'Essential Functions', 'wpshadow' ) ) . '</strong>
						<br />
						<small style="color: #999;">' . 
							esc_html( __( 'Required for the plugin to work (always enabled)', 'wpshadow' ) ) . 
						'</small>
					</span>
				</label>

				<label style="display: flex; align-items: center; margin: 12px 0; cursor: pointer;">
					<input type="checkbox" name="error_reporting" checked disabled style="margin-right: 10px; cursor: not-allowed;" />
					<span>
						<strong>' . esc_html( __( 'Error Reporting', 'wpshadow' ) ) . '</strong>
						<br />
						<small style="color: #999;">' . 
							esc_html( __( 'Help us fix problems (no personal data)', 'wpshadow' ) ) . 
						'</small>
					</span>
				</label>

				<label style="display: flex; align-items: center; margin: 12px 0; cursor: pointer;">
					<input type="checkbox" name="anonymized_telemetry" style="margin-right: 10px;" />
					<span>
						<strong>' . esc_html( __( 'Anonymous Analytics', 'wpshadow' ) ) . '</strong>
						<br />
						<small style="color: #999;">' . 
							esc_html( __( 'Show us which features help most (optional)', 'wpshadow' ) ) . 
						'</small>
					</span>
				</label>
			</div>

			<div style="background: #f5f5f5; padding: 12px; border-radius: 4px; margin: 15px 0; font-size: 12px; color: #666;">
				' . wp_kses_post( __( '<strong>We never:</strong> Track IPs, collect passwords, or share data with third parties.', 'wpshadow' ) ) . '
			</div>

			<div style="display: flex; gap: 10px;">
				<button class="wpshadow-consent-dismiss" style="
					flex: 1;
					padding: 10px;
					background: #f0f0f0;
					border: 1px solid #ddd;
					border-radius: 4px;
					cursor: pointer;
				">' . esc_html( __( 'Not now', 'wpshadow' ) ) . '</button>
				<button class="wpshadow-consent-accept" style="
					flex: 1;
					padding: 10px;
					background: #2196f3;
					color: white;
					border: none;
					border-radius: 4px;
					cursor: pointer;
					font-weight: bold;
				">' . esc_html( __( 'Save preferences', 'wpshadow' ) ) . '</button>
			</div>
			<p style="margin: 10px 0 0; font-size: 12px;">
				<a href="https://wpshadow.com/kb/privacy/?utm_source=wpshadow&utm_medium=plugin&utm_campaign=consent" target="_blank" style="color: #2563eb; text-decoration: none; font-weight: 600;">
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
	 * Dismiss consent flow for 30 days.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public static function dismiss_consent( $user_id ) {
		$until = time() + ( 30 * DAY_IN_SECONDS );
		update_user_meta( (int) $user_id, 'wpshadow_consent_dismissed_until', $until );
	}
}
