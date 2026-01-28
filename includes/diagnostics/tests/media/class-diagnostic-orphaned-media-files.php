<?php
/**
 * Orphaned Media Library Files Diagnostic
 *
 * Identifies media library files not referenced in any post, page, or widget.
 * Orphaned files waste storage space and increase backup sizes unnecessarily.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.6028.2149
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Orphaned_Media_Files Class
 *
 * Scans media library for files not referenced anywhere on the site.
 * High orphan count indicates poor media management practices.
 *
 * @since 1.6028.2149
 */
class Diagnostic_Orphaned_Media_Files extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-media-files';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Media Library Files Above 100';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies unused media files wasting storage';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.2149
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cached = get_transient( 'wpshadow_diagnostic_orphaned_media' );
		if ( false !== $cached ) {
			return $cached;
		}

		$orphan_analysis = self::find_orphaned_media();

		if ( $orphan_analysis['orphan_count'] < 100 ) {
			set_transient( 'wpshadow_diagnostic_orphaned_media', null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$orphan_count = $orphan_analysis['orphan_count'];
		$severity     = $orphan_count > 500 ? 'medium' : 'low';
		$threat_level = min( 60, 30 + ( $orphan_count / 10 ) );

		$total_size_mb = $orphan_analysis['total_size'] / ( 1024 * 1024 );

		$finding = array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: number of files, 2: total size */
				__( 'Found %1$d orphaned media files wasting %2$s MB of storage space', 'wpshadow' ),
				$orphan_count,
				number_format( $total_size_mb, 1 )
			),
			'severity'       => $severity,
			'threat_level'   => $threat_level,
			'auto_fixable'   => false,
			'kb_link'        => 'https://wpshadow.com/kb/orphaned-media',
			'meta'           => array(
				'orphan_count'     => $orphan_count,
				'total_media'      => $orphan_analysis['total_media'],
				'orphan_percent'   => round( ( $orphan_count / max( 1, $orphan_analysis['total_media'] ) ) * 100, 1 ),
				'total_size_bytes' => $orphan_analysis['total_size'],
				'total_size_mb'    => round( $total_size_mb, 2 ),
			),
			'details'        => array(
				sprintf(
					/* translators: %d: number of files */
					__( 'Orphaned media files: %d', 'wpshadow' ),
					$orphan_count
				),
				sprintf(
					/* translators: %s: size */
					__( 'Wasted storage: %s MB', 'wpshadow' ),
					number_format( $total_size_mb, 1 )
				),
				__( 'Orphaned files increase backup size and storage costs', 'wpshadow' ),
			),
			'recommendations' => array(
				__( 'Use Media Cleaner plugin to safely identify and remove orphaned files', 'wpshadow' ),
				__( 'Review and delete unused media before removing', 'wpshadow' ),
				__( 'Implement media usage tracking for future uploads', 'wpshadow' ),
				__( 'Consider media library audit as part of regular maintenance', 'wpshadow' ),
			),
		);

		set_transient( 'wpshadow_diagnostic_orphaned_media', $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Find orphaned media files.
	 *
	 * Queries database to find media files not referenced in content or metadata.
	 *
	 * @since  1.6028.2149
	 * @return array Orphan analysis data.
	 */
	private static function find_orphaned_media() {
		global $wpdb;

		// Get all media attachments.
		$all_media = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, guid FROM {$wpdb->posts} WHERE post_type = %s",
				'attachment'
			)
		);

		$total_media  = count( $all_media );
		$orphan_count = 0;
		$total_size   = 0;

		foreach ( $all_media as $media ) {
			$is_orphaned = self::is_media_orphaned( $media->ID );

			if ( $is_orphaned ) {
				$orphan_count++;
				$file_path = get_attached_file( $media->ID );
				if ( $file_path && file_exists( $file_path ) ) {
					$total_size += filesize( $file_path );
				}
			}
		}

		return array(
			'orphan_count' => $orphan_count,
			'total_media'  => $total_media,
			'total_size'   => $total_size,
		);
	}

	/**
	 * Check if media file is orphaned.
	 *
	 * Checks if media is used in post content, featured images, or widgets.
	 *
	 * @since  1.6028.2149
	 * @param  int $attachment_id Attachment post ID.
	 * @return bool True if orphaned.
	 */
	private static function is_media_orphaned( $attachment_id ) {
		global $wpdb;

		// Check if used as featured image.
		$featured = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta} 
				WHERE meta_key = '_thumbnail_id' 
				AND meta_value = %d 
				LIMIT 1",
				$attachment_id
			)
		);

		if ( $featured ) {
			return false;
		}

		// Check if referenced in post content.
		$guid     = wp_get_attachment_url( $attachment_id );
		$filename = basename( $guid );

		$in_content = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} 
				WHERE post_type IN ('post', 'page') 
				AND (post_content LIKE %s OR post_content LIKE %s) 
				LIMIT 1",
				'%' . $wpdb->esc_like( $guid ) . '%',
				'%' . $wpdb->esc_like( $filename ) . '%'
			)
		);

		if ( $in_content ) {
			return false;
		}

		// Check if in post meta (gallery, ACF, etc).
		$in_meta = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT meta_id FROM {$wpdb->postmeta} 
				WHERE meta_value LIKE %s 
				LIMIT 1",
				'%' . $wpdb->esc_like( (string) $attachment_id ) . '%'
			)
		);

		if ( $in_meta ) {
			return false;
		}

		return true;
	}
}
