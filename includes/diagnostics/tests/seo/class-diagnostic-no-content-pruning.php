<?php
/**
 * No Content Pruning Strategy Diagnostic
 *
 * Tests whether the site has a content pruning strategy. Less than 5% of old
 * content updated or removed indicates outdated content diluting authority.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Content_Pruning Class
 *
 * Detects when old content is not being updated or pruned. Outdated content
 * dilutes site authority and can hurt overall rankings.
 *
 * @since 1.5003.1200
 */
class Diagnostic_No_Content_Pruning extends Diagnostic_Base {

	protected static $slug = 'no-content-pruning';
	protected static $title = 'No Content Pruning Strategy';
	protected static $description = 'Tests whether old content is being updated or removed';
	protected static $family = 'keyword-strategy';

	public static function check() {
		$score          = 0;
		$max_score      = 4;
		$score_details  = array();
		$recommendations = array();

		// Get old posts (1+ years old).
		$old_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
				'post_status'    => 'publish',
				'date_query'     => array(
					array(
						'before' => '1 year ago',
					),
				),
			)
		);

		$old_count = count( $old_posts );

		if ( $old_count === 0 ) {
			// Site is new, no old content yet.
			return null;
		}

		// Check how many have been modified in last 6 months.
		$recently_updated = 0;

		foreach ( $old_posts as $post ) {
			$modified = strtotime( $post->post_modified );
			$six_months_ago = strtotime( '6 months ago' );
			
			if ( $modified > $six_months_ago ) {
				++$recently_updated;
			}
		}

		$update_percentage = ( $recently_updated / $old_count ) * 100;

		// Score based on update frequency.
		if ( $update_percentage >= 20 ) {
			$score = 4;
			$score_details[] = sprintf( __( '✓ Active content pruning (%d%% of old content updated)', 'wpshadow' ), round( $update_percentage ) );
		} elseif ( $update_percentage >= 10 ) {
			$score = 3;
			$score_details[]   = sprintf( __( '✓ Moderate content updates (%d%% updated)', 'wpshadow' ), round( $update_percentage ) );
			$recommendations[] = __( 'Increase content refresh rate to 20%+ annually', 'wpshadow' );
		} elseif ( $update_percentage >= 5 ) {
			$score = 2;
			$score_details[]   = sprintf( __( '◐ Low content refresh rate (%d%% updated)', 'wpshadow' ), round( $update_percentage ) );
			$recommendations[] = __( 'Review and update top-performing old content quarterly', 'wpshadow' );
		} else {
			$score = 1;
			$score_details[]   = sprintf( __( '✗ Minimal content pruning (%d old posts, only %d%% updated)', 'wpshadow' ), $old_count, round( $update_percentage ) );
			$recommendations[] = __( 'Implement content audit: update/consolidate/delete underperforming old content', 'wpshadow' );
		}

		// Check for very old posts (3+ years).
		$very_old_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
				'date_query'     => array(
					array(
						'before' => '3 years ago',
					),
				),
			)
		);

		if ( count( $very_old_posts ) > 20 ) {
			$score_details[]   = sprintf( __( '⚠ %d posts are 3+ years old', 'wpshadow' ), count( $very_old_posts ) );
			$recommendations[] = __( 'Prioritize updating content older than 3 years - outdated content dilutes authority', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 30;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage, %d: update percentage, %d: old posts */
				__( 'Content pruning score: %d%% (only %d%% of %d old posts updated). Outdated content dilutes site authority. Updated old posts get 45%% traffic boost. Strategy: Quarterly content audit - update top performers, consolidate similar posts, delete/noindex thin content. Fresh dates signal relevance.', 'wpshadow' ),
				$score_percentage,
				round( $update_percentage ),
				$old_count
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/content-pruning',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Regular content updates show search engines the site is maintained, outdated content is refreshed, and users get current information.', 'wpshadow' ),
		);
	}
}
