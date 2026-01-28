<?php
/**
 * Thin Content Pages Diagnostic
 *
 * Identifies pages with insufficient content that provide poor user
 * value and trigger Panda algorithm quality penalties.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Thin_Content_Pages Class
 *
 * Detects pages with insufficient content.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Thin_Content_Pages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'thin-content-pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Thin Content Pages';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies pages with insufficient content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if thin content found, null otherwise.
	 */
	public static function check() {
		$thin_pages = self::find_thin_content();

		if ( $thin_pages['count'] === 0 ) {
			return null; // No thin content
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of thin pages */
				__( '%d pages have thin content (<300 words). Google Panda targets low-value pages, potentially penalizing entire site.', 'wpshadow' ),
				$thin_pages['count']
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/thin-content',
			'family'       => self::$family,
			'meta'         => array(
				'thin_pages_count' => $thin_pages['count'],
				'minimum_words'    => 300,
				'recommended_words' => 600,
				'panda_risk'       => __( 'Thin content triggers Google Panda penalty' ),
			),
			'details'      => array(
				'what_is_thin_content'    => array(
					__( 'Pages with little text (<300 words)' ),
					__( 'Pages with no unique value (duplicates elsewhere)' ),
					__( 'Auto-generated pages with minimal info' ),
					__( 'Doorway pages designed only for rankings' ),
				),
				'why_thin_content_bad'    => array(
					__( 'Google Panda algorithm targets low-quality sites' ),
					__( 'Penalty affects entire domain, not just thin pages' ),
					__( 'Users bounce quickly (high bounce rate signal)' ),
					__( 'Reduces site authority and trust' ),
				),
				'word_count_guidelines'   => array(
					'< 300 words' => 'Thin content - expand or noindex',
					'300-600 words' => 'Acceptable for some pages',
					'600-1000 words' => 'Good for most blog posts',
					'1000-2000 words' => 'Ideal for comprehensive guides',
					'2000+ words' => 'Authoritative, ranks well',
				),
				'exceptions_to_rule'      => array(
					'Contact Pages' => 'Can be short (address, form)',
					'About Pages' => 'Should be 300+ words minimum',
					'Product Pages' => '300+ with specs, reviews',
					'Category Pages' => 'Add intro paragraph (200+ words)',
				),
				'fixing_thin_content'     => array(
					'Option 1: Expand Content' => array(
						'Add 300-500 more words',
						'Include examples, case studies',
						'Add images, videos',
						'Link to related resources',
					),
					'Option 2: Consolidate' => array(
						'Merge multiple thin pages',
						'Create one comprehensive page',
						'301 redirect old pages to merged',
					),
					'Option 3: Noindex' => array(
						'Add noindex meta tag',
						'Remove from search results',
						'Keep page for users but hide from Google',
					),
					'Option 4: Delete' => array(
						'If no value, delete page',
						'Return 410 Gone status',
						'Remove internal links',
					),
				),
				'content_expansion_tips'  => array(
					__( 'Add FAQ section (common questions)' ),
					__( 'Include statistics and data' ),
					__( 'Add step-by-step instructions' ),
					__( 'Embed relevant videos' ),
					__( 'Include customer testimonials' ),
					__( 'Add comparison tables' ),
					__( 'Link to related articles (with context)' ),
				),
			),
		);
	}

	/**
	 * Find thin content pages.
	 *
	 * @since  1.2601.2148
	 * @return array Thin content statistics.
	 */
	private static function find_thin_content() {
		global $wpdb;

		$results = $wpdb->get_results(
			"SELECT ID, post_content 
			FROM {$wpdb->posts} 
			WHERE post_status = 'publish' 
			AND post_type IN ('post', 'page')
			LIMIT 100"
		);

		$thin_count = 0;
		foreach ( $results as $post ) {
			$word_count = str_word_count( wp_strip_all_tags( $post->post_content ) );
			if ( $word_count < 300 ) {
				$thin_count++;
			}
		}

		return array(
			'count' => $thin_count,
		);
	}
}
