<?php
/**
 * Author Activity Diagnostic
 *
 * Checks if author list is current (no inactive accounts).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1300
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author Activity Diagnostic Class
 *
 * Verifies that all author accounts are actively contributing and
 * identifies inactive or unused author accounts.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Author_Activity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'author-activity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Author Activity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if author list is current (no inactive accounts)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the author activity diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if author activity issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Get all users with author+ capabilities.
		$author_args = array(
			'who' => 'authors',
		);

		$authors = get_users( $author_args );

		if ( empty( $authors ) ) {
			$warnings[] = __( 'No authors found', 'wpshadow' );
			return null;
		}

		$stats['total_authors'] = count( $authors );

		// Check each author's activity.
		$active_authors = 0;
		$inactive_authors = 0;
		$inactive_list = array();

		$six_months_ago = strtotime( '-6 months' );

		foreach ( $authors as $author ) {
			// Get author's latest post.
			$latest_post = get_posts( array(
				'author'         => $author->ID,
				'posts_per_page' => 1,
				'post_status'    => 'publish',
				'orderby'        => 'post_date',
				'order'          => 'DESC',
			) );

			$has_posts = ! empty( $latest_post );
			$last_post_date = null;
			$is_active = false;

			if ( $has_posts ) {
				$last_post_date = strtotime( $latest_post[0]->post_date );
				$is_active = $last_post_date > $six_months_ago;
			}

			if ( $is_active ) {
				$active_authors++;
			} else {
				$inactive_authors++;
				$inactive_list[] = array(
					'name'        => $author->display_name,
					'email'       => $author->user_email,
					'last_post'   => $last_post_date ? date( 'Y-m-d', $last_post_date ) : 'Never',
					'posts_count' => count_user_posts( $author->ID ),
				);
			}
		}

		$stats['active_authors'] = $active_authors;
		$stats['inactive_authors'] = $inactive_authors;

		// Check for inactive contributors.
		if ( $inactive_authors > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d inactive authors (no posts in 6 months)', 'wpshadow' ),
				$inactive_authors
			);
		}

		// Check for authors with no posts.
		$no_posts_count = 0;
		foreach ( $authors as $author ) {
			$post_count = count_user_posts( $author->ID );
			if ( $post_count === 0 ) {
				$no_posts_count++;
			}
		}

		$stats['authors_no_posts'] = $no_posts_count;

		if ( $no_posts_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number */
				__( '%d authors with no published posts', 'wpshadow' ),
				$no_posts_count
			);
		}

		// Check for unused administrator accounts.
		$admins = get_users( array(
			'role' => 'administrator',
		) );

		$inactive_admins = 0;
		$admin_no_posts = 0;

		foreach ( $admins as $admin ) {
			// Check admin last login (if available via plugin).
			$last_login = get_user_meta( $admin->ID, 'wp_last_login', true );

			if ( ! $last_login ) {
				// Fallback: check if they have recent posts or changes.
				$admin_posts = count_user_posts( $admin->ID );
				if ( $admin_posts === 0 ) {
					$admin_no_posts++;
				}
			} else {
				$last_login_time = strtotime( $last_login );
				if ( $last_login_time < $six_months_ago ) {
					$inactive_admins++;
				}
			}
		}

		$stats['total_admins'] = count( $admins );
		$stats['inactive_admins'] = $inactive_admins;

		if ( $admin_no_posts > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d administrator accounts with no posts (may be unused)', 'wpshadow' ),
				$admin_no_posts
			);
		}

		// Check for suspicious author activity.
		$posts_by_others = array();
		foreach ( $authors as $author ) {
			$post_count = count_user_posts( $author->ID );
			
			if ( $post_count > 0 ) {
				$posts_by_others[ $author->display_name ] = $post_count;
			}
		}

		arsort( $posts_by_others );
		$stats['top_authors'] = array_slice( $posts_by_others, 0, 3, true );

		// If one author has 95%+ of posts, flag it.
		$total_posts = array_sum( $posts_by_others );
		if ( $total_posts > 0 ) {
			$top_author_posts = reset( $posts_by_others );
			$percentage = ( $top_author_posts / $total_posts ) * 100;

			if ( $percentage > 95 ) {
				$warnings[] = sprintf(
					/* translators: %d: percentage */
					__( 'One author has %d%% of posts - consider diversifying', 'wpshadow' ),
					intval( $percentage )
				);
			}
		}

		// Check contributor management.
		$contributors = get_users( array(
			'role' => 'contributor',
		) );

		$stats['contributor_count'] = count( $contributors );

		if ( count( $contributors ) > 20 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( 'High number of contributors (%d) - ensure proper management', 'wpshadow' ),
				count( $contributors )
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Author activity has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/author-activity',
				'context'      => array(
					'stats'          => $stats,
					'inactive_list'  => array_slice( $inactive_list, 0, 5 ),
					'issues'         => $issues,
					'warnings'       => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Author activity has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/author-activity',
				'context'      => array(
					'stats'          => $stats,
					'inactive_list'  => array_slice( $inactive_list, 0, 5 ),
					'warnings'       => $warnings,
				),
			);
		}

		return null; // Author activity is healthy.
	}
}
