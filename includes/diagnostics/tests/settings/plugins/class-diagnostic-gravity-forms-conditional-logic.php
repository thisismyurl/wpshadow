<?php
/**
 * Gravity Forms Conditional Logic Diagnostic
 *
 * Gravity Forms conditional logic too complex.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.257.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Conditional Logic Diagnostic Class
 *
 * @since 1.257.0000
 */
class Diagnostic_GravityFormsConditionalLogic extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-conditional-logic';
	protected static $title = 'Gravity Forms Conditional Logic';
	protected static $description = 'Gravity Forms conditional logic too complex';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
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
				'severity'    => self::calculate_severity( 35 ),
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-conditional-logic',
			);
		}
		
		return null;
	}
}
