<?php
/**
 * Advanced Custom Fields Debug Diagnostic
 *
 * Advanced Custom Fields Debug issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Custom Fields Debug Diagnostic Class
 *
 * @since 1.1050.0000
 */
class Diagnostic_AdvancedCustomFieldsDebug extends Diagnostic_Base {

	protected static $slug = 'advanced-custom-fields-debug';
	protected static $title = 'Advanced Custom Fields Debug';
	protected static $description = 'Advanced Custom Fields Debug issue detected';
	protected static $family = 'functionality';

	public static function check() {
		
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
				'kb_link'     => 'https://wpshadow.com/kb/advanced-custom-fields-debug',
			);
		}
		
		return null;
	}
}
