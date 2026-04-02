<?php
/**
 * Badge System
 *
 * Manages badge awarding and display.
 * Phase 8: Gamification System - Badge Management
 *
 * @package    WPShadow
 * @subpackage Gamification
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Gamification;

use WPShadow\Core\Hook_Subscriber_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Badge System Class
 *
 * Handles badge definitions and awarding logic.
 *
 * @since 1.6093.1200
 */
class Badge_System extends Hook_Subscriber_Base {

	/**
	 * Badge definitions.
	 *
	 * @var array
	 */
	private static $badges = array();

	/**
	 * Get hook subscriptions.
	 *
	 * @since 1.6093.1200
	 * @return array Hook subscriptions.
	 */
	protected static function get_hooks(): array {
		return array(); // Badges registered in register_badges(), no hooks needed
	}

	/**
	 * Get the minimum required version for this feature.
	 *
	 * @since 1.6093.1200
	 * @return string Minimum required version.
	 */
	protected static function get_required_version(): string {
		return '1.6180';
	}

	/**
	 * Initialize badge system (deprecated).
	 *
	 * @deprecated1.0 Use Badge_System::subscribe() instead
	 * @since 1.6093.1200
	 * @return     void
	 */
	public static function init() {
		self::register_badges();
	}

	/**
	 * Register all badges.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private static function register_badges() {
		// Tier badges (based on points)
		self::register( 'bronze', array(
			'name'        => __( 'Bronze Member', 'wpshadow' ),
			'description' => __( 'Earn 500 points', 'wpshadow' ),
			'emoji'       => '🥉',
			'tier'        => 'bronze',
			'requirement' => array( 'type' => 'points', 'value' => 500 ),
		) );

		self::register( 'silver', array(
			'name'        => __( 'Silver Member', 'wpshadow' ),
			'description' => __( 'Earn 2,000 points', 'wpshadow' ),
			'emoji'       => '🥈',
			'tier'        => 'silver',
			'requirement' => array( 'type' => 'points', 'value' => 2000 ),
		) );

		self::register( 'gold', array(
			'name'        => __( 'Gold Member', 'wpshadow' ),
			'description' => __( 'Earn 5,000 points', 'wpshadow' ),
			'emoji'       => '🥇',
			'tier'        => 'gold',
			'requirement' => array( 'type' => 'points', 'value' => 5000 ),
		) );

		self::register( 'platinum', array(
			'name'        => __( 'Platinum Member', 'wpshadow' ),
			'description' => __( 'Earn 10,000 points', 'wpshadow' ),
			'emoji'       => '💎',
			'tier'        => 'platinum',
			'requirement' => array( 'type' => 'points', 'value' => 10000 ),
		) );

		// Activity badges
		self::register( 'early_bird', array(
			'name'        => __( 'Early Bird', 'wpshadow' ),
			'description' => __( 'Use WPShadow before 6 AM', 'wpshadow' ),
			'emoji'       => '🌅',
			'tier'        => 'special',
		) );

		self::register( 'night_owl', array(
			'name'        => __( 'Night Owl', 'wpshadow' ),
			'description' => __( 'Use WPShadow after midnight', 'wpshadow' ),
			'emoji'       => '🦉',
			'tier'        => 'special',
		) );

		self::register( 'speed_runner', array(
			'name'        => __( 'Speed Runner', 'wpshadow' ),
			'description' => __( 'Complete 10 actions in under 5 minutes', 'wpshadow' ),
			'emoji'       => '⚡',
			'tier'        => 'special',
		) );

		// Expertise badges
		self::register( 'security_expert', array(
			'name'        => __( 'Security Expert', 'wpshadow' ),
			'description' => __( 'Pass all security diagnostics', 'wpshadow' ),
			'emoji'       => '🔐',
			'tier'        => 'expertise',
		) );

		self::register( 'performance_guru', array(
			'name'        => __( 'Performance Guru', 'wpshadow' ),
			'description' => __( 'Achieve 100% performance score', 'wpshadow' ),
			'emoji'       => '💨',
			'tier'        => 'expertise',
		) );

		self::register( 'seo_master', array(
			'name'        => __( 'SEO Master', 'wpshadow' ),
			'description' => __( 'Complete all SEO optimizations', 'wpshadow' ),
			'emoji'       => '📈',
			'tier'        => 'expertise',
		) );
	}

	/**
	 * Register a badge.
	 *
	 * @since 1.6093.1200
	 * @param  string $id    Badge ID.
	 * @param  array  $badge Badge data.
	 * @return void
	 */
	public static function register( $id, $badge ) {
		self::$badges[ $id ] = wp_parse_args(
			$badge,
			array(
				'name'        => '',
				'description' => '',
				'emoji'       => '🏅',
				'tier'        => 'standard',
				'requirement' => null,
			)
		);
	}

