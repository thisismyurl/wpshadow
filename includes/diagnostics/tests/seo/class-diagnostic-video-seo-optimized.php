<?php
/**
 * Video SEO Optimized Diagnostic
 *
 * Tests whether the site optimizes videos for search discovery with proper titles, descriptions, and tags.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video SEO Optimized Diagnostic Class
 *
 * Optimized video SEO increases discovery by 350%. Proper metadata is critical
 * for both YouTube and Google video search rankings.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Video_Seo_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'video-seo-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video SEO Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site optimizes videos for search discovery with proper titles, descriptions, and tags';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'video-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$seo_score = 0;
		$max_score = 6;

		// Check for video schema.
		$video_schema = self::check_video_schema();
		if ( $video_schema ) {
			$seo_score++;
		} else {
			$issues[] = __( 'No video schema markup for search engines', 'wpshadow' );
		}

		// Check for descriptive titles.
		$descriptive_titles = self::check_descriptive_titles();
		if ( $descriptive_titles ) {
			$seo_score++;
		} else {
			$issues[] = __( 'Video content lacks descriptive, keyword-rich titles', 'wpshadow' );
		}

		// Check for video descriptions.
		$video_descriptions = self::check_video_descriptions();
		if ( $video_descriptions ) {
			$seo_score++;
		} else {
			$issues[] = __( 'Videos lack detailed descriptions with keywords', 'wpshadow' );
		}

		// Check for video sitemap.
		$video_sitemap = self::check_video_sitemap();
		if ( $video_sitemap ) {
			$seo_score++;
		} else {
			$issues[] = __( 'No video sitemap for search engine discovery', 'wpshadow' );
		}

		// Check for transcripts.
		$transcripts = self::check_transcripts();
		if ( $transcripts ) {
			$seo_score++;
		} else {
			$issues[] = __( 'No video transcripts for SEO and accessibility', 'wpshadow' );
		}

		// Check for video hosting.
		$optimized_hosting = self::check_optimized_hosting();
		if ( $optimized_hosting ) {
			$seo_score++;
		} else {
			$issues[] = __( 'Videos not hosted on SEO-friendly platform', 'wpshadow' );
		}

		// Determine severity based on video SEO.
		$seo_percentage = ( $seo_score / $max_score ) * 100;

		if ( $seo_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $seo_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Video SEO optimization percentage */
				__( 'Video SEO optimization at %d%%. ', 'wpshadow' ),
				(int) $seo_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Optimized video SEO increases discovery by 350%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/video-seo-optimized',
			);
		}

		return null;
	}

	/**
	 * Check video schema.
	 *
	 * @since 1.6093.1200
	 * @return bool True if schema exists, false otherwise.
	 */
	private static function check_video_schema() {
		// Check for schema plugin.
		if ( is_plugin_active( 'wp-seopress/seopress.php' ) ||
			 is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_video_schema', false );
	}

	/**
	 * Check descriptive titles.
	 *
	 * @since 1.6093.1200
	 * @return bool True if titles optimized, false otherwise.
	 */
	private static function check_descriptive_titles() {
		// Check for video posts with proper titles.
		$videos = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				's'              => 'video',
			)
		);

		foreach ( $videos as $video ) {
			// Good titles are typically longer than 40 chars.
			if ( strlen( $video->post_title ) > 40 ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check video descriptions.
	 *
	 * @since 1.6093.1200
	 * @return bool True if descriptions exist, false otherwise.
	 */
	private static function check_video_descriptions() {
		// Check for video content with descriptions.
		$query = new \WP_Query(
			array(
				's'              => 'video watch youtube',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		if ( $query->have_posts() ) {
			$query->the_post();
			$content = get_the_content();
			wp_reset_postdata();
			return ( strlen( $content ) > 200 );
		}

		return false;
	}

	/**
	 * Check video sitemap.
	 *
	 * @since 1.6093.1200
	 * @return bool True if sitemap exists, false otherwise.
	 */
	private static function check_video_sitemap() {
		// Yoast and SEOPress support video sitemaps.
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ||
			 is_plugin_active( 'wp-seopress/seopress.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_video_sitemap', false );
	}

	/**
	 * Check transcripts.
	 *
	 * @since 1.6093.1200
	 * @return bool True if transcripts exist, false otherwise.
	 */
	private static function check_transcripts() {
		// Check for transcript content.
		$query = new \WP_Query(
			array(
				's'              => 'transcript video text version',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check optimized hosting.
	 *
	 * @since 1.6093.1200
	 * @return bool True if hosting optimized, false otherwise.
	 */
	private static function check_optimized_hosting() {
		// YouTube and Vimeo are SEO-friendly.
		$query = new \WP_Query(
			array(
				's'              => 'youtube.com vimeo.com',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}
}
