<?php
/**
 * Vault class alias for core-support plugin.
 *
 * This file delegates all Vault operations to the canonical implementation
 * in vault-support-thisismyurl plugin. No logic duplication.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Alias vault-support Vault class into this namespace if available.
// WP Support works independently - Vault Support is optional for extended features.
if ( class_exists( '\\WPS\\VaultSupport\\WPS_Vault' ) ) {
	class_alias( '\\WPS\\VaultSupport\\WPS_Vault', __NAMESPACE__ . '\\WPS_Vault' );
} else {
	/**
	 * Stub WPS_Vault class when Vault Support plugin is not available.
	 * Provides no-op methods to prevent fatal errors.
	 *
	 * @package wp_support_SUPPORT
	 */
	class WPS_Vault {
		/**
		 * No-op init method.
		 *
		 * @return void
		 */
		public static function init(): void {
			// Vault Support not available - extended vault features disabled.
		}

		/**
		 * No-op get_settings method.
		 *
		 * @return array Empty settings array.
		 */
		public static function get_settings(): array {
			return array();
		}

		/**
		 * No-op get_logs method.
		 *
		 * @param int $offset Offset for pagination.
		 * @param int $limit  Limit for pagination.
		 * @return array Empty logs array.
		 */
		public static function get_logs( int $offset = 0, int $limit = 10 ): array {
			return array();
		}

		/**
		 * No-op get_pending_contributor_uploads method.
		 *
		 * @param int $limit Limit for results.
		 * @return array Empty array.
		 */
		public static function get_pending_contributor_uploads( int $limit = 5 ): array {
			return array();
		}

		/**
		 * No-op add_log method.
		 *
		 * @param string $level   Log level.
		 * @param int    $user_id User ID.
		 * @param string $message Log message.
		 * @param string $context Log context.
		 * @return void
		 */
		public static function add_log( string $level, int $user_id, string $message, string $context = '' ): void {
			// Vault Support not available - logging disabled.
		}

		/**
		 * No-op maybe_handle_settings_submission method.
		 *
		 * @param bool $network Network admin context.
		 * @return void
		 */
		public static function maybe_handle_settings_submission( bool $network ): void {
			// Vault Support not available.
		}

		/**
		 * No-op maybe_handle_tools_submission method.
		 *
		 * @param bool $network Network admin context.
		 * @return void
		 */
		public static function maybe_handle_tools_submission( bool $network ): void {
			// Vault Support not available.
		}

		/**
		 * No-op maybe_handle_log_action method.
		 *
		 * @return void
		 */
		public static function maybe_handle_log_action(): void {
			// Vault Support not available.
		}

		/**
		 * No-op site_override_allowed method.
		 *
		 * @return bool Always returns true when Vault not available.
		 */
		public static function site_override_allowed(): bool {
			return true;
		}
	}
}
