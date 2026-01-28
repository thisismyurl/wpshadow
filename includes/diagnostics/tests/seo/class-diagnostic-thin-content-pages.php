<?php
/**
 * Thin Content Pages Diagnostic
 *
 * Identifies pages with <300 words that Google considers low-quality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Thin Content Pages Class
 *
 * Tests for pages with insufficient content.
 *
 * @since 1.26028.1905
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
	protected static $description = 'Identifies pages with <300 words that Google considers low-quality';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$thin_pages = self::find_thin_content_pages();
		
		if ( $thin_pages['count'] > 0 ) {
			$severity = 'low';
			if ( $thin_pages['count'] > 50 ) {
				$severity = 'medium';
			} elseif ( $thin_pages['count'] > 100 ) {
				$severity = 'high';
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of thin content pages */
					__( '%d pages have <300 words (Google Panda considers this thin content)', 'wpshadow' ),
					$thin_pages['count']
				),
				'severity'     => $severity,
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/thin-content-pages',
				'meta'         => array(
					'thin_page_count'       => $thin_pages['count'],
					'indexed_thin_pages'    => $thin_pages['indexed_count'],
					'thin_tag_pages'        => $thin_pages['tag_pages'],
					'thin_category_pages'   => $thin_pages['category_pages'],
					'average_word_count'    => $thin_pages['average_words'],
				),
			);
		}

		return null;
	}

	/**
	 * Find pages with thin content.
	 *
	 * @since  1.26028.1905
	 * @return array Statistics about thin content.
	 */
	private static function find_thin_content_pages() {
		global $wpdb;

		$thin_threshold = 300;
		$thin_pages = array(
			'count'           => 0,
			'indexed_count'   => 0,
			'tag_pages'       => 0,
			'category_pages'  => 0,
			'average_words'   => 0,
		);

		// Get all published posts and pages.
		$posts = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_content, post_type
				FROM {$wpdb->posts}
				WHERE post_status = %s
				AND post_type IN ('post', 'page')
				LIMIT 1000",
				'publish'
			)
		);

		$total_words = 0;
		$thin_count = 0;

		foreach ( $posts as $post ) {
			// Strip HTML and shortcodes.
			$content = wp_strip_all_tags( $post->post_content );
			$content = strip_shortcodes( $content );
			
			// Count words.
			$word_count = str_word_count( $content );
			$total_words += $word_count;

			if ( $word_count < $thin_threshold ) {
				++$thin_count;

				// Check if page is indexed (no noindex meta).
				$noindex = get_post_meta( $post->ID, '_yoast_wpseo_meta-robots-noindex', true );
				if ( 1 !== (int) $noindex ) {
					++$thin_pages['indexed_count'];
				}
			}
		}

		$thin_pages['count'] = $thin_count;
		$thin_pages['average_words'] = count( $posts ) > 0 ? (int) ( $total_words / count( $posts ) ) : 0;

		// Check tag archives.
		$tag_count = wp_count_terms( array( 'taxonomy' => 'post_tag' ) );
		if ( ! is_wp_error( $tag_count ) ) {
			// Estimate thin tag pages (tags with <5 posts).
			$thin_tags = $wpdb->get_var(
				"SELECT COUNT(DISTINCT term_id)
				FROM {$wpdb->term_taxonomy}
				WHERE taxonomy = 'post_tag'
				AND count < 5"
			);
			$thin_pages['tag_pages'] = (int) $thin_tags;
		}

		// Check category archives.
		$thin_categories = $wpdb->get_var(
			"SELECT COUNT(DISTINCT term_id)
			FROM {$wpdb->term_taxonomy}
			WHERE taxonomy = 'category'
			AND count < 5"
		);
		$thin_pages['category_pages'] = (int) $thin_categories;

		return $thin_pages;
	}
}
