<?php
/**
 * Points System
 *
 * Manages points earning, spending, and tracking.
 * Phase 8: Gamification System - Points Management
 *
 * @package    WPShadow
 * @subpackage Gamification
 * @since      1.2604.0400
 */

declare(strict_types=1);

namespace WPShadow\Gamification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Points System Class
 *
 * Handles all point-based operations.
 *
 * @since 1.2604.0400
 */
class Points_System {

	/**
	 * Award points to a user.
	 *
	 * @since  1.2604.0400
	 * @param  int    $user_id User ID.
	 * @param  int    $points  Points to award.
	 * @param  string $reason  Reason for points.
	 * @param  array  $meta    Optional metadata for the action.
	 * @return bool True on success.
	 */
	public static function award_points( $user_id, $points, $reason = '', $meta = array() ) {
		if ( ! $user_id || $points <= 0 ) {
			return false;
		}

		$current = self::get_balance( $user_id );
		$new_balance = $current + $points;

		update_user_meta( $user_id, 'wpshadow_points_balance', $new_balance );

		// Record transaction
		self::record_transaction( $user_id, $points, 'earned', $reason, $meta );

		// Check tier badges
		self::check_tier_badges( $user_id, $new_balance );

		// Trigger action
		do_action( 'wpshadow_points_awarded', $user_id, $points, $reason );

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'points_earned',
				sprintf(
					/* translators: 1: points, 2: reason */
					__( 'Earned %1$d points: %2$s', 'wpshadow' ),
					$points,
					$reason
				),
				'',
				array( 'points' => $points )
			);
		}

		return true;
	}

	/**
	 * Spend points.
	 *
	 * @since  1.2604.0400
	 * @param  int    $user_id User ID.
	 * @param  int    $points  Points to spend.
	 * @param  string $reason  Reason for spending.
	 * @return bool True on success.
	 */
	public static function spend_points( $user_id, $points, $reason = '' ) {
		if ( ! $user_id || $points <= 0 ) {
			return false;
		}

		$current = self::get_balance( $user_id );

		// Check sufficient balance
		if ( $current < $points ) {
			return false;
		}

		$new_balance = $current - $points;

		update_user_meta( $user_id, 'wpshadow_points_balance', $new_balance );

		// Record transaction
		self::record_transaction( $user_id, -$points, 'spent', $reason );

		// Trigger action
		do_action( 'wpshadow_points_spent', $user_id, $points, $reason );

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'points_spent',
				sprintf(
					/* translators: 1: points, 2: reason */
					__( 'Spent %1$d points: %2$s', 'wpshadow' ),
					$points,
					$reason
				),
				'',
				array( 'points' => $points )
			);
		}

		return true;
	}

	/**
	 * Get user's point balance.
	 *
	 * @since  1.2604.0400
	 * @param  int $user_id User ID.
	 * @return int Point balance.
	 */
	public static function get_balance( $user_id ) {
		return (int) get_user_meta( $user_id, 'wpshadow_points_balance', true );
	}

	/**
	 * Get user's lifetime points (all earned).
	 *
	 * @since  1.2604.0400
	 * @param  int $user_id User ID.
	 * @return int Lifetime points.
	 */
	public static function get_lifetime_points( $user_id ) {
		return (int) get_user_meta( $user_id, 'wpshadow_lifetime_points', true );
	}

	/**
	 * Record a point transaction.
	 *
	 * @since  1.2604.0400
	 * @param  int    $user_id User ID.
	 * @param  int    $points  Points (negative for spending).
	 * @param  string $type    Transaction type (earned/spent).
	 * @param  string $reason  Reason.
	 * @param  array  $meta    Optional metadata for the action.
	 * @return void
	 */
	private static function record_transaction( $user_id, $points, $type, $reason, $meta = array() ) {
		$history = get_user_meta( $user_id, 'wpshadow_points_history', true );

		if ( ! is_array( $history ) ) {
			$history = array();
		}

		$history[] = array(
			'timestamp' => current_time( 'mysql' ),
			'points'    => $points,
			'type'      => $type,
			'reason'    => $reason,
			'meta'      => is_array( $meta ) ? $meta : array(),
		);

		// Keep last 100 transactions
		if ( count( $history ) > 100 ) {
			$history = array_slice( $history, -100 );
		}

		update_user_meta( $user_id, 'wpshadow_points_history', $history );

		// Update lifetime points if earned
		if ( $points > 0 ) {
			$lifetime = self::get_lifetime_points( $user_id );
			update_user_meta( $user_id, 'wpshadow_lifetime_points', $lifetime + $points );
		}
	}

	/**
	 * Get action count for a specific reason.
	 *
	 * @since  1.2604.0400
	 * @param  int         $user_id    User ID.
	 * @param  string      $reason     Action reason to count.
	 * @param  string|null $meta_key   Optional meta key filter.
	 * @param  string|null $meta_value Optional meta value filter.
	 * @return int Action count.
	 */
	public static function get_action_count( $user_id, $reason, $meta_key = null, $meta_value = null ) {
		$history = get_user_meta( $user_id, 'wpshadow_points_history', true );

		if ( ! is_array( $history ) ) {
			return 0;
		}

		$count = 0;

		foreach ( $history as $entry ) {
			if ( empty( $entry['reason'] ) || $entry['reason'] !== $reason ) {
				continue;
			}

			if ( $meta_key ) {
				$meta = $entry['meta'] ?? array();
				if ( ! is_array( $meta ) || ! array_key_exists( $meta_key, $meta ) ) {
					continue;
				}

				if ( null !== $meta_value && (string) $meta[ $meta_key ] !== (string) $meta_value ) {
					continue;
				}
			}

			++$count;
		}

		return $count;
	}

	/**
	 * Get point transaction history.
	 *
	 * @since  1.2604.0400
	 * @param  int $user_id User ID.
	 * @param  int $limit   Number of transactions to return.
	 * @return array Transaction history.
	 */
	public static function get_history( $user_id, $limit = 20 ) {
		$history = get_user_meta( $user_id, 'wpshadow_points_history', true );

		if ( ! is_array( $history ) ) {
			return array();
		}

		// Return most recent first
		$history = array_reverse( $history );

		return array_slice( $history, 0, $limit );
	}

	/**
	 * Check and award tier badges based on points.
	 *
	 * @since  1.2604.0400
	 * @param  int $user_id User ID.
	 * @param  int $points  Current points.
	 * @return void
	 */
	private static function check_tier_badges( $user_id, $points ) {
		$tiers = array(
			'bronze'   => 500,
			'silver'   => 2000,
			'gold'     => 5000,
			'platinum' => 10000,
		);

		foreach ( $tiers as $badge_id => $required_points ) {
			if ( $points >= $required_points ) {
				Badge_System::award_badge( $user_id, $badge_id );
			}
		}
	}

	/**
	 * Get points summary for user.
	 *
	 * @since  1.2604.0400
	 * @param  int $user_id User ID.
	 * @return array {
	 *     Points summary.
	 *
	 *     @type int    $balance  Current balance.
	 *     @type int    $lifetime Lifetime earned.
	 *     @type int    $spent    Total spent.
	 *     @type string $tier     Current tier.
	 *     @type int    $next_tier_points Points to next tier.
	 * }
	 */
	public static function get_summary( $user_id ) {
		$balance = self::get_balance( $user_id );
		$lifetime = self::get_lifetime_points( $user_id );
		$spent = $lifetime - $balance;

		$tiers = array(
			10000 => 'platinum',
			5000  => 'gold',
			2000  => 'silver',
			500   => 'bronze',
		);

		$tier = 'none';
		$next_tier_points = 500;

		foreach ( $tiers as $points => $tier_name ) {
			if ( $lifetime >= $points ) {
				$tier = $tier_name;
				break;
			}
			$next_tier_points = $points;
		}

		return array(
			'balance'           => $balance,
			'lifetime'          => $lifetime,
			'spent'             => $spent,
			'tier'              => $tier,
			'next_tier_points'  => $next_tier_points,
		);
	}

	/**
	 * Get the next milestone for a user
	 *
	 * @since  1.2604.0400
	 * @param  int $user_id User ID.
	 * @return array {
	 *     Next milestone information.
	 *
	 *     @type int    $points   Points to next milestone.
	 *     @type string $label    Milestone label.
	 *     @type string $progress Progress percentage.
	 * }
	 */
	public static function get_next_milestone( $user_id ) {
		$balance = self::get_balance( $user_id );

		$milestones = array(
			100   => __( 'Getting Started', 'wpshadow' ),
			500   => __( 'Active User', 'wpshadow' ),
			1000  => __( 'Power User', 'wpshadow' ),
			5000  => __( 'Expert', 'wpshadow' ),
			10000 => __( 'Master', 'wpshadow' ),
		);

		$next_milestone_points = 10100; // Default: beyond max
		$next_label = __( 'Master Achieved', 'wpshadow' );

		foreach ( $milestones as $points => $label ) {
			if ( $balance < $points ) {
				$next_milestone_points = $points;
				$next_label = $label;
				break;
			}
		}

		$progress = ( $balance / $next_milestone_points ) * 100;
		$progress = min( 100, max( 0, $progress ) );

		return array(
			'points'   => $next_milestone_points - $balance,
			'label'    => $next_label,
			'progress' => round( $progress, 1 ),
		);
	}
}
