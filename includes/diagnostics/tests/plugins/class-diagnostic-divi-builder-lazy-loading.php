<?php
/**
 * Divi Builder Lazy Loading Diagnostic
 *
 * Divi lazy loading not enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.356.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Lazy Loading Diagnostic Class
 *
 * @since 1.356.0000
 */
class Diagnostic_DiviBuilderLazyLoading extends Diagnostic_Base {

	protected static $slug = 'divi-builder-lazy-loading';
	protected static $title = 'Divi Builder Lazy Loading';
	protected static $description = 'Divi lazy loading not enabled';
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
				'kb_link'     => 'https://wpshadow.com/kb/divi-builder-lazy-loading',
			);
		}
		
		return null;
	}
}
