<?php
/**
 * Cookie Notice Performance Diagnostic
 *
 * Cookie Notice slowing page loads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.423.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Notice Performance Diagnostic Class
 *
 * @since 1.423.0000
 */
class Diagnostic_CookieNoticePerformance extends Diagnostic_Base {

	protected static $slug = 'cookie-notice-performance';
	protected static $title = 'Cookie Notice Performance';
	protected static $description = 'Cookie Notice slowing page loads';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'COOKIE_NOTICE_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Script async loading
		$async = get_option( 'cookie_notice_script_async_enabled', 0 );
		if ( ! $async ) {
			$issues[] = 'Script async loading not enabled';
		}
		
		// Check 2: CSS minification
		$css = get_option( 'cookie_notice_css_minification_enabled', 0 );
		if ( ! $css ) {
			$issues[] = 'CSS minification not enabled';
		}
		
		// Check 3: JavaScript minification
		$js = get_option( 'cookie_notice_js_minification_enabled', 0 );
		if ( ! $js ) {
			$issues[] = 'JavaScript minification not enabled';
		}
		
		// Check 4: Caching strategy
		$cache = get_option( 'cookie_notice_caching_enabled', 0 );
		if ( ! $cache ) {
			$issues[] = 'Caching strategy not configured';
		}
		
		// Check 5: Lazy loading
		$lazy = get_option( 'cookie_notice_lazy_loading_enabled', 0 );
		if ( ! $lazy ) {
			$issues[] = 'Lazy loading not enabled';
		}
		
		// Check 6: Code splitting
		$split = get_option( 'cookie_notice_code_splitting_enabled', 0 );
		if ( ! $split ) {
			$issues[] = 'Code splitting not enabled';
		}
		
		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 30;
			$threat_multiplier = 6;
			$max_threat = 60;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );
			
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d performance issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/cookie-notice-performance',
			);
		}
		
		return null;
	}
}
