<?php
/**
 * Caldera Forms Conditional Logic Diagnostic
 *
 * Caldera Forms logic slowing forms.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.474.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms Conditional Logic Diagnostic Class
 *
 * @since 1.474.0000
 */
class Diagnostic_CalderaFormsConditionalLogic extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-conditional-logic';
	protected static $title = 'Caldera Forms Conditional Logic';
	protected static $description = 'Caldera Forms logic slowing forms';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-conditional-logic',
			);
		}
		
		return null;
	}
}
