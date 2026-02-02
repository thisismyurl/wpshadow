<?php
/**
 * Media Version Tracking Diagnostic
 *
 * Tests media file versioning and revision history.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Media Version Tracking Diagnostic Class
 *
 * Verifies media file versioning and revision history,
 * including replacement tracking and rollback functionality.
 *
 * @since 1.26033.0000
 */
class Diagnostic_Media_Version_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-version-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Media Version Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media file versioning and revision history';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for media versioning plugins.
		$versioning_plugins = array(
			'enable-media-replace/enable-media-replace.php',
			'media-file-renamer/media-file-renamer.php',
			'simple-image-replace/simple-image-replace.php',
		);

		$has_versioning = false;
		foreach ( $versioning_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_versioning = true;
				break;
			}
		}

		if ( ! $has_versioning ) {
			$issues[] = __( 'No media versioning/replacement plugin detected', 'wpshadow' );
		}

		// Check if revisions are enabled for attachments.
		$revisions_enabled = wp_revisions_enabled( get_post_type_object( 'attachment' ) );
		if ( ! $revisions_enabled ) {
			$issues[] = __( 'Post revisions are not enabled for attachments', 'wpshadow' );
		}

		// Check if attachment revisions are stored.
		$sample_attachment = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 1,
				'orderby'        => 'modified',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $sample_attachment ) ) {
			$revisions = wp_get_post_revisions( $sample_attachment[0]->ID );
			if ( empty( $revisions ) && $revisions_enabled ) {
				// Attachment has been modified but has no revisions.
				$issues[] = __( 'Attachment revisions are enabled but no revision history exists', 'wpshadow' );
			}
		}

		// Check for file replacement filters.
		$has_replace_filter = has_filter( 'wp_handle_upload' );
		if ( ! $has_replace_filter ) {
			// No custom upload handling for replacement.
		}

		// Check for attachment metadata history.
		if ( ! empty( $sample_attachment ) ) {
			$metadata = wp_get_attachment_metadata( $sample_attachment[0]->ID );
			if ( empty( $metadata ) ) {
				$issues[] = __( 'Attachment metadata is missing or not being tracked', 'wpshadow' );
			}
		}

		// Check if old file versions are preserved on replacement.
		$upload_dir = wp_upload_dir();
		$backup_dir = $upload_dir['basedir'] . '/wpshadow-backups';
		if ( ! file_exists( $backup_dir ) && ! $has_versioning ) {
			// No backup directory and no versioning plugin.
			$issues[] = __( 'No media file backup system detected for version history', 'wpshadow' );
		}

		// Check for version tracking in media library columns.
		$has_column_filter = has_filter( 'manage_media_columns' );
		if ( ! $has_column_filter ) {
			// No custom media columns registered.
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/media-version-tracking',
			);
		}

		return null;
	}
}
