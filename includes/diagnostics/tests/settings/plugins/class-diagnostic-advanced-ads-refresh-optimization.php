<?php
/**
 * Advanced Ads Auto Refresh Diagnostic
 *
 * Ad refresh consuming resources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.295.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Advanced Ads Auto Refresh Diagnostic Class
 *
 * @since 1.295.0000
 */
class Diagnostic_AdvancedAdsRefreshOptimization extends Diagnostic_Base {

	protected static $slug = 'advanced-ads-refresh-optimization';
	protected static $title = 'Advanced Ads Auto Refresh';
	protected static $description = 'Ad refresh consuming resources';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ADVADS_VERSION' ) ) {
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
				'severity'    => 35,
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/advanced-ads-refresh-optimization',
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
