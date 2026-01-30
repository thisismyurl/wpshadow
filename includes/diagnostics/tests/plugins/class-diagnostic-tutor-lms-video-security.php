<?php
/**
 * Tutor LMS Video Security Diagnostic
 *
 * Tutor LMS video content not protected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.377.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tutor LMS Video Security Diagnostic Class
 *
 * @since 1.377.0000
 */
class Diagnostic_TutorLmsVideoSecurity extends Diagnostic_Base {

	protected static $slug = 'tutor-lms-video-security';
	protected static $title = 'Tutor LMS Video Security';
	protected static $description = 'Tutor LMS video content not protected';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'TUTOR_VERSION' ) ) {
			return null;
		}
		
		// Check if Tutor LMS is active
		if ( ! defined( 'TUTOR_VERSION' ) && ! function_exists( 'tutor' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		global $wpdb;

		// Check video lessons
		$video_lessons = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				 WHERE p.post_type = %s
				 AND pm.meta_key = %s",
				'lesson',
				'_video'
			)
		);

		if ( $video_lessons > 0 ) {
			// Check video source protection
			$video_protection = get_option( 'tutor_video_source_protection', 'no' );
			if ( $video_protection === 'no' ) {
				$issues[] = 'video_source_protection_disabled';
				$threat_level += 30;
			}

			// Check hotlink protection
			$hotlink_protection = get_option( 'tutor_hotlink_protection', 'no' );
			if ( $hotlink_protection === 'no' ) {
				$issues[] = 'hotlink_protection_disabled';
				$threat_level += 25;
			}
		}

		// Check enrollment requirement
		$enrollment_required = get_option( 'tutor_video_enrollment_required', 'yes' );
		if ( $enrollment_required === 'no' ) {
			$issues[] = 'enrollment_not_required';
			$threat_level += 35;
		}

		// Check lesson completion tracking
		$completion_tracking = get_option( 'tutor_lesson_completion_required', 'yes' );
		if ( $completion_tracking === 'no' ) {
			$issues[] = 'completion_tracking_disabled';
			$threat_level += 15;
		}

		// Check for publicly accessible videos
		$public_videos = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} p
				 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
				 WHERE p.post_type = %s
				 AND p.post_status = %s
				 AND pm.meta_key = %s",
				'lesson',
				'publish',
				'_video'
			)
		);
		if ( $public_videos > 5 ) {
			$issues[] = 'public_video_lessons_found';
			$threat_level += 20;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of security issues */
				__( 'Tutor LMS video content is not properly protected: %s. This allows unauthorized access to paid content.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/tutor-lms-video-security',
			);
		}
		
		return null;
	}
}
