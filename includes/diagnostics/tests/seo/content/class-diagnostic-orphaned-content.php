<?php
/**
 * Orphaned Content Detector
 *
 * Identifies pages and posts with zero internal links, making them unreachable
 * through normal site navigation. This indicates wasted content effort and
 * poor information architecture.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6028.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orphaned Content Diagnostic Class
 *
 * Detects published content that has no internal links pointing to it,
 * indicating it's not discoverable through site navigation.
 *
 * @since 1.6028.1045
 */
class Diagnostic_Orphaned_Content extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-content';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Content Detector';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies pages and posts with no internal links, making them unreachable through site navigation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * Scans all published posts and pages to find those with zero internal
	 * links pointing to them. These orphaned pieces of content represent
	 * wasted effort and poor information architecture.
	 *
	 * @since  1.6028.1045
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_orphaned_content_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$orphaned_content = self::find_orphaned_content();

		if ( empty( $orphaned_content ) ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$orphan_count = count( $orphaned_content );
		$severity     = self::calculate_severity( $orphan_count );

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of orphaned content items */
				__( 'Found %d published pages/posts with no internal links, making them unreachable through site navigation.', 'wpshadow' ),
				$orphan_count
			),
			'severity'     => $severity,
			'threat_level' => min( 70, 35 + ( $orphan_count * 2 ) ),
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/orphaned-content',
			'meta'         => array(
				'orphan_count'      => $orphan_count,
				'orphaned_items'    => array_slice( $orphaned_content, 0, 10 ), // Limit to 10 for display.
				'total_content'     => self::get_total_content_count(),
				'orphan_percentage' => self::calculate_orphan_percentage( $orphan_count ),
			),
			'details'      => self::build_details( $orphaned_content ),
			'recommendation' => self::get_recommendation( $orphan_count ),
		);

		set_transient( $cache_key, $finding, 12 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Find orphaned content.
	 *
	 * Queries all published posts/pages and checks which ones have no
	 * internal links pointing to them from other content.
	 *
	 * @since  1.6028.1045
	 * @return array Array of orphaned post data.
	 */
	private static function find_orphaned_content() {
		global $wpdb;

		// Get all published posts and pages.
		$content_items = $wpdb->get_results(
			"SELECT ID, post_title, post_type, post_date
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')
			ORDER BY post_date DESC",
			ARRAY_A
		);

		if ( empty( $content_items ) ) {
			return array();
		}

		$orphaned = array();

		foreach ( $content_items as $item ) {
			$permalink   = get_permalink( $item['ID'] );
			$link_count  = self::count_internal_links_to( $permalink );

			if ( 0 === $link_count ) {
				$orphaned[] = array(
					'id'         => $item['ID'],
					'title'      => $item['post_title'],
					'type'       => $item['post_type'],
					'permalink'  => $permalink,
					'published'  => $item['post_date'],
				);
			}
		}

		return $orphaned;
	}

	/**
	 * Count internal links pointing to a URL.
	 *
	 * Searches through all published content to find how many internal
	 * links point to the specified URL.
	 *
	 * @since  1.6028.1045
	 * @param  string $url The URL to search for.
	 * @return int Number of internal links found.
	 */
	private static function count_internal_links_to( $url ) {
		global $wpdb;

		// Extract the path from the URL for flexible matching.
		$parsed_url = wp_parse_url( $url );
		$path       = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '';

		if ( empty( $path ) ) {
			return 0;
		}

		// Search for the path in post content and excerpts.
		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->posts}
				WHERE post_status = 'publish'
				AND (post_content LIKE %s OR post_excerpt LIKE %s)",
				'%' . $wpdb->esc_like( $path ) . '%',
				'%' . $wpdb->esc_like( $path ) . '%'
			)
		);

		return (int) $count;
	}

	/**
	 * Get total content count.
	 *
	 * @since  1.6028.1045
	 * @return int Total number of published posts and pages.
	 */
	private static function get_total_content_count() {
		global $wpdb;

		$count = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_type IN ('post', 'page')"
		);

		return (int) $count;
	}

	/**
	 * Calculate orphan percentage.
	 *
	 * @since  1.6028.1045
	 * @param  int $orphan_count Number of orphaned items.
	 * @return float Percentage of orphaned content.
	 */
	private static function calculate_orphan_percentage( $orphan_count ) {
		$total = self::get_total_content_count();

		if ( 0 === $total ) {
			return 0.0;
		}

		return round( ( $orphan_count / $total ) * 100, 2 );
	}

	/**
	 * Calculate severity based on orphan count.
	 *
	 * @since  1.6028.1045
	 * @param  int $count Number of orphaned items.
	 * @return string Severity level.
	 */
	private static function calculate_severity( $count ) {
		if ( $count >= 20 ) {
			return 'high';
		} elseif ( $count >= 10 ) {
			return 'medium';
		}
		return 'low';
	}

	/**
	 * Build details array.
	 *
	 * @since  1.6028.1045
	 * @param  array $orphaned_content Array of orphaned content data.
	 * @return array Details array.
	 */
	private static function build_details( $orphaned_content ) {
		$details = array(
			__( 'Orphaned content has no internal links pointing to it', 'wpshadow' ),
			__( 'These pages are not discoverable through site navigation', 'wpshadow' ),
		);

		if ( count( $orphaned_content ) >= 20 ) {
			$details[] = __( 'High number of orphaned items indicates poor content strategy', 'wpshadow' );
		}

		return $details;
	}

	/**
	 * Get recommendation based on orphan count.
	 *
	 * @since  1.6028.1045
	 * @param  int $count Number of orphaned items.
	 * @return string Recommendation text.
	 */
	private static function get_recommendation( $count ) {
		if ( $count >= 20 ) {
			return __( 'Review and either link these pages from relevant content or consider removing them if no longer needed.', 'wpshadow' );
		} elseif ( $count >= 10 ) {
			return __( 'Add internal links to these pages from related content to improve discoverability.', 'wpshadow' );
		}

		return __( 'Create internal links to these pages or add them to your navigation menu.', 'wpshadow' );
	}
}
