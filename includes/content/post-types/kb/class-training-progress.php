<?php
/**
 * Training Progress Tracker
 *
 * Tracks user progress through training courses.
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\KnowledgeBase;

use WPShadow\Core\User_Preferences_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Training Progress Tracker
 */
class Training_Progress {
	/**
	 * Meta key for training progress.
	 *
	 * @var string
	 */
	private static $meta_key = 'wpshadow_training_progress';

	/**
	 * Mark a topic as completed.
	 *
	 * @param int    $user_id User ID.
	 * @param string $topic_id Topic ID.
	 * @return bool Success.
	 */
	public static function mark_topic_complete( $user_id, $topic_id ) {
		$progress = self::get_progress( $user_id );

		if ( ! isset( $progress['topics'] ) ) {
			$progress['topics'] = array();
		}

		$progress['topics'][ $topic_id ] = array(
			'completed_at' => current_time( 'mysql' ),
			'completed'    => true,
		);

		return update_user_meta( (int) $user_id, self::$meta_key, $progress );
	}

	/**
	 * Mark a course as completed.
	 *
	 * @param int    $user_id User ID.
	 * @param string $course_id Course ID.
	 * @return bool Success.
	 */
	public static function mark_course_complete( $user_id, $course_id ) {
		$progress = self::get_progress( $user_id );

		if ( ! isset( $progress['courses'] ) ) {
			$progress['courses'] = array();
		}

		$progress['courses'][ $course_id ] = array(
			'completed_at' => current_time( 'mysql' ),
			'completed'    => true,
			'certificate'  => true,
		);

		return update_user_meta( (int) $user_id, self::$meta_key, $progress );
	}

	/**
	 * Get user's training progress.
	 *
	 * @param int $user_id User ID.
	 * @return array Progress data.
	 */
	public static function get_progress( $user_id ) {
		return (array) get_user_meta( (int) $user_id, self::$meta_key, true );
	}

	/**
	 * Get completed topics.
	 *
	 * @param int $user_id User ID.
	 * @return array Completed topic IDs.
	 */
	public static function get_completed_topics( $user_id ) {
		$progress = self::get_progress( $user_id );
		return isset( $progress['topics'] ) ? $progress['topics'] : array();
	}

	/**
	 * Get completed courses.
	 *
	 * @param int $user_id User ID.
	 * @return array Completed course IDs.
	 */
	public static function get_completed_courses( $user_id ) {
		$progress = self::get_progress( $user_id );
		return isset( $progress['courses'] ) ? $progress['courses'] : array();
	}

	/**
	 * Get progress percentage for a course.
	 *
	 * @param int    $user_id User ID.
	 * @param string $course_id Course ID.
	 * @return int Percentage 0-100.
	 */
	public static function get_course_progress_percent( $user_id, $course_id ) {
		$course = Training_Provider::get_course( $course_id );

		if ( ! $course ) {
			return 0;
		}

		$topics = isset( $course['topics'] ) ? $course['topics'] : array();

		if ( empty( $topics ) ) {
			return 0;
		}

		$completed       = self::get_completed_topics( $user_id );
		$completed_count = 0;

		foreach ( $topics as $topic_id ) {
			if ( isset( $completed[ $topic_id ] ) && $completed[ $topic_id ]['completed'] ) {
				++$completed_count;
			}
		}

		return (int) round( ( $completed_count / count( $topics ) ) * 100 );
	}

	/**
	 * Get total training progress across all courses.
	 *
	 * @param int $user_id User ID.
	 * @return array Total progress stats.
	 */
	public static function get_total_progress( $user_id ) {
		$courses           = Training_Provider::get_courses();
		$total_topics      = 0;
		$completed_topics  = 0;
		$completed_courses = self::get_completed_courses( $user_id );

		foreach ( $courses as $course_id => $course ) {
			$topics        = isset( $course['topics'] ) ? $course['topics'] : array();
			$total_topics += count( $topics );
		}

		$completed_data   = self::get_completed_topics( $user_id );
		$completed_topics = count(
			array_filter(
				$completed_data,
				function ( $t ) {
					return isset( $t['completed'] ) && $t['completed'];
				}
			)
		);

		return array(
			'total_courses'     => count( $courses ),
			'completed_courses' => count( $completed_courses ),
			'total_topics'      => $total_topics,
			'completed_topics'  => $completed_topics,
			'percent_complete'  => $total_topics > 0 ? round( ( $completed_topics / $total_topics ) * 100 ) : 0,
		);
	}

	/**
	 * Check if user is a training "champion" (high progress).
	 *
	 * @param int $user_id User ID.
	 * @return bool True if 75%+ progress.
	 */
	public static function is_training_champion( $user_id ) {
		$progress = self::get_total_progress( $user_id );
		return $progress['percent_complete'] >= 75;
	}

	/**
	 * Award a badge to user.
	 *
	 * @param int    $user_id User ID.
	 * @param string $badge_id Badge ID.
	 * @return bool Success.
	 */
	public static function award_badge( $user_id, $badge_id ) {
		$progress = self::get_progress( $user_id );

		if ( ! isset( $progress['badges'] ) ) {
			$progress['badges'] = array();
		}

		$progress['badges'][ $badge_id ] = array(
			'awarded_at' => current_time( 'mysql' ),
			'badge_id'   => $badge_id,
		);

		return update_user_meta( (int) $user_id, self::$meta_key, $progress );
	}

	/**
	 * Get user's earned badges.
	 *
	 * @param int $user_id User ID.
	 * @return array Badge IDs.
	 */
	public static function get_badges( $user_id ) {
		$progress = self::get_progress( $user_id );
		return isset( $progress['badges'] ) ? $progress['badges'] : array();
	}
}
