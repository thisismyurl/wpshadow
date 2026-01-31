<?php
/**
 * Tutor LMS Course Access Diagnostic
 *
 * Tutor LMS course access bypassable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.372.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tutor LMS Course Access Diagnostic Class
 *
 * @since 1.372.0000
 */
class Diagnostic_TutorLmsCourseAccess extends Diagnostic_Base {

	protected static $slug = 'tutor-lms-course-access';
	protected static $title = 'Tutor LMS Course Access';
	protected static $description = 'Tutor LMS course access bypassable';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'TUTOR_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Course access control
		$access = get_option( 'tutor_course_access_control_enabled', 0 );
		if ( ! $access ) {
			$issues[] = 'Course access control not enabled';
		}

		// Check 2: Enrollment verification
		$enrollment = get_option( 'tutor_enrollment_verification_enabled', 0 );
		if ( ! $enrollment ) {
			$issues[] = 'Enrollment verification not enabled';
		}

		// Check 3: Capability checking
		$capability = get_option( 'tutor_capability_checking_enabled', 0 );
		if ( ! $capability ) {
			$issues[] = 'Capability checking not enabled';
		}

		// Check 4: Nonce verification
		$nonce = get_option( 'tutor_nonce_verification_enabled', 0 );
		if ( ! $nonce ) {
			$issues[] = 'Nonce verification not enabled';
		}

		// Check 5: Direct access prevention
		$direct = get_option( 'tutor_direct_access_prevention_enabled', 0 );
		if ( ! $direct ) {
			$issues[] = 'Direct access prevention not enabled';
		}

		// Check 6: Authentication checks
		$auth = get_option( 'tutor_auth_checks_on_course_access_enabled', 0 );
		if ( ! $auth ) {
			$issues[] = 'Authentication checks not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d course access security issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/tutor-lms-course-access',
			);
		}

		return null;
	}
}
