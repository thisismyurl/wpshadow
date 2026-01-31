<?php
/**
 * Litespeed Cache Js Minification Diagnostic
 *
 * Litespeed Cache Js Minification not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.905.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Litespeed Cache Js Minification Diagnostic Class
 *
 * @since 1.905.0000
 */
class Diagnostic_LitespeedCacheJsMinification extends Diagnostic_Base {

	protected static $slug = 'litespeed-cache-js-minification';
	protected static $title = 'Litespeed Cache Js Minification';
	protected static $description = 'Litespeed Cache Js Minification not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: JS minification enabled
		$js_minify = get_option( 'litespeed_js_minification', 0 );
		if ( ! $js_minify ) {
			$issues[] = 'JS minification not enabled';
		}

		// Check 2: JS combination
		$js_combine = get_option( 'litespeed_js_combine', 0 );
		if ( ! $js_combine ) {
			$issues[] = 'JS file combining not enabled';
		}

		// Check 3: Deferred JS loading
		$defer_js = get_option( 'litespeed_defer_js_loading', 0 );
		if ( ! $defer_js ) {
			$issues[] = 'Deferred JS loading not enabled';
		}

		// Check 4: Async JS loading
		$async_js = get_option( 'litespeed_async_js_loading', 0 );
		if ( ! $async_js ) {
			$issues[] = 'Async JS loading not enabled';
		}

		// Check 5: JS excludes configured
		$js_excludes = get_option( 'litespeed_js_minification_excludes', '' );
		if ( empty( $js_excludes ) ) {
			$issues[] = 'JS minification exclusions not configured';
		}

		// Check 6: Critical JS identified
		$critical_js = get_option( 'litespeed_critical_js_identified', 0 );
		if ( ! $critical_js ) {
			$issues[] = 'Critical JS path not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d JS minification issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/litespeed-cache-js-minification',
			);
		}

		return null;
	}
}
