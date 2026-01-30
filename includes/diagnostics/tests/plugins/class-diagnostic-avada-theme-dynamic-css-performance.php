<?php
/**
 * Avada Theme Dynamic Css Performance Diagnostic
 *
 * Avada Theme Dynamic Css Performance needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1307.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Avada Theme Dynamic Css Performance Diagnostic Class
 *
 * @since 1.1307.0000
 */
class Diagnostic_AvadaThemeDynamicCssPerformance extends Diagnostic_Base {

	protected static $slug = 'avada-theme-dynamic-css-performance';
	protected static $title = 'Avada Theme Dynamic Css Performance';
	protected static $description = 'Avada Theme Dynamic Css Performance needs optimization';
	protected static $family = 'performance';

	public static function check() {
		// Check for Avada theme
		$theme = wp_get_theme();
		if ( 'Avada' !== $theme->get( 'Name' ) && 'Avada' !== $theme->get_template() ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CSS caching enabled
		$css_cache = get_option( 'avada_css_cache', 'on' );
		if ( 'off' === $css_cache ) {
			$issues[] = __( 'CSS caching disabled (regenerated every page load)', 'wpshadow' );
		}
		
		// Check 2: CSS compiler mode
		$compiler_mode = get_option( 'avada_compiler_mode', 'file' );
		if ( 'inline' === $compiler_mode ) {
			$issues[] = __( 'Inline CSS mode (increases HTML size)', 'wpshadow' );
		}
		
		// Check 3: Dynamic CSS file size
		$upload_dir = wp_upload_dir();
		$css_dir = $upload_dir['basedir'] . '/avada-styles/';
		
		if ( is_dir( $css_dir ) ) {
			$css_files = glob( $css_dir . '*.css' );
			if ( count( $css_files ) > 0 ) {
				$total_size = 0;
				foreach ( $css_files as $file ) {
					$total_size += filesize( $file );
				}
				
				if ( $total_size > ( 500 * 1024 ) ) { // 500KB
					$issues[] = sprintf( __( 'Dynamic CSS: %s (consider optimization)', 'wpshadow' ), size_format( $total_size ) );
				}
			}
		}
		
		// Check 4: CSS minification
		$minify_css = get_option( 'avada_css_minify', 'on' );
		if ( 'off' === $minify_css ) {
			$issues[] = __( 'CSS minification disabled (larger file sizes)', 'wpshadow' );
		}
		
		// Check 5: Dynamic CSS regeneration
		$regen_css = get_option( 'avada_regen_css', 'off' );
		if ( 'on' === $regen_css ) {
			$issues[] = __( 'CSS auto-regeneration enabled (performance overhead)', 'wpshadow' );
		}
		
		// Check 6: Critical CSS
		$critical_css = get_option( 'avada_critical_css', 'off' );
		if ( 'off' === $critical_css ) {
			$issues[] = __( 'Critical CSS disabled (render-blocking CSS)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of CSS performance issues */
				__( 'Avada dynamic CSS has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/avada-theme-dynamic-css-performance',
		);
	}
}
