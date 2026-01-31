<?php
/**
 * Oxygen Builder Code Blocks Validation Diagnostic
 *
 * Oxygen Builder Code Blocks Validation issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.813.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Oxygen Builder Code Blocks Validation Diagnostic Class
 *
 * @since 1.813.0000
 */
class Diagnostic_OxygenBuilderCodeBlocksValidation extends Diagnostic_Base {

	protected static $slug = 'oxygen-builder-code-blocks-validation';
	protected static $title = 'Oxygen Builder Code Blocks Validation';
	protected static $description = 'Oxygen Builder Code Blocks Validation issues found';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/oxygen-builder-code-blocks-validation',
			);
		}
		
		return null;
	}
}
