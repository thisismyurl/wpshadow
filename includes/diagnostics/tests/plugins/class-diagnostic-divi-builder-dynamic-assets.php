<?php
/**
 * Divi Builder Dynamic Assets Diagnostic
 *
 * Divi dynamic CSS/JS not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.351.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Divi Builder Dynamic Assets Diagnostic Class
 *
 * @since 1.351.0000
 */
class Diagnostic_DiviBuilderDynamicAssets extends Diagnostic_Base {

	protected static $slug = 'divi-builder-dynamic-assets';
	protected static $title = 'Divi Builder Dynamic Assets';
	protected static $description = 'Divi dynamic CSS/JS not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'et_divi_fonts_url' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Dynamic CSS
		$dynamic_css = get_option( 'et_pb_static_css_file', 'off' );
		if ( 'off' === $dynamic_css ) {
			$issues[] = __( 'Dynamic CSS generation (slow page loads)', 'wpshadow' );
		}

		// Check 2: Minification
		$minify = get_option( 'et_pb_minify_assets', 'off' );
		if ( 'off' === $minify ) {
			$issues[] = __( 'Assets not minified (larger files)', 'wpshadow' );
		}

		// Check 3: Critical CSS
		$critical_css = get_option( 'et_pb_critical_css', 'off' );
		if ( 'off' === $critical_css ) {
			$issues[] = __( 'No critical CSS (render blocking)', 'wpshadow' );
		}

		// Check 4: Font loading
		$defer_fonts = get_option( 'et_pb_defer_fonts', 'off' );
		if ( 'off' === $defer_fonts ) {
			$issues[] = __( 'Fonts not deferred (blocking)', 'wpshadow' );
		}

		// Check 5: Unused CSS
		$remove_unused = get_option( 'et_pb_remove_unused_css', 'off' );
		if ( 'off' === $remove_unused ) {
			$issues[] = __( 'Unused CSS included (bloated stylesheets)', 'wpshadow' );
		}

		// Check 6: JavaScript defer
		$defer_js = get_option( 'et_pb_defer_scripts', 'off' );
		if ( 'off' === $defer_js ) {
			$issues[] = __( 'Scripts not deferred (blocking)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 60;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 72;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 66;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Divi Builder has %d asset optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/divi-builder-dynamic-assets',
		);
	}
}
