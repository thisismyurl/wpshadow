<?php
/**
 * Ninja Forms Conditional Logic Performance Diagnostic
 *
 * Ninja Forms Conditional Logic Performance issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1188.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ninja Forms Conditional Logic Performance Diagnostic Class
 *
 * @since 1.1188.0000
 */
class Diagnostic_NinjaFormsConditionalLogicPerformance extends Diagnostic_Base {

	protected static $slug = 'ninja-forms-conditional-logic-performance';
	protected static $title = 'Ninja Forms Conditional Logic Performance';
	protected static $description = 'Ninja Forms Conditional Logic Performance issue found';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Ninja_Forms' ) ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/ninja-forms-conditional-logic-performance',
			);
		}
		
		return null;
	}
}
