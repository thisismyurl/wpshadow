<?php
/**
 * Formidable Forms Api Hooks Diagnostic
 *
 * Formidable Forms Api Hooks issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1196.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms Api Hooks Diagnostic Class
 *
 * @since 1.1196.0000
 */
class Diagnostic_FormidableFormsApiHooks extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-api-hooks';
	protected static $title = 'Formidable Forms Api Hooks';
	protected static $description = 'Formidable Forms Api Hooks issue found';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-api-hooks',
			);
		}
		
		return null;
	}
}
