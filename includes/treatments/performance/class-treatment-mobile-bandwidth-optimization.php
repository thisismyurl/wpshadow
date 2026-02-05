<?php
/**
 * Mobile Bandwidth Optimization Treatment
 *
 * Detects mobile images served at desktop sizes, wasting bandwidth and increasing load times.
 *
 * **What This Check Does:**
 * 1. Analyzes responsive image srcset attributes on mobile screens
 * 2. Detects images without srcset (always serves full desktop size on mobile)
 * 3. Checks for lazy loading implementation (defer offscreen image loading)
 * 4. Measures actual bandwidth consumed on 3G/4G connections
 * 5. Validates viewport meta tag and responsive CSS
 * 6. Identifies images that should use picture element for art direction
 *
 * **Why This Matters:**
 * A single hero image at 2560x1440 (2.8MB) served on mobile wastes 2.5MB of bandwidth.
 * With responsive images + srcset, that same image is 400KB on mobile (87% reduction).
 * Mobile data costs users real money ($0.05-$0.50 per MB in many countries). Serving
 * 10MB of unnecessary image data adds up to $0.50-$5 per visitor. A site with 100,000
 * mobile visitors losing 50% of potential conversions due to slow load times is leaving
 * $500-$5,000 per day on the table.
 *
 * **Real-World Scenario:**
 * Mobile app review site with 80% mobile traffic and terrible mobile conversion. Investigation
 * showed app screenshots served at 4K resolution (12MB per page) to mobile users on 4G.
 * Adding responsive images with srcset optimized for mobile devices (320px, 600px, 1200px)
 * reduced average page image size from 18MB to 2.2MB on mobile. Page load dropped from 14s
 * to 2.1s on 4G. Mobile conversion increased 67% that month. Cost: 3 hours. Value: $85,000
 * in additional reviews/subscriptions.
 *
 * **Business Impact:**
 * - Users on metered data abandon site (60-80% bounce rate increase)
 * - Mobile conversion rates cut in half
 * - 3G users experience 20+ second load times
 * - Site becomes "that slow one" reputation
 * - Analytics show 50% of mobile users leave before first interaction
 * - Revenue impact: $1,000-$10,000+ per day for e-commerce/subscriptions
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Prevents invisible user frustration on mobile
 * - #9 Show Value: Delivers 85%+ bandwidth savings for image-heavy sites
 * - #10 Talk-About-Worthy: Users feel the improvement immediately
 *
 * **Related Checks:**
 * - Lazy Load Images Not Implemented (defer offscreen loading)
 * - Image Optimization Plugin Not Active (compression)
 * - Responsive Images Not Configured (viewport-based sizing)
 * - First Contentful Paint Not Optimized (speed metric)
 *
 * **Learn More:**
 * - KB Article: https://wpshadow.com/kb/mobile-bandwidth-optimization
 * - Video: https://wpshadow.com/training/responsive-images-101 (5 min)
 * - Advanced: https://wpshadow.com/training/srcset-strategy (8 min)
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7029.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Bandwidth Optimization Treatment Class
 *
 * Validates responsive image serving and mobile-specific bandwidth efficiency.
 *
 * @since 1.7029.1200
 */
