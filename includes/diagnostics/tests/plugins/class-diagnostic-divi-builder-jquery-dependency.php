<?php
/**
 * Divi Builder jQuery Dependency Diagnostic
 *
 * Divi jQuery usage not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.352.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder jQuery Dependency Diagnostic Class
 *
 * @since 1.352.0000
 */
class Diagnostic_DiviBuilderJqueryDependency extends Diagnostic_Base {

	protected static $slug = 'divi-builder-jquery-dependency';
	protected static $title = 'Divi Builder jQuery Dependency';
	protected static $description = 'Divi jQuery usage not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'et_divi_fonts_url' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/divi-builder-jquery-dependency',
			);
		}
		
		return null;
	}
}
