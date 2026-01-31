<?php
/**
 * Imagify Bulk Optimization Performance Diagnostic
 *
 * Imagify Bulk Optimization Performance detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.739.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imagify Bulk Optimization Performance Diagnostic Class
 *
 * @since 1.739.0000
 */
class Diagnostic_ImagifyBulkOptimizationPerformance extends Diagnostic_Base {

	protected static $slug = 'imagify-bulk-optimization-performance';
	protected static $title = 'Imagify Bulk Optimization Performance';
	protected static $description = 'Imagify Bulk Optimization Performance detected';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'IMAGIFY_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/imagify-bulk-optimization-performance',
			);
		}
		
		return null;
	}
}
