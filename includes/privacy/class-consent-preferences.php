<?php
/**
 * Consent Preferences Manager
 *
 * Manages user consent preferences for data collection.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Privacy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Consent Preferences Manager
 */
class Consent_Preferences {
	/**
	 * Default consent preferences.
	 *
	 * @return array Default preferences.
	 */
	public static function get_defaults() {
		return array(
			'version'              => '1',
			'functional_cookies'   => true,  // Always enabled
			'error_reporting'      => true,  // Always enabled
			'anonymized_telemetry' => false, // Opt-in
			'consented_at'         => null,
		);
	}

	/**
	 * Get user's consent preferences.
	 *
	 * @param int $user_id User ID.
	 * @return array Preferences.
	 */
	public static function get_preferences( $user_id ) {
		$prefs = get_user_meta( (int) $user_id, 'wpshadow_consent_preferences', true );

		if ( empty( $prefs ) ) {
			return self::get_defaults();
		}

		return wp_parse_args( $prefs, self::get_defaults() );
	}

	/**
	 * Update user's consent preferences.
	 *
	 * @param int   $user_id User ID.
	 * @param array $preferences Preferences to update.
	 * @return bool Success.
	 */
	public static function set_preferences( $user_id, $preferences ) {
		$current = self::get_preferences( $user_id );

		// Only allow specific preferences to be updated
		$allowed = array( 'anonymized_telemetry' );

		foreach ( $allowed as $key ) {
			if ( isset( $preferences[ $key ] ) ) {
				$current[ $key ] = (bool) $preferences[ $key ];
			}
		}

		$current['consented_at'] = current_time( 'mysql' );

		return update_user_meta( (int) $user_id, 'wpshadow_consent_preferences', $current );
	}

	/**
	 * Check if user has consented to specific data collection.
	 *
	 * @param int    $user_id User ID.
	 * @param string $type    Type of consent: 'functional', 'error_reporting', 'telemetry'.
	 * @return bool True if consented.
	 */
	public static function has_consented( $user_id, $type = 'functional' ) {
		$prefs = self::get_preferences( $user_id );

		$mapping = array(
			'functional'    => 'functional_cookies',
			'errors'        => 'error_reporting',
			'telemetry'     => 'anonymized_telemetry',
		);

		$key = isset( $mapping[ $type ] ) ? $mapping[ $type ] : $type;

		return isset( $prefs[ $key ] ) && $prefs[ $key ];
	}

	/**
	 * Check if user has seen and accepted first-run consent.
	 *
	 * @param int $user_id User ID.
	 * @return bool True if already consented.
	 */
	public static function has_initial_consent( $user_id ) {
		$prefs = self::get_preferences( $user_id );
		return ! empty( $prefs['consented_at'] );
	}

	/**
	 * Get consent history for audit.
	 *
	 * @param int $user_id User ID.
	 * @return array Consent records.
	 */
	public static function get_consent_history( $user_id ) {
		return (array) get_user_meta( (int) $user_id, 'wpshadow_consent_history', true );
	}

	/**
	 * Record consent decision in history.
	 *
	 * @param int    $user_id User ID.
	 * @param string $decision Decision: 'accept_all', 'essential_only', 'custom'.
	 * @param array  $preferences Preferences chosen.
	 * @return void
	 */
	public static function record_consent( $user_id, $decision, $preferences = array() ) {
		$history = self::get_consent_history( $user_id );

		$history[] = array(
			'timestamp'     => current_time( 'mysql' ),
			'decision'      => $decision,
			'ip_hash'       => self::hash_ip(),
			'preferences'   => $preferences,
		);

		update_user_meta( (int) $user_id, 'wpshadow_consent_history', $history );
	}

	/**
	 * Hash IP address (not stored, just for uniqueness).
	 *
	 * @return string IP hash.
	 */
	private static function hash_ip() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return substr( hash( 'sha256', $ip ), 0, 8 );
	}

	/**
	 * Get global consent statistics (for admins).
	 *
	 * @return array Statistics.
	 */
	public static function get_consent_stats() {
		global $wpdb;

		$total_users = count_users();
		$admin_users = count( get_users( array( 'role' => 'administrator' ) ) );

		// Count users who have consented
		$query = $wpdb->prepare(
			"SELECT COUNT(DISTINCT user_id) as count FROM {$wpdb->usermeta} 
			WHERE meta_key = %s AND meta_value LIKE %s",
			'wpshadow_consent_preferences',
			'%consented_at%'
		);

		$consented = (int) $wpdb->get_var( $query );

		return array(
			'total_users'          => $total_users['total_users'],
			'admin_users'          => $admin_users,
			'users_consented'      => $consented,
			'consent_rate'         => $total_users['total_users'] > 0 ? round( ( $consented / $total_users['total_users'] ) * 100 ) : 0,
		);
	}

	/**
	 * Export user's consents for GDPR.
	 *
	 * @param int $user_id User ID.
	 * @return array Export data.
	 */
	public static function export_consent_data( $user_id ) {
		return array(
			'current_preferences' => self::get_preferences( $user_id ),
			'consent_history'     => self::get_consent_history( $user_id ),
			'exported_date'       => current_time( 'mysql' ),
		);
	}
}
