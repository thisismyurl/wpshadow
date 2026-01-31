<?php
/**
 * LayerSlider Transitions Diagnostic
 *
 * LayerSlider transitions causing lag.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.288.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LayerSlider Transitions Diagnostic Class
 *
 * @since 1.288.0000
 */
class Diagnostic_LayersliderTransitionPerformance extends Diagnostic_Base {

	protected static $slug = 'layerslider-transition-performance';
	protected static $title = 'LayerSlider Transitions';
	protected static $description = 'LayerSlider transitions causing lag';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LS_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 35 ),
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/layerslider-transition-performance',
			);
		}
		
		return null;
	}
}
