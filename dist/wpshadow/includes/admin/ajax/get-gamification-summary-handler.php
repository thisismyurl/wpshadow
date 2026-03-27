<?php
declare( strict_types=1 );

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;
use WPShadow\Gamification\Achievement_System;
use WPShadow\Gamification\Badge_Manager;
use WPShadow\Gamification\Leaderboard_Manager;
use WPShadow\Gamification\Milestone_Notifier;
use WPShadow\Gamification\Streak_Tracker;

/**
 * AJAX Handler: Get gamification summary
 *
 * Action: wp_ajax_wpshadow_get_gamification_summary
 * Nonce: wpshadow_dashboard_nonce
 * Capability: read
 *
 * @package WPShadow
 */
class Get_Gamification_Summary_Handler extends AJAX_Handler_Base {

	/**
	 * Register AJAX hook
	 */
	public static function register(): void {
		add_action( 'wp_ajax_wpshadow_get_gamification_summary', array( __CLASS__, 'handle' ) );
	}

	/**
	 * Handle summary request
	 */
	public static function handle(): void {
		self::verify_request( 'wpshadow_dashboard_nonce', 'read' );

		$user_id = get_current_user_id();

		$achievements  = Achievement_System::get_user_achievements( $user_id );
		$points        = Achievement_System::get_user_points( $user_id );
		$progress      = Achievement_System::get_user_progress( $user_id );
		$streaks       = Streak_Tracker::get_streaks( $user_id );
		$badges        = Badge_Manager::get_user_badges( $user_id );
		$notifications = Milestone_Notifier::get_notifications( $user_id, 10 );
		$rank          = Leaderboard_Manager::get_user_rank( $user_id );
		$leaderboard   = Leaderboard_Manager::get_top_achievers( 5 );

		self::send_success(
			array(
				'achievements'  => $achievements,
				'points'        => $points,
				'progress'      => $progress,
				'streaks'       => $streaks,
				'badges'        => $badges,
				'notifications' => $notifications,
				'rank'          => $rank,
				'leaderboard'   => $leaderboard,
			)
		);
	}
}
