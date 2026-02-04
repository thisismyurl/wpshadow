<?php
/**
 * No Video Content Strategy Diagnostic
 *
 * Checks if video content strategy is in place.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Content Strategy Diagnostic
 *
 * Video content gets 1200% more shares than text+images, and viewers
 * retain 95% of video information vs 10% of text.
 *
 * @since 1.6035.0000
 */
class Diagnostic_No_Video_Content_Strategy extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-video-content-strategy';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Video Content Strategy';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if video content strategy is in place';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since  1.6035.0000
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_video_strategy() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No video content strategy detected. Video content gets 1200% more shares than text+image, and viewers retain 95% of video info vs 10% of text. YouTube is the 2nd largest search engine. Start: 1) Educational (teaching customers your process), 2) Testimonial (customers sharing results), 3) Product demos (how to use), 4) Explainers (why problem matters), 5) Tutorials (step-by-step guides), 6) Behind-the-scenes (build trust, show team). Upload to YouTube, embed on site. Add transcripts (accessibility + SEO). Optimize titles/descriptions for search. Video changes everything about engagement and conversion.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/video-content-strategy',
				'details'     => array(
					'issue'               => __( 'No video content strategy detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement video content across product demos, testimonials, and education', 'wpshadow' ),
					'business_impact'     => __( 'Missing 1200% share increase and 95% retention vs 10% for text', 'wpshadow' ),
					'video_types'         => self::get_video_types(),
					'video_platforms'     => self::get_video_platforms(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if video strategy exists.
	 *
	 * @since  1.6035.0000
	 * @return bool True if strategy detected, false otherwise.
	 */
	private static function has_video_strategy() {
		// Check for video-related content
		$video_posts = self::count_posts_by_keywords(
			array(
				'video',
				'youtube',
				'vimeo',
				'tutorial',
				'webinar',
				'demo',
			)
		);

		if ( $video_posts > 0 ) {
			return true;
		}

		// Check for video plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$video_keywords = array(
			'video',
			'youtube',
			'vimeo',
			'wistia',
			'kaltura',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $video_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					if ( is_plugin_active( $plugin_file ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since  1.6035.0000
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get video types to create.
	 *
	 * @since  1.6035.0000
	 * @return array Video content types with descriptions.
	 */
	private static function get_video_types() {
		return array(
			'educational'      => __( 'Educational: Teaching customers your process (builds authority)', 'wpshadow' ),
			'testimonial'      => __( 'Testimonial: Customers sharing results (builds trust)', 'wpshadow' ),
			'demo'             => __( 'Product Demo: How to use your product (drives adoption)', 'wpshadow' ),
			'explainer'        => __( 'Explainer: Why the problem matters (educates market)', 'wpshadow' ),
			'tutorial'         => __( 'Tutorial: Step-by-step guides (builds audience)', 'wpshadow' ),
			'behind_the_scenes' => __( 'Behind-the-Scenes: Show your team and process (builds connection)', 'wpshadow' ),
			'case_study'       => __( 'Case Study: Detailed customer success story (builds credibility)', 'wpshadow' ),
			'announcement'     => __( 'Announcement: New features, news, updates (engages audience)', 'wpshadow' ),
		);
	}

	/**
	 * Get video platforms.
	 *
	 * @since  1.6035.0000
	 * @return array Video platforms with strategies.
	 */
	private static function get_video_platforms() {
		return array(
			'youtube'  => array(
				'name'     => __( 'YouTube (2nd largest search engine)', 'wpshadow' ),
				'strategy' => __( 'Upload consistently (weekly), optimize titles/descriptions, create playlists', 'wpshadow' ),
			),
			'website'  => array(
				'name'     => __( 'Your Website (highest conversion)', 'wpshadow' ),
				'strategy' => __( 'Embed videos on product pages, homepage, about page', 'wpshadow' ),
			),
			'email'    => array(
				'name'     => __( 'Email (increases click-through)', 'wpshadow' ),
				'strategy' => __( 'Send video thumbnails with compelling subject lines', 'wpshadow' ),
			),
			'social'   => array(
				'name'     => __( 'Social Media (viral potential)', 'wpshadow' ),
				'strategy' => __( 'Repurpose for TikTok, Instagram Reels, LinkedIn (short clips)', 'wpshadow' ),
			),
		);
	}
}
