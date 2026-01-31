<?php
/**
 * Divi Builder Pro Theme Options Database Diagnostic
 *
 * Divi Builder Pro Theme Options Database issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.808.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Pro Theme Options Database Diagnostic Class
 *
 * @since 1.808.0000
 */
class Diagnostic_DiviBuilderProThemeOptionsDatabase extends Diagnostic_Base {

	protected static $slug = 'divi-builder-pro-theme-options-database';
	protected static $title = 'Divi Builder Pro Theme Options Database';
	protected static $description = 'Divi Builder Pro Theme Options Database issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'et_setup_theme' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/divi-builder-pro-theme-options-database',
			);
		}
		
		return null;
	}
}
