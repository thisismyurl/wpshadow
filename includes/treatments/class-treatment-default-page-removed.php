<?php
/**
 * Treatment: Delete the default "Sample Page"
 *
 * WordPress ships with a sample page (slug: sample-page, title: "Sample Page",
 * body containing "This is an example page"). Leaving it live pollutes the site
 * navigation with a placeholder and signals an incomplete setup.
 *
 * This treatment permanently deletes the page. The check guards that the default
 * placeholder body text is still present before deleting.
 *
 * Undo: not supported — permanently deleted posts cannot be restored
 * automatically.
 *
 * @package WPShadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permanently deletes the default WordPress "Sample Page".
 */
class Treatment_Default_Page_Removed extends Treatment_Base {

	/** @var string */
	protected static $slug = 'default-page-removed';

	/** @return string */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Locate and permanently delete the default sample page.
	 *
	 * @return array
	 */
	public static function apply(): array {
		// Primary lookup: canonical slug.
		$page = get_page_by_path( 'sample-page', OBJECT, 'page' );

		// Fallback: slug changed but title still matches.
		if ( null === $page ) {
			$query = new \WP_Query(
				array(
					'post_type'      => 'page',
					'post_status'    => array( 'publish', 'draft', 'private', 'future' ),
					'title'          => 'Sample Page',
					'posts_per_page' => 1,
					'no_found_rows'  => true,
				)
			);
			$page = $query->have_posts() ? $query->posts[0] : null;
		}

		// Fallback: content still matches.
		if ( null === $page ) {
			$content_query = new \WP_Query(
				array(
					'post_type'              => 'page',
					'post_status'            => array( 'publish', 'draft', 'private', 'future' ),
					's'                      => 'This is an example page',
					'posts_per_page'         => 1,
					'no_found_rows'          => true,
					'fields'                 => 'ids',
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);
			$page_id = $content_query->have_posts() ? (int) $content_query->posts[0] : 0;
			if ( $page_id ) {
				$page = get_post( $page_id );
			}
		}

		if ( null === $page ) {
			return array(
				'success' => true,
				'message' => __( 'Default "Sample Page" not found — it may have already been removed.', 'wpshadow' ),
			);
		}

		// Guard: only delete if the default body text is still present.
		if ( ! str_contains( (string) $page->post_content, 'This is an example page' ) ) {
			return array(
				'success' => false,
				'message' => __( 'The page with this slug or title has custom content — it will not be deleted automatically. Remove it manually if it is no longer needed.', 'wpshadow' ),
			);
		}

		$deleted = wp_delete_post( $page->ID, true );

		if ( ! $deleted ) {
			return array(
				'success' => false,
				'message' => __( 'Could not delete the sample page. Try removing it manually from Pages → All Pages.', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Default "Sample Page" permanently deleted.', 'wpshadow' ),
		);
	}

	/**
	 * Undo is not supported — permanently deleted posts cannot be restored.
	 *
	 * @return array
	 */
	public static function undo(): array {
		return array(
			'success' => false,
			'message' => __( 'The sample page was permanently deleted and cannot be restored automatically. Re-create it manually if needed.', 'wpshadow' ),
		);
	}
}
