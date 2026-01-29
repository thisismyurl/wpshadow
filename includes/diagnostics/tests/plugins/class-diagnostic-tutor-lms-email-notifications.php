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
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/tutor-lms-email-notifications',
			);
		}
		
		return null;
	}
}
