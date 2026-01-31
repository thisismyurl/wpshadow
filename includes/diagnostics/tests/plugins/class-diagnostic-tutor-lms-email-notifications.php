<?php
/**
 * Tutor LMS Email Notifications Diagnostic
 *
 * Tutor LMS email settings misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.375.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tutor LMS Email Notifications Diagnostic Class
 *
 * @since 1.375.0000
 */
class Diagnostic_TutorLmsEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'tutor-lms-email-notifications';
	protected static $title = 'Tutor LMS Email Notifications';
	protected static $description = 'Tutor LMS email settings misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'TUTOR_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get email settings
		$email_settings = get_option( 'tutor_option', array() );

		// Check if emails are enabled
		$emails_enabled = isset( $email_settings['email_disable'] ) ? ! $email_settings['email_disable'] : true;
		if ( ! $emails_enabled ) {
			$issues[] = 'emails_disabled';
			$threat_level += 20;
			return $this->build_finding( $issues, $threat_level );
		}

		// Check from email
		$from_email = isset( $email_settings['email_from_address'] ) ? $email_settings['email_from_address'] : '';
		if ( empty( $from_email ) ) {
			$issues[] = 'no_from_email';
			$threat_level += 10;
		}

		// Check enrollment notification
		$enroll_notification = isset( $email_settings['email_to_students']['course_enroll'] ) ? $email_settings['email_to_students']['course_enroll'] : false;
		if ( ! $enroll_notification ) {
			$issues[] = 'enrollment_emails_disabled';
			$threat_level += 15;
		}

		// Check completion notification
		$completion_notification = isset( $email_settings['email_to_students']['course_complete'] ) ? $email_settings['email_to_students']['course_complete'] : false;
		if ( ! $completion_notification ) {
			$issues[] = 'completion_emails_disabled';
			$threat_level += 15;
		}

		// Check instructor notifications
		$instructor_new_enrollment = isset( $email_settings['email_to_teachers']['a_student_enrolled'] ) ? $email_settings['email_to_teachers']['a_student_enrolled'] : false;
		if ( ! $instructor_new_enrollment ) {
			$issues[] = 'instructor_notifications_disabled';
			$threat_level += 10;
		}

		// Check quiz notification
		$quiz_notification = isset( $email_settings['email_to_students']['quiz_completed'] ) ? $email_settings['email_to_students']['quiz_completed'] : false;
		if ( ! $quiz_notification ) {
			$issues[] = 'quiz_emails_disabled';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of email notification issues */
				__( 'Tutor LMS email notifications are not properly configured: %s. This prevents students and instructors from receiving important course updates.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/tutor-lms-email-notifications',
			);
		}
		
		return null;
	}
}
