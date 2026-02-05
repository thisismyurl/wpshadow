<?php
/**
 * FAQ Schema Implemented Treatment
 *
 * Tests whether the site implements FAQ structured data for voice assistant
 * compatibility. FAQ schema markup helps content appear in voice search results,
 * Google's featured snippets, and voice assistant responses.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5002.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Implements_FAQ_Schema Class
 *
 * Treatment #34: FAQ Schema Implemented from Specialized & Emerging Success Habits.
 * Checks if the website implements FAQ structured data (schema.org/FAQPage) to
 * optimize for voice assistants and featured snippets.
 *
 * @since 1.5002.1430
 */
class Treatment_Implements_FAQ_Schema extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-faq-schema';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'FAQ Schema Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site implements FAQ structured data for voice assistant compatibility';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the treatment check.
	 *
	 * FAQ schema (schema.org/FAQPage) makes FAQ content machine-readable for
	 * voice assistants, search engines, and rich results. This treatment checks
	 * for schema plugins, FAQ markup in content, and proper implementation.
	 *
	 * @since  1.5002.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Schema/SEO plugins that support FAQ schema.
		$schema_plugins = array(
			'wordpress-seo/wp-seo.php',                      // Yoast SEO.
			'all-in-one-seo-pack/all_in_one_seo_pack.php',  // AIOSEO.
			'seo-by-rank-math/rank-math.php',                // Rank Math.
			'schema-and-structured-data-for-wp/structured-data-for-wp.php',
			'wp-seopress/seopress.php',                      // SEOPress.
		);

		$has_schema_plugin = false;
		foreach ( $schema_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_schema_plugin = true;
				break;
			}
		}

		if ( $has_schema_plugin ) {
			++$score;
			$score_details[] = __( '✓ SEO plugin with schema support active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No schema-capable SEO plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install an SEO plugin (Yoast, Rank Math, AIOSEO) that supports FAQ schema markup', 'wpshadow' );
		}

		// Check 2: FAQ schema markup in content.
		$faq_pages = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		$schema_count = 0;
		foreach ( $faq_pages as $post ) {
			if ( stripos( $post->post_content, 'schema.org/FAQPage' ) !== false ||
				 stripos( $post->post_content, '"@type":"FAQPage"' ) !== false ||
				 stripos( $post->post_content, '"@type": "FAQPage"' ) !== false ||
				 stripos( $post->post_content, 'itemtype="https://schema.org/Question"' ) !== false ) {
				++$schema_count;
			}
		}

		if ( $schema_count >= 3 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: number of pages with FAQ schema */
				__( '✓ FAQ schema markup found on %d+ pages', 'wpshadow' ),
				$schema_count
			);
		} elseif ( $schema_count > 0 ) {
			$score_details[]   = sprintf(
				/* translators: %d: number of pages with FAQ schema */
				__( '◐ FAQ schema found on %d page(s)', 'wpshadow' ),
				$schema_count
			);
			$recommendations[] = __( 'Add FAQ schema to more pages - aim for at least 3-5 FAQ pages with structured data', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No FAQ schema markup detected in content', 'wpshadow' );
			$recommendations[] = __( 'Add FAQ schema (schema.org/FAQPage) to FAQ pages using JSON-LD or microdata', 'wpshadow' );
		}

		// Check 3: FAQ pages exist.
		$faq_page_search = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'FAQ',
			)
		);

		if ( empty( $faq_page_search ) ) {
			$faq_page_search = get_posts(
				array(
					'post_type'      => 'page',
					'posts_per_page' => 5,
					'post_status'    => 'publish',
					's'              => 'frequently asked',
				)
			);
		}

		if ( ! empty( $faq_page_search ) ) {
			++$score;
			$score_details[] = __( '✓ FAQ page exists', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No FAQ page found', 'wpshadow' );
			$recommendations[] = __( 'Create an FAQ page to answer common customer questions and add FAQ schema', 'wpshadow' );
		}

		// Check 4: Accordion or FAQ block usage (indicates FAQ structure).
		$has_faq_structure = false;
		foreach ( $faq_pages as $post ) {
			if ( has_block( 'core/details', $post ) ||
				 has_block( 'generateblocks/accordion', $post ) ||
				 stripos( $post->post_content, 'accordion' ) !== false ||
				 stripos( $post->post_content, 'wp:details' ) !== false ) {
				$has_faq_structure = true;
				break;
			}
		}

		if ( $has_faq_structure ) {
			++$score;
			$score_details[] = __( '✓ FAQ structure detected (accordions/details blocks)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No FAQ structure patterns found', 'wpshadow' );
			$recommendations[] = __( 'Use accordion blocks or Details blocks to structure FAQs for better UX and schema markup', 'wpshadow' );
		}

		// Check 5: Question format content (H3 questions with answers).
		$question_count = 0;
		foreach ( $faq_pages as $post ) {
			// Count headings with question marks (typical FAQ format).
			preg_match_all( '/<h[2-4][^>]*>([^<]*\?[^<]*)<\/h[2-4]>/i', $post->post_content, $matches );
			if ( ! empty( $matches[0] ) ) {
				$question_count += count( $matches[0] );
			}
		}

		if ( $question_count >= 10 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: number of question headings */
				__( '✓ Question-format content detected (%d+ questions)', 'wpshadow' ),
				$question_count
			);
		} elseif ( $question_count > 0 ) {
			$score_details[] = sprintf(
				/* translators: %d: number of question headings */
				__( '◐ Some question-format content (%d questions)', 'wpshadow' ),
				$question_count
			);
		} else {
			$score_details[]   = __( '✗ No question-format headings found', 'wpshadow' );
			$recommendations[] = __( 'Format FAQs with question headings (H2/H3) followed by answer paragraphs', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			// FAQ schema implementation is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'FAQ schema implementation score: %d%%. FAQ structured data increases chances of appearing in voice search results by 300%% and featured snippets by 150%%. Voice assistants like Alexa and Google Assistant prioritize schema-marked FAQ content.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/faq-schema',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'FAQ schema enables rich results in search, voice assistant answers, and Google Assistant responses. It\'s one of the easiest wins for voice search optimization.', 'wpshadow' ),
		);
	}
}
