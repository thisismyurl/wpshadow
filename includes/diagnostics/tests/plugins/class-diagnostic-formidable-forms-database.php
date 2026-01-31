<?php
/**
 * Formidable Forms Database Diagnostic
 *
 * Formidable Forms database not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.262.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formidable Forms Database Diagnostic Class
 *
 * @since 1.262.0000
 */
class Diagnostic_FormidableFormsDatabase extends Diagnostic_Base {

	protected static $slug = 'formidable-forms-database';
	protected static $title = 'Formidable Forms Database';
	protected static $description = 'Formidable Forms database not optimized';
	protected static $family = 'performance';

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
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/formidable-forms-database',
			);
		}
		
		return null;
	}
}
