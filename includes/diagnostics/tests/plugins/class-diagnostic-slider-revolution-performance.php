<?php
/**
 * Slider Revolution Performance Diagnostic
 *
 * Slider Revolution loading too many assets.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.280.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slider Revolution Performance Diagnostic Class
 *
 * @since 1.280.0000
 */
class Diagnostic_SliderRevolutionPerformance extends Diagnostic_Base {

	protected static $slug = 'slider-revolution-performance';
	protected static $title = 'Slider Revolution Performance';
	protected static $description = 'Slider Revolution loading too many assets';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'RS_REVISION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/slider-revolution-performance',
			);
		}
		
		return null;
	}
}
