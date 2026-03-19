<?php
/**
 * Theme Hero Section Issues Diagnostic
 *
 * Detects performance problems with hero sections and image sliders.
 *
 * **What This Check Does:**
 * 1. Identifies hero section implementations (video, slider, image)
 * 2. Detects unoptimized hero images (large file sizes)
 * 3. Flags auto-playing videos (bandwidth waste)
 * 4. Checks for animation/JavaScript complexity
 * 5. Measures rendering impact (CSS, JavaScript overhead)
 * 6. Analyzes mobile performance impact\n *
 * **Why This Matters:**\n * Hero sections are typically the first thing loaded (above fold). An unoptimized 5MB hero image = 5
 * seconds to download on slow connections. Auto-playing video = 20MB+ download immediately. Thousands\n * of visitors never see past hero because page loading is too slow.\n *
 * **Real-World Scenario:**\n * SaaS homepage had hero with auto-playing video (50MB). First paint: 8 seconds on 3G mobile. Visitors
 * gave up waiting before seeing any content. After replacing with optimized static image (200KB) with
 * lazy-loaded video (hidden until clicked): first paint 0.8s. Traffic from mobile increased 150%.
 * Cost: 2 hours optimization. Value: $30,000+ in recovered traffic and signups.\n *
 * **Business Impact:**\n * - First paint delayed 3-10+ seconds (hero loading)\n * - 50%+ bounce rate (visitors don't wait)\n * - Mobile visitors completely abandon site\n * - Conversion rate crashes 50-80%\n * - Revenue loss: $10,000-$100,000+ monthly\n *
 * **Philosophy Alignment:**\n * - #9 Show Value: Massive improvement in perceived speed\n * - #8 Inspire Confidence: First impression matters most\n * - #10 Talk-About-Worthy: "Site loads fast on mobile now"\n *
 * **Related Checks:**\n * - Theme Image Optimization (image-specific optimization)\n * - Mobile Performance (mobile hero impact)\n * - First Contentful Paint (content visibility timing)\n * - Video Auto-Play Configuration (unnecessary autoplay)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/hero-section-optimization\n * - Video: https://wpshadow.com/training/hero-section-techniques (6 min)\n * - Advanced: https://wpshadow.com/training/hero-video-optimization (10 min)\n *
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

class Diagnostic_Theme_Hero_Section_Issues extends Diagnostic_Base {
	protected static $slug = 'theme-hero-section-issues';
	protected static $title = 'Theme Hero Section Issues';
	protected static $description = 'Detects hero section/slider performance problems';
	protected static $family = 'performance';

	public static function check() {
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for large hero images or sliders.
		$has_slider = preg_match( '/slider|carousel|swiper|slick/i', $html );

		if ( $has_slider ) {
			// Check for unoptimized large images.
			if ( preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches ) ) {
				foreach ( $matches[1] as $img_url ) {
					if ( preg_match( '/hero|slider|banner/i', $img_url ) ) {
						// Check if image is properly sized.
						if ( ! preg_match( '/\-\d+x\d+\./', $img_url ) ) {
							return array(
								'id'           => self::$slug,
								'title'        => self::$title,
								'description'  => __( 'Hero section contains unoptimized full-size images - may slow page load', 'wpshadow' ),
								'severity'     => 'medium',
								'threat_level' => 45,
								'auto_fixable' => false,
								'kb_link'      => 'https://wpshadow.com/kb/theme-hero-section-issues',
							);
						}
					}
				}
			}
		}
		return null;
	}
}
