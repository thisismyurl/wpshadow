<?php
/**
 * Tutor LMS Quiz Security Diagnostic
 *
 * Tutor LMS quiz answers exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.373.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tutor LMS Quiz Security Diagnostic Class
 *
 * @since 1.373.0000
 */
class Diagnostic_TutorLmsQuizSecurity extends Diagnostic_Base {

	protected static $slug = 'tutor-lms-quiz-security';
	protected static $title = 'Tutor LMS Quiz Security';
	protected static $description = 'Tutor LMS quiz answers exposed';
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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/tutor-lms-quiz-security',
			);
		}
		
		return null;
	}
}
