<?php
/**
 * Internal Linking Health Diagnostic
 *
 * Analyzes content for strategic internal links and identifies
 * orphaned pages with no internal link connections.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internal Linking Health Class
 *
 * Identifies orphaned content and measures internal link density.
 * Strategic internal linking improves SEO and user navigation.
 *
 * @since 1.5029.1045
 */
class Diagnostic_Internal_Linking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'internal-linking-health';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internal Linking Health';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes internal link strategy and orphaned content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes internal linking using get_posts() and content parsing.
	 * Identifies orphaned pages and calculates link density.
	 *
	 * @since  1.5029.1045
	 * @return array|null Finding array if linking issues found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_internal_linking_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get published posts and pages using WordPress API (NO $wpdb).
		$posts = get_posts( array(
			'post_type'   => array( 'post', 'page' ),
			'post_status' => 'publish',
			'numberposts' => 100,
			'orderby'     => 'date',
			'order'       => 'DESC',
		) );

		if ( empty( $posts ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$orphaned_posts = array();
		$low_link_posts = array();
		$site_url = home_url();

		foreach ( $posts as $post ) {
			// Count internal links in content.
			$content = $post->post_content;
			$internal_link_count = self::count_internal_links( $content, $site_url );

			// Check if post is orphaned (no internal links pointing TO it).
			$incoming_links = self::count_incoming_links( $post->ID, $posts );

			if ( 0 === $incoming_links && 'page' !== $post->post_type ) {
				$orphaned_posts[] = array(
					'id'    => $post->ID,
					'title' => $post->post_title,
					'url'   => get_permalink( $post->ID ),
				);
			}

			// Check if post has too few outgoing links (< 2 for posts > 500 words).
			$word_count = str_word_count( strip_tags( $content ) );
			if ( $word_count > 500 && $internal_link_count < 2 ) {
				$low_link_posts[] = array(
					'id'         => $post->ID,
					'title'      => $post->post_title,
					'word_count' => $word_count,
					'links'      => $internal_link_count,
				);
			}
		}

		$issues = array();

		if ( count( $orphaned_posts ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned posts */
				__( '%d posts have no incoming internal links (orphaned)', 'wpshadow' ),
				count( $orphaned_posts )
			);
		}

		if ( count( $low_link_posts ) > 10 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d long-form posts have insufficient internal links', 'wpshadow' ),
				count( $low_link_posts )
			);
		}

		// Calculate average internal link density.
		$total_links = 0;
		$total_words = 0;
		foreach ( $posts as $post ) {
			$content = $post->post_content;
			$total_links += self::count_internal_links( $content, $site_url );
			$total_words += str_word_count( strip_tags( $content ) );
		}

		$link_density = $total_words > 0 ? ( $total_links / count( $posts ) ) : 0;

		if ( $link_density < 2.0 ) {
			$issues[] = sprintf(
				/* translators: %s: link density */
				__( 'Low internal link density: %s links per post (target: 3-5)', 'wpshadow' ),
				number_format( $link_density, 1 )
			);
		}

		// If issues found, flag it.
		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( 'Internal linking strategy has %d issues. Improve site structure with strategic internal links.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-internal-linking',
				'data'         => array(
					'orphaned_count'   => count( $orphaned_posts ),
					'orphaned_posts'   => array_slice( $orphaned_posts, 0, 10 ),
					'low_link_count'   => count( $low_link_posts ),
					'low_link_posts'   => array_slice( $low_link_posts, 0, 10 ),
					'link_density'     => $link_density,
					'total_analyzed'   => count( $posts ),
					'issues'           => $issues,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Count internal links in content.
	 *
	 * @since  1.5029.1045
	 * @param  string $content  Post content.
	 * @param  string $site_url Site URL.
	 * @return int Number of internal links.
	 */
	private static function count_internal_links( $content, $site_url ) {
		preg_match_all( '/<a\s+(?:[^>]*?\s+)?href=(["\'])(.*?)\1/i', $content, $matches );

		if ( empty( $matches[2] ) ) {
			return 0;
		}

		$internal_count = 0;
		foreach ( $matches[2] as $url ) {
			if ( 0 === strpos( $url, $site_url ) || 0 === strpos( $url, '/' ) ) {
				$internal_count++;
			}
		}

		return $internal_count;
	}

	/**
	 * Count incoming links to a post.
	 *
	 * @since  1.5029.1045
	 * @param  int   $post_id Post ID.
	 * @param  array $posts   All posts to search.
	 * @return int Number of incoming links.
	 */
	private static function count_incoming_links( $post_id, $posts ) {
		$post_url = get_permalink( $post_id );
		$count = 0;

		foreach ( $posts as $post ) {
			if ( $post->ID === $post_id ) {
				continue;
			}

			if ( strpos( $post->post_content, $post_url ) !== false ) {
				$count++;
			}
		}

		return $count;
	}
}
