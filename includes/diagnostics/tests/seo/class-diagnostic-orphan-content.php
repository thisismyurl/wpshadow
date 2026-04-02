<?php
/**
 * Orphan Content Diagnostic
 *
 * Tests for orphan content (posts with zero internal links pointing to them).
 * Google may not discover or rank orphaned content properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Orphan_Content Class
 *
 * Detects posts with no incoming internal links. Orphaned content is hard
 * for users and search engines to discover, leading to poor rankings.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Orphan_Content extends Diagnostic_Base {

	protected static $slug = 'orphan-content';
	protected static $title = 'Orphan Content';
	protected static $description = 'Tests for orphan content (posts with zero internal links)';
	protected static $family = 'internal-linking';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();
		$orphan_posts    = array();

		// Get all published posts.
		$all_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 100,
				'post_status'    => 'publish',
			)
		);

		// Build a map of all internal links.
		$link_map = array();
		$home_url = home_url();

		foreach ( $all_posts as $post ) {
			// Extract all internal links from this post.
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches );
			
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					// Normalize the URL.
					$url = trim( $url );
					
					// Check if internal link.
					if ( strpos( $url, $home_url ) === 0 ) {
						// Extract path.
						$path = str_replace( $home_url, '', $url );
						$path = strtok( $path, '?' ); // Remove query strings.
						$path = strtok( $path, '#' ); // Remove fragments.
						
						if ( ! isset( $link_map[ $path ] ) ) {
							$link_map[ $path ] = 0;
						}
						++$link_map[ $path ];
					} elseif ( strpos( $url, '/' ) === 0 && strpos( $url, '//' ) !== 0 ) {
						// Relative URL.
						$path = strtok( $url, '?' );
						$path = strtok( $path, '#' );
						
						if ( ! isset( $link_map[ $path ] ) ) {
							$link_map[ $path ] = 0;
						}
						++$link_map[ $path ];
					}
				}
			}
		}

		// Check which posts have no incoming links.
		$orphan_count = 0;
		$total_checked = 0;

		foreach ( $all_posts as $post ) {
			++$total_checked;
			$permalink = get_permalink( $post );
			$path = str_replace( $home_url, '', $permalink );
			$path = strtok( $path, '?' );
			$path = strtok( $path, '#' );

			// Check if this post has incoming links.
			$incoming_links = isset( $link_map[ $path ] ) ? $link_map[ $path ] : 0;

			if ( $incoming_links === 0 ) {
				++$orphan_count;
				$orphan_posts[] = array(
					'title' => $post->post_title,
					'url'   => $permalink,
					'date'  => get_the_date( '', $post ),
				);
			}
		}

		// Score based on orphan percentage.
		if ( $total_checked > 0 ) {
			$orphan_percentage = ( $orphan_count / $total_checked ) * 100;

			if ( $orphan_percentage < 10 ) {
				$score = 3;
				$score_details[] = sprintf( __( '✓ Low orphan rate (%d%% orphaned)', 'wpshadow' ), round( $orphan_percentage ) );
			} elseif ( $orphan_percentage < 25 ) {
				$score = 2;
				$score_details[]   = sprintf( __( '◐ Moderate orphan content (%d posts, %d%%)', 'wpshadow' ), $orphan_count, round( $orphan_percentage ) );
				$recommendations[] = __( 'Add internal links to orphaned posts from related content', 'wpshadow' );
			} else {
				$score = 0;
				$score_details[]   = sprintf( __( '✗ High orphan rate (%d posts, %d%%)', 'wpshadow' ), $orphan_count, round( $orphan_percentage ) );
				$recommendations[] = __( 'Critical: Many posts lack internal links - add contextual links immediately', 'wpshadow' );
			}
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'critical';
		$threat_level = 55;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage, %d: orphan count */
				__( 'Orphan content score: %d%% (%d orphaned posts found). Orphaned content has zero incoming internal links, making it invisible to users navigating your site. Google discovers content through links - orphans may never be found or ranked. Every post needs 3-5 incoming links from related content.', 'wpshadow' ),
				$score_percentage,
				$orphan_count
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/orphan-content',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'problem_examples' => array_slice( $orphan_posts, 0, 10 ),
			'impact'           => __( 'Orphaned content is invisible to users and hard for search engines to discover, essentially wasting the effort put into creating it.', 'wpshadow' ),
		);
	}
}