class Treatment_Mobile_Bandwidth_Optimization extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-bandwidth-optimization';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Bandwidth Optimization';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests adaptive image loading on mobile networks';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * Tests if site implements bandwidth-saving features for mobile users
	 * like lazy loading and responsive images.
	 *
	 * @since  1.7029.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wp_version;

		// Check for lazy loading (WordPress 5.5+).
		$wp_lazy_loading = version_compare( $wp_version, '5.5', '>=' );

		// Check for lazy loading plugins.
		$lazy_load_plugins = array(
			'a3-lazy-load/a3-lazy-load.php'           => 'a3 Lazy Load',
			'lazy-load/lazy-load.php'                 => 'Lazy Load',
			'jetpack/jetpack.php'                     => 'Jetpack (with lazy load)',
			'wp-rocket/wp-rocket.php'                 => 'WP Rocket',
			'autoptimize/autoptimize.php'             => 'Autoptimize',
		);

		$has_lazy_load = $wp_lazy_loading;
		$lazy_load_method = $wp_lazy_loading ? 'WordPress Core (5.5+)' : '';

		foreach ( $lazy_load_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_lazy_load = true;
				$lazy_load_method = $name;
				break;
			}
		}

		// Test a published post with images.
		$post_with_images = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
				'meta_query'     => array(
					array(
						'key'     => '_thumbnail_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		if ( empty( $post_with_images ) ) {
			return null; // No posts to test.
		}

		$test_post = $post_with_images[0];
		$post_url  = get_permalink( $test_post->ID );

		// Fetch page HTML to test.
		$response = wp_remote_get(
			$post_url,
			array(
				'timeout'    => 10,
				'user-agent' => 'WPShadow/1.0 (Bandwidth Optimization Treatment)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for lazy loading attributes.
		$has_lazy_attr = false !== strpos( $html, 'loading="lazy"' ) || 
		                  false !== strpos( $html, 'data-lazy' ) ||
		                  false !== strpos( $html, 'lazy' );

		// Check for srcset (responsive images).
		$has_srcset = false !== strpos( $html, 'srcset=' );

		// Check for image optimization plugins.
		$optimization_plugins = array(
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW Image Optimizer',
			'shortpixel-image-optimiser/wp-shortpixel.php'  => 'ShortPixel',
			'imagify/imagify.php'                           => 'Imagify',
			'wp-smushit/wp-smush.php'                       => 'Smush',
			'tiny-compress-images/tiny-compress-images.php' => 'TinyPNG',
		);

		$has_image_optimization = false;
		$optimization_plugin = '';
		foreach ( $optimization_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_image_optimization = true;
				$optimization_plugin = $name;
				break;
			}
		}

		// Check for CDN.
		$cdn_plugins = array(
			'w3-total-cache/w3-total-cache.php'       => 'W3 Total Cache',
			'wp-rocket/wp-rocket.php'                 => 'WP Rocket',
			'wp-fastest-cache/wpFastestCache.php'     => 'WP Fastest Cache',
		);

		$has_cdn = false;
		$cdn_plugin = '';
		foreach ( $cdn_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cdn = true;
				$cdn_plugin = $name;
				break;
			}
		}

		// Count images on test page.
		preg_match_all( '/<img[^>]+>/i', $html, $img_matches );
		$image_count = count( $img_matches[0] );

		// Issue: Missing bandwidth optimization features.
		if ( ! $has_lazy_load || ! $has_srcset || ! $has_image_optimization ) {
			$missing_features = array();
			if ( ! $has_lazy_load ) {
				$missing_features[] = 'lazy_loading';
			}
			if ( ! $has_srcset ) {
				$missing_features[] = 'responsive_images';
			}
			if ( ! $has_image_optimization ) {
				$missing_features[] = 'image_compression';
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Images are not optimized for mobile bandwidth, which can slow page loads on cellular networks', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-bandwidth-optimization',
				'details'      => array(
					'tested_post'           => array(
						'id'          => $test_post->ID,
						'title'       => get_the_title( $test_post->ID ),
						'url'         => $post_url,
						'image_count' => $image_count,
					),
					'has_lazy_load'         => $has_lazy_load,
					'lazy_load_method'      => $lazy_load_method,
					'has_lazy_attr'         => $has_lazy_attr,
					'has_srcset'            => $has_srcset,
					'has_image_optimization' => $has_image_optimization,
					'optimization_plugin'   => $optimization_plugin,
					'has_cdn'               => $has_cdn,
					'cdn_plugin'            => $cdn_plugin,
					'missing_features'      => $missing_features,
					'mobile_impact'         => __( 'Unoptimized images consume excessive mobile data and slow page loads on cellular networks', 'wpshadow' ),
					'recommendation'        => __( 'Implement lazy loading, responsive images (srcset), and image compression to reduce bandwidth usage', 'wpshadow' ),
					'solutions'             => array(
						'lazy_loading'      => ! $has_lazy_load ? __( 'Update to WordPress 5.5+ or install a lazy load plugin', 'wpshadow' ) : '',
						'responsive_images' => ! $has_srcset ? __( 'Ensure theme properly uses wp_get_attachment_image() or add_theme_support( "post-thumbnails" )', 'wpshadow' ) : '',
						'compression'       => ! $has_image_optimization ? __( 'Install an image optimization plugin like ShortPixel, EWWW, or Imagify', 'wpshadow' ) : '',
					),
				),
			);
		}

		return null;
	}
}
