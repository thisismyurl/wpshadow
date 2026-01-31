<?php
/**
 * Sg Optimizer Caching Strategy Diagnostic
 *
 * Sg Optimizer Caching Strategy not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.909.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Sg Optimizer Caching Strategy Diagnostic Class
 *
 * @since 1.909.0000
 */
class Diagnostic_SgOptimizerCachingStrategy extends Diagnostic_Base {

	protected static $slug = 'sg-optimizer-caching-strategy';
	protected static $title = 'Sg Optimizer Caching Strategy';
	protected static $description = 'Sg Optimizer Caching Strategy not optimized';
	protected static $family = 'performance';

	public static function check() {
		
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
				'severity'    => 45,
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/sg-optimizer-caching-strategy',
			);
		}
		

		// Performance optimization checks
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			$issues[] = __( 'Caching not enabled', 'wpshadow' );
		}
		if ( ! extension_loaded( 'zlib' ) ) {
			$issues[] = __( 'Gzip compression unavailable', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
