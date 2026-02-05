<?php
/**
 * Long-Form Content Focus Treatment
 *
 * Verifies site publishes comprehensive long-form content for depth
 * and authority building.
 *
 * @package    WPShadow
 * @subpackage Treatments\Content
 * @since      1.6034.2327
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Long-Form Content Focus Treatment Class
 *
 * Analyzes content length distribution to detect strategic use of
 * comprehensive, in-depth articles.
 *
 * **Why This Matters:**
 * - Long-form content (2000+ words) ranks 77% better
 * - Comprehensive posts earn 3.5x more backlinks
 * - Higher engagement and time-on-page
 * - Establishes authority and expertise
 * - Better for featured snippets
 *
 * **Long-Form Benefits:**
 * - More thorough keyword coverage
 * - Higher perceived value
 * - More social shares (75% more)
 * - Better conversion rates
 * - Longer content shelf life
 *
 * @since 1.6034.2327
 */
class Treatment_Publishes_Long_Form_Content extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'publishes-long-form-content';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Long-Form Content Focus';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site publishes comprehensive long-form content';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check
	 *
	 * @since  1.6034.2327
	 * @return array|null Finding array if insufficient long-form content, null otherwise.
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

		$word_counts = array(
			'short'      => 0,  // < 500 words
			'medium'     => 0,  // 500-1499 words
			'long'       => 0,  // 1500-2499 words
			'very_long'  => 0,  // 2500+ words
		);

		$total_words = 0;
		$long_form_posts = array();

		foreach ( $recent_posts as $post ) {
			$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
			$total_words += $word_count;

			if ( $word_count < 500 ) {
				$word_counts['short']++;
			} elseif ( $word_count < 1500 ) {
				$word_counts['medium']++;
			} elseif ( $word_count < 2500 ) {
				$word_counts['long']++;
				$long_form_posts[] = array(
					'id'         => $post->ID,
					'title'      => $post->post_title,
					'word_count' => $word_count,
				);
			} else {
				$word_counts['very_long']++;
				$long_form_posts[] = array(
					'id'         => $post->ID,
					'title'      => $post->post_title,
					'word_count' => $word_count,
				);
			}
		}

		$avg_word_count = $total_words / count( $recent_posts );
		$long_form_count = $word_counts['long'] + $word_counts['very_long'];
		$long_form_percentage = ( $long_form_count / count( $recent_posts ) ) * 100;

		// 25%+ long-form content (1500+ words) = good strategy
		if ( $long_form_percentage >= 25 ) {
			return null; // Adequate long-form content
		}

		$severity = 'medium';
		$threat_level = 50;

		if ( $long_form_percentage < 10 ) {
			$severity = 'high';
			$threat_level = 65;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: long-form percentage, 2: average word count */
				__( 'Insufficient long-form content (%1$d%% of posts 1500+ words, avg: %2$d words). Long-form content ranks 77%% better and earns 3.5x more backlinks.', 'wpshadow' ),
				round( $long_form_percentage ),
				round( $avg_word_count )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/long-form-content',
			'details'      => array(
				'avg_word_count'        => round( $avg_word_count ),
				'long_form_percentage'  => round( $long_form_percentage, 1 ),
				'long_form_count'       => $long_form_count,
				'distribution'          => $word_counts,
				'sample_long_form'      => array_slice( $long_form_posts, 0, 5 ),
				'recommendation'        => __( 'Target 25-30% of posts at 1500+ words for core topics', 'wpshadow' ),
				'long_form_strategy'    => array(
					'Focus long-form on pillar topics',
					'Target 2000-3000 words for guides',
					'Include table of contents',
					'Use subheadings every 300 words',
					'Add images/visuals every 500 words',
					'Provide actionable takeaways',
				),
			),
		);
	}
}
