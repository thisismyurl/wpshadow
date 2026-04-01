<?php
/**
 * Diagnostic: No Authority Links
 *
 * Detects absence of links to credible sources. Google values pages that
 * cite authoritative references.
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
 * No Authority Links Diagnostic Class
 *
 * Checks for citations to high-authority sources.
 *
 * Detection methods:
 * - Authority domain detection (.gov, .edu)
 * - Major publication identification
 * - External link analysis
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Authority_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-authority-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Authority Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Zero links to credible sources - Google values pages citing authorities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'external-linking';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 3 points: ≥60% posts have authority links
	 * - 2 points: ≥40% have authority links
	 * - 0 points: <40% have authority links
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score                   = 0;
		$max_score               = 3;
		$posts_with_authority    = 0;
		$posts_without_authority = array();

		// Authority domain patterns.
		$authority_patterns = array(
			'.gov',
			'.edu',
			'wikipedia.org',
			'nytimes.com',
			'forbes.com',
			'techcrunch.com',
			'wired.com',
			'bbc.com',
			'cnn.com',
			'reuters.com',
			'harvard.edu',
			'stanford.edu',
			'mit.edu',
			'nih.gov',
			'cdc.gov',
		);

		// Get sample posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 40,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		foreach ( $posts as $post ) {
			$content    = $post->post_content;
			$word_count = str_word_count( wp_strip_all_tags( $content ) );

			// Only check substantial posts (500+ words).
			if ( $word_count < 500 ) {
				continue;
			}

			// Extract external links.
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches );

			$has_authority_link = false;

			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					// Skip internal links.
					if ( strpos( $url, home_url() ) !== false || strpos( $url, '/' ) === 0 ) {
						continue;
					}

					// Check if URL matches authority patterns.
					$url_lower = strtolower( $url );
					foreach ( $authority_patterns as $pattern ) {
						if ( strpos( $url_lower, $pattern ) !== false ) {
							$has_authority_link = true;
							break 2;
						}
					}
				}
			}

			if ( $has_authority_link ) {
				$posts_with_authority++;
			} else {
				if ( count( $posts_without_authority ) < 10 ) {
					$posts_without_authority[] = array(
						'post_id'    => $post->ID,
						'title'      => $post->post_title,
						'word_count' => $word_count,
						'url'        => get_permalink( $post->ID ),
					);
				}
			}
		}

		$checked_posts = count( $posts );
		$authority_percentage = $checked_posts > 0 ? ( $posts_with_authority / $checked_posts ) * 100 : 0;

		// Scoring.
		if ( $authority_percentage >= 60 ) {
			$score = 3;
		} elseif ( $authority_percentage >= 40 ) {
			$score = 2;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: percentage of posts with authority links */
				__( 'Only %d%% of posts cite authoritative sources. Linking to authorities provides: Trust signals (you\'ve done research), Context and credibility (backing claims with sources), E-A-T boost (Expertise, Authoritativeness, Trustworthiness), Better user experience (readers can verify claims), SEO benefits (outbound links to relevant, quality sites help rankings). Authority types: Government (.gov) - official statistics, regulations, Educational (.edu) - research, academic papers, Major publications (NYT, Forbes, BBC) - news, analysis, Industry leaders - niche-specific authorities, Primary sources - original research, official documentation. When to link: Citing statistics (link to source), Making claims (support with evidence), Technical info (link to documentation), Best practices (reference industry standards). Quality > quantity - 1-3 authority links per 1,000 words.', 'wpshadow' ),
				round( $authority_percentage )
			),
			'severity'      => 'medium',
			'threat_level'  => 35,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/no-authority-links?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'posts_without_authority' => $posts_without_authority,
			'stats'         => array(
				'posts_checked'        => $checked_posts,
				'with_authority'       => $posts_with_authority,
				'authority_percentage' => round( $authority_percentage, 1 ),
			),
			'recommendation' => __( 'Add 1-3 authority citations per post. Link to .gov/.edu when citing statistics. Reference industry publications for trends. Use primary sources for technical information. Always relevant, never just for SEO.', 'wpshadow' ),
		);
	}
}
