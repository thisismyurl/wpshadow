<?php
/**
 * Leaderboard System
 *
 * Manages global and category leaderboards (opt-in only, privacy-first).
 * Phase 8: Gamification System - Leaderboards
 *
 * @package    WPShadow
 * @subpackage Gamification
 * @since 1.6180
 */

declare(strict_types=1);

namespace WPShadow\Gamification;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Leaderboard Class
 *
 * Privacy-first leaderboard system.
 *
 * @since 1.6180
 */
class Leaderboard extends Hook_Subscriber_Base {

	/**
	 * Get hook subscriptions.
	 *
	 * @since  1.7035.1400
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		if ( ! Gamification_Release_Gate::is_released() ) {
			return array();
		}

		return array(
			'init' => 'schedule_leaderboard_refresh',
		);
	}

	/**
	 * Get the minimum required version for this feature.
	 *
	 * @since  1.6180
	 * @return string Minimum required version.
	 */
	protected static function get_required_version(): string {
		return '1.6180';
	}

	/**
	 * Initialize leaderboard system (deprecated).
	 *
	 * @deprecated 1.7035.1400 Use Leaderboard::subscribe() instead
	 * @since      1.6004.0400
	 * @return     void
	 */
	public static function init() {
		if ( ! Gamification_Release_Gate::is_released() ) {
			return;
		}

		self::subscribe();
	}

	/**
	 * Schedule leaderboard cache refresh.
	 *
	 * @since  1.6004.0400
	 * @return void
	 */
	public static function schedule_leaderboard_refresh() {
		if ( ! Gamification_Release_Gate::is_released() ) {
			return;
		}

		if ( ! wp_next_scheduled( 'wpshadow_refresh_leaderboard' ) ) {
			wp_schedule_event( time(), 'hourly', 'wpshadow_refresh_leaderboard' );
		}

		add_action( 'wpshadow_refresh_leaderboard', array( __CLASS__, 'refresh_cache' ) );
	}

	/**
	 * Check if user is opted into leaderboard.
	 *
	 * @since  1.6004.0400
	 * @param  int $user_id User ID (defaults to current user).
	 * @return bool True if opted in.
	 */
	public static function is_opted_in( $user_id = null ) {
		if ( ! Gamification_Release_Gate::is_released() ) {
			return false;
		}

		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Default: opt-out (privacy-first)
		return (bool) get_user_meta( $user_id, 'wpshadow_leaderboard_optin', true );
	}

	/**
	 * Opt user in/out of leaderboard.
	 *
	 * @since  1.6004.0400
	 * @param  int  $user_id User ID.
	 * @param  bool $optin   True to opt in, false to opt out.
	 * @return void
	 */
	public static function set_optin( $user_id, $optin ) {
		if ( ! Gamification_Release_Gate::is_released() ) {
			return;
		}

		update_user_meta( $user_id, 'wpshadow_leaderboard_optin', (bool) $optin );

		// Refresh cache when optin changes
		self::refresh_cache();
	}

	/**
	 * Get leaderboard display name.
	 *
	 * @since  1.6004.0400
	 * @param  int $user_id User ID.
	 * @return string Display name.
	 */
	public static function get_display_name( $user_id ) {
		// Check if user has custom alias
		$alias = get_user_meta( $user_id, 'wpshadow_leaderboard_alias', true );

		if ( $alias ) {
			return sanitize_text_field( $alias );
		}

		// Fall back to display name
		$user = get_userdata( $user_id );

		return $user ? $user->display_name : __( 'Anonymous', 'wpshadow' );
	}

	/**
	 * Set leaderboard display alias.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id User ID.
	 * @param  string $alias   Alias (max 50 chars).
	 * @return void
	 */
	public static function set_alias( $user_id, $alias ) {
		$alias = sanitize_text_field( substr( $alias, 0, 50 ) );
		update_user_meta( $user_id, 'wpshadow_leaderboard_alias', $alias );
	}

	/**
	 * Get global leaderboard.
	 *
	 * @since  1.6004.0400
	 * @param  string $period Period (all_time|monthly|weekly).
	 * @param  int    $limit  Number of users to return.
	 * @return array Leaderboard data.
	 */
	public static function get_global( $period = 'all_time', $limit = 50 ) {
		if ( ! Gamification_Release_Gate::is_released() ) {
			return array();
		}

		$cache_key = "wpshadow_leaderboard_{$period}_{$limit}";
		$cached = \WPShadow\Core\Cache_Manager::get(
			$cache_key,
			'wpshadow_leaderboard'
		);

		if ( false !== $cached ) {
			return $cached;
		}

		$results = get_users(
			array(
				'meta_key'   => 'wpshadow_leaderboard_optin',
				'meta_value' => '1',
				'fields'     => 'ID',
				'number'     => $limit,
			)
		);

		$leaderboard = array();

		foreach ( $results as $user_id ) {
			$user_id = (int) $user_id;
			$points = 0;

			if ( 'all_time' === $period ) {
				$points = Points_System::get_lifetime_points( $user_id );
			} else {
				// Calculate points for period
				$history = get_user_meta( $user_id, 'wpshadow_points_history', true );

				if ( is_array( $history ) ) {
					foreach ( $history as $transaction ) {
						$timestamp = $transaction['timestamp'];

						if ( 'monthly' === $period && strpos( $timestamp, date( 'Y-m' ) ) === 0 ) {
							$points += max( 0, $transaction['points'] );
						} elseif ( 'weekly' === $period && strtotime( $timestamp ) >= strtotime( '-7 days' ) ) {
							$points += max( 0, $transaction['points'] );
						}
					}
				}
			}

			if ( $points > 0 ) {
				$leaderboard[] = array(
					'user_id'      => $user_id,
					'display_name' => self::get_display_name( $user_id ),
					'points'       => $points,
					'badges'       => count( Badge_System::get_earned_badges( $user_id ) ),
					'achievements' => count( Achievement_Registry::get_unlocked( $user_id ) ),
				);
			}
		}

		// Sort by points descending
		usort(
			$leaderboard,
			function( $a, $b ) {
				return $b['points'] - $a['points'];
			}
		);

		// Add rank
		$rank = 1;
		foreach ( $leaderboard as &$entry ) {
			$entry['rank'] = $rank++;
		}

		// Cache for 1 hour
		\WPShadow\Core\Cache_Manager::set(
			$cache_key,
			$leaderboard,
			HOUR_IN_SECONDS,
			'wpshadow_leaderboard'
		);

		return $leaderboard;
	}

	/**
	 * Get user's rank.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id User ID.
	 * @param  string $period  Period.
	 * @return int|null Rank or null if not on leaderboard.
	 */
	public static function get_user_rank( $user_id, $period = 'all_time' ) {
		if ( ! self::is_opted_in( $user_id ) ) {
			return null;
		}

		$leaderboard = self::get_global( $period, 100 );

		foreach ( $leaderboard as $entry ) {
			if ( $entry['user_id'] === $user_id ) {
				return $entry['rank'];
			}
		}

		return null;
	}

	/**
	 * Refresh leaderboard cache.
	 *
	 * @since  1.6004.0400
	 * @return void
	 */
	public static function refresh_cache() {
		\WPShadow\Core\Cache_Manager::delete( 'leaderboard_all_time_50', 'wpshadow_leaderboard' );
		\WPShadow\Core\Cache_Manager::delete( 'leaderboard_monthly_50', 'wpshadow_leaderboard' );
		\WPShadow\Core\Cache_Manager::delete( 'leaderboard_weekly_50', 'wpshadow_leaderboard' );
	}
}
