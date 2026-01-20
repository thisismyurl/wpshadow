<?php

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Session_Manager {

	private const SESSION_TTL = 3600;

	private const SESSION_PREFIX = 'wpshadow_session_';

	public static function get_user_session( ?int $user_id = null ): array {
		$user_id = $user_id ?? get_current_user_id();

		if ( ! $user_id ) {
			return array();
		}

		$key = self::SESSION_PREFIX . $user_id;
		$session = get_transient( $key );

		return is_array( $session ) ? $session : array();
	}

	public static function set_user_session( array $data, ?int $user_id = null, ?int $ttl = null ): bool {
		$user_id = $user_id ?? get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$key = self::SESSION_PREFIX . $user_id;
		$ttl = $ttl ?? self::SESSION_TTL;

		return set_transient( $key, $data, $ttl );
	}

	public static function update_user_session( array $updates, ?int $user_id = null, ?int $ttl = null ): bool {
		$current = self::get_user_session( $user_id );
		$merged  = array_merge( $current, $updates );

		return self::set_user_session( $merged, $user_id, $ttl );
	}

	public static function get_session_key( string $key, mixed $default = null, ?int $user_id = null ): mixed {
		$session = self::get_user_session( $user_id );

		return isset( $session[ $key ] ) ? $session[ $key ] : $default;
	}

	public static function set_session_key( string $key, mixed $value, ?int $user_id = null, ?int $ttl = null ): bool {
		$session         = self::get_user_session( $user_id );
		$session[ $key ] = $value;

		return self::set_user_session( $session, $user_id, $ttl );
	}

	public static function delete_session_key( string $key, ?int $user_id = null ): bool {
		$session = self::get_user_session( $user_id );

		if ( ! isset( $session[ $key ] ) ) {
			return false;
		}

		unset( $session[ $key ] );

		return self::set_user_session( $session, $user_id );
	}

	public static function clear_user_session( ?int $user_id = null ): bool {
		$user_id = $user_id ?? get_current_user_id();

		if ( ! $user_id ) {
			return false;
		}

		$key = self::SESSION_PREFIX . $user_id;

		return delete_transient( $key );
	}

	public static function has_session_key( string $key, ?int $user_id = null ): bool {
		$session = self::get_user_session( $user_id );

		return isset( $session[ $key ] );
	}

	public static function get_session_count( ?int $user_id = null ): int {
		$session = self::get_user_session( $user_id );

		return count( $session );
	}
}
