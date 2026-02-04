<?php
/**
 * Diagnostic: Single Author Dependency
 *
 * Detects over-reliance on a single author (>90% of posts). Single author
 * dependency creates business risk and limits scaling potential.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4383
 *
 * @package    WPShadow
 * @subpackage Diagnostics\ContentStrategy
 * @since      1.6034.1440
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Single Author Dependency Diagnostic
 *
 * Checks if content production is over-reliant on a single author.
 * Diversified authorship improves resilience and scalability.
 *
 * @since 1.6034.1440
 */
class Diagnostic_Content_Single_Author_Dependency extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'single-author-dependency';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Single Author Dependency';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects over-reliance on single author that creates business risk';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Check for single author dependency.
	 *
	 * Analyzes last 90 days of posts. If one author creates >90% of content,
	 * this represents a significant business continuity risk.
	 *
	 * @since  1.6034.1440
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get posts from last 90 days.
		$ninety_days_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-90 days' ) );
		
		$author_stats = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_author, COUNT(*) as post_count
				FROM {$wpdb->posts}
				WHERE post_type = 'post'
				AND post_status = 'publish'
				AND post_date > %s
				GROUP BY post_author
				ORDER BY post_count DESC",
				$ninety_days_ago
			)
		);

		if ( empty( $author_stats ) ) {
			return null;
		}

		// Calculate total posts.
		$total_posts = array_sum( wp_list_pluck( $author_stats, 'post_count' ) );

		if ( $total_posts < 5 ) {
			// Not enough posts to determine dependency.
			return null;
		}

		// Check if top author has >90% of posts.
		$top_author_posts      = (int) $author_stats[0]->post_count;
		$top_author_percentage = ( $top_author_posts / $total_posts ) * 100;

		if ( $top_author_percentage < 90 ) {
			// No single-author dependency.
			return null;
		}

		$threat_level = 60; // Medium-high severity.
		
		if ( $top_author_percentage >= 95 ) {
			$threat_level = 70; // Very high dependency.
		}

		// Get author name.
		$author_data = get_userdata( (int) $author_stats[0]->post_author );
		$author_name = $author_data ? $author_data->display_name : __( 'Unknown', 'wpshadow' );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage, 2: author name, 3: post count, 4: total posts */
				__(
					'%.1f%% of recent posts (%3$d of %4$d) are by %2$s. Single author dependency creates business continuity risk and limits content scaling. Consider developing additional contributors.',
					'wpshadow'
				),
				$top_author_percentage,
				esc_html( $author_name ),
				$top_author_posts,
				$total_posts
			),
			'severity'     => 'medium',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/single-author-dependency',
		);
	}
}
