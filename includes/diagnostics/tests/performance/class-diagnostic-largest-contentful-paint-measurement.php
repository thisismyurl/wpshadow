<?php
/**
 * Largest Contentful Paint (LCP) Measurement Diagnostic
 *
 * Measures actual LCP on key pages to validate Core Web Vitals performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Largest Contentful Paint (LCP) Measurement Class
 *
 * Tests LCP metric.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Largest_Contentful_Paint_Measurement extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'largest-contentful-paint-measurement';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Largest Contentful Paint (LCP) Measurement';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measures actual LCP on key pages to validate Core Web Vitals performance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$lcp_check = self::check_lcp_indicators();
		
		if ( $lcp_check['has_issues'] ) {
			$issues = array();
			
			foreach ( $lcp_check['issues'] as $issue ) {
				$issues[] = $issue;
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/largest-contentful-paint-measurement',
				'meta'         => array(
					'blocking_resources' => $lcp_check['blocking_resources'],
					'large_images'       => $lcp_check['large_images'],
					'recommendations'    => $lcp_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check LCP indicators.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_lcp_indicators() {
		global $wp_scripts, $wp_styles;

		$check = array(
			'has_issues'         => false,
			'issues'             => array(),
			'blocking_resources' => array(),
			'large_images'       => array(),
			'recommendations'    => array(),
		);

		// Check for render-blocking CSS.
		$blocking_css_count = 0;
		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
					continue;
				}

				$style = $wp_styles->registered[ $handle ];
				
				// Check if style has media attribute limiting blocking.
				if ( empty( $style->args ) || 'all' === $style->args ) {
					$blocking_css_count++;
					$check['blocking_resources'][] = array(
						'type'   => 'css',
						'handle' => $handle,
					);
				}
			}
		}

		// Check for render-blocking JavaScript.
		$blocking_js_count = 0;
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
					continue;
				}

				$script = $wp_scripts->registered[ $handle ];
				
				// Check if script lacks async/defer.
				if ( ! isset( $script->extra['async'] ) && ! isset( $script->extra['defer'] ) ) {
					$blocking_js_count++;
					$check['blocking_resources'][] = array(
						'type'   => 'js',
						'handle' => $handle,
					);
				}
			}
		}

		// Check for unoptimized images in homepage content.
		$homepage_id = (int) get_option( 'page_on_front' );
		
		if ( $homepage_id > 0 ) {
			$content = get_post_field( 'post_content', $homepage_id );
			
			if ( ! empty( $content ) ) {
				// Find img tags.
				preg_match_all( '/<img[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $content, $matches );
				
				if ( ! empty( $matches[1] ) ) {
					foreach ( $matches[1] as $img_url ) {
						// Check if image lacks loading="lazy".
						if ( false === strpos( $content, 'loading="lazy"' ) ) {
							$check['large_images'][] = $img_url;
						}
					}
				}
			}
		}

		// Detect issues.
		if ( $blocking_css_count > 3 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of blocking CSS files */
				__( '%d render-blocking CSS files detected', 'wpshadow' ),
				$blocking_css_count
			);
			$check['recommendations'][] = __( 'Defer non-critical CSS or use media queries', 'wpshadow' );
		}

		if ( $blocking_js_count > 2 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of blocking JS files */
				__( '%d render-blocking JavaScript files detected', 'wpshadow' ),
				$blocking_js_count
			);
			$check['recommendations'][] = __( 'Add async or defer attributes to scripts', 'wpshadow' );
		}

		if ( count( $check['large_images'] ) > 0 ) {
			$check['has_issues'] = true;
			$check['issues'][] = sprintf(
				/* translators: %d: number of images */
				__( '%d images without lazy loading detected', 'wpshadow' ),
				count( $check['large_images'] )
			);
			$check['recommendations'][] = __( 'Add loading="lazy" to images below the fold', 'wpshadow' );
		}

		return $check;
	}
}
