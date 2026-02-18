<?php
/**
 * Video Sales Letters Diagnostic
 *
 * Tests whether the site uses video to explain products and increase conversion rates.
 *
 * @since   1.6034.0230
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Sales Letters Diagnostic Class
 *
 * Video sales letters (VSLs) can increase conversion rates by 80%+ compared
 * to text-only pages. They're particularly effective for complex products.
 *
 * @since 1.6034.0230
 */
class Diagnostic_Video_Sales_Letters extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-sales-letters';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Sales Letters';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses video to explain products and increase conversion rates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$vsl_score = 0;
		$max_score = 7;

		// Check for video hosting.
		$video_hosting = self::check_video_hosting();
		if ( $video_hosting ) {
			$vsl_score++;
		} else {
			$issues[] = __( 'No video hosting or embedding functionality detected', 'wpshadow' );
		}

		// Check for sales videos on key pages.
		$sales_videos = self::check_sales_videos();
		if ( $sales_videos ) {
			$vsl_score++;
		} else {
			$issues[] = __( 'No sales videos on landing or product pages', 'wpshadow' );
		}

		// Check video placement.
		$video_placement = self::check_video_placement();
		if ( $video_placement ) {
			$vsl_score++;
		} else {
			$issues[] = __( 'Videos not strategically placed above the fold', 'wpshadow' );
		}

		// Check for CTAs in videos.
		$video_ctas = self::check_video_ctas();
		if ( $video_ctas ) {
			$vsl_score++;
		} else {
			$issues[] = __( 'No clear call-to-action within or after videos', 'wpshadow' );
		}

		// Check video analytics.
		$video_analytics = self::check_video_analytics();
		if ( $video_analytics ) {
			$vsl_score++;
		} else {
			$issues[] = __( 'No video engagement tracking or analytics', 'wpshadow' );
		}

		// Check mobile optimization.
		$mobile_video = self::check_mobile_video();
		if ( $mobile_video ) {
			$vsl_score++;
		} else {
			$issues[] = __( 'Videos may not be optimized for mobile playback', 'wpshadow' );
		}

		// Check for testimonial videos.
		$testimonial_videos = self::check_testimonial_videos();
		if ( $testimonial_videos ) {
			$vsl_score++;
		} else {
			$issues[] = __( 'No video testimonials to build trust', 'wpshadow' );
		}

		// Determine severity based on VSL implementation.
		$vsl_percentage = ( $vsl_score / $max_score ) * 100;

		if ( $vsl_percentage < 30 ) {
			$severity = 'low';
			$threat_level = 35;
		} elseif ( $vsl_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: VSL implementation percentage */
				__( 'Video sales letter strategy at %d%%. ', 'wpshadow' ),
				(int) $vsl_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'VSLs can increase conversions by 80%+', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-sales-letters',
			);
		}

		return null;
	}

	/**
	 * Check for video hosting.
	 *
	 * @since  1.6034.0230
	 * @return bool True if video hosting exists, false otherwise.
	 */
	private static function check_video_hosting() {
		// Check for video plugins.
		$video_plugins = array(
			'video-embed-thumbnail-generator/video-embed-thumbnail-generator.php',
			'youtube-embed-plus/youtube.php',
			'vimeo-master/vimeo.php',
			'easy-video-player/easy-video-player.php',
		);

		foreach ( $video_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for video embeds in content.
		$pages = get_posts(
			array(
				'post_type'      => array( 'post', 'page', 'product' ),
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		foreach ( $pages as $page ) {
			if ( has_shortcode( $page->post_content, 'video' ) ||
				 has_shortcode( $page->post_content, 'youtube' ) ||
				 has_shortcode( $page->post_content, 'vimeo' ) ||
				 strpos( $page->post_content, 'youtube.com' ) !== false ||
				 strpos( $page->post_content, 'vimeo.com' ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_video_hosting', false );
	}

	/**
	 * Check for sales videos.
	 *
	 * @since  1.6034.0230
	 * @return bool True if sales videos exist, false otherwise.
	 */
	private static function check_sales_videos() {
		// Check landing pages for videos.
		$keywords = array( 'watch video', 'see how it works', 'product demo', 'explainer video' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'product' ),
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		// Check WooCommerce product videos.
		if ( class_exists( 'WooCommerce' ) ) {
			$products = get_posts(
				array(
					'post_type'      => 'product',
					'posts_per_page' => 10,
					'post_status'    => 'publish',
				)
			);

			foreach ( $products as $product ) {
				if ( strpos( $product->post_content, 'youtube.com' ) !== false ||
					 strpos( $product->post_content, 'vimeo.com' ) !== false ||
					 has_shortcode( $product->post_content, 'video' ) ) {
					return true;
				}
			}
		}

		return apply_filters( 'wpshadow_has_sales_videos', false );
	}

	/**
	 * Check video placement.
	 *
	 * @since  1.6034.0230
	 * @return bool True if strategic placement exists, false otherwise.
	 */
	private static function check_video_placement() {
		// Check for videos in header/hero sections.
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
			)
		);

		foreach ( $pages as $page ) {
			$content = $page->post_content;
			// Check if video appears early in content (first 500 characters).
			$early_content = substr( $content, 0, 500 );
			if ( strpos( $early_content, 'youtube' ) !== false ||
				 strpos( $early_content, 'vimeo' ) !== false ||
				 strpos( $early_content, '[video' ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_strategic_video_placement', false );
	}

	/**
	 * Check for CTAs in videos.
	 *
	 * @since  1.6034.0230
	 * @return bool True if video CTAs exist, false otherwise.
	 */
	private static function check_video_ctas() {
		$cta_keywords = array( 'buy now', 'get started', 'learn more', 'sign up', 'order today' );

		// Check pages with videos for nearby CTAs.
		$pages = get_posts(
			array(
				'post_type'      => array( 'page', 'product' ),
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		foreach ( $pages as $page ) {
			$has_video = ( strpos( $page->post_content, 'youtube' ) !== false ||
						  strpos( $page->post_content, 'vimeo' ) !== false ||
						  has_shortcode( $page->post_content, 'video' ) );

			if ( $has_video ) {
				foreach ( $cta_keywords as $cta ) {
					if ( stripos( $page->post_content, $cta ) !== false ) {
						return true;
					}
				}
			}
		}

		return apply_filters( 'wpshadow_has_video_ctas', false );
	}

	/**
	 * Check video analytics.
	 *
	 * @since  1.6034.0230
	 * @return bool True if analytics exist, false otherwise.
	 */
	private static function check_video_analytics() {
		// Check for analytics plugins.
		if ( is_plugin_active( 'google-site-kit/google-site-kit.php' ) ||
			 is_plugin_active( 'matomo/matomo.php' ) ) {
			return true;
		}

		// Wistia and Vimeo Pro have built-in analytics.
		$pages = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		foreach ( $pages as $page ) {
			if ( strpos( $page->post_content, 'wistia' ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_video_analytics', false );
	}

	/**
	 * Check mobile video optimization.
	 *
	 * @since  1.6034.0230
	 * @return bool True if mobile-optimized, false otherwise.
	 */
	private static function check_mobile_video() {
		// Check if theme is responsive.
		$theme = wp_get_theme();
		$theme_tags = $theme->get( 'Tags' );

		if ( is_array( $theme_tags ) && in_array( 'responsive', array_map( 'strtolower', $theme_tags ), true ) ) {
			return true;
		}

		// YouTube and Vimeo embeds are responsive by default with modern themes.
		if ( is_plugin_active( 'jetpack/jetpack.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_videos_mobile_optimized', true );
	}

	/**
	 * Check for testimonial videos.
	 *
	 * @since  1.6034.0230
	 * @return bool True if testimonial videos exist, false otherwise.
	 */
	private static function check_testimonial_videos() {
		$keywords = array( 'customer testimonial', 'review video', 'customer story', 'success story' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				// Check if page actually has video.
				$post = $query->posts[0];
				if ( strpos( $post->post_content, 'youtube' ) !== false ||
					 strpos( $post->post_content, 'vimeo' ) !== false ||
					 has_shortcode( $post->post_content, 'video' ) ) {
					return true;
				}
			}
		}

		return apply_filters( 'wpshadow_has_testimonial_videos', false );
	}
}
