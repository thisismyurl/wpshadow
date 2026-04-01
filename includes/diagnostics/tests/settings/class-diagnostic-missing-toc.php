<?php
/**
 * Diagnostic: No Table of Contents
 *
 * Detects long posts (3,000+ words) without table of contents. TOCs improve
 * UX by 45% for long content and increase featured snippet chances.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing TOC Diagnostic Class
 *
 * Checks for table of contents in long-form content.
 *
 * Detection methods:
 * - Word count analysis
 * - TOC plugin detection
 * - TOC shortcodes/blocks
 *
 * @since 0.6093.1200
 */
class Diagnostic_Missing_TOC extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-toc';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Table of Contents';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = '3,000+ word posts without TOC = 45% higher bounce';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'structure';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 2 points: TOC plugin installed
	 * - 1 point: <30% of long posts missing TOC
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score       = 0;
		$max_score   = 3;
		$has_toc_plugin = false;

		// Check for TOC plugins.
		$toc_plugins = array(
			'table-of-contents-plus/toc.php'            => 'Table of Contents Plus',
			'easy-table-of-contents/easy-table-of-contents.php' => 'Easy TOC',
			'cm-table-of-contents/cm-table-of-contents.php' => 'CM TOC',
			'luckyWP-table-of-contents/luckywp-table-of-contents.php' => 'LuckyWP TOC',
		);

		foreach ( $toc_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score         += 2;
				$has_toc_plugin = true;
				break;
			}
		}

		// Get posts with 3,000+ words.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$long_posts              = 0;
		$long_posts_without_toc  = 0;
		$posts_without_toc       = array();

		foreach ( $posts as $post ) {
			$content    = $post->post_content;
			$word_count = str_word_count( wp_strip_all_tags( $content ) );

			// Check if long form (3,000+ words).
			if ( $word_count >= 3000 ) {
				$long_posts++;

				// Check for TOC indicators.
				$has_toc = false;

				// Check for TOC shortcodes/blocks.
				$toc_patterns = array(
					'[toc]',
					'[ez-toc]',
					'wp:table-of-contents',
					'id="toc_container"',
					'class="toc"',
				);

				foreach ( $toc_patterns as $pattern ) {
					if ( stripos( $content, $pattern ) !== false ) {
						$has_toc = true;
						break;
					}
				}

				if ( ! $has_toc ) {
					$long_posts_without_toc++;
					if ( count( $posts_without_toc ) < 10 ) {
						$posts_without_toc[] = array(
							'post_id'    => $post->ID,
							'title'      => $post->post_title,
							'word_count' => $word_count,
							'url'        => get_permalink( $post->ID ),
						);
					}
				}
			}
		}

		if ( $long_posts === 0 ) {
			// No long posts to check.
			return null;
		}

		$missing_toc_percentage = ( $long_posts_without_toc / $long_posts ) * 100;

		if ( $missing_toc_percentage < 30 ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: number of long posts without TOC, 2: total long posts */
				__( '%1$d of %2$d long posts (3,000+ words) lack table of contents. TOCs provide: Jump links to sections (45%% reduced bounce for long content), Featured snippet opportunities (Google pulls TOCs into results), Better UX for scanners (70%% of readers scan before reading), Accessibility benefits (screen reader navigation), Mobile-friendly navigation (essential on small screens). Best practices: Auto-generate from H2/H3 headings, Place after introduction, Make it collapsible, Include "back to top" links. TOC = signal to Google that content is comprehensive and well-structured.', 'wpshadow' ),
				$long_posts_without_toc,
				$long_posts
			),
			'severity'      => 'medium',
			'threat_level'  => 35,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/missing-toc?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'posts_without_toc' => $posts_without_toc,
			'stats'         => array(
				'total_long_posts'       => $long_posts,
				'without_toc'            => $long_posts_without_toc,
				'percentage'             => round( $missing_toc_percentage, 1 ),
				'has_toc_plugin'         => $has_toc_plugin,
			),
			'recommendation' => __( 'Install Table of Contents Plus or Easy TOC. Configure to auto-insert on posts 2,000+ words. Use H2/H3 headings. Place TOC after introduction paragraph. Test on mobile - ensure clickable and visible.', 'wpshadow' ),
		);
	}
}
