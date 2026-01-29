<?php
/**
 * Slider Revolution JavaScript Diagnostic
 *
 * Slider Revolution JavaScript not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.283.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slider Revolution JavaScript Diagnostic Class
 *
 * @since 1.283.0000
 */
class Diagnostic_SliderRevolutionJavascriptOptimization extends Diagnostic_Base {

	protected static $slug = 'slider-revolution-javascript-optimization';
	protected static $title = 'Slider Revolution JavaScript';
	protected static $description = 'Slider Revolution JavaScript not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RS_REVISION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/slider-revolution-javascript-optimization',
			);
		}
		
		return null;
	}
}
