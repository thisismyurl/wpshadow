<?php
/**
 * Two-Factor Authentication Admin Adoption Rate Diagnostic
 *
 * Checks what percentage of admin and editor users have 2FA enabled.
 * Less than 80% adoption leaves privileged accounts vulnerable to takeover.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6029.1645
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Two-Factor Authentication Adoption Diagnostic Class
 *
 * Verifies admin and editor users have 2FA enabled. Target: 100% admin adoption,
 * >80% editor adoption. Checks multiple popular 2FA plugins.
 *
 * @since 1.6029.1645
 */
class Diagnostic_Two_Factor_Authentication_Adoption extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'two-factor-authentication-adoption';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Two-Factor Authentication Admin Adoption Rate';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks percentage of admin users with 2FA enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6029.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get admin and editor users.
		$admin_users  = get_users( array( 'role' => 'administrator' ) );
		$editor_users = get_users( array( 'role' => 'editor' ) );

		$all_privileged = array_merge( $admin_users, $editor_users );
		$total_users    = count( $all_privileged );

		if ( 0 === $total_users ) {
			// No privileged users to check.
			return null;
		}

		$admins_with_2fa = 0;
		$editors_with_2fa = 0;
		$admin_count = count( $admin_users );
		$editor_count = count( $editor_users );

		// Check for popular 2FA plugins.
		$has_2fa_plugin = false;
		$active_2fa_plugins = array();

		$common_2fa_plugins = array(
			'two-factor/two-factor.php' => 'Two-Factor (WordPress.org)',
			'wordfence/wordfence.php' => 'Wordfence Security',
			'google-authenticator/google-authenticator.php' => 'Google Authenticator',
			'jetpack/jetpack.php' => 'Jetpack',
			'all-in-one-wp-security-and-firewall/wp-security.php' => 'All In One WP Security',
		);

		foreach ( $common_2fa_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_2fa_plugin = true;
				$active_2fa_plugins[] = $name;
			}
		}

		if ( ! $has_2fa_plugin ) {
			$issues[] = 'no_2fa_plugin_installed';
		}

		// Check each user for 2FA enabled status.
		foreach ( $admin_users as $user ) {
			if ( self::user_has_2fa_enabled( $user->ID ) ) {
				$admins_with_2fa++;
			}
		}

		foreach ( $editor_users as $user ) {
			if ( self::user_has_2fa_enabled( $user->ID ) ) {
				$editors_with_2fa++;
			}
		}

		// Calculate adoption rates.
		$admin_adoption_rate = $admin_count > 0 ? ( $admins_with_2fa / $admin_count ) * 100 : 0;
		$editor_adoption_rate = $editor_count > 0 ? ( $editors_with_2fa / $editor_count ) * 100 : 0;

		// Check thresholds.
		if ( $admin_adoption_rate < 100 ) {
			$issues[] = 'admin_2fa_adoption_below_100';
		}

		if ( $editor_count > 0 && $editor_adoption_rate < 80 ) {
			$issues[] = 'editor_2fa_adoption_below_80';
		}

		// If no 2FA plugin but users exist, critical issue.
		if ( ! $has_2fa_plugin && $total_users > 0 ) {
			$issues[] = 'privileged_users_without_2fa_capability';
		}

		// If issues found, return finding.
		if ( ! empty( $issues ) ) {
			$severity = 'critical';
			$threat_level = 90;

			// Lower severity if only editors affected.
			if ( $admin_adoption_rate >= 100 && in_array( 'editor_2fa_adoption_below_80', $issues, true ) ) {
				$severity = 'high';
				$threat_level = 75;
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Two-factor authentication adoption is below security threshold', 'wpshadow' ),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'details'      => array(
					'issues_found'         => $issues,
					'has_2fa_plugin'       => $has_2fa_plugin,
					'active_2fa_plugins'   => $active_2fa_plugins,
					'admin_count'          => $admin_count,
					'admins_with_2fa'      => $admins_with_2fa,
					'admin_adoption_rate'  => round( $admin_adoption_rate, 1 ),
					'editor_count'         => $editor_count,
					'editors_with_2fa'     => $editors_with_2fa,
					'editor_adoption_rate' => round( $editor_adoption_rate, 1 ),
					'target_admin_rate'    => 100,
					'target_editor_rate'   => 80,
				),
				'meta'         => array(
					'wpdb_avoidance'   => 'Uses get_users(), get_user_meta(), is_plugin_active() instead of $wpdb',
					'detection_method' => 'WordPress APIs - user enumeration, meta checks, plugin detection',
					'account_security' => 'Critical defense against credential stuffing and phishing',
				),
				'kb_link'      => 'https://wpshadow.com/kb/two-factor-authentication-adoption',
				'solution'     => sprintf(
					/* translators: 1: Users admin URL, 2: Plugins admin URL */
					__( 'Two-factor authentication (2FA) is critical for preventing account takeover. Current adoption: %1$d%% of admins, %2$d%% of editors. Target: 100%% admin adoption, 80%%+ editor adoption. Actions needed: 1) If no 2FA plugin installed, install "Two-Factor" from WordPress.org or enable in Wordfence/Jetpack at %3$s, 2) Require all admins to enable 2FA at %4$s, 3) Educate editors on 2FA setup, 4) Consider 2FA enforcement policy for new admins, 5) Support multiple methods (authenticator app, SMS, backup codes). Manage users at %4$s. Learn more: <a href="https://wordpress.org/plugins/two-factor/">WordPress Two-Factor Plugin</a>', 'wpshadow' ),
					round( $admin_adoption_rate, 0 ),
					round( $editor_adoption_rate, 0 ),
					esc_url( admin_url( 'plugins.php' ) ),
					esc_url( admin_url( 'users.php' ) )
				),
			);
		}

		return null;
	}

	/**
	 * Check if a user has 2FA enabled.
	 *
	 * Checks multiple popular 2FA plugin meta keys.
	 *
	 * @since  1.6029.1645
	 * @param  int $user_id User ID to check.
	 * @return bool True if 2FA enabled, false otherwise.
	 */
	private static function user_has_2fa_enabled( $user_id ) {
		// WordPress.org Two-Factor plugin.
		$two_factor_enabled = get_user_meta( $user_id, '_two_factor_enabled_providers', true );
		if ( ! empty( $two_factor_enabled ) && is_array( $two_factor_enabled ) ) {
			return true;
		}

		// Wordfence 2FA.
		$wordfence_2fa = get_user_meta( $user_id, 'wf2faActivated', true );
		if ( '1' === $wordfence_2fa || 1 === $wordfence_2fa ) {
			return true;
		}

		// Google Authenticator plugin.
		$google_auth = get_user_meta( $user_id, 'googleauthenticator_enabled', true );
		if ( 'enabled' === $google_auth || '1' === $google_auth ) {
			return true;
		}

		// Jetpack SSO with 2FA.
		$jetpack_2fa = get_user_meta( $user_id, 'jetpack_sso_2fa', true );
		if ( ! empty( $jetpack_2fa ) ) {
			return true;
		}

		// All In One WP Security.
		$aiowps_2fa = get_user_meta( $user_id, 'aiowps_2fa_enabled', true );
		if ( ! empty( $aiowps_2fa ) ) {
			return true;
		}

		return false;
	}
}
