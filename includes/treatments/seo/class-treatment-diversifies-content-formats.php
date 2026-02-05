<?php
/**
 * Content Format Diversity Treatment
 *
 * Tests for mix of content formats and types to maintain audience
 * engagement and reach different learning styles.
 *
 * @package    WPShadow
 * @subpackage Treatments\Content
 * @since      1.6034.2325
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Format Diversity Treatment Class
 *
 * Analyzes published content to detect variety in formats (text, video,
 * lists, how-tos, interviews, etc.) for engagement and accessibility.
 *
 * **Why This Matters:**
 * - 65% of people are visual learners
 * - Video increases engagement by 80%
 * - Mixed formats increase reach by 120%
 * - Different formats appeal to different audiences
 * - Reduces content monotony and fatigue
 *
 * **Content Format Types:**
 * - How-to guides and tutorials
 * - List posts (top 10, best of)
 * - Video content
 * - Infographics
 * - Case studies and interviews
 * - Long-form articles
 *
 * @since 1.6034.2325
 */
class Treatment_Diversifies_Content_Formats extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'diversifies-content-formats';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Content Format Diversity';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for mix of content formats and types';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2325
	 * @return array|null Finding array if low format diversity, null otherwise.
	 */
	public static function check() {
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( count( $recent_posts ) < 15 ) {
			return null; // Need sufficient content to assess
		}

		$format_counts = array(
			'video'        => 0,
			'list'         => 0,
			'how_to'       => 0,
			'interview'    => 0,
			'long_form'    => 0,
			'infographic'  => 0,
		);

		foreach ( $recent_posts as $post ) {
			$title = strtolower( $post->post_title );
			$content = strtolower( $post->post_content );
			$combined = $title . ' ' . $content;
			$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );

			// Video content
			if ( preg_match( '/youtube|vimeo|<video|<iframe.*video/', $content ) ) {
				$format_counts['video']++;
			}

			// List posts
			if ( preg_match( '/\b(top|best) \d+|\d+ (ways|tips|reasons|steps)/', $title ) ||
				 preg_match( '/<ol|<ul/', $content ) ) {
				$format_counts['list']++;
			}

			// How-to guides
			if ( preg_match( '/\bhow to\b|tutorial|guide|step[- ]by[- ]step/', $combined ) ) {
				$format_counts['how_to']++;
			}

			// Interviews/case studies
			if ( preg_match( '/interview|case study|success story|q&a|conversation with/', $combined ) ) {
				$format_counts['interview']++;
			}

			// Long-form content (2000+ words)
			if ( $word_count >= 2000 ) {
				$format_counts['long_form']++;
			}

			// Infographics
			if ( preg_match( '/infographic|<img.*infographic/', $content ) ) {
				$format_counts['infographic']++;
			}
		}

		$formats_used = 0;
		$formats_details = array();

		foreach ( $format_counts as $format => $count ) {
			if ( $count > 0 ) {
				$formats_used++;
				$formats_details[ $format ] = $count;
			}
		}

		// Need at least 4 different formats
		if ( $formats_used >= 4 ) {
			return null; // Good format diversity
		}

		$severity = 'medium';
		$threat_level = 45;

		if ( $formats_used <= 2 ) {
			$severity = 'high';
			$threat_level = 60;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of formats detected */
				__( 'Low content format diversity detected (%d of 6 formats used). Mixed formats increase engagement by 80%% and reach different learning styles.', 'wpshadow' ),
				$formats_used
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-formats',
			'details'      => array(
				'formats_used'     => $formats_used,
				'formats_detected' => $formats_details,
				'total_posts'      => count( $recent_posts ),
				'recommendation'   => __( 'Diversify content with video, lists, how-tos, and interviews', 'wpshadow' ),
				'format_ideas'     => array(
					'How-to guides and tutorials',
					'List posts (Top 10, Best of)',
					'Video content (demos, explanations)',
					'Case studies and success stories',
					'Interviews with experts',
					'Infographics and visual content',
				),
			),
		);
	}
}
