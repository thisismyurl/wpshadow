<?php
/**
 * Mobile Responsiveness Testing Diagnostic
 *
 * Tests if site is properly responsive on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Responsiveness Testing Diagnostic Class
 *
 * Validates that the site is mobile-friendly with proper viewport
 * settings, touch targets, and responsive design.
 *
 * @since 1.7034.1330
 */
class Diagnostic_Mobile_Responsiveness_Testing extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-responsiveness-testing';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Responsiveness Testing';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if site is properly responsive on mobile devices';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests mobile responsiveness including viewport meta tag,
	 * touch target sizes, and responsive images.
	 *
	 * @since  1.7034.1330
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check viewport meta tag.
		$header_file = get_template_directory() . '/header.php';
		$has_viewport_meta = false;
		$viewport_content = '';

		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			if ( preg_match( '/<meta[^>]*name=["\']viewport["\'][^>]*content=["\']([^"\']*)["\']/', $header_content, $matches ) ) {
				$has_viewport_meta = true;
				$viewport_content = $matches[1];
			}
		}

		// Check if viewport allows scaling.
		$viewport_allows_scaling = true;
		if ( ! empty( $viewport_content ) ) {
			$viewport_allows_scaling = ( strpos( $viewport_content, 'user-scalable=no' ) === false ) &&
									 ( strpos( $viewport_content, 'maximum-scale=1' ) === false );
		}

		// Check for mobile menu.
		$has_mobile_menu = false;
		if ( file_exists( $header_file ) ) {
			$header_content = file_get_contents( $header_file );
			$has_mobile_menu = ( strpos( $header_content, 'mobile-menu' ) !== false ) ||
							 ( strpos( $header_content, 'hamburger' ) !== false ) ||
							 ( strpos( $header_content, 'menu-toggle' ) !== false );
		}

		// Check CSS for media queries.
		$style_css = get_stylesheet_directory() . '/style.css';
		$media_query_count = 0;

		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			preg_match_all( '/@media[^{]*/', $style_content, $matches );
			$media_query_count = count( $matches[0] );
		}

		// Check for responsive images (srcset).
		global $wpdb;
		$recent_posts = $wpdb->get_results(
			"SELECT post_content FROM {$wpdb->posts}
			 WHERE post_type = 'post' AND post_status = 'publish'
			 ORDER BY post_date DESC LIMIT 10",
			ARRAY_A
		);

		$posts_with_srcset = 0;
		$posts_with_images = 0;

		foreach ( $recent_posts as $post ) {
			$content = $post['post_content'];
			if ( strpos( $content, '<img' ) !== false ) {
				$posts_with_images++;
				if ( strpos( $content, 'srcset=' ) !== false ) {
					$posts_with_srcset++;
				}
			}
		}

		$srcset_coverage = $posts_with_images > 0 ? ( $posts_with_srcset / $posts_with_images ) * 100 : 0;

		// Check for mobile-specific plugins.
		$has_mobile_plugin = is_plugin_active( 'jetpack/jetpack.php' ) ||
						   is_plugin_active( 'amp/amp.php' );

		// Check touch target sizes (via CSS).
		$has_touch_optimized_buttons = false;
		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			// Look for button/link padding indicating touch-friendly sizes.
			$has_touch_optimized_buttons = ( strpos( $style_content, 'min-height' ) !== false );
		}

		// Check font sizes are readable on mobile.
		$has_responsive_typography = false;
		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			$has_responsive_typography = ( strpos( $style_content, 'font-size' ) !== false ) &&
									   ( strpos( $style_content, '@media' ) !== false );
		}

		// Check for horizontal scrolling issues.
		$prevents_horizontal_scroll = false;
		if ( file_exists( $style_css ) ) {
			$style_content = file_get_contents( $style_css );
			$prevents_horizontal_scroll = ( strpos( $style_content, 'overflow-x: hidden' ) !== false ) ||
										( strpos( $style_content, 'max-width: 100%' ) !== false );
		}

		// Check for issues.
		$issues = array();

		// Issue 1: No viewport meta tag.
		if ( ! $has_viewport_meta ) {
			$issues[] = array(
				'type'        => 'no_viewport_meta',
				'description' => __( 'No viewport meta tag; mobile browsers will render at desktop width', 'wpshadow' ),
			);
		}

		// Issue 2: Viewport disables user scaling.
		if ( ! $viewport_allows_scaling ) {
			$issues[] = array(
				'type'        => 'viewport_no_scaling',
				'description' => __( 'Viewport disables user scaling; violates WCAG 1.4.4 (users cannot zoom)', 'wpshadow' ),
			);
		}

		// Issue 3: No mobile menu detected.
		if ( ! $has_mobile_menu ) {
			$issues[] = array(
				'type'        => 'no_mobile_menu',
				'description' => __( 'No mobile menu detected; navigation may be unusable on small screens', 'wpshadow' ),
			);
		}

		// Issue 4: Few or no media queries.
		if ( $media_query_count < 3 ) {
			$issues[] = array(
				'type'        => 'few_media_queries',
				'description' => sprintf(
					/* translators: %d: number of media queries */
					__( 'Only %d media queries found; responsive design may be limited', 'wpshadow' ),
					$media_query_count
				),
			);
		}

		// Issue 5: Images not responsive (no srcset).
		if ( $srcset_coverage < 50 ) {
			$issues[] = array(
				'type'        => 'no_responsive_images',
				'description' => __( 'Images lack srcset attribute; mobile users download full-size images', 'wpshadow' ),
			);
		}

		// Issue 6: Touch targets not optimized.
		if ( ! $has_touch_optimized_buttons ) {
			$issues[] = array(
				'type'        => 'small_touch_targets',
				'description' => __( 'Touch targets may be too small; buttons/links should be 44x44px minimum', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site is not properly optimized for mobile devices, providing poor experience for mobile users', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-responsiveness-testing',
				'details'      => array(
					'has_viewport_meta'       => $has_viewport_meta,
					'viewport_content'        => $viewport_content,
					'viewport_allows_scaling' => $viewport_allows_scaling,
					'has_mobile_menu'         => $has_mobile_menu,
					'media_query_count'       => $media_query_count,
					'srcset_coverage'         => round( $srcset_coverage, 1 ) . '%',
					'has_mobile_plugin'       => $has_mobile_plugin,
					'has_touch_optimized_buttons' => $has_touch_optimized_buttons,
					'has_responsive_typography' => $has_responsive_typography,
					'prevents_horizontal_scroll' => $prevents_horizontal_scroll,
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Add viewport meta, use responsive design, implement mobile menu, optimize touch targets', 'wpshadow' ),
					'viewport_meta_required'  => '<meta name="viewport" content="width=device-width, initial-scale=1">',
					'mobile_best_practices'   => array(
						'Viewport meta'       => 'Enable responsive scaling',
						'Touch targets'       => 'Minimum 44x44px for buttons/links',
						'Font sizes'          => 'Minimum 16px to prevent zoom',
						'Media queries'       => 'Breakpoints at 768px, 1024px',
						'Responsive images'   => 'Use srcset for different screen sizes',
						'Mobile menu'         => 'Hamburger menu for small screens',
						'No horizontal scroll' => 'Content fits viewport width',
					),
					'wcag_mobile_requirements' => array(
						'WCAG 1.4.4'  => 'Resize text - Allow user scaling',
						'WCAG 1.4.10' => 'Reflow - No horizontal scrolling at 320px',
						'WCAG 2.5.5'  => 'Target Size - 44x44px minimum',
					),
					'mobile_traffic_stats'    => '60%+ of web traffic is mobile devices',
					'google_mobile_first'     => 'Google uses mobile version for indexing and ranking',
				),
			);
		}

		return null;
	}
}
