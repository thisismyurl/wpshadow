<?php
/**
 * Treatment: Clean Up Tag Overuse
 *
 * Deletes tags with fewer than 3 posts to reduce thin content
 * and improve site structure.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Tag_Overuse Class
 *
 * Deletes low-value tags to reduce clutter.
 *
 * @since 0.6093.1200
 */
class Treatment_Tag_Overuse extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'tag-overuse';
	}

	/**
	 * Apply the treatment.
	 *
	 * Deletes tags that have fewer than 3 posts associated with them.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about changes made.
	 * }
	 */
	public static function apply() {
		$tags = get_tags(
			array(
				'hide_empty' => false,
				'orderby'    => 'count',
				'order'      => 'ASC',
			)
		);

		$deleted_count = 0;
		$deleted_tags  = array();
		$kept_count    = 0;

		foreach ( $tags as $tag ) {
			// Delete tags with fewer than 3 posts.
			if ( $tag->count < 3 ) {
				$result = wp_delete_term( $tag->term_id, 'post_tag' );

				if ( ! is_wp_error( $result ) ) {
					$deleted_count++;
					$deleted_tags[] = array(
						'name'  => $tag->name,
						'slug'  => $tag->slug,
						'count' => $tag->count,
					);
				}
			} else {
				$kept_count++;
			}
		}

		if ( $deleted_count > 0 ) {
			// Clear caches.
			delete_option( 'wpshadow_tag_cache' );
			wp_cache_flush();

			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: 1: number of tags deleted, 2: number of tags kept */
					__( 'Deleted %1$d low-value tags (less than 3 posts each). Kept %2$d quality tags.', 'wpshadow' ),
					$deleted_count,
					$kept_count
				),
				'details' => array(
					'deleted_count' => $deleted_count,
					'kept_count'    => $kept_count,
					'deleted_tags'  => array_slice( $deleted_tags, 0, 20 ), // First 20 for display.
					'recommendation' => __( 'Consider merging similar tags and using categories for main topics.', 'wpshadow' ),
				),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'No low-value tags found. All tags have 3 or more posts.', 'wpshadow' ),
			'details' => array(
				'total_tags' => count( $tags ),
			),
		);
	}
}
