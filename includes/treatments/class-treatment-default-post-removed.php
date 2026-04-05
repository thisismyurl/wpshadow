<?php
/**
 * Treatment: Delete the default "Hello world!" post
 *
 * WordPress ships with a sample post (slug: hello-world, title: "Hello world!",
 * body containing "Welcome to WordPress. This is your first post"). Leaving it
 * live signals an incomplete setup and pollutes the blog archive with
 * placeholder content.
 *
 * This treatment permanently deletes the post. Because deleting real content is
 * irreversible, the check guards that the default placeholder body text is still
 * present before deleting — the same guard used by the diagnostic.
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
 * Permanently deletes the default WordPress "Hello world!" sample post.
 */
class Treatment_Default_Post_Removed extends Treatment_Base {

	/** @var string */
	protected static $slug = 'default-post-removed';

	/** @return string */
	public static function get_risk_level(): string {
		return 'moderate';
	}

	/**
	 * Locate and permanently delete the default starter post.
	 *
	 * @return array
	 */
	public static function apply(): array {
		// Primary lookup: canonical slug.
		$post = get_page_by_path( 'hello-world', OBJECT, 'post' );

		// Fallback: slug changed but title still matches.
		if ( null === $post ) {
			$query = new \WP_Query(
				array(
					'post_type'      => 'post',
					'post_status'    => array( 'publish', 'draft', 'private', 'future' ),
					'title'          => 'Hello world!',
					'posts_per_page' => 1,
					'no_found_rows'  => true,
				)
			);
			$post = $query->have_posts() ? $query->posts[0] : null;
		}

		// Fallback: content still matches.
		if ( null === $post ) {
			$content_query = new \WP_Query(
				array(
					'post_type'              => 'post',
					'post_status'            => array( 'publish', 'draft', 'private', 'future' ),
					's'                      => 'Welcome to WordPress. This is your first post',
					'posts_per_page'         => 1,
					'no_found_rows'          => true,
					'fields'                 => 'ids',
					'ignore_sticky_posts'    => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);
			$post_id = $content_query->have_posts() ? (int) $content_query->posts[0] : 0;
			if ( $post_id ) {
				$post = get_post( $post_id );
			}
		}

		if ( null === $post ) {
			return array(
				'success' => true,
				'message' => __( 'Default "Hello world!" post not found — it may have already been removed.', 'wpshadow' ),
			);
		}

		// Guard: only delete if the default body text is still present.
		if ( ! str_contains( (string) $post->post_content, 'Welcome to WordPress. This is your first post' ) ) {
			return array(
				'success' => false,
				'message' => __( 'The post with this slug or title has custom content — it will not be deleted automatically. Remove it manually if it is no longer needed.', 'wpshadow' ),
			);
		}

		$deleted = wp_delete_post( $post->ID, true );

		if ( ! $deleted ) {
			return array(
				'success' => false,
				'message' => __( 'Could not delete the default post. Try removing it manually from Posts → All Posts.', 'wpshadow' ),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Default "Hello world!" post permanently deleted.', 'wpshadow' ),
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
			'message' => __( 'The default post was permanently deleted and cannot be restored automatically. Re-create it manually if needed.', 'wpshadow' ),
		);
	}
}
