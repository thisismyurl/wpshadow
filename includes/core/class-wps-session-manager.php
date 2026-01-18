<?php
/**
 * WPShadow Session Manager
 *
 * Centralized user session handling with transient caching.
 * Consolidates scattered get_transient() calls for consistency.
 *
 * @package WPShadow\Core
 */

declare(strict_types=1);

namespace WPShadow\Core;

/**
 * Session Manager for user-specific data storage
 *
 * Provides consistent API for managing user session data via transients.
 * Default TTL is 1 hour, easily configurable per operation.
 *
 * Usage:
 *   // Get session data
 *   $data = WPSHADOW_Session_Manager::get_user_session();
 *
 *   // Set session data
 *   WPSHADOW_Session_Manager::set_user_session( ['status' => 'active'] );
 *
 *   // Update specific keys
 *   WPSHADOW_Session_Manager::update_user_session( ['updated' => time()] );
 *
 *   // Clear session
 *   WPSHADOW_Session_Manager::clear_user_session();
 */
class WPSHADOW_Session_Manager {

	/**
	 * Default session TTL (1 hour)
	 */
	private const SESSION_TTL = 3600;

	/**
	 * Session key prefix
	 */
	private const SESSION_PREFIX = 'wpshadow_session_';

	/**
	 * Get user session data
	 *
	 * Retrieves all session data for current user or specified user.
	 *
	 * @param int|null $user_id User ID (null for current user).
	 * @return array Session data array.
	 */
	public static function get_user_session( ?int $user_id = null ): array {
		$user_id = $user_id ?? get_current_user_id();

		if ( ! $user_id ) {
			return array();
		}

		$key = self::SESSION_PREFIX . $user_id;
		$session = get_transient( $key );

		return is_array( $session ) ? $session : array();
	}

	/**
	 * Set user session data
	 *
	 * Replaces entire session data for user.
	 *
	 * @param array    $data    Session data to store.
	 * @param int|null $user_id User ID (null for current user).
	 * @param int|null $ttl     TTL in seconds (null for default 1 hour).
	 * @return bool True if session was set.
	 */
	public static function set_user_session( array $data, ?int $user_id = null, ?int $ttl = null ): bool {
		$user_id = $user_id ?? get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$key = self::SESSION_PREFIX . $user_id;
		$ttl = $ttl ?? self::SESSION_TTL;

		return set_transient( $key, $data, $ttl );
	}

	/**
	 * Update session data (merge with existing)
	 *
	 * Merges provided data with existing session data.
	 *
	 * @param array    $updates Data to update/merge.
	 * @param int|null $user_id User ID (null for current user).
	 * @param int|null $ttl     TTL in seconds (null for default 1 hour).
	 * @return bool True if session was updated.
	 */
	public static function update_user_session( array $updates, ?int $user_id = null, ?int $ttl = null ): bool {
		$current = self::get_user_session( $user_id );
		$merged  = array_merge( $current, $updates );

		return self::set_user_session( $merged, $user_id, $ttl );
	}

	/**
	 * Get specific session key
	 *
	 * @param string   $key     Session key to retrieve.
	 * @param mixed    $default Default value if key doesn't exist.
	 * @param int|null $user_id User ID (null for current user).
	 * @return mixed Session value or default.
	 */
	public static function get_session_key( string $key, mixed $default = null, ?int $user_id = null ): mixed {
		$session = self::get_user_session( $user_id );

		return isset( $session[ $key ] ) ? $session[ $key ] : $default;
	}

	/**
	 * Set specific session key
	 *
	 * @param string   $key     Session key to set.
	 * @param mixed    $value   Value to set.
	 * @param int|null $user_id User ID (null for current user).
	 * @param int|null $ttl     TTL in seconds (null for default 1 hour).
	 * @return bool True if key was set.
	 */
	public static function set_session_key( string $key, mixed $value, ?int $user_id = null, ?int $ttl = null ): bool {
		$session         = self::get_user_session( $user_id );
		$session[ $key ] = $value;

		return self::set_user_session( $session, $user_id, $ttl );
	}

	/**
	 * Delete specific session key
	 *
	 * @param string   $key     Session key to delete.
	 * @param int|null $user_id User ID (null for current user).
	 * @return bool True if key was deleted.
	 */
	public static function delete_session_key( string $key, ?int $user_id = null ): bool {
		$session = self::get_user_session( $user_id );

		if ( ! isset( $session[ $key ] ) ) {
			return false;
		}

		unset( $session[ $key ] );

		return self::set_user_session( $session, $user_id );
	}

	/**
	 * Clear entire user session
	 *
	 * @param int|null $user_id User ID (null for current user).
	 * @return bool True if session was cleared.
	 */
	public static function clear_user_session( ?int $user_id = null ): bool {
		$user_id = $user_id ?? get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$key = self::SESSION_PREFIX . $user_id;

		return delete_transient( $key );
	}

	/**
	 * Check if session key exists
	 *
	 * @param string   $key     Session key to check.
	 * @param int|null $user_id User ID (null for current user).
	 * @return bool True if key exists in session.
	 */
	public static function has_session_key( string $key, ?int $user_id = null ): bool {
		$session = self::get_user_session( $user_id );

		return isset( $session[ $key ] );
	}

	/**
	 * Get session key count
	 *
	 * @param int|null $user_id User ID (null for current user).
	 * @return int Number of keys in session.
	 */
	public static function get_session_count( ?int $user_id = null ): int {
		$session = self::get_user_session( $user_id );

		return count( $session );
	}
}
