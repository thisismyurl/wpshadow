<?php
/**
 * Autoptimize Css Optimization Diagnostic
 *
 * Autoptimize Css Optimization not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.913.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoptimize Css Optimization Diagnostic Class
 *
 * @since 1.913.0000
 */
class Diagnostic_AutoptimizeCssOptimization extends Diagnostic_Base {

	protected static $slug = 'autoptimize-css-optimization';
	protected static $title = 'Autoptimize Css Optimization';
	protected static $description = 'Autoptimize Css Optimization not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AUTOPTIMIZE_PLUGIN_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/autoptimize-css-optimization',
			);
		}
		
		return null;
	}
}
