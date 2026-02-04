<?php
/**
 * Incomplete Media Library Export Diagnostic
 *
 * Detects when media attachments are excluded from exports or
 * only references without files.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7033.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Incomplete Media Library Export Diagnostic Class
 *
 * Tests for media attachment export completeness.
 *
 * @since 1.7033.1200
 */
class Diagnostic_Incomplete_Media_Library_Export extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'incomplete-media-library-export';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Incomplete Media Library Export';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects media attachments excluded from exports';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that media attachments are properly included
	 * in export files.
	 *
	 * @since  1.7033.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Get all attachment posts.
		$total_attachments = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->posts} 
				WHERE post_type = %s 
				AND post_status IN (%s, %s)",
				'attachment',
				'publish',
				'inherit'
			)
		);

		// Check for attachments with missing files.
		$upload_dir = wp_upload_dir();
		$missing_files = 0;
		$broken_attachments = array();

		$attachments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT ID, post_title, guid, post_parent 
				FROM {$wpdb->posts} 
				WHERE post_type = %s 
				AND post_status IN (%s, %s) 
				LIMIT 200",
				'attachment',
				'publish',
				'inherit'
			)
		);

		foreach ( $attachments as $attachment ) {
			// Check if file exists.
			$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $attachment->guid );
			$file_path = str_replace( get_home_url(), ABSPATH, $file_path );

			if ( ! file_exists( $file_path ) ) {
				$missing_files++;

				if ( count( $broken_attachments ) < 10 ) {
					$broken_attachments[] = array(
						'attachment_id' => $attachment->ID,
						'title'         => $attachment->post_title,
						'guid'          => $attachment->guid,
						'parent_post'   => $attachment->post_parent,
						'issue'         => __( 'File missing', 'wpshadow' ),
					);
				}
			}
		}

		// Get storage size.
		$total_attachment_size = 0;
		foreach ( $attachments as $attachment ) {
			$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $attachment->guid );
			if ( file_exists( $file_path ) ) {
				$total_attachment_size += filesize( $file_path );
			}
		}

		// Check for attachment metadata.
		$attachments_with_metadata = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) 
				FROM {$wpdb->postmeta} 
				WHERE meta_key LIKE %s 
				AND post_id IN (
					SELECT ID FROM {$wpdb->posts} 
					WHERE post_type = %s
				)",
				'%_wp_attachment%',
				'attachment'
			)
		);

		// Check for external media (hosted elsewhere).
		$external_media = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->posts} 
				WHERE post_type = %s 
				AND guid NOT LIKE %s",
				'attachment',
				$upload_dir['baseurl'] . '%'
			)
		);

		// Check for attached media vs orphaned media.
		$attached_media = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) 
				FROM {$wpdb->posts} 
				WHERE post_type = %s 
				AND post_parent > 0",
				'attachment'
			)
		);

		$orphaned_media = $total_attachments - $attached_media;

		// Check WXR attachment export support.
		$wxr_attachments_included = apply_filters( 'wxr_export_attachments', true );

		// Check for attachment post meta that might get excluded.
		$attachment_meta_excluded = apply_filters( 'wxr_export_skip_postmeta', false, array( 'post_type' => 'attachment' ) );

		// Check for CDN or media library plugins that might affect export.
		$media_plugins = array(
			'amazon-s3-and-cloudfront/wordpress-s3.php' => 'WP Offload Media',
			'offload-media-lite/offload-media-lite.php' => 'Offload Media Lite',
			'bunny-cdn/bunny-cdn.php' => 'Bunny CDN',
		);

		$external_storage_plugin_active = false;
		foreach ( $media_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$external_storage_plugin_active = true;
				break;
			}
		}

		if ( $total_attachments > 0 && ( $missing_files > 0 || $external_media > 0 || ! $wxr_attachments_included ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of attachment issues */
					__( 'Media library export issues detected with %d attachments', 'wpshadow' ),
					$total_attachments
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/incomplete-media-library-export',
				'details'      => array(
					'total_attachments'               => $total_attachments,
					'attached_media'                  => $attached_media,
					'orphaned_media'                  => $orphaned_media,
					'missing_files'                   => $missing_files,
					'external_media_references'       => $external_media,
					'total_attachment_storage'        => size_format( $total_attachment_size ),
					'attachments_with_metadata'       => $attachments_with_metadata,
					'broken_attachment_examples'      => $broken_attachments,
					'wxr_attachments_export_enabled'  => $wxr_attachments_included,
					'attachment_meta_excluded'        => $attachment_meta_excluded,
					'external_storage_plugin_active'  => $external_storage_plugin_active,
					'visual_content_impact'           => sprintf(
						/* translators: %d: total size */
						__( '%d MB of visual content at risk in export', 'wpshadow' ),
						round( $total_attachment_size / 1024 / 1024 )
					),
					'backup_incompleteness'           => __( 'Backup export will be missing images, videos, and documents', 'wpshadow' ),
					'migration_risk'                  => __( 'Migrated site will have broken image links and missing media', 'wpshadow' ),
					'restore_degradation'             => __( 'Restored site will appear broken without visual content', 'wpshadow' ),
					'fix_methods'                     => array(
						__( 'Use export plugin with media library support', 'wpshadow' ),
						__( 'Create separate backup of /uploads directory', 'wpshadow' ),
						__( 'Use database backup with file system backup', 'wpshadow' ),
						__( 'Enable attachment export in WXR settings', 'wpshadow' ),
						__( 'For external storage, export metadata and files separately', 'wpshadow' ),
					),
					'verification'                    => array(
						__( 'Download WXR export and search for <wp:attachment_url> entries', 'wpshadow' ),
						__( 'Verify attachment count in XML matches site count', 'wpshadow' ),
						__( 'Check for broken image links in export', 'wpshadow' ),
						__( 'Test import on staging site', 'wpshadow' ),
						__( 'Verify all media displays after import', 'wpshadow' ),
					),
					'critical_note'                   => __( 'Without media library backup, visual content is unrecoverable - exports must include or reference all attachments', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
