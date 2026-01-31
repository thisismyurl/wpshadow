<?php
/**
 * Divi Builder Critical CSS Diagnostic
 *
 * Divi critical CSS not loading.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.350.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Critical CSS Diagnostic Class
 *
 * @since 1.350.0000
 */
class Diagnostic_DiviBuilderCriticalCss extends Diagnostic_Base {

	protected static $slug = 'divi-builder-critical-css';
	protected static $title = 'Divi Builder Critical CSS';
	protected static $description = 'Divi critical CSS not loading';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'et_divi_fonts_url' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/divi-builder-critical-css',
			);
		}
		
		return null;
	}
}