	/**
	 * Award a badge to a user.
	 *
	 * @since 1.6093.1200
	 * @param  int    $user_id User ID.
	 * @param  string $badge_id Badge ID.
	 * @return bool True if awarded.
	 */
	public static function award_badge( $user_id, $badge_id ) {
		if ( ! $user_id ) {
			return false;
		}

		// Check if already earned
		if ( self::has_badge( $user_id, $badge_id ) ) {
			return true;
		}

		$badge = self::get( $badge_id );

		if ( ! $badge ) {
			return false;
		}

		// Get user's badges
		$badges = get_user_meta( $user_id, 'wpshadow_badges', true );

		if ( ! is_array( $badges ) ) {
			$badges = array();
		}

		// Add badge
		$badges[ $badge_id ] = array(
			'earned_at' => current_time( 'mysql' ),
		);

		update_user_meta( $user_id, 'wpshadow_badges', $badges );

		// Trigger action
		do_action( 'wpshadow_badge_earned', $user_id, $badge_id, $badge );

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'badge_earned',
				sprintf(
					/* translators: %s: badge name */
					__( 'Badge earned: %s', 'wpshadow' ),
					$badge['name']
				),
				'',
				array( 'badge_id' => $badge_id )
			);
		}

		return true;
	}

	/**
	 * Check if user has a badge.
	 *
	 * @since 1.6093.1200
	 * @param  int    $user_id  User ID.
	 * @param  string $badge_id Badge ID.
	 * @return bool True if user has badge.
	 */
	public static function has_badge( $user_id, $badge_id ) {
		$badges = get_user_meta( $user_id, 'wpshadow_badges', true );

		return is_array( $badges ) && isset( $badges[ $badge_id ] );
	}

	/**
	 * Get badge definition.
	 *
	 * @since 1.6093.1200
	 * @param  string $badge_id Badge ID.
	 * @return array|null Badge data or null.
	 */
	public static function get( $badge_id ) {
		return self::$badges[ $badge_id ] ?? null;
	}

	/**
	 * Get user's earned badges.
	 *
	 * @since 1.6093.1200
	 * @param  int $user_id User ID.
	 * @return array Earned badges with details.
	 */
	public static function get_earned_badges( $user_id ) {
		$earned = get_user_meta( $user_id, 'wpshadow_badges', true );

		if ( ! is_array( $earned ) ) {
			return array();
		}

		$result = array();

		foreach ( $earned as $id => $data ) {
			$badge = self::get( $id );

			if ( $badge ) {
				$result[ $id ] = array_merge( $badge, $data );
			}
		}

		return $result;
	}

	/**
	 * Get recent badges for user.
	 *
	 * @since 1.6093.1200
	 * @param  int $user_id User ID.
	 * @param  int $limit   Number of badges to return.
	 * @return array Recent badges.
	 */
	public static function get_recent_badges( $user_id, $limit = 5 ) {
		$earned = self::get_earned_badges( $user_id );

		// Sort by earned_at descending
		uasort(
			$earned,
			function( $a, $b ) {
				return strtotime( $b['earned_at'] ) - strtotime( $a['earned_at'] );
			}
		);

		return array_slice( $earned, 0, $limit, true );
	}

	/**
	 * Get all badges.
	 *
	 * @since 1.6093.1200
	 * @return array All badge definitions.
	 */
	public static function get_all() {
		return self::$badges;
	}
}
