<?php
/**
 * No Long-Tail Keywords Diagnostic
 *
 * Tests whether the site focuses only on competitive head terms instead of
 * long-tail keywords. Long-tail keywords represent 70% of all searches.
 *
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

/**
 * Diagnostic_No_Long_Tail_Keywords Class
 *
 * Detects when sites focus only on 1-2 word competitive terms instead of
 * 3-5 word long-tail keywords that are easier to rank for.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Long_Tail_Keywords extends Diagnostic_Base {

	protected static $slug = 'no-long-tail-keywords';
	protected static $title = 'No Long-Tail Keywords';
	protected static $description = 'Tests whether the site targets long-tail keywords';
	protected static $family = 'keyword-strategy';

	public static function check() {
		$score          = 0;
		$max_score      = 4;
		$score_details  = array();
		$recommendations = array();

		// Get sample of recent posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 30,
				'post_status'    => 'publish',
			)
		);

		$posts_checked = 0;
		$posts_with_long_titles = 0;
		$total_word_count = 0;

		foreach ( $posts as $post ) {
			++$posts_checked;
			$title = $post->post_title;
			$word_count = str_word_count( $title );
			$total_word_count += $word_count;

			// Long-tail typically 4+ words in title.
			if ( $word_count >= 4 ) {
				++$posts_with_long_titles;
			}
		}

		// Calculate average title length.
		$average_words = $posts_checked > 0 ? round( $total_word_count / $posts_checked, 1 ) : 0;

		// Score based on long-tail targeting.
		if ( $average_words >= 6 ) {
			$score = 4;
			$score_details[] = sprintf( __( '✓ Strong long-tail targeting (average %s words per title)', 'wpshadow' ), $average_words );
		} elseif ( $average_words >= 4 ) {
			$score = 3;
			$score_details[]   = sprintf( __( '✓ Moderate long-tail keywords (average %s words)', 'wpshadow' ), $average_words );
			$recommendations[] = __( 'Increase title length to 6-8 words for more specific long-tail targeting', 'wpshadow' );
		} elseif ( $average_words >= 3 ) {
			$score = 2;
			$score_details[]   = sprintf( __( '◐ Short titles (average %s words) - missing long-tail opportunity', 'wpshadow' ), $average_words );
			$recommendations[] = __( 'Target 4-7 word phrases like "how to fix WordPress login errors" vs "WordPress errors"', 'wpshadow' );
		} else {
			$score = 1;
			$score_details[]   = sprintf( __( '✗ Very short titles (average %s words) - competing for head terms only', 'wpshadow' ), $average_words );
			$recommendations[] = __( 'Shift strategy to long-tail keywords: specific, less competitive, higher conversion', 'wpshadow' );
		}

		// Check for SEO plugin with keyword tracking.
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			++$score;
			$score_details[] = __( '✓ SEO plugin active (keyword tracking available)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No SEO plugin for keyword tracking', 'wpshadow' );
			$recommendations[] = __( 'Install Yoast SEO or Rank Math to track focus keywords', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 30;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage, %s: average words */
				__( 'Long-tail keyword score: %d%% (average title: %s words). Long-tail keywords (3-5+ words) represent 70%% of all searches, convert 2.5x better, and are 50%% easier to rank for. Example: "best WordPress hosting for ecommerce" beats "WordPress hosting". Target specific user intent with detailed titles.', 'wpshadow' ),
				$score_percentage,
				$average_words
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/long-tail-keywords',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Long-tail keywords target specific user intent, face less competition, and convert better than broad head terms.', 'wpshadow' ),
		);
	}
}
