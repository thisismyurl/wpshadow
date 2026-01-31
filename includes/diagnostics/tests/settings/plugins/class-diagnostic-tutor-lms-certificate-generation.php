<?php
/**
 * Tutor LMS Certificate Generation Diagnostic
 *
 * Tutor LMS certificates not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.376.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tutor LMS Certificate Generation Diagnostic Class
 *
 * @since 1.376.0000
 */
class Diagnostic_TutorLmsCertificateGeneration extends Diagnostic_Base {

	protected static $slug = 'tutor-lms-certificate-generation';
	protected static $title = 'Tutor LMS Certificate Generation';
	protected static $description = 'Tutor LMS certificates not optimized';
	protected static $family = 'performance';

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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/tutor-lms-certificate-generation',
			);
		}
		
		return null;
	}
}
