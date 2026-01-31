<?php
/**
 * Internal Link Anchor Text Diversity Diagnostic
 *
 * Analyzes internal link anchor text patterns to detect over-optimization
 * or keyword stuffing that could trigger search engine penalties.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since      1.6028.2120
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internal Link Anchor Text Diversity Diagnostic Class
 *
 * Examines internal linking patterns to ensure natural anchor text diversity.
 * Over-optimization with exact-match anchors can trigger Penguin penalties.
 *
 * @since 1.6028.2120
 */
class Diagnostic_Internal_Link_Anchor_Diversity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'internal-link-anchor-diversity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internal Link Anchor Text Diversity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes internal link anchor text patterns for over-optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.2120
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check cache first.
		$cache_key = 'wpshadow_internal_link_anchor_diversity_check';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		// Extract internal links from sample pages.
		$links = self::extract_internal_links();

		if ( empty( $links ) ) {
			$result = null;
		} else {
			// Analyze anchor text distribution.
			$analysis = self::analyze_anchor_distribution( $links );

			// Determine if there's an issue.
			$exact_match_percentage = $analysis['exact_match_percentage'];

			if ( $exact_match_percentage < 50 ) {
				$result = null; // Good diversity.
			} else {
				// Warning or critical.
				$severity     = $exact_match_percentage > 80 ? 'high' : 'medium';
				$threat_level = $exact_match_percentage > 80 ? 65 : 45;

				$result = array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => sprintf(
						/* translators: %s: exact match percentage */
						__( '%.1f%% of internal links use exact-match anchor text, risking over-optimization penalties', 'wpshadow' ),
						$exact_match_percentage
					),
					'severity'     => $severity,
					'threat_level' => $threat_level,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/seo-anchor-text-diversity',
					'family'       => self::$family,
					'meta'         => array(
						'total_links'            => $analysis['total_links'],
						'unique_anchors'         => $analysis['unique_anchors'],
						'exact_match_percentage' => round( $exact_match_percentage, 2 ),
						'diversity_score'        => round( $analysis['diversity_score'], 2 ),
						'thresholds'             => array(
							'good'     => 50,
							'warning'  => 80,
							'critical' => 90,
						),
					),
					'details'      => array(
						'top_anchors'       => array_slice( $analysis['top_anchors'], 0, 10 ),
						'over_optimized'    => $analysis['over_optimized'],
					),
					'recommendations' => array(
						__( 'Vary anchor text with branded terms, URLs, and natural phrases', 'wpshadow' ),
						__( 'Use "click here", "learn more", or contextual phrases occasionally', 'wpshadow' ),
						__( 'Mix exact-match keywords with partial matches and synonyms', 'wpshadow' ),
						__( 'Ensure anchor text flows naturally within content', 'wpshadow' ),
						__( 'Avoid over-optimization of money keywords in internal links', 'wpshadow' ),
					),
				);
			}
		}

		// Cache for 24 hours.
		set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );

		return $result;
	}

	/**
	 * Extract internal links from sample pages.
	 *
	 * @since  1.6028.2120
	 * @return array Array of internal links with anchor text.
	 */
	private static function extract_internal_links() {
		$links = array();

		// Get homepage links.
		$home_links = self::extract_links_from_url( home_url( '/' ) );
		$links      = array_merge( $links, $home_links );

		// Get links from recent posts.
		$posts = get_posts(
			array(
				'numberposts' => 5,
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);

		foreach ( $posts as $post ) {
			$post_links = self::extract_links_from_url( get_permalink( $post->ID ) );
			$links      = array_merge( $links, $post_links );
		}

		return $links;
	}

	/**
	 * Extract links from a specific URL.
	 *
	 * @since  1.6028.2120
	 * @param  string $url URL to extract links from.
	 * @return array Array of links.
	 */
	private static function extract_links_from_url( $url ) {
		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$html = wp_remote_retrieve_body( $response );
		$home = home_url( '/' );

		// Extract anchor tags with href and text.
		preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER );

		$links = array();
		foreach ( $matches as $match ) {
			$href        = $match[1];
			$anchor_text = wp_strip_all_tags( $match[2] );

			// Filter for internal links only.
			if ( strpos( $href, $home ) === 0 || ( strpos( $href, '/' ) === 0 && strpos( $href, '//' ) !== 0 ) ) {
				// Convert relative to absolute.
				if ( strpos( $href, '/' ) === 0 && strpos( $href, '//' ) !== 0 ) {
					$href = home_url( $href );
				}

				$links[] = array(
					'url'    => $href,
					'anchor' => trim( $anchor_text ),
				);
			}
		}

		return $links;
	}

	/**
	 * Analyze anchor text distribution.
	 *
	 * @since  1.6028.2120
	 * @param  array $links Array of links.
	 * @return array Analysis results.
	 */
	private static function analyze_anchor_distribution( $links ) {
		$total_links = count( $links );
		$anchors     = array();

		// Count anchor text usage.
		foreach ( $links as $link ) {
			$anchor = strtolower( $link['anchor'] );
			if ( ! isset( $anchors[ $anchor ] ) ) {
				$anchors[ $anchor ] = 0;
			}
			++$anchors[ $anchor ];
		}

		// Sort by frequency.
		arsort( $anchors );

		// Calculate exact-match keywords (repeated more than once).
		$exact_match_count = 0;
		$over_optimized    = array();

		foreach ( $anchors as $anchor => $count ) {
			if ( $count > 1 && strlen( $anchor ) > 3 ) {
				$exact_match_count += $count;
				if ( $count > 3 ) {
					$over_optimized[] = array(
						'anchor' => $anchor,
						'count'  => $count,
					);
				}
			}
		}

		$exact_match_percentage = $total_links > 0 ? ( $exact_match_count / $total_links ) * 100 : 0;
		$unique_anchors         = count( $anchors );
		$diversity_score        = $total_links > 0 ? ( $unique_anchors / $total_links ) * 100 : 0;

		// Top anchors.
		$top_anchors = array();
		$count       = 0;
		foreach ( $anchors as $anchor => $frequency ) {
			$top_anchors[] = array(
				'anchor'     => $anchor,
				'count'      => $frequency,
				'percentage' => round( ( $frequency / $total_links ) * 100, 2 ),
			);
			++$count;
			if ( $count >= 15 ) {
				break;
			}
		}

		return array(
			'total_links'            => $total_links,
			'unique_anchors'         => $unique_anchors,
			'exact_match_percentage' => $exact_match_percentage,
			'diversity_score'        => $diversity_score,
			'top_anchors'            => $top_anchors,
			'over_optimized'         => $over_optimized,
		);
	}
}
