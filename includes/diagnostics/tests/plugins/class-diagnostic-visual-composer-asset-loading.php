<?php
/**
 * Visual Composer Asset Loading Diagnostic
 *
 * Visual Composer Asset Loading issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.830.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Visual Composer Asset Loading Diagnostic Class
 *
 * @since 1.830.0000
 */
class Diagnostic_VisualComposerAssetLoading extends Diagnostic_Base {

	protected static $slug = 'visual-composer-asset-loading';
	protected static $title = 'Visual Composer Asset Loading';
	protected static $description = 'Visual Composer Asset Loading issues found';
	protected static $family = 'performance';

	public static function check() {
		// Check for Visual Composer / WPBakery
		$has_vc = defined( 'WPB_VC_VERSION' ) ||
		          class_exists( 'Vc_Manager' ) ||
		          function_exists( 'vc_is_inline' );
		
		if ( ! $has_vc ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Conditional loading
		$conditional_load = get_option( 'wpb_js_use_custom', 'off' );
		if ( 'off' === $conditional_load ) {
			$issues[] = __( 'Assets load on all pages (unused JS/CSS)', 'wpshadow' );
		}
		
		// Check 2: Asset minification
		$disable_minify = get_option( 'wpb_js_not_responsive_css', 'off' );
		if ( 'on' === $disable_minify ) {
			$issues[] = __( 'Minification disabled (large file sizes)', 'wpshadow' );
		}
		
		// Check 3: Font loading
		$google_fonts = get_option( 'wpb_js_google_fonts_subsets', 'latin,latin-ext' );
		if ( strpos( $google_fonts, ',' ) !== false && count( explode( ',', $google_fonts ) ) > 3 ) {
			$issues[] = __( 'Many font subsets (slow loading)', 'wpshadow' );
		}
		
		// Check 4: Template caching
		$cache_templates = get_option( 'wpb_js_cache_templates', 'no' );
		if ( 'no' === $cache_templates ) {
			$issues[] = __( 'Templates not cached (repeated processing)', 'wpshadow' );
		}
		
		// Check 5: Deprecated elements
		$use_deprecated = get_option( 'wpb_js_use_deprecated', 'yes' );
		if ( 'yes' === $use_deprecated ) {
			$issues[] = __( 'Deprecated elements loaded (compatibility risk)', 'wpshadow' );
		}
		
		// Check 6: Animation library
		$animate_css = get_option( 'wpb_js_animate_css', 'yes' );
		if ( 'yes' === $animate_css ) {
			$issues[] = __( 'Animate.css on all pages (unused animations)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 67;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 61;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Visual Composer asset loading issues */
				__( 'Visual Composer has %d asset loading issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/visual-composer-asset-loading',
		);
	}
}
