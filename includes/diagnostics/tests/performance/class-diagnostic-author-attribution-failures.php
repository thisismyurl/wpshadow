<?php
/**
 * Author Attribution Failures Diagnostic
 *
 * Detects when imported posts lose correct author assignments.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Author Attribution Failures Diagnostic Class
 *
 * Detects when imported posts lose correct author assignments or have wrong authors.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Author_Attribution_Failures extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'author-attribution-failures';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Author Attribution Failures';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when imported posts have incorrect author assignments';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for posts assigned to admin/default user.
		$admin_user_id = 1; // Typically WordPress admin.
		$posts_with_admin = $wpdb->get_var( "
			SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_author = {$admin_user_id}
			AND post_type IN ('post', 'page')
			AND post_status = 'publish'
		" );

		$total_posts = $wpdb->get_var( "
			SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_type IN ('post', 'page')
			AND post_status = 'publish'
		" );

		if ( $total_posts > 0 && $posts_with_admin > ( $total_posts * 0.7 ) ) {
			$percentage = ( $posts_with_admin / $total_posts ) * 100;
			$issues[] = sprintf(
				/* translators: %d: percentage of posts assigned to admin */
				__( '%d%% of published posts are assigned to admin user', 'wpshadow' ),
				round( $percentage )
			);
		}

		// Check for posts with non-existent author IDs.
		$orphaned_posts = $wpdb->get_results( "
			SELECT p.ID, p.post_author
			FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID
			WHERE p.post_type IN ('post', 'page')
			AND u.ID IS NULL
			LIMIT 5
		" );

		if ( ! empty( $orphaned_posts ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with orphaned authors */
				__( '%d posts assigned to non-existent user IDs', 'wpshadow' ),
				count( $orphaned_posts )
			);
		}

		// Check for author archive functionality.
		$author_archive_enabled = get_option( 'users_can_register' );
		if ( ! $author_archive_enabled ) {
			// Doesn't directly relate to author archives, but check if taxonomy archive works.
			$sample_author = get_users( array( 'number' => 1 ) );
			if ( ! empty( $sample_author ) ) {
				$author_url = get_author_posts_url( $sample_author[0]->ID );
				if ( empty( $author_url ) ) {
					$issues[] = __( 'Author archive URLs not generating correctly', 'wpshadow' );
				}
			}
		}

		// Check for author display names vs usernames in posts.
		$posts_by_author = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 5,
			'orderby'        => 'modified',
		) );

		$author_display_issues = 0;
		foreach ( $posts_by_author as $post ) {
			$author = get_the_author_meta( 'display_name', $post->post_author );
			if ( empty( $author ) ) {
				$author_display_issues++;
			}
		}

		if ( $author_display_issues > count( $posts_by_author ) * 0.3 ) {
			$issues[] = __( 'Author display names missing on multiple posts', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/author-attribution-failures',
			);
		}

		return null;
	}
}
