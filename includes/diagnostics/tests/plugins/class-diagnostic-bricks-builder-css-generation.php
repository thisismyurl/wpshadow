<?php
/**
 * Bricks Builder Css Generation Diagnostic
 *
 * Bricks Builder Css Generation issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.822.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bricks Builder Css Generation Diagnostic Class
 *
 * @since 1.822.0000
 */
class Diagnostic_BricksBuilderCssGeneration extends Diagnostic_Base {

	protected static $slug = 'bricks-builder-css-generation';
	protected static $title = 'Bricks Builder Css Generation';
	protected static $description = 'Bricks Builder Css Generation issues found';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Bricks Builder
		$theme = wp_get_theme();
		if ( 'Bricks' !== $theme->name && 'Bricks' !== $theme->parent_theme ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Pages built with Bricks
		$bricks_pages = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'_bricks_page_content_2'
			)
		);
		
		if ( $bricks_pages === 0 ) {
			return null;
		}
		
		// Check 2: CSS loading method
		$css_loading = get_option( 'bricks_css_loading_method', 'file' );
		if ( 'inline' === $css_loading && $bricks_pages > 10 ) {
			$issues[] = __( 'Inline CSS loading with many pages (large HTML)', 'wpshadow' );
		}
		
		// Check 3: CSS file generation
		$upload_dir = wp_upload_dir();
		$css_dir = $upload_dir['basedir'] . '/bricks/css';
		
		if ( is_dir( $css_dir ) ) {
			$css_files = glob( $css_dir . '/*.css' );
			$large_files = 0;
			
			foreach ( $css_files as $file ) {
				if ( filesize( $file ) > 100000 ) { // 100KB
					$large_files++;
				}
			}
			
			if ( $large_files > 3 ) {
				$issues[] = sprintf( __( '%d large CSS files (>100KB each)', 'wpshadow' ), $large_files );
			}
		}
		
		// Check 4: CSS minification
		$minify_css = get_option( 'bricks_minify_css', true );
		if ( ! $minify_css ) {
			$issues[] = __( 'CSS minification not enabled (larger file sizes)', 'wpshadow' );
		}
		
		// Check 5: Critical CSS
		$critical_css = get_option( 'bricks_generate_critical_css', false );
		if ( ! $critical_css ) {
			$issues[] = __( 'Critical CSS not generated (render-blocking)', 'wpshadow' );
		}
		
		// Check 6: CSS regeneration
		$last_regeneration = get_option( 'bricks_css_last_regeneration', 0 );
		if ( $last_regeneration > 0 && ( time() - $last_regeneration ) > ( 30 * DAY_IN_SECONDS ) ) {
			$issues[] = __( 'CSS files not regenerated in 30+ days (stale styles)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of CSS generation issues */
				__( 'Bricks Builder CSS generation has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/bricks-builder-css-generation',
		);
	}
}
