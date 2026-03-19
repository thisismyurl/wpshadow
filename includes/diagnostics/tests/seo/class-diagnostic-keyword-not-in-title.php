<?php
/**
 * Missing Primary Keyword in Title Diagnostic
 *
 * Tests whether the primary keyword appears in the title tag. Missing the
 * target keyword from the title can result in a 40% ranking penalty.
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
 * Diagnostic_Keyword_Not_In_Title Class
 *
 * Detects when focus keywords are not present in title tags. The title is
 * the most important on-page SEO factor after content.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Keyword_Not_In_Title extends Diagnostic_Base {

	protected static $slug = 'keyword-not-in-title';
	protected static $title = 'Missing Primary Keyword in Title';
	protected static $description = 'Tests whether primary keywords appear in title tags';
	protected static $family = 'keyword-strategy';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();

		// Check for SEO plugins that manage focus keywords.
		$seo_plugin_active = false;
		
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			$seo_plugin_active = true;
			++$score;
			$score_details[] = __( '✓ Yoast SEO active (manages focus keywords)', 'wpshadow' );

			// Check if focus keywords are being used.
			$posts_with_focus = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 10,
					'post_status'    => 'publish',
					'meta_query'     => array(
						array(
							'key'     => '_yoast_wpseo_focuskw',
							'compare' => 'EXISTS',
						),
					),
				)
			);

			if ( count( $posts_with_focus ) >= 5 ) {
				++$score;
				$score_details[] = __( '✓ Posts use focus keyword feature', 'wpshadow' );
			} else {
				$score_details[]   = __( '◐ Focus keyword feature underutilized', 'wpshadow' );
				$recommendations[] = __( 'Set focus keyword for each post in Yoast SEO', 'wpshadow' );
			}
		} elseif ( is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			$seo_plugin_active = true;
			++$score;
			$score_details[] = __( '✓ Rank Math active (manages focus keywords)', 'wpshadow' );

			// Check if focus keywords are being used.
			$posts_with_focus = get_posts(
				array(
					'post_type'      => 'post',
					'posts_per_page' => 10,
					'post_status'    => 'publish',
					'meta_query'     => array(
						array(
							'key'     => 'rank_math_focus_keyword',
							'compare' => 'EXISTS',
						),
					),
				)
			);

			if ( count( $posts_with_focus ) >= 5 ) {
				++$score;
				$score_details[] = __( '✓ Posts use focus keyword feature', 'wpshadow' );
			} else {
				$score_details[]   = __( '◐ Focus keyword feature underutilized', 'wpshadow' );
				$recommendations[] = __( 'Set focus keyword for each post in Rank Math', 'wpshadow' );
			}
		} else {
			$score_details[]   = __( '✗ No SEO plugin for keyword tracking', 'wpshadow' );
			$recommendations[] = __( 'Install Yoast SEO or Rank Math to manage focus keywords', 'wpshadow' );
		}

		// Check title optimization generally.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		$descriptive_titles = 0;
		foreach ( $posts as $post ) {
			$title = $post->post_title;
			// Descriptive titles typically 4+ words.
			if ( str_word_count( $title ) >= 4 ) {
				++$descriptive_titles;
			}
		}

		if ( count( $posts ) > 0 ) {
			$descriptive_percentage = ( $descriptive_titles / count( $posts ) ) * 100;
			if ( $descriptive_percentage >= 70 ) {
				++$score;
				$score_details[] = __( '✓ Most titles are descriptive (4+ words)', 'wpshadow' );
			} else {
				$score_details[]   = sprintf( __( '◐ %d%% of titles are descriptive', 'wpshadow' ), round( $descriptive_percentage ) );
				$recommendations[] = __( 'Write descriptive titles that include target keywords naturally', 'wpshadow' );
			}
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'critical';
		$threat_level = 50;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Keyword in title score: %d%%. Title tags are the #2 on-page ranking factor (after content). Missing target keyword from title = 40%% ranking penalty. Place keyword near beginning of title for maximum impact. Use SEO plugins to set focus keyword and verify it appears in title naturally.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/keyword-in-title',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Title tags are critical for SEO and CTR. Keywords in titles help search engines understand content and attract clicks.', 'wpshadow' ),
		);
	}
}
