<?php
/**
 * Weak Internal Linking Structure Diagnostic
 *
 * Tests for weak internal linking (< 3 internal links per post). Strong internal
 * linking improves SEO, helps users discover content, and increases engagement.
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
 * Diagnostic_Weak_Internal_Linking Class
 *
 * Detects posts with fewer than 3 internal links. Strong internal linking
 * structure can boost rankings by 40% and increases page views per session.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Weak_Internal_Linking extends Diagnostic_Base {

	protected static $slug = 'weak-internal-linking';
	protected static $title = 'Weak Internal Linking Structure';
	protected static $description = 'Tests for weak internal linking (< 3 internal links per post)';
	protected static $family = 'internal-linking';

	public static function check() {
		$score          = 0;
		$max_score      = 4;
		$score_details  = array();
		$recommendations = array();
		$weak_posts      = array();

		// Get sample of recent posts.
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 30,
				'post_status'    => 'publish',
			)
		);

		$posts_checked = 0;
		$posts_with_weak_linking = 0;
		$total_internal_links = 0;
		$home_url = home_url();

		foreach ( $posts as $post ) {
			++$posts_checked;
			
			// Count internal links in content.
			preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches );
			
			$internal_link_count = 0;
			if ( ! empty( $matches[1] ) ) {
				foreach ( $matches[1] as $url ) {
					// Check if internal link (relative or full domain).
					if ( strpos( $url, $home_url ) === 0 || ( strpos( $url, '/' ) === 0 && strpos( $url, '//' ) !== 0 ) ) {
						++$internal_link_count;
					}
				}
			}

			$total_internal_links += $internal_link_count;

			if ( $internal_link_count < 3 ) {
				++$posts_with_weak_linking;
				$weak_posts[] = array(
					'title' => $post->post_title,
					'url'   => get_permalink( $post ),
					'links' => $internal_link_count,
				);
			}
		}

		// Calculate average.
		$average_links = $posts_checked > 0 ? round( $total_internal_links / $posts_checked, 1 ) : 0;

		// Score based on average internal links per post.
		if ( $average_links >= 5 ) {
			$score = 4;
			$score_details[] = sprintf( __( '✓ Strong internal linking (average %s links per post)', 'wpshadow' ), $average_links );
		} elseif ( $average_links >= 3 ) {
			$score = 3;
			$score_details[]   = sprintf( __( '✓ Moderate internal linking (average %s links per post)', 'wpshadow' ), $average_links );
			$recommendations[] = __( 'Aim for 5-7 internal links per post for optimal SEO', 'wpshadow' );
		} elseif ( $average_links >= 2 ) {
			$score = 2;
			$score_details[]   = sprintf( __( '◐ Weak internal linking (average %s links per post)', 'wpshadow' ), $average_links );
			$recommendations[] = __( 'Increase internal links to 3-5 per post minimum', 'wpshadow' );
		} else {
			$score = 1;
			$score_details[]   = sprintf( __( '✗ Very weak internal linking (average %s links per post)', 'wpshadow' ), $average_links );
			$recommendations[] = __( 'Add 3-5 contextual internal links to every post', 'wpshadow' );
		}

		// Check for related posts plugins.
		if ( is_plugin_active( 'yet-another-related-posts-plugin/yarpp.php' ) || is_plugin_active( 'contextual-related-posts/contextual-related-posts.php' ) ) {
			++$score;
			$score_details[] = __( '✓ Related posts plugin active (automatic linking)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No related posts plugin', 'wpshadow' );
			$recommendations[] = __( 'Install YARPP or similar to automatically suggest related content', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 35;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage, %s: average links */
				__( 'Internal linking score: %d%% (average: %s links/post). Strong internal linking boosts rankings by 40%%, increases pages/session by 50%%, and helps Google discover content. Aim for 5-7 contextual internal links per post. Related posts plugins automate this.', 'wpshadow' ),
				$score_percentage,
				$average_links
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/internal-linking-strategy',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'problem_examples' => array_slice( $weak_posts, 0, 5 ),
			'impact'           => __( 'Internal linking helps users discover content, signals content relationships to search engines, and distributes page authority across your site.', 'wpshadow' ),
		);
	}
}
