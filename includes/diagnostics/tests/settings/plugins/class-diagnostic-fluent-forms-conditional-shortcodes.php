<?php
/**
 * Fluent Forms Conditional Shortcodes Diagnostic
 *
 * Fluent Forms Conditional Shortcodes issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1203.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fluent Forms Conditional Shortcodes Diagnostic Class
 *
 * @since 1.1203.0000
 */
class Diagnostic_FluentFormsConditionalShortcodes extends Diagnostic_Base {

	protected static $slug = 'fluent-forms-conditional-shortcodes';
	protected static $title = 'Fluent Forms Conditional Shortcodes';
	protected static $description = 'Fluent Forms Conditional Shortcodes issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'FLUENTFORM' ) ) {
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
				'severity'    => 50,
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/fluent-forms-conditional-shortcodes',
			);
		}
		
		return null;
	}
}
