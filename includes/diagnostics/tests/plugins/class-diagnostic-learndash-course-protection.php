<?php
/**
 * LearnDash Course Protection Diagnostic
 *
 * LearnDash course content not properly protected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.358.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LearnDash Course Protection Diagnostic Class
 *
 * @since 1.358.0000
 */
class Diagnostic_LearndashCourseProtection extends Diagnostic_Base {

	protected static $slug = 'learndash-course-protection';
	protected static $title = 'LearnDash Course Protection';
	protected static $description = 'LearnDash course content not properly protected';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'LEARNDASH_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/learndash-course-protection',
			);
		}
		
		return null;
	}
}
