<?php
/**
 * Post Meta Save Reliability Diagnostic
 *
 * Checks if post meta saves reliably when posts are updated. Tests for save failures,
 * permission issues, and hook conflicts that prevent meta data from persisting.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post Meta Save Reliability Diagnostic Class
 *
 * Checks for issues with post meta save reliability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Post_Meta_Save_Reliability extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-meta-save-reliability';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Post Meta Save Reliability';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates post meta saves reliably without failures or data loss';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check for excessive save_post hooks (can cause save failures).
		$save_post_hooks = $GLOBALS['wp_filter']['save_post'] ?? null;
		if ( $save_post_hooks && count( $save_post_hooks ) > 25 ) {
			$issues[] = sprintf(
				/* translators: %d: number of hooks */
				__( '%d save_post hooks registered (may cause timeouts or save failures)', 'wpshadow' ),
				count( $save_post_hooks )
			);
		}

		// Check for posts with missing expected meta (could indicate save failures).
		$posts_with_meta_count = $wpdb->get_var(
			"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta}"
		);

		$total_published_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type IN ('post', 'page')"
		);

		if ( $total_published_posts > 100 && $posts_with_meta_count > 0 ) {
			$meta_percentage = ( $posts_with_meta_count / $total_published_posts ) * 100;
			if ( $meta_percentage < 50 ) {
				$issues[] = sprintf(
					/* translators: %d: percentage */
					__( 'Only %d%% of posts have custom fields (possible meta save failures)', 'wpshadow' ),
					round( $meta_percentage )
				);
			}
		}

		// Check for duplicate meta keys (can indicate save race conditions).
		$duplicate_meta = $wpdb->get_results(
			"SELECT post_id, meta_key, COUNT(*) as count
			FROM {$wpdb->postmeta}
			WHERE meta_key NOT LIKE '\\_%%'
			GROUP BY post_id, meta_key
			HAVING count > 1
			LIMIT 10",
			ARRAY_A
		);

		if ( ! empty( $duplicate_meta ) && count( $duplicate_meta ) > 5 ) {
			$issues[] = sprintf(
				/* translators: %d: number of duplicates */
				__( '%d posts have duplicate meta keys (race condition in save process)', 'wpshadow' ),
				count( $duplicate_meta )
			);
		}

		// Check if max_input_vars is too low (causes $_POST truncation).
		$max_input_vars = ini_get( 'max_input_vars' );
		if ( $max_input_vars && (int) $max_input_vars < 3000 ) {
			$issues[] = sprintf(
				/* translators: %d: current value */
				__( 'max_input_vars is %d (recommended: 3000+ to prevent meta data loss)', 'wpshadow' ),
				(int) $max_input_vars
			);
		}

		// Check for update_post_meta hooks that might interfere.
		$update_meta_hooks = $GLOBALS['wp_filter']['update_post_meta'] ?? null;
		if ( $update_meta_hooks && count( $update_meta_hooks ) > 15 ) {
			$issues[] = sprintf(
				/* translators: %d: number of hooks */
				__( '%d update_post_meta hooks registered (may interfere with saves)', 'wpshadow' ),
				count( $update_meta_hooks )
			);
		}

		// Check for posts recently modified but meta not updated.
		$posts_modified_recently = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts}
				WHERE post_modified > %s
				AND post_status = 'publish'
				AND post_type IN ('post', 'page')
				LIMIT 20",
				gmdate( 'Y-m-d H:i:s', strtotime( '-7 days' ) )
			)
		);

		if ( ! empty( $posts_modified_recently ) ) {
			$missing_meta_update = 0;
			foreach ( $posts_modified_recently as $post_id ) {
				// Check if post has any non-private meta.
				$has_meta = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT COUNT(*) FROM {$wpdb->postmeta}
						WHERE post_id = %d
						AND meta_key NOT LIKE '\\_%%'",
						$post_id
					)
				);

				if ( 0 === (int) $has_meta ) {
					++$missing_meta_update;
				}
			}

			if ( $missing_meta_update > 5 ) {
				$issues[] = sprintf(
					/* translators: %d: number of posts */
					__( '%d recently updated posts have no custom fields (meta may not be saving)', 'wpshadow' ),
					$missing_meta_update
				);
			}
		}

		// Check for meta box save callbacks that might fail.
		global $wp_meta_boxes;
		if ( isset( $wp_meta_boxes['post'] ) || isset( $wp_meta_boxes['page'] ) ) {
			$invalid_callbacks = 0;
			foreach ( array( 'post', 'page' ) as $post_type ) {
				if ( ! isset( $wp_meta_boxes[ $post_type ] ) ) {
					continue;
				}
				foreach ( $wp_meta_boxes[ $post_type ] as $context => $priority_boxes ) {
					foreach ( $priority_boxes as $priority => $boxes ) {
						foreach ( $boxes as $box_id => $box ) {
							if ( is_string( $box['callback'] ) && ! function_exists( $box['callback'] ) ) {
								++$invalid_callbacks;
							}
						}
					}
				}
			}

			if ( $invalid_callbacks > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of invalid callbacks */
					__( '%d meta box callbacks don\'t exist (meta data won\'t save from these boxes)', 'wpshadow' ),
					$invalid_callbacks
				);
			}
		}

		// Check for nonce verification that might block saves.
		$wp_nonce_tick = wp_nonce_tick();
		if ( $wp_nonce_tick < 1 ) {
			$issues[] = __( 'Nonce tick calculation invalid (may cause meta save failures)', 'wpshadow' );
		}

		// Check for capability issues.
		$current_user = wp_get_current_user();
		if ( $current_user && $current_user->ID > 0 ) {
			if ( ! current_user_can( 'edit_posts' ) ) {
				$issues[] = __( 'Current user cannot edit posts (meta saves will fail)', 'wpshadow' );
			}
		}

		// Check database errors in postmeta table.
		$wpdb->query( "SELECT 1 FROM {$wpdb->postmeta} LIMIT 1" );
		if ( ! empty( $wpdb->last_error ) ) {
			$issues[] = __( 'Database error accessing postmeta table (meta saves will fail)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/post-meta-save-reliability',
			);
		}

		return null;
	}
}
