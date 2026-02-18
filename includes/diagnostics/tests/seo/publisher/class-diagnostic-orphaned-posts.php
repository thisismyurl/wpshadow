<?php
/**
 * Orphaned Posts Diagnostic
 *
 * Checks for unpublished or unlisted old content that should be reviewed.
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
 * Orphaned Posts Diagnostic Class
 *
 * Verifies that unpublished, drafts, and old unlisted posts are
 * periodically reviewed and cleaned up.
 *
 * @since 1.6035.1300
 */
class Diagnostic_Orphaned_Posts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-posts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Posts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for unpublished or unlisted old content that should be reviewed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the orphaned posts diagnostic check.
	 *
	 * @since  1.6035.1300
	 * @return array|null Finding array if orphaned post issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for draft posts.
		$draft_posts = get_posts( array(
			'posts_per_page' => -1,
			'post_status'    => 'draft',
			'post_type'      => 'post',
		) );

		$stats['draft_count'] = count( $draft_posts );

		if ( count( $draft_posts ) > 50 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d draft posts - consider archiving or deleting old ones', 'wpshadow' ),
				count( $draft_posts )
			);
		}

		// Check for pending approval posts.
		$pending_posts = get_posts( array(
			'posts_per_page' => -1,
			'post_status'    => 'pending',
			'post_type'      => 'post',
		) );

		$stats['pending_count'] = count( $pending_posts );

		if ( count( $pending_posts ) > 10 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d pending posts awaiting approval', 'wpshadow' ),
				count( $pending_posts )
			);
		}

		// Check for scheduled but never published.
		$scheduled_posts = get_posts( array(
			'posts_per_page' => -1,
			'post_status'    => 'future',
			'post_type'      => 'post',
		) );

		$stats['scheduled_count'] = count( $scheduled_posts );

		// Check age of old scheduled posts.
		$old_scheduled = 0;
		$three_months_ago = strtotime( '-3 months' );
		foreach ( $scheduled_posts as $post ) {
			if ( strtotime( $post->post_date ) < $three_months_ago ) {
				$old_scheduled++;
			}
		}

		if ( $old_scheduled > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d scheduled posts scheduled for >3 months ago - may be forgotten', 'wpshadow' ),
				$old_scheduled
			);
		}

		// Check for private posts.
		$private_posts = get_posts( array(
			'posts_per_page' => -1,
			'post_status'    => 'private',
			'post_type'      => 'post',
		) );

		$stats['private_count'] = count( $private_posts );

		if ( count( $private_posts ) > 100 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d private posts - ensure these are intentionally private', 'wpshadow' ),
				count( $private_posts )
			);
		}

		// Check for very old unpublished posts.
		$old_unpublished = array();
		$six_months_ago = strtotime( '-6 months' );

		$old_drafts = get_posts( array(
			'posts_per_page' => -1,
			'post_status'    => 'draft',
			'post_type'      => 'post',
			'before'         => date( 'Y-m-d', $six_months_ago ),
			'orderby'        => 'modified',
		) );

		$stats['old_draft_count'] = count( $old_drafts );

		if ( count( $old_drafts ) > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number, %d: months */
				__( '%d draft posts older than 6 months - should be archived or deleted', 'wpshadow' ),
				count( $old_drafts )
			);
		}

		// Check for posts with no category (orphaned).
		$posts_no_category = get_posts( array(
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'post_type'      => 'post',
		) );

		$uncategorized_count = 0;
		foreach ( $posts_no_category as $post ) {
			$categories = get_the_category( $post->ID );
			if ( empty( $categories ) || ( count( $categories ) === 1 && $categories[0]->slug === 'uncategorized' ) ) {
				$uncategorized_count++;
			}
		}

		$stats['uncategorized_posts'] = $uncategorized_count;

		if ( $uncategorized_count > 10 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d posts have no category or only "Uncategorized" - improve organization', 'wpshadow' ),
				$uncategorized_count
			);
		}

		// Check for posts with no tags.
		$no_tags_count = 0;
		foreach ( $posts_no_category as $post ) {
			$tags = get_the_tags( $post->ID );
			if ( empty( $tags ) ) {
				$no_tags_count++;
			}
		}

		$stats['no_tags_posts'] = $no_tags_count;

		if ( $no_tags_count > count( $posts_no_category ) * 0.3 ) {
			// More than 30% of posts have no tags.
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d posts have no tags - improves discoverability', 'wpshadow' ),
				$no_tags_count
			);
		}

		// Check for posts with short content (likely incomplete/orphaned).
		$short_content_count = 0;
		foreach ( array_slice( $posts_no_category, 0, 20 ) as $post ) {
			if ( strlen( $post->post_content ) < 100 ) {
				$short_content_count++;
			}
		}

		$stats['short_content_posts'] = $short_content_count;

		if ( $short_content_count > 5 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d posts have very short content (<100 chars) - may be incomplete', 'wpshadow' ),
				$short_content_count
			);
		}

		// Check for posts with no featured image.
		$no_image_count = 0;
		foreach ( array_slice( $posts_no_category, 0, 20 ) as $post ) {
			if ( ! has_post_thumbnail( $post->ID ) ) {
				$no_image_count++;
			}
		}

		$stats['no_featured_image_posts'] = $no_image_count;

		if ( $no_image_count > 10 ) {
			$warnings[] = sprintf(
				/* translators: %d: number */
				__( '%d posts have no featured image - improves presentation', 'wpshadow' ),
				$no_image_count
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Orphaned posts have critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/orphaned-posts',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Orphaned posts have recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/orphaned-posts',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Orphaned posts are well managed.
	}
}
