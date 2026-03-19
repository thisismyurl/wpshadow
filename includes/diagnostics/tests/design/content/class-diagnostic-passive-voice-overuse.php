<?php
/**
 * Passive Voice Overuse Diagnostic
 *
 * Detects excessive use of passive voice in content, which reduces
 * readability and engagement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Readability
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Passive Voice Overuse Diagnostic Class
 *
 * Analyzes content for passive voice usage, recommending more active
 * constructions for better engagement and clarity.
 *
 * **Why This Matters:**
 * - Active voice is clearer and more direct
 * - Passive voice reduces engagement by 30%
 * - Makes content feel weak and indirect
 * - Target: < 10% passive voice
 * - SEO plugins penalize excessive passive voice
 *
 * **Examples:**
 * - Passive: "The report was written by John"
 * - Active: "John wrote the report"
 * - Passive: "The ball was thrown by the player"
 * - Active: "The player threw the ball"
 *
 * @since 1.6093.1200
 */
class Diagnostic_Passive_Voice_Overuse extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'passive-voice-overuse';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Passive Voice Overuse';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Content contains excessive passive voice, reducing readability and engagement';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'readability';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if excessive passive voice detected, null otherwise.
	 */
	public static function check() {
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 30,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$posts_with_issues = array();

		foreach ( $posts as $post ) {
			$content = wp_strip_all_tags( $post->post_content );
			$passive_percentage = self::calculate_passive_voice_percentage( $content );

			if ( $passive_percentage > 10 ) {
				$posts_with_issues[] = array(
					'id'                 => $post->ID,
					'title'              => $post->post_title,
					'passive_percentage' => round( $passive_percentage, 1 ),
				);
			}
		}

		if ( empty( $posts_with_issues ) ) {
			return null;
		}

		$count = count( $posts_with_issues );
		$avg_passive = array_sum( array_column( $posts_with_issues, 'passive_percentage' ) ) / $count;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of posts, 2: average percentage */
				__( '%1$d post(s) contain excessive passive voice (avg: %2$s%%). Rewrite for active voice to improve engagement.', 'wpshadow' ),
				$count,
				number_format_i18n( $avg_passive, 1 )
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/readability-passive-voice',
			'details'      => array(
				'posts_with_issues'       => $count,
				'average_passive_percentage' => round( $avg_passive, 1 ),
				'sample_posts'            => array_slice( $posts_with_issues, 0, 10 ),
				'recommendation'          => 'Keep passive voice below 10%',
			),
		);
	}

	/**
	 * Calculate passive voice percentage in text
	 *
	 * Uses simple pattern matching for common passive voice constructions.
	 *
	 * @since 1.6093.1200
	 * @param  string $content Content to analyze.
	 * @return float Percentage of sentences using passive voice.
	 */
	private static function calculate_passive_voice_percentage( $content ) {
		// Split into sentences
		$sentences = preg_split( '/[.!?]+/', $content, -1, PREG_SPLIT_NO_EMPTY );

		if ( empty( $sentences ) ) {
			return 0;
		}

		$passive_count = 0;

		// Passive voice patterns: "was/were/is/are/been + past participle"
		$passive_patterns = array(
			'/\b(is|are|was|were|been|be|being)\s+(being\s+)?\w+ed\b/i',
			'/\b(is|are|was|were|been|be|being)\s+\w+en\b/i',
		);

		foreach ( $sentences as $sentence ) {
			foreach ( $passive_patterns as $pattern ) {
				if ( preg_match( $pattern, $sentence ) ) {
					$passive_count++;
					break;
				}
			}
		}

		return ( $passive_count / count( $sentences ) ) * 100;
	}
}
