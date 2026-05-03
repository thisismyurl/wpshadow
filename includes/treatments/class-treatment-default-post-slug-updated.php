<?php
/**
 * Treatment: Update default "Hello world!" post slug and/or title
 *
 * When a site has repurposed the starter post with custom content but never
 * updated its original slug (hello-world) or title ("Hello world!"), the
 * placeholder identifiers remain in public URLs, browser tabs, and social share
 * cards. This treatment renames only the stale identifiers:
 *   - Slug  hello-world  → welcome
 *   - Title "Hello world!" → "Welcome"
 *
 * It only updates fields that still hold the WordPress default values so it
 * never overwrites intentional customisation.
 *
 * Undo: restores the previous slug and title from stored values.
 *
 * @package ThisIsMyURL\Shadow
 * @since   0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Treatments;

use ThisIsMyURL\Shadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renames stale slug/title identifiers on the repurposed Hello World post.
 */
class Treatment_Default_Post_Slug_Updated extends Treatment_Base {

	/** @var string */
	protected static $slug = 'default-post-slug-updated';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Locate the post and update any stale default identifiers.
	 *
	 * @return array
	 */
	public static function apply(): array {
		// Primary lookup: canonical slug from a fresh WordPress install.
		$post = get_page_by_path( 'hello-world', OBJECT, 'post' );

		// Fallback: slug was changed but title may still be the default.
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

		if ( null === $post ) {
			return array(
				'success' => true,
				'message' => __( 'Default starter post not found — slug and title may have already been updated.', 'thisismyurl-shadow' ),
			);
		}

		// If default body text is still present, the "remove" diagnostic covers this — skip.
		if ( str_contains( (string) $post->post_content, 'Welcome to WordPress. This is your first post' ) ) {
			return array(
				'success' => false,
				'message' => __( 'This post still contains the original starter content. Use the "Delete default Hello World post" treatment instead.', 'thisismyurl-shadow' ),
			);
		}

		$has_default_slug  = ( 'hello-world' === $post->post_name );
		$has_default_title = ( 'Hello world!' === $post->post_title );

		if ( ! $has_default_slug && ! $has_default_title ) {
			return array(
				'success' => true,
				'message' => __( 'The post\'s slug and title have already been customised. No changes made.', 'thisismyurl-shadow' ),
			);
		}

		// Store originals for undo().
		static::save_backup_value(
			'thisismyurl_shadow_default_post_slug_prev',
			array(
				'id'    => $post->ID,
				'slug'  => $post->post_name,
				'title' => $post->post_title,
			)
		);

		$update_args = array( 'ID' => $post->ID );
		$changed     = array();

		if ( $has_default_slug ) {
			$update_args['post_name'] = 'welcome';
			$changed[]                = __( 'slug updated to "welcome"', 'thisismyurl-shadow' );
		}

		if ( $has_default_title ) {
			$update_args['post_title'] = 'Welcome';
			$changed[]                 = __( 'title updated to "Welcome"', 'thisismyurl-shadow' );
		}

		$result = wp_update_post( $update_args, true );

		if ( is_wp_error( $result ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: WP_Error message */
					__( 'Could not update the post: %s', 'thisismyurl-shadow' ),
					$result->get_error_message()
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: %s: comma-separated list of changes made */
				__( 'Post identifiers updated: %s. Edit the post to set a more specific title and permalink if needed.', 'thisismyurl-shadow' ),
				implode( ', ', $changed )
			),
		);
	}

	/**
	 * Restore the previous post slug and title.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$loaded = static::load_backup_array( 'thisismyurl_shadow_default_post_slug_prev', array( 'id', 'slug', 'title' ), true );
		$prev   = $loaded['value'];

		if ( ! $loaded['found'] || ! is_array( $prev ) ) {
			return array(
				'success' => false,
				'message' => __( 'No stored post data to restore.', 'thisismyurl-shadow' ),
			);
		}

		$result = wp_update_post(
			array(
				'ID'         => (int) $prev['id'],
				'post_name'  => $prev['slug'],
				'post_title' => $prev['title'],
			),
			true
		);

		if ( is_wp_error( $result ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: WP_Error message */
					__( 'Could not restore the post: %s', 'thisismyurl-shadow' ),
					$result->get_error_message()
				),
			);
		}

		return array(
			'success' => true,
			'message' => sprintf(
				/* translators: 1: slug, 2: title */
				__( 'Post restored to slug "%1$s" and title "%2$s".', 'thisismyurl-shadow' ),
				esc_html( $prev['slug'] ),
				esc_html( $prev['title'] )
			),
		);
	}
}
