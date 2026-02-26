<?php
/**
 * Academy Release Gate
 *
 * Centralized release-date gate for Academy functionality.
 *
 * @package    WPShadow
 * @subpackage Academy
 * @since      1.6057.0000
 */

declare(strict_types=1);

namespace WPShadow\Academy;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Academy_Release_Gate Class
 *
 * Controls whether Academy functionality is available.
 *
 * @since 1.6057.0000
 */
class Academy_Release_Gate {

	/**
	 * Check whether Academy is available.
	 *
	 * Defaults to May 1, 2026 (Toronto time) and can be filtered.
	 *
	 * @since  1.6057.0000
	 * @return bool True when Academy should be enabled.
	 */
	public static function is_available(): bool {
		$timezone = new \DateTimeZone( 'America/Toronto' );

		$release_datetime = (string) apply_filters(
			'wpshadow_academy_release_datetime',
			'2026-05-01 00:00:00'
		);

		$release_at = \DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $release_datetime, $timezone );
		if ( false === $release_at ) {
			$release_at = new \DateTimeImmutable( '2026-05-01 00:00:00', $timezone );
		}

		$now = new \DateTimeImmutable( 'now', $timezone );

		return $now >= $release_at;
	}

	/**
	 * Get holdback message shown while Academy is gated.
	 *
	 * @since  1.6057.0000
	 * @return string User-friendly message.
	 */
	public static function get_hold_message(): string {
		return (string) __( 'WPShadow Academy is preparing for the May release. This area will unlock automatically when the release goes live.', 'wpshadow' );
	}
}
