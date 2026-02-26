<?php
/**
 * Gamification Release Gate
 *
 * Centralized release gating for achievements and related gamification features.
 *
 * @package    WPShadow
 * @subpackage Gamification
 * @since      1.6035.2150
 */

declare(strict_types=1);

namespace WPShadow\Gamification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gamification_Release_Gate Class
 *
 * Provides a single source of truth for whether achievements are available.
 *
 * @since 1.6035.2150
 */
class Gamification_Release_Gate {

	/**
	 * Default release datetime in site timezone.
	 *
	 * @var string
	 */
	private const DEFAULT_RELEASE_DATETIME = '2026-07-01 00:00:00';

	/**
	 * Check whether achievements are released.
	 *
	 * @since  1.6035.2150
	 * @return bool True when released.
	 */
	public static function is_released(): bool {
		$release_datetime = apply_filters( 'wpshadow_achievements_release_datetime', self::DEFAULT_RELEASE_DATETIME );

		try {
			$timezone = wp_timezone();
			$release  = new \DateTimeImmutable( (string) $release_datetime, $timezone );
			$now      = new \DateTimeImmutable( 'now', $timezone );

			return $now >= $release;
		} catch ( \Exception $exception ) {
			return false;
		}
	}
}
