<?php
/**
 * Tutor LMS Database Performance Diagnostic
 *
 * Tutor LMS queries not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.378.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tutor LMS Database Performance Diagnostic Class
 *
 * @since 1.378.0000
 */
class Diagnostic_TutorLmsDatabasePerformance extends Diagnostic_Base {

	protected static $slug = 'tutor-lms-database-performance';
	protected static $title = 'Tutor LMS Database Performance';
	protected static $description = 'Tutor LMS queries not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'TUTOR_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Database query optimization
		$query_opt = get_option( 'tutor_query_optimization_enabled', 0 );
		if ( ! $query_opt ) {
			$issues[] = 'Database query optimization not enabled';
		}
		
		// Check 2: Caching enabled
		$cache = get_option( 'tutor_lms_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'LMS caching not enabled';
		}
		
		// Check 3: Index optimization
		$index = get_option( 'tutor_db_index_optimization', 0 );
		if ( ! $index ) {
			$issues[] = 'Database index optimization not configured';
		}
		
		// Check 4: Course query optimization
		$course_opt = get_option( 'tutor_course_query_optimization', 0 );
		if ( ! $course_opt ) {
			$issues[] = 'Course query optimization not enabled';
		}
		
		// Check 5: Lesson query optimization
		$lesson_opt = get_option( 'tutor_lesson_query_optimization', 0 );
		if ( ! $lesson_opt ) {
			$issues[] = 'Lesson query optimization not enabled';
		}
		
		// Check 6: Analytics optimization
		$analytics = get_option( 'tutor_analytics_db_optimization', 0 );
		if ( ! $analytics ) {
			$issues[] = 'Analytics database optimization not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Tutor LMS DB performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/tutor-lms-database-performance',
			);
		}
		
		return null;
	}
}
