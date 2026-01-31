<?php
/**
 * Divi Builder jQuery Dependency Diagnostic
 *
 * Divi jQuery usage not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.352.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder jQuery Dependency Diagnostic Class
 *
 * @since 1.352.0000
 */
class Diagnostic_DiviBuilderJqueryDependency extends Diagnostic_Base {

	protected static $slug = 'divi-builder-jquery-dependency';
	protected static $title = 'Divi Builder jQuery Dependency';
	protected static $description = 'Divi jQuery usage not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'et_divi_fonts_url' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: jQuery dependency optimization
		$jquery_opt = get_option( 'divi_jquery_optimization_enabled', 0 );
		if ( ! $jquery_opt ) {
			$issues[] = 'jQuery optimization not enabled';
		}

		// Check 2: jQuery async loading
		$async = get_option( 'divi_jquery_async_enabled', 0 );
		if ( ! $async ) {
			$issues[] = 'jQuery async loading not enabled';
		}

		// Check 3: Deferred loading
		$defer = get_option( 'divi_jquery_defer_enabled', 0 );
		if ( ! $defer ) {
			$issues[] = 'jQuery deferred loading not enabled';
		}

		// Check 4: jQuery minification
		$minify = get_option( 'divi_jquery_minification_enabled', 0 );
		if ( ! $minify ) {
			$issues[] = 'jQuery minification not enabled';
		}

		// Check 5: jQuery bundling
		$bundle = get_option( 'divi_jquery_bundling_enabled', 0 );
		if ( ! $bundle ) {
			$issues[] = 'jQuery bundling not configured';
		}

		// Check 6: Conditional jQuery loading
		$conditional = get_option( 'divi_conditional_jquery_loading_enabled', 0 );
		if ( ! $conditional ) {
			$issues[] = 'Conditional jQuery loading not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 35;
			$threat_multiplier = 6;
			$max_threat = 65;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d jQuery optimization issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/divi-builder-jquery-dependency',
			);
		}

		return null;
	}
}
