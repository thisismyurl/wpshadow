<?php
/**
 * Formidable Forms API Security Diagnostic
 *
 * Formidable Forms API endpoints exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.263.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms API Security Diagnostic Class
 *
 * @since 1.263.0000
 */
class Diagnostic_FormidableFormsApiSecurity extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-api-security';
	protected static $title = 'Formidable Forms API Security';
	protected static $description = 'Formidable Forms API endpoints exposed';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'FrmAppHelper' ) ) {
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-api-security',
			);
		}
		
		return null;
	}
}
