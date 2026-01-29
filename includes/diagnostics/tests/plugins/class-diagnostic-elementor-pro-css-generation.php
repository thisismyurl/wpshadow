<?php
/**
 * Elementor Pro Css Generation Diagnostic
 *
 * Elementor Pro Css Generation issues found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.798.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Elementor Pro Css Generation Diagnostic Class
 *
 * @since 1.798.0000
 */
class Diagnostic_ElementorProCssGeneration extends Diagnostic_Base {

	protected static $slug = 'elementor-pro-css-generation';
	protected static $title = 'Elementor Pro Css Generation';
	protected static $description = 'Elementor Pro Css Generation issues found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify CSS Print Method is optimized
		$css_print_method = get_option( 'elementor_css_print_method', 'external' );
		if ( 'internal' === $css_print_method ) {
			// Internal method adds CSS inline - bad for performance
			$issues[] = 'css_inline_method';
		}
		
		// Check 2: Verify if CSS files are being regenerated properly
		$upload_dir = wp_upload_dir();
		$css_dir = $upload_dir['basedir'] . '/elementor/css';
		
		if ( file_exists( $css_dir ) ) {
			// Check if CSS directory is writable
			if ( ! is_writable( $css_dir ) ) {
				$issues[] = 'css_directory_not_writable';
			}
			
			// Check for stale CSS files (older than 30 days)
			$css_files = glob( $css_dir . '/*.css' );
			if ( ! empty( $css_files ) ) {
				$old_files = 0;
				foreach ( $css_files as $file ) {
					if ( ( time() - filemtime( $file ) ) > 30 * DAY_IN_SECONDS ) {
						$old_files++;
					}
				}
				if ( $old_files > 10 ) {
					$issues[] = 'stale_css_files';
				}
			}
		} else {
			$issues[] = 'css_directory_missing';
		}
		
		// Check 3: Verify Google Fonts loading strategy
		$disable_google_fonts = get_option( 'elementor_disable_google_fonts', '' );
		$google_fonts = get_option( 'elementor_google_fonts', 'yes' );
		
		if ( 'yes' === $google_fonts && 'yes' !== $disable_google_fonts ) {
			// Google Fonts enabled - check if preconnect is set
			$preconnect = get_option( 'elementor_font_display', 'auto' );
			if ( 'swap' !== $preconnect ) {
				$issues[] = 'google_fonts_not_optimized';
			}
		}
		
		// Check 4: Verify minification is enabled
		$minify_css = get_option( 'elementor_optimized_css_output', '' );
		if ( 'yes' !== $minify_css ) {
			$issues[] = 'css_not_minified';
		}
		
		// Check 5: Check if CSS regeneration is needed
		$regenerate_css = get_option( '_elementor_global_css_updated', '' );
		if ( empty( $regenerate_css ) ) {
			$issues[] = 'css_needs_regeneration';
		}
		
		// Check 6: Verify dynamic CSS is not being loaded on every page
		if ( 'external' === $css_print_method ) {
			// Check if CSS files exist for recently edited pages
			$recent_posts = get_posts( array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'orderby'        => 'modified',
				'meta_query'     => array(
					array(
						'key'     => '_elementor_edit_mode',
						'value'   => 'builder',
						'compare' => '=',
					),
				),
			) );
			
			foreach ( $recent_posts as $post ) {
				$css_file = $css_dir . '/post-' . $post->ID . '.css';
				if ( ! file_exists( $css_file ) ) {
					$issues[] = 'missing_post_css_files';
					break;
				}
			}
		}
		
		if ( ! empty( $issues ) ) {
			$issues = array_unique( $issues );
			$description = sprintf(
				/* translators: %s: list of CSS generation issues */
				__( 'Elementor Pro CSS generation has performance issues: %s. Inefficient CSS handling can significantly slow down page load times.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/elementor-pro-css-generation',
			);
		}
		
		return null;
	}
}
