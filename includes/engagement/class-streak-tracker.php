<?php
declare(strict_types=1);

namespace WPShadow\Gamification;

/**
 * Streak Tracker
 *
 * Tracks consecutive action streaks (scans, fixes, etc).
 * Philosophy: Inspire Confidence (#8) - Visual progress motivation
 * Philosophy: Show Value (#9) - Prove consistent improvement
 *
 * @since 1.2601
 * @package WPShadow
 */
class Streak_Tracker {

	/**
	 * User streaks meta key
	 */
	const USER_STREAKS_KEY = 'wpshadow_user_streaks';

	/**
	 * Get all user streaks
	 *
	 * @param int $user_id User ID
	 * @return array Current streaks
	 */
	public static function get_streaks( $user_id ): array {
		return get_user_meta( $user_id, self::USER_STREAKS_KEY, true ) ?: array(
			'daily_scans'          => 0,
			'daily_scans_last'     => null,
			'weekly_scans'         => 0,
			'weekly_scans_last'    => null,
			'fixes'                => 0,
			'fixes_last'           => null,
			'longest_daily_streak' => 0,
		);
	}

	/**
	 * Record a scan action (increments scan streak)
	 *
	 * @param int $user_id User ID
	 * @return array Updated streak data
	 */
	public static function record_scan( $user_id ): array {
		$streaks   = self::get_streaks( $user_id );
		$today     = date( 'Y-m-d' );
		$last_date = $streaks['daily_scans_last'] ?? null;

		// Check if continuing a streak
		if ( $last_date === $today ) {
			// Already scanned today, no change
			return $streaks;
		}

		$yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );

		if ( $last_date === $yesterday ) {
			// Continuing streak
			++$streaks['daily_scans'];
		} else {
			// New streak
			$streaks['daily_scans'] = 1;
		}

		// Update longest streak
		if ( $streaks['daily_scans'] > $streaks['longest_daily_streak'] ) {
			$streaks['longest_daily_streak'] = $streaks['daily_scans'];
		}

		$streaks['daily_scans_last'] = $today;

		update_user_meta( $user_id, self::USER_STREAKS_KEY, $streaks );

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'scan_streak_updated',
				sprintf( 'Scan streak: %d day(s)', $streaks['daily_scans'] ),
				'',
				array(
					'user_id' => $user_id,
					'streak'  => $streaks['daily_scans'],
				)
			);
		}

		return $streaks;
	}

	/**
	 * Record a fix action (increments fix streak)
	 *
	 * @param int $user_id User ID
	 * @return array Updated streak data
	 */
	public static function record_fix( $user_id ): array {
		$streaks   = self::get_streaks( $user_id );
		$today     = date( 'Y-m-d' );
		$last_date = $streaks['fixes_last'] ?? null;

		// Check if continuing a streak
		if ( $last_date === $today ) {
			// Already fixed today, count multiple fixes
			++$streaks['fixes'];
		} else {
			$yesterday = date( 'Y-m-d', strtotime( '-1 day' ) );

			if ( $last_date === $yesterday ) {
				// Continuing streak
				$streaks['fixes'] = 1;
			} else {
				// New streak
				$streaks['fixes'] = 1;
			}
		}

		$streaks['fixes_last'] = $today;

		update_user_meta( $user_id, self::USER_STREAKS_KEY, $streaks );

		return $streaks;
	}

	/**
	 * Reset streaks (when broken)
	 *
	 * @param int    $user_id User ID
	 * @param string $streak_type Type of streak to reset
	 * @return array Updated streaks
	 */
	public static function reset_streak( $user_id, $streak_type = 'daily_scans' ): array {
		$streaks = self::get_streaks( $user_id );

		switch ( $streak_type ) {
			case 'daily_scans':
				$streaks['daily_scans']      = 0;
				$streaks['daily_scans_last'] = null;
				break;
			case 'fixes':
				$streaks['fixes']      = 0;
				$streaks['fixes_last'] = null;
				break;
		}

		update_user_meta( $user_id, self::USER_STREAKS_KEY, $streaks );

		return $streaks;
	}

	/**
	 * Get current streak status
	 *
	 * @param int $user_id User ID
	 * @return array Current streaks
	 */
	public static function get_current_streaks( $user_id ): array {
		$streaks = self::get_streaks( $user_id );
		$today   = date( 'Y-m-d' );

		// Check if streaks are still active
		if ( $streaks['daily_scans_last'] !== $today && $streaks['daily_scans_last'] !== date( 'Y-m-d', strtotime( '-1 day' ) ) ) {
			$streaks['daily_scans'] = 0;
		}

		if ( $streaks['fixes_last'] !== $today && $streaks['fixes_last'] !== date( 'Y-m-d', strtotime( '-1 day' ) ) ) {
			$streaks['fixes'] = 0;
		}

		return $streaks;
	}

	/**
	 * Get streak fire emoji representation
	 *
	 * @param int $streak_count Streak count
	 * @return string Fire emoji for streak size
	 */
	public static function get_streak_emoji( $streak_count ): string {
		if ( $streak_count === 0 ) {
			return '❄️';
		} elseif ( $streak_count < 7 ) {
			return '🔥';
		} elseif ( $streak_count < 14 ) {
			return '🔥🔥';
		} elseif ( $streak_count < 30 ) {
			return '🔥🔥🔥';
		} else {
			return '⭐🔥';
		}
	}
}
