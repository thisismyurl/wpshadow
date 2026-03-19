<?php
/**
 * Keyword Cannibalization Diagnostic
 *
 * Tests for keyword cannibalization where multiple pages compete for the same
 * keyword, causing both pages to rank worse.
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
 * Diagnostic_Keyword_Cannibalization Class
 *
 * Detects when multiple pages target the same keyword, splitting ranking
 * power and confusing search engines about which page to rank.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Keyword_Cannibalization extends Diagnostic_Base {

	protected static $slug = 'keyword-cannibalization';
	protected static $title = 'Keyword Cannibalization';
	protected static $description = 'Tests for multiple pages competing for same keywords';
	protected static $family = 'keyword-strategy';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();
		$duplicate_keywords = array();

		// Get posts with focus keywords (if SEO plugin active).
		$focus_keywords = array();
		
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			++$score;
			
			// Get posts with Yoast focus keywords.
			$posts = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 100,
					'post_status'    => 'publish',
					'meta_query'     => array(
						array(
							'key'     => '_yoast_wpseo_focuskw',
							'compare' => 'EXISTS',
						),
					),
				)
			);

			foreach ( $posts as $post ) {
				$focus_kw = get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true );
				if ( ! empty( $focus_kw ) ) {
					$focus_kw = strtolower( trim( $focus_kw ) );
					if ( ! isset( $focus_keywords[ $focus_kw ] ) ) {
						$focus_keywords[ $focus_kw ] = array();
					}
					$focus_keywords[ $focus_kw ][] = array(
						'title' => $post->post_title,
						'url'   => get_permalink( $post ),
					);
				}
			}

			// Find duplicates.
			foreach ( $focus_keywords as $keyword => $posts_array ) {
				if ( count( $posts_array ) > 1 ) {
					$duplicate_keywords[ $keyword ] = $posts_array;
				}
			}

			if ( empty( $duplicate_keywords ) ) {
				++$score;
				$score_details[] = __( '✓ No keyword cannibalization detected', 'wpshadow' );
			} else {
				$score_details[]   = sprintf( __( '✗ %d keyword(s) targeted by multiple posts', 'wpshadow' ), count( $duplicate_keywords ) );
				$recommendations[] = __( 'Consolidate or differentiate content targeting the same keywords', 'wpshadow' );
			}
		} elseif ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			++$score;
			
			// Get posts with Rank Math focus keywords.
			$posts = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 100,
					'post_status'    => 'publish',
					'meta_query'     => array(
						array(
							'key'     => 'rank_math_focus_keyword',
							'compare' => 'EXISTS',
						),
					),
				)
			);

			foreach ( $posts as $post ) {
				$focus_kw = get_post_meta( $post->ID, 'rank_math_focus_keyword', true );
				if ( ! empty( $focus_kw ) ) {
					$focus_kw = strtolower( trim( $focus_kw ) );
					if ( ! isset( $focus_keywords[ $focus_kw ] ) ) {
						$focus_keywords[ $focus_kw ] = array();
					}
					$focus_keywords[ $focus_kw ][] = array(
						'title' => $post->post_title,
						'url'   => get_permalink( $post ),
					);
				}
			}

			// Find duplicates.
			foreach ( $focus_keywords as $keyword => $posts_array ) {
				if ( count( $posts_array ) > 1 ) {
					$duplicate_keywords[ $keyword ] = $posts_array;
				}
			}

			if ( empty( $duplicate_keywords ) ) {
				++$score;
				$score_details[] = __( '✓ No keyword cannibalization detected', 'wpshadow' );
			} else {
				$score_details[]   = sprintf( __( '✗ %d keyword(s) targeted by multiple posts', 'wpshadow' ), count( $duplicate_keywords ) );
				$recommendations[] = __( 'Consolidate or differentiate content targeting the same keywords', 'wpshadow' );
			}
		} else {
			$score_details[]   = __( '✗ No SEO plugin - cannot detect keyword cannibalization', 'wpshadow' );
			$recommendations[] = __( 'Install Yoast SEO or Rank Math to track focus keywords and detect cannibalization', 'wpshadow' );
		}

		// Check for duplicate titles (another cannibalization indicator).
		$all_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
				'post_status'    => 'publish',
			)
		);

		$titles = array();
		$duplicate_titles = 0;

		foreach ( $all_posts as $post ) {
			$title = strtolower( trim( $post->post_title ) );
			if ( isset( $titles[ $title ] ) ) {
				++$duplicate_titles;
			}
			$titles[ $title ] = true;
		}

		if ( $duplicate_titles === 0 ) {
			++$score;
			$score_details[] = __( '✓ All post titles are unique', 'wpshadow' );
		} else {
			$score_details[]   = sprintf( __( '◐ %d duplicate title(s) found', 'wpshadow' ), $duplicate_titles );
			$recommendations[] = __( 'Make all titles unique to avoid content overlap', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'critical';
		$threat_level = 45;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Keyword cannibalization score: %d%%. When multiple pages target the same keyword, they compete against each other - both rank worse. Fix: consolidate similar content or differentiate keywords. Example: "WordPress SEO" on one page, "WordPress SEO plugins" on another. Cannibalization splits ranking power 50/50 instead of 100%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/keyword-cannibalization',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'problem_examples' => array_slice( $duplicate_keywords, 0, 5 ),
			'impact'           => __( 'Keyword cannibalization confuses search engines and splits ranking power between competing pages, resulting in lower rankings for all.', 'wpshadow' ),
		);
	}
}
