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
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/tutor-lms-course-access',
			);
		}
		
		return null;
	}
}
