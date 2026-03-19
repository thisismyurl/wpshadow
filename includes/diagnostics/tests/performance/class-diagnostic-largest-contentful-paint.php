<?php
/**
 * Largest Contentful Paint (LCP) Diagnostic
 *
 * Measures Largest Contentful Paint time for Core Web Vitals.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Largest Contentful Paint Diagnostic Class
 *
 * Measures factors affecting LCP (Largest Contentful Paint).
 * LCP is the most important Core Web Vital for perceived load speed.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Largest_Contentful_Paint extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'largest-contentful-paint';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Largest Contentful Paint (LCP)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures Largest Contentful Paint timing (Core Web Vital)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks factors affecting LCP:
	 * - Server response time (TTFB)
	 * - Resource load times
	 * - Client-side rendering
	 * - Image optimization
	 *
	 * Thresholds:
	 * - Good: <2.5s
	 * - Needs Improvement: 2.5-4.0s
	 * - Poor: >4.0s
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$score  = 0;
		
		// Check TTFB (major LCP factor)
		$start_time   = defined( 'WPSHADOW_REQUEST_START' ) ? WPSHADOW_REQUEST_START : $_SERVER['REQUEST_TIME_FLOAT'];
		$current_time = microtime( true );
		$ttfb_ms      = round( ( $current_time - $start_time ) * 1000 );
		
		if ( $ttfb_ms > 600 ) {
			$issues[] = sprintf(
				/* translators: %d: TTFB in milliseconds */
				__( 'Slow server response time (%dms)', 'wpshadow' ),
				$ttfb_ms
			);
			$score += 35;
		}
		
		// Check for unoptimized hero images
		if ( is_singular() ) {
			$post_id = get_the_ID();
			if ( $post_id && has_post_thumbnail( $post_id ) ) {
				$thumbnail_id = get_post_thumbnail_id( $post_id );
				$image_meta   = wp_get_attachment_metadata( $thumbnail_id );
				
				if ( $image_meta && isset( $image_meta['width'], $image_meta['height'] ) ) {
					$image_size = $image_meta['width'] * $image_meta['height'];
					
					// Large hero image (>1920x1080)
					if ( $image_size > 2073600 ) {
						$issues[] = sprintf(
							/* translators: 1: width, 2: height */
							__( 'Large hero image (%1$dx%2$d pixels)', 'wpshadow' ),
							$image_meta['width'],
							$image_meta['height']
						);
						$score += 25;
					}
					
					// Check if image is optimized
					$file_path = get_attached_file( $thumbnail_id );
					if ( $file_path && file_exists( $file_path ) ) {
						$file_size = filesize( $file_path );
						
						// Image >500KB
						if ( $file_size > 512000 ) {
							$issues[] = sprintf(
								/* translators: %s: file size */
								__( 'Unoptimized hero image (%s)', 'wpshadow' ),
								size_format( $file_size )
							);
							$score += 30;
						}
					}
				}
			}
		}
		
		// Check for lazy loading on LCP element (bad practice)
		global $wp_scripts;
		$lazy_load_enabled = false;
		
		if ( $wp_scripts && isset( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && isset( $script->src ) ) {
					if ( is_string( $script->src ) && ( strpos( $script->src, 'lazy' ) !== false || 
					     strpos( $script->src, 'lazyload' ) !== false ) ) {
						$lazy_load_enabled = true;
						break;
					}
				}
			}
		}
		
		if ( $lazy_load_enabled && has_post_thumbnail() ) {
			$issues[] = __( 'Lazy loading may delay hero image (LCP element)', 'wpshadow' );
			$score += 20;
		}
		
		// Check for render-blocking stylesheets
		global $wp_styles;
		$blocking_styles = 0;
		
		if ( $wp_styles && isset( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				$style = $wp_styles->registered[ $handle ] ?? null;
				if ( $style && ( empty( $style->extra['media'] ) || 'all' === $style->extra['media'] ) ) {
					$blocking_styles++;
				}
			}
		}
		
		if ( $blocking_styles > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of stylesheets */
				__( '%d render-blocking stylesheets delay LCP', 'wpshadow' ),
				$blocking_styles
			);
			$score += 15;
		}
		
		// Check for web fonts without font-display
		$theme_dir = get_stylesheet_directory();
		$css_files = glob( $theme_dir . '/*.css' );
		$font_face_without_display = false;
		
		if ( $css_files ) {
			foreach ( $css_files as $css_file ) {
				$content = file_get_contents( $css_file );
				if ( preg_match( '/@font-face[^}]*(?!font-display)[^}]*}/s', $content ) ) {
					$font_face_without_display = true;
					break;
				}
			}
		}
		
		if ( $font_face_without_display ) {
			$issues[] = __( 'Web fonts without font-display cause text invisible during load', 'wpshadow' );
			$score += 20;
		}
		
		// If significant issues found
		if ( $score > 40 ) {
			$severity = 'high';
			if ( $score > 70 ) {
				$severity = 'critical';
			}
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: list of LCP issues */
					__( 'Factors affecting Largest Contentful Paint (primary Core Web Vital): %s. LCP measures how quickly the main content loads, directly affecting Google rankings and user experience.', 'wpshadow' ),
					implode( '; ', $issues )
				),
				'severity'     => $severity,
				'threat_level' => min( 100, $score ),
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/largest-contentful-paint',
				'meta'         => array(
					'ttfb_ms'                      => $ttfb_ms,
					'blocking_styles'              => $blocking_styles,
					'lazy_load_detected'           => $lazy_load_enabled,
					'font_display_missing'         => $font_face_without_display,
					'has_hero_image'               => has_post_thumbnail(),
					'score'                        => $score,
					'good_threshold'               => '2.5s',
					'poor_threshold'               => '4.0s',
					'primary_factors'              => 'TTFB, image size, render-blocking CSS',
				),
			);
		}
		
		return null;
	}
}
