<?php
/**
 * Achievement Registry
 *
 * Manages achievement definitions and unlock tracking.
 * Phase 8: Gamification System - Achievement Management
 *
 * @package    WPShadow
 * @subpackage Gamification
 * @since      1.6004.0400
 */

declare(strict_types=1);

namespace WPShadow\Gamification;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Achievement Registry Class
 *
 * Central registry for all achievements and their unlock status.
 *
 * @since 1.6004.0400
 */
class Achievement_Registry {

	/**
	 * Achievement definitions.
	 *
	 * @var array
	 */
	private static $achievements = array();

	/**
	 * Initialize achievement registry.
	 *
	 * @since  1.6004.0400
	 * @return void
	 */
	public static function init() {
		self::register_achievements();
	}

	/**
	 * Register all achievements.
	 *
	 * @since  1.6004.0400
	 * @return void
	 */
	private static function register_achievements() {
		// Getting Started achievements
		self::register( 'first_diagnostic', array(
			'name'        => __( 'First Steps', 'wpshadow' ),
			'description' => __( 'Run your first diagnostic check', 'wpshadow' ),
			'category'    => 'getting_started',
			'emoji'       => '🎯',
			'points'      => 50,
			'badge_id'    => 'rookie',
		) );

		self::register( 'first_treatment', array(
			'name'        => __( 'Healing Touch', 'wpshadow' ),
			'description' => __( 'Apply your first treatment', 'wpshadow' ),
			'category'    => 'getting_started',
			'emoji'       => '💊',
			'points'      => 100,
			'badge_id'    => 'healer_novice',
		) );

		self::register( 'first_backup', array(
			'name'        => __( 'Safety First', 'wpshadow' ),
			'description' => __( 'Create your first backup', 'wpshadow' ),
			'category'    => 'getting_started',
			'emoji'       => '💾',
			'points'      => 75,
			'badge_id'    => 'protector',
		) );

		self::register( 'first_workflow', array(
			'name'        => __( 'Automation Beginner', 'wpshadow' ),
			'description' => __( 'Create your first workflow', 'wpshadow' ),
			'category'    => 'getting_started',
			'emoji'       => '⚡',
			'points'      => 150,
			'badge_id'    => 'automator',
		) );

		// Setup milestones
		self::register( 'guardian_enabled', array(
			'name'        => __( 'Guardian Ready', 'wpshadow' ),
			'description' => __( 'Enable Guardian monitoring', 'wpshadow' ),
			'category'    => 'guardian',
			'emoji'       => '🛡️',
			'points'      => 150,
		) );

		self::register( 'backup_enabled', array(
			'name'        => __( 'Backup Ready', 'wpshadow' ),
			'description' => __( 'Enable backups for safe changes', 'wpshadow' ),
			'category'    => 'site_health',
			'emoji'       => '💾',
			'points'      => 100,
		) );

		self::register( 'backup_scheduled', array(
			'name'        => __( 'Scheduled Safety', 'wpshadow' ),
			'description' => __( 'Schedule automated backups', 'wpshadow' ),
			'category'    => 'site_health',
			'emoji'       => '🗓️',
			'points'      => 75,
		) );

		self::register( 'cloud_connected', array(
			'name'        => __( 'Cloud Connected', 'wpshadow' ),
			'description' => __( 'Connect to WPShadow Cloud services', 'wpshadow' ),
			'category'    => 'guardian',
			'emoji'       => '☁️',
			'points'      => 150,
		) );

		// Site Health achievements
		self::register( 'clean_health', array(
			'name'        => __( 'Spotless', 'wpshadow' ),
			'description' => __( 'Achieve 100% site health score', 'wpshadow' ),
			'category'    => 'site_health',
			'emoji'       => '✨',
			'points'      => 500,
			'badge_id'    => 'perfectionist',
		) );

		self::register( 'performance_pro', array(
			'name'        => __( 'Speed Demon', 'wpshadow' ),
			'description' => __( 'Achieve A+ performance grade', 'wpshadow' ),
			'category'    => 'site_health',
			'emoji'       => '🚀',
			'points'      => 300,
			'badge_id'    => 'performance_expert',
		) );

		self::register( 'security_fortress', array(
			'name'        => __( 'Fort Knox', 'wpshadow' ),
			'description' => __( 'Pass all security checks', 'wpshadow' ),
			'category'    => 'site_health',
			'emoji'       => '🔒',
			'points'      => 400,
			'badge_id'    => 'security_master',
		) );

		self::register( 'optimization_master', array(
			'name'        => __( 'Fully Optimized', 'wpshadow' ),
			'description' => __( 'Complete all optimization recommendations', 'wpshadow' ),
			'category'    => 'site_health',
			'emoji'       => '⚙️',
			'points'      => 350,
			'badge_id'    => 'optimizer',
		) );

		// Learning achievements
		self::register( 'knowledge_seeker', array(
			'name'        => __( 'Knowledge Seeker', 'wpshadow' ),
			'description' => __( 'Read your first KB article', 'wpshadow' ),
			'category'    => 'learning',
			'emoji'       => '📚',
			'points'      => 25,
			'badge_id'    => 'reader',
		) );

		self::register( 'avid_learner', array(
			'name'        => __( 'Avid Learner', 'wpshadow' ),
			'description' => __( 'Read 10 KB articles', 'wpshadow' ),
			'category'    => 'learning',
			'emoji'       => '📖',
			'points'      => 100,
			'badge_id'    => 'scholar',
		) );

		self::register( 'video_viewer', array(
			'name'        => __( 'Video Viewer', 'wpshadow' ),
			'description' => __( 'Complete your first training video', 'wpshadow' ),
			'category'    => 'learning',
			'emoji'       => '🎥',
			'points'      => 50,
			'badge_id'    => 'student',
		) );

		self::register( 'academy_graduate', array(
			'name'        => __( 'Academy Graduate', 'wpshadow' ),
			'description' => __( 'Complete 20 training videos', 'wpshadow' ),
			'category'    => 'learning',
			'emoji'       => '🎓',
			'points'      => 500,
			'badge_id'    => 'graduate',
		) );

		// Engagement achievements
		self::register( 'weekly_warrior', array(
			'name'        => __( 'Weekly Warrior', 'wpshadow' ),
			'description' => __( 'Use WPShadow 7 days in a row', 'wpshadow' ),
			'category'    => 'engagement',
			'emoji'       => '📅',
			'points'      => 200,
			'badge_id'    => 'consistent',
		) );

		self::register( 'monthly_champion', array(
			'name'        => __( 'Monthly Champion', 'wpshadow' ),
			'description' => __( 'Use WPShadow 30 days in a row', 'wpshadow' ),
			'category'    => 'engagement',
			'emoji'       => '🏆',
			'points'      => 750,
			'badge_id'    => 'dedicated',
		) );

		self::register( 'community_contributor', array(
			'name'        => __( 'Community Hero', 'wpshadow' ),
			'description' => __( 'Help others in the WPShadow community', 'wpshadow' ),
			'category'    => 'engagement',
			'emoji'       => '🤝',
			'points'      => 300,
			'badge_id'    => 'helper',
		) );

		self::register( 'community_reviewer', array(
			'name'        => __( 'Helpful Reviewer', 'wpshadow' ),
			'description' => __( 'Leave a WordPress.org review', 'wpshadow' ),
			'category'    => 'engagement',
			'emoji'       => '⭐',
			'points'      => 200,
		) );

		self::register( 'social_supporter', array(
			'name'        => __( 'Social Supporter', 'wpshadow' ),
			'description' => __( 'Share WPShadow with your community', 'wpshadow' ),
			'category'    => 'engagement',
			'emoji'       => '📣',
			'points'      => 150,
		) );

		self::register( 'referral_champion', array(
			'name'        => __( 'Ambassador', 'wpshadow' ),
			'description' => __( 'Refer 5 users to WPShadow', 'wpshadow' ),
			'category'    => 'engagement',
			'emoji'       => '🌟',
			'points'      => 500,
			'badge_id'    => 'ambassador',
		) );

		// Advanced achievements
		self::register( 'workflow_wizard', array(
			'name'        => __( 'Workflow Wizard', 'wpshadow' ),
			'description' => __( 'Create 10 custom workflows', 'wpshadow' ),
			'category'    => 'advanced',
			'emoji'       => '🧙',
			'points'      => 600,
			'badge_id'    => 'wizard',
		) );

		self::register( 'multi_site_master', array(
			'name'        => __( 'Multi-Site Master', 'wpshadow' ),
			'description' => __( 'Manage 5+ sites with WPShadow', 'wpshadow' ),
			'category'    => 'advanced',
			'emoji'       => '🌐',
			'points'      => 800,
			'badge_id'    => 'enterprise',
		) );

		self::register( 'zero_downtime', array(
			'name'        => __( 'Always Online', 'wpshadow' ),
			'description' => __( 'Maintain 99.9% uptime for 90 days', 'wpshadow' ),
			'category'    => 'advanced',
			'emoji'       => '⏰',
			'points'      => 1000,
			'badge_id'    => 'reliable',
		) );

		self::register( 'agency_expert', array(
			'name'        => __( 'Agency Expert', 'wpshadow' ),
			'description' => __( 'Manage 20+ client sites', 'wpshadow' ),
			'category'    => 'advanced',
			'emoji'       => '💼',
			'points'      => 1500,
			'badge_id'    => 'agency_master',
		) );

		// Guardian-specific achievements
		self::register( 'guardian_initiate', array(
			'name'        => __( 'Guardian Initiate', 'wpshadow' ),
			'description' => __( 'Complete your first Guardian scan', 'wpshadow' ),
			'category'    => 'guardian',
			'emoji'       => '🛡️',
			'points'      => 100,
			'badge_id'    => 'guardian_rookie',
		) );

		self::register( 'guardian_adept', array(
			'name'        => __( 'Guardian Adept', 'wpshadow' ),
			'description' => __( 'Complete 10 Guardian scans', 'wpshadow' ),
			'category'    => 'guardian',
			'emoji'       => '🗡️',
			'points'      => 300,
			'badge_id'    => 'guardian_adept',
		) );

		self::register( 'guardian_champion', array(
			'name'        => __( 'Guardian Champion', 'wpshadow' ),
			'description' => __( 'Complete 50 Guardian scans', 'wpshadow' ),
			'category'    => 'guardian',
			'emoji'       => '⚔️',
			'points'      => 1000,
			'badge_id'    => 'guardian_champion',
		) );
	}

