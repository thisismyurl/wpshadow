<?php
/**
 * Content Duplication Check
 *
 * Detects duplicate or near-duplicate pages/posts that hurt SEO rankings
 * and confuse users. Analyzes title and content similarity.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6028.1046
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Duplication Diagnostic Class
 *
 * Identifies duplicate or highly similar content that may hurt SEO
 * performance and create user confusion.
 *
 * @since 1.6028.1046
 */
class Diagnostic_Content_Duplication extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-duplication';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Duplication Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects duplicate or near-duplicate content that hurts SEO and confuses users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 *
	 */
	protected static $family = 'content';

	/**
	 * Similarity threshold for flagging content as duplicate (0-100)
	 *
	 * @var int
	 */
	const SIMILARITY_THRESHOLD = 80;

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes all published content to detect duplicate or highly similar
	 * titles and content that may hurt SEO rankings.
	 *
	 * @since  1.6028.1046
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_content_duplication_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$duplicates = self::find_duplicate_content();

		if ( empty( $duplicates ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$duplicate_count = count( $duplicates );

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of duplicate content pairs */
				__( 'Found %d instances of duplicate or near-duplicate content that may hurt SEO rankings.', 'wpshadow' ),
				$duplicate_count
			),
			'severity'     => 'high',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-duplication',
			'meta'         => array(
				'duplicate_count' => $duplicate_count,
				'duplicate_pairs' => array_slice( $duplicates, 0, 10 ), // Limit display.
			),
			'details'      => array(
				__( 'Duplicate content confuses search engines and dilutes page authority', 'wpshadow' ),
				__( 'Can result in lower search rankings for affected pages', 'wpshadow' ),
				__( 'Creates poor user experience with redundant information', 'wpshadow' ),
			),
			'recommendation' => __( 'Review duplicate content pairs and consolidate, redirect, or use canonical URLs to indicate preferred versions.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Find duplicate content.
	 *
	 * Compares all published posts/pages to identify duplicates based on
	 * title and content similarity.
	 *
	 * @since  1.6028.1046
	 * @return array Array of duplicate content pairs.
	 */
	private static function find_duplicate_content() {
		global $wpdb;

		$posts = $wpdb->get_results(
			"SELECT ID, post_title, post_content, post_type
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			ORDER BY post_date DESC
			LIMIT 200",
			ARRAY_A
		);

		if ( empty( $posts ) || count( $posts ) < 2 ) {
			return array();
		}

		$duplicates = array();
		$checked    = array();

		foreach ( $posts as $i => $post1 ) {
			foreach ( $posts as $j => $post2 ) {
				// Skip same post and already checked pairs.
				if ( $i >= $j ) {
					continue;
				}

				$pair_key = $post1['ID'] . '-' . $post2['ID'];
				if ( isset( $checked[ $pair_key ] ) ) {
					continue;
				}

				$similarity = self::calculate_similarity( $post1, $post2 );

				if ( $similarity >= self::SIMILARITY_THRESHOLD ) {
					$duplicates[] = array(
						'post1_id'    => $post1['ID'],
						'post1_title' => $post1['post_title'],
						'post1_type'  => $post1['post_type'],
						'post2_id'    => $post2['ID'],
						'post2_title' => $post2['post_title'],
						'post2_type'  => $post2['post_type'],
						'similarity'  => $similarity,
					);
				}

				$checked[ $pair_key ] = true;
			}
		}

		return $duplicates;
	}

	/**
	 * Calculate similarity between two posts.
	 *
	 * Uses title and content comparison to determine similarity percentage.
	 *
	 * @since  1.6028.1046
	 * @param  array $post1 First post data.
	 * @param  array $post2 Second post data.
	 * @return int Similarity percentage (0-100).
	 */
	private static function calculate_similarity( $post1, $post2 ) {
		// Title similarity (40% weight).
		$title_similarity = 0;
		similar_text( $post1['post_title'], $post2['post_title'], $title_similarity );

		// Content similarity (60% weight).
		$content1 = wp_strip_all_tags( $post1['post_content'] );
		$content2 = wp_strip_all_tags( $post2['post_content'] );

		$content_similarity = 0;
		similar_text( $content1, $content2, $content_similarity );

		// Weighted average.
		$overall = ( $title_similarity * 0.4 ) + ( $content_similarity * 0.6 );

		return (int) round( $overall );
	}
}
