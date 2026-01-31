<?php
/**
 * Litespeed Cache Image Optimization Diagnostic
 *
 * Litespeed Cache Image Optimization not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.901.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Litespeed Cache Image Optimization Diagnostic Class
 *
 * @since 1.901.0000
 */
class Diagnostic_LitespeedCacheImageOptimization extends Diagnostic_Base {

	protected static $slug = 'litespeed-cache-image-optimization';
	protected static $title = 'Litespeed Cache Image Optimization';
	protected static $description = 'Litespeed Cache Image Optimization not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/litespeed-cache-image-optimization',
			);
		}
		
		return null;
	}
}
