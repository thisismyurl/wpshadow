<?php
/**
 * Demo Media Removed Diagnostic
 *
 * Checks the media library for demo, placeholder, or theme starter-content
 * images that should be replaced with branded assets.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Demo_Media_Removed Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Demo_Media_Removed extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'demo-media-removed';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Demo or Placeholder Media Detected in Library';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks the media library for images and files installed as theme starter content or matching well-known placeholder filename patterns.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'code-quality';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Filename substrings that identify demo or placeholder media assets.
	 *
	 * Matched against the attachment GUID (the full original upload URL).
	 *
	 * @var string[]
	 */
	private const DEMO_FILENAME_PATTERNS = array(
		'hello-world.jpg',
		'hello-world-1.jpg',
		'/placeholder',
		'/sample-image',
		'/demo-image',
	);

	/**
	 * Post-meta keys WordPress uses to tag theme starter-content attachments.
	 *
	 * @var string[]
	 */
	private const STARTER_CONTENT_META_KEYS = array(
		'_wp_attachment_context',
		'_theme_starter_content',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Uses two strategies: (1) theme starter-content post-meta flag, and
	 * (2) filename pattern matching against attachment GUIDs.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		global $wpdb;

		$found_ids = array();

		// Strategy 1: theme starter-content meta — get_posts() respects the object
		// cache and avoids a manual JOIN, unlike a direct $wpdb query.
		foreach ( self::STARTER_CONTENT_META_KEYS as $meta_key ) {
			$ids = get_posts(
				array(
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'meta_key'       => $meta_key, // phpcs:ignore WordPress.DB.SlowDBQuery
					'posts_per_page' => 20,
					'fields'         => 'ids',
				)
			);
			foreach ( (array) $ids as $id ) {
				$found_ids[ (int) $id ] = true;
			}
		}

		// Strategy 2: filename pattern matching.
		foreach ( self::DEMO_FILENAME_PATTERNS as $pattern ) {
			$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				$wpdb->prepare(
					"SELECT ID
					 FROM   {$wpdb->posts}
					 WHERE  post_type   = 'attachment'
					 AND    post_status = 'inherit'
					 AND    guid LIKE %s
					 LIMIT  20",
					'%' . $wpdb->esc_like( $pattern ) . '%'
				)
			);
			foreach ( (array) $ids as $id ) {
				$found_ids[ (int) $id ] = true;
			}
		}

		if ( empty( $found_ids ) ) {
			return null;
		}

		$affected = array();
		foreach ( array_keys( $found_ids ) as $post_id ) {
			$att = get_post( $post_id );
			if ( null === $att ) {
				continue;
			}
			$affected[] = array(
				'attachment_id' => $post_id,
				'title'         => $att->post_title,
				'filename'      => basename( (string) $att->guid ),
				'edit_url'      => get_edit_post_link( $post_id, 'raw' ),
			);
		}

		if ( empty( $affected ) ) {
			return null;
		}

		$count = count( $affected );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => 1 === $count
				? sprintf(
					/* translators: %s: filename */
					__( 'One demo or placeholder media file ("%s") was found in your media library. Replace it with your own branded asset.', 'wpshadow' ),
					esc_html( $affected[0]['filename'] )
				)
				: sprintf(
					/* translators: %d: number of media files */
					_n(
						'%d demo or placeholder media file was found in your media library.',
						'%d demo or placeholder media files were found in your media library.',
						$count,
						'wpshadow'
					),
					$count
				),
			'severity'     => $count > 5 ? 'medium' : 'low',
			'threat_level' => $count > 5 ? 20 : 10,
			'kb_link'      => '',
			'details'      => array(
				'affected_count' => $count,
				'affected_files' => $affected,
				'fix'            => __( 'Go to Media &rsaquo; Library. For each flagged file, confirm it is not used by real content, then delete it and upload your own branded replacement.', 'wpshadow' ),
			),
		);
	}
}
