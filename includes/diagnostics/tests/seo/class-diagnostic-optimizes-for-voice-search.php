<?php
/**
 * Voice Search Optimized Diagnostic
 *
 * Tests whether the site optimizes content for voice search queries with
 * conversational language and featured snippets. Voice search optimization
 * captures the growing segment of users searching via voice assistants.
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
 * Diagnostic_Optimizes_For_Voice_Search Class
 *
 * Diagnostic #33: Voice Search Optimized from Specialized & Emerging Success Habits.
 * Checks if the website optimizes content for voice search with conversational
 * language, question-format headings, and featured snippet optimization.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Optimizes_For_Voice_Search extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'optimizes-for-voice-search';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Voice Search Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site optimizes content for voice search queries with conversational language and featured snippets';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the diagnostic check.
	 *
	 * Voice search queries are typically longer, conversational, and question-based.
	 * This diagnostic checks for question-format content, conversational headings,
	 * structured data, and featured snippet optimization.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 6;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Question-format headings (how, what, why, when, where).
		$recent_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$question_keywords = array( 'how to', 'what is', 'why is', 'when to', 'where to', 'who is', 'can i', 'should i' );
		$question_count = 0;

		foreach ( $recent_posts as $post ) {
			$title_lower = strtolower( $post->post_title );
			foreach ( $question_keywords as $keyword ) {
				if ( stripos( $title_lower, $keyword ) !== false ) {
					++$question_count;
					break;
				}
			}
		}

		if ( $question_count >= 5 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: number of question-format posts */
				__( '✓ Question-format content detected (%d+ posts with conversational titles)', 'wpshadow' ),
				$question_count
			);
		} elseif ( $question_count > 0 ) {
			$score_details[]   = sprintf(
				/* translators: %d: number of question-format posts */
				__( '◐ Some question-format content (%d posts)', 'wpshadow' ),
				$question_count
			);
			$recommendations[] = __( 'Create more content with conversational question titles like "How to...", "What is...", "Why should..."', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No question-format content found', 'wpshadow' );
			$recommendations[] = __( 'Optimize for voice search by writing conversational question-based headlines', 'wpshadow' );
		}

		// Check 2: Long-tail keywords (voice searches are typically 3+ words).
		$long_title_count = 0;
		foreach ( $recent_posts as $post ) {
			$word_count = str_word_count( $post->post_title );
			if ( $word_count >= 6 ) {
				++$long_title_count;
			}
		}

		if ( $long_title_count >= 5 ) {
			++$score;
			$score_details[] = __( '✓ Long-tail titles detected (6+ words - natural for voice queries)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Few long-tail titles found', 'wpshadow' );
			$recommendations[] = __( 'Use longer, conversational titles (6+ words) that mirror how people speak', 'wpshadow' );
		}

		// Check 3: Featured snippet optimization (paragraph answers, lists, tables).
		$snippet_optimized = 0;
		foreach ( $recent_posts as $post ) {
			$content = $post->post_content;
			// Check for early paragraph answers (first 300 characters).
			$first_para = wp_trim_words( wp_strip_all_tags( $content ), 50 );
			if ( strlen( $first_para ) >= 150 && strlen( $first_para ) <= 300 ) {
				++$snippet_optimized;
			}
		}

		if ( $snippet_optimized >= 5 ) {
			++$score;
			$score_details[] = __( '✓ Content optimized for featured snippets (concise opening paragraphs)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Content not optimized for featured snippets', 'wpshadow' );
			$recommendations[] = __( 'Start posts with 40-60 word answers that directly address the question in the title', 'wpshadow' );
		}

		// Check 4: Local business schema (voice searches are often local).
		$has_local_schema = false;
		foreach ( $recent_posts as $post ) {
			if ( stripos( $post->post_content, 'schema.org/LocalBusiness' ) !== false ||
				 stripos( $post->post_content, '"@type":"LocalBusiness"' ) !== false ) {
				$has_local_schema = true;
				break;
			}
		}

		if ( $has_local_schema ) {
			++$score;
			$score_details[] = __( '✓ Local Business schema detected (voice searches are 58% local)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No Local Business schema found', 'wpshadow' );
			$recommendations[] = __( 'Add Local Business schema if you serve a local area - most voice searches are local', 'wpshadow' );
		}

		// Check 5: Mobile-friendliness (98% of voice searches are mobile).
		$theme = wp_get_theme();
		$responsive_keywords = array( 'responsive', 'mobile', 'adaptive', 'fluid' );
		$is_mobile_friendly = false;
		$theme_description  = (string) $theme->get( 'Description' );
		$theme_tags         = $theme->get( 'Tags' );
		$theme_tags_text    = is_array( $theme_tags ) ? implode( ' ', $theme_tags ) : (string) $theme_tags;

		foreach ( $responsive_keywords as $keyword ) {
			if ( stripos( $theme_description, $keyword ) !== false ||
				 stripos( $theme_tags_text, $keyword ) !== false ) {
				$is_mobile_friendly = true;
				break;
			}
		}

		// Also check for viewport meta tag.
		$has_viewport = false;
		ob_start();
		wp_head();
		$head_content = ob_get_clean();
		if ( stripos( $head_content, 'viewport' ) !== false ) {
			$has_viewport = true;
		}

		if ( $is_mobile_friendly || $has_viewport ) {
			++$score;
			$score_details[] = __( '✓ Mobile-responsive theme (essential for voice search)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Theme may not be fully mobile-responsive', 'wpshadow' );
			$recommendations[] = __( 'Ensure your theme is mobile-responsive - 98% of voice searches happen on mobile', 'wpshadow' );
		}

		// Check 6: Site speed (voice search favors fast sites).
		$has_caching = ( is_plugin_active( 'wp-super-cache/wp-cache.php' ) ||
						 is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ||
						 is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' ) ||
						 is_plugin_active( 'wp-rocket/wp-rocket.php' ) ||
						 is_plugin_active( 'litespeed-cache/litespeed-cache.php' ) );

		if ( $has_caching ) {
			++$score;
			$score_details[] = __( '✓ Caching plugin active (site speed is critical for voice search)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No caching plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a caching plugin (WP Rocket, LiteSpeed Cache) - voice search prioritizes fast sites', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 35 ) {
			$severity     = 'medium';
			$threat_level = 30;
		} elseif ( $score_percentage < 65 ) {
			$severity     = 'low';
			$threat_level = 20;
		} else {
			// Voice search optimization is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Voice search optimization score: %d%%. By 2024, 50%% of all searches will be voice-based. Voice queries are 3x longer than text searches and favor conversational, question-based content. Featured snippet optimization increases voice search visibility by 400%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/voice-search-optimization?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Voice search optimization captures early-stage queries and informational searches, driving high-intent traffic to your site.', 'wpshadow' ),
		);
	}
}
