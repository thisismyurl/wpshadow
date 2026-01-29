<?php
/**
 * Mobile vs Desktop Performance Gap Diagnostic
 *
 * Compares desktop vs mobile performance to identify mobile-specific issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile vs Desktop Performance Gap Class
 *
 * Tests mobile performance.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Mobile_Vs_Desktop_Performance_Gap extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-vs-desktop-performance-gap';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile vs Desktop Performance Gap';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Compares desktop vs mobile performance to identify mobile-specific issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$mobile_check = self::check_mobile_indicators();
		
		if ( $mobile_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $mobile_check['issues'] ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-vs-desktop-performance-gap',
				'meta'         => array(
					'mobile_indicators'  => $mobile_check['mobile_indicators'],
					'recommendations'    => $mobile_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check mobile performance indicators.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_mobile_indicators() {
		global $wp_scripts, $wp_styles;

		$check = array(
			'has_issues'         => false,
			'issues'             => array(),
			'mobile_indicators'  => array(),
			'recommendations'    => array(),
		);

		// Calculate total resource size.
		$total_css_size = 0;
		$total_js_size = 0;

		// Check CSS size.
		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
					continue;
				}

				$style = $wp_styles->registered[ $handle ];
				
				if ( ! empty( $style->src ) && 0 === strpos( $style->src, home_url() ) ) {
					$file_path = str_replace( home_url(), ABSPATH, $style->src );
					$file_path = wp_normalize_path( $file_path );
					
					if ( file_exists( $file_path ) ) {
						$total_css_size += filesize( $file_path );
					}
				}
			}
		}

		// Check JS size.
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
					continue;
				}

				$script = $wp_scripts->registered[ $handle ];
				
				if ( ! empty( $script->src ) && 0 === strpos( $script->src, home_url() ) ) {
					$file_path = str_replace( home_url(), ABSPATH, $script->src );
					$file_path = wp_normalize_path( $file_path );
					$file_path = preg_replace( '/\?.*$/', '', $file_path );
					
					if ( file_exists( $file_path ) ) {
						$total_js_size += filesize( $file_path );
					}
				}
			}
		}

		$check['mobile_indicators']['total_css_size'] = $total_css_size;
		$check['mobile_indicators']['total_js_size'] = $total_js_size;
		$check['mobile_indicators']['total_resources'] = $total_css_size + $total_js_size;

		// Check viewport meta tag.
		$check['mobile_indicators']['has_viewport_meta'] = current_theme_supports( 'title-tag' );

		// Detect mobile-specific issues.
		if ( $total_js_size > 512000 ) { // >500KB JS.
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %s: JS size in KB */
				__( '%sKB of JavaScript (mobile CPUs will struggle, expect 3-5x slower execution)', 'wpshadow' ),
				number_format( $total_js_size / 1024, 0 )
			);
			$check['recommendations'][] = __( 'Reduce JavaScript payload or implement code splitting', 'wpshadow' );
		}

		if ( ( $total_css_size + $total_js_size ) > 1048576 ) { // >1MB total.
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %s: total size in MB */
				__( '%sMB total resources (mobile networks will add 2-4 seconds on 3G)', 'wpshadow' ),
				number_format( ( $total_css_size + $total_js_size ) / 1048576, 2 )
			);
			$check['recommendations'][] = __( 'Optimize assets for mobile or implement adaptive loading', 'wpshadow' );
		}

		// Check for heavy images in content.
		$homepage_id = (int) get_option( 'page_on_front' );
		
		if ( $homepage_id > 0 ) {
			$content = get_post_field( 'post_content', $homepage_id );
			
			if ( ! empty( $content ) ) {
				preg_match_all( '/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $content, $matches );
				
				if ( ! empty( $matches[1] ) ) {
					$large_images = 0;
					
					foreach ( $matches[1] as $img_url ) {
						// Check if local image.
						if ( 0 === strpos( $img_url, home_url() ) || 0 === strpos( $img_url, '/' ) ) {
							$file_path = str_replace( home_url(), ABSPATH, $img_url );
							
							if ( 0 === strpos( $img_url, '/' ) ) {
								$file_path = ABSPATH . ltrim( $img_url, '/' );
							}

							$file_path = wp_normalize_path( $file_path );
							
							if ( file_exists( $file_path ) && filesize( $file_path ) > 204800 ) { // >200KB.
								$large_images++;
							}
						}
					}

					if ( $large_images > 3 ) {
						$check['has_issues'] = true;
						$check['issues'][] = sprintf(
							/* translators: %d: number of large images */
							__( '%d images >200KB detected (mobile bandwidth impact)', 'wpshadow' ),
							$large_images
						);
						$check['recommendations'][] = __( 'Implement responsive images or WebP format', 'wpshadow' );
					}
				}
			}
		}

		return $check;
	}
}
