<?php
/**
 * Inconsistent Content Depth Diagnostic
 *
 * Detects inconsistent content depth across posts, suggesting
 * need for editorial standards.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6034.2203
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inconsistent Content Depth Diagnostic Class
 *
 * Analyzes content length and depth variations to identify lack
 * of editorial standards or content quality guidelines.
 *
 * **Why This Matters:**
 * - Inconsistency confuses readers about expectations
 * - Signals lack of content strategy
 * - Affects brand professionalism
 * - SEO penalty for thin content mixed with quality
 * - Ideal: consistent depth per content type
 *
 * **Content Depth Guidelines:**
 * - News posts: 300-600 words
 * - Blog posts: 800-1500 words
 * - Pillar content: 2000-4000+ words
 * - Variation within 30% is acceptable
 *
 * @since 1.6034.2203
 */
class Diagnostic_Inconsistent_Content_Depth extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'inconsistent-content-depth';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inconsistent Content Depth';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Content depth varies too widely, suggesting lack of editorial standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2203
	 * @return array|null Finding array if inconsistent depth detected, null otherwise.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
			)
		);

		if ( count( $posts ) < 10 ) {
			return null; // Need sufficient sample size
		}

		$word_counts = array();
		foreach ( $posts as $post ) {
			$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
			$word_counts[] = array(
				'id'         => $post->ID,
				'title'      => $post->post_title,
				'word_count' => $word_count,
			);
		}

		// Calculate statistics
		$counts = array_column( $word_counts, 'word_count' );
		$avg = array_sum( $counts ) / count( $counts );
		$std_dev = self::calculate_std_dev( $counts );
		$coefficient_variation = ( $std_dev / $avg ) * 100;

		// Coefficient of variation > 50% indicates high inconsistency
		if ( $coefficient_variation < 50 ) {
			return null;
		}

		$min = min( $counts );
		$max = max( $counts );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: min words, 2: max words, 3: average */
				__( 'Content depth varies widely (min: %1$d words, max: %2$d words, avg: %3$d). Establish editorial guidelines for consistency.', 'wpshadow' ),
				$min,
				$max,
				round( $avg )
			),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-depth-consistency',
			'details'      => array(
				'min_words'               => $min,
				'max_words'               => $max,
				'average_words'           => round( $avg ),
				'coefficient_variation'   => round( $coefficient_variation, 1 ),
				'sample_extremes'         => array(
					'shortest' => array_slice( array_filter( $word_counts, function( $p ) use ( $min ) {
						return $p['word_count'] === $min;
					} ), 0, 3 ),
					'longest'  => array_slice( array_filter( $word_counts, function( $p ) use ( $max ) {
						return $p['word_count'] === $max;
					} ), 0, 3 ),
				),
			),
		);
	}

	/**
	 * Calculate standard deviation
	 *
	 * @since  1.6034.2203
	 * @param  array $values Array of numeric values.
	 * @return float Standard deviation.
	 */
	private static function calculate_std_dev( $values ) {
		$avg = array_sum( $values ) / count( $values );
		$sum_squares = 0;

		foreach ( $values as $value ) {
			$sum_squares += pow( $value - $avg, 2 );
		}

		return sqrt( $sum_squares / count( $values ) );
	}
}