	/**
	 * Register an achievement.
	 *
	 * @since  1.6004.0400
	 * @param  string $id          Achievement ID.
	 * @param  array  $achievement Achievement data.
	 * @return void
	 */
	public static function register( $id, $achievement ) {
		self::$achievements[ $id ] = wp_parse_args(
			$achievement,
			array(
				'name'        => '',
				'description' => '',
				'category'    => 'general',
				'emoji'       => '🏅',
				'points'      => 0,
				'badge_id'    => null,
				'hidden'      => false,
			)
		);
	}

	/**
	 * Get all registered achievements.
	 *
	 * @since  1.6004.0400
	 * @param  string $category Optional. Filter by category.
	 * @return array Achievements.
	 */
	public static function get_all( $category = '' ) {
		if ( empty( $category ) ) {
			return self::$achievements;
		}

		return array_filter(
			self::$achievements,
			function( $achievement ) use ( $category ) {
				return $achievement['category'] === $category;
			}
		);
	}

	/**
	 * Get achievement definition.
	 *
	 * @since  1.6004.0400
	 * @param  string $id Achievement ID.
	 * @return array|null Achievement data or null.
	 */
	public static function get( $id ) {
		return self::$achievements[ $id ] ?? null;
	}

	/**
	 * Unlock an achievement for a user.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id       User ID.
	 * @param  string $achievement_id Achievement ID.
	 * @return bool True if unlocked (newly or already), false on failure.
	 */
	public static function unlock( $user_id, $achievement_id ) {
		if ( ! $user_id ) {
			return false;
		}

		// Check if already unlocked
		if ( self::is_unlocked( $user_id, $achievement_id ) ) {
			return true;
		}

		$achievement = self::get( $achievement_id );

		if ( ! $achievement ) {
			return false;
		}

		// Get user's unlocked achievements
		$unlocked = get_user_meta( $user_id, 'wpshadow_achievements', true );

		if ( ! is_array( $unlocked ) ) {
			$unlocked = array();
		}

		// Add achievement with timestamp
		$unlocked[ $achievement_id ] = array(
			'unlocked_at' => current_time( 'mysql' ),
			'points'      => $achievement['points'],
		);

		update_user_meta( $user_id, 'wpshadow_achievements', $unlocked );

		// Award points if specified
		if ( $achievement['points'] > 0 ) {
			Points_System::award_points(
				$user_id,
				$achievement['points'],
				'achievement_unlocked',
				array( 'achievement' => $achievement_id )
			);
		}

		// Award badge if specified
		if ( ! empty( $achievement['badge_id'] ) ) {
			Badge_System::award_badge( $user_id, $achievement['badge_id'] );
		}

		// Trigger action
		do_action( 'wpshadow_achievement_unlocked', $user_id, $achievement_id, $achievement );

		// Log activity
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'achievement_unlocked',
				sprintf(
					/* translators: %s: achievement name */
					__( 'Achievement unlocked: %s', 'wpshadow' ),
					$achievement['name']
				),
				'',
				array( 'achievement_id' => $achievement_id )
			);
		}

		// Show notification
		self::show_notification( $user_id, $achievement_id );

		return true;
	}

	/**
	 * Check if user has unlocked an achievement.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id       User ID.
	 * @param  string $achievement_id Achievement ID.
	 * @return bool True if unlocked.
	 */
	public static function is_unlocked( $user_id, $achievement_id ) {
		$unlocked = get_user_meta( $user_id, 'wpshadow_achievements', true );

		return is_array( $unlocked ) && isset( $unlocked[ $achievement_id ] );
	}

	/**
	 * Get user's unlocked achievements.
	 *
	 * @since  1.6004.0400
	 * @param  int $user_id User ID.
	 * @return array Unlocked achievements.
	 */
	public static function get_unlocked( $user_id ) {
		$unlocked = get_user_meta( $user_id, 'wpshadow_achievements', true );

		if ( ! is_array( $unlocked ) ) {
			return array();
		}

		// Add achievement details
		$result = array();

		foreach ( $unlocked as $id => $data ) {
			$achievement = self::get( $id );

			if ( $achievement ) {
				$result[ $id ] = array_merge( $achievement, $data );
			}
		}

		return $result;
	}

	/**
	 * Get user's locked achievements.
	 *
	 * @since  1.6004.0400
	 * @param  int $user_id User ID.
	 * @return array Locked achievements.
	 */
	public static function get_locked( $user_id ) {
		$unlocked = self::get_unlocked( $user_id );
		$all      = self::get_all();

		return array_diff_key( $all, $unlocked );
	}

	/**
	 * Get achievement progress.
	 *
	 * @since  1.6004.0400
	 * @param  int $user_id User ID.
	 * @return array Progress statistics.
	 */
	public static function get_progress( $user_id ) {
		$unlocked    = count( self::get_unlocked( $user_id ) );
		$total       = count( self::$achievements );
		$by_category = array();

		foreach ( self::$achievements as $id => $achievement ) {
			$category = $achievement['category'];

			if ( ! isset( $by_category[ $category ] ) ) {
				$by_category[ $category ] = array(
					'total'    => 0,
					'unlocked' => 0,
				);
			}

			$by_category[ $category ]['total']++;

			if ( self::is_unlocked( $user_id, $id ) ) {
				$by_category[ $category ]['unlocked']++;
			}
		}

		return array(
			'total'       => $total,
			'unlocked'    => $unlocked,
			'locked'      => $total - $unlocked,
			'percentage'  => $total > 0 ? round( ( $unlocked / $total ) * 100 ) : 0,
			'by_category' => $by_category,
		);
	}

	/**
	 * Show achievement unlocked notification.
	 *
	 * @since  1.6004.0400
	 * @param  int    $user_id       User ID.
	 * @param  string $achievement_id Achievement ID.
	 * @return void
	 */
	private static function show_notification( $user_id, $achievement_id ) {
		$achievement = self::get( $achievement_id );

		if ( ! $achievement ) {
			return;
		}

		// Store notification for display
		$notifications = get_user_meta( $user_id, 'wpshadow_pending_notifications', true );

		if ( ! is_array( $notifications ) ) {
			$notifications = array();
		}

		$notifications[] = array(
			'type'    => 'achievement',
			'id'      => $achievement_id,
			'title'   => $achievement['name'],
			'message' => $achievement['description'],
			'emoji'   => $achievement['emoji'],
			'points'  => $achievement['points'],
			'time'    => current_time( 'timestamp' ),
		);

		update_user_meta( $user_id, 'wpshadow_pending_notifications', $notifications );
	}

	/**
	 * Get achievement categories.
	 *
	 * @since  1.6004.0400
	 * @return array Categories with labels.
	 */
	public static function get_categories() {
		return array(
			'getting_started' => __( 'Getting Started', 'wpshadow' ),
			'site_health'     => __( 'Site Health', 'wpshadow' ),
			'learning'        => __( 'Learning', 'wpshadow' ),
			'engagement'      => __( 'Engagement', 'wpshadow' ),
			'advanced'        => __( 'Advanced', 'wpshadow' ),
			'guardian'        => __( 'Guardian', 'wpshadow' ),
		);
	}
}
