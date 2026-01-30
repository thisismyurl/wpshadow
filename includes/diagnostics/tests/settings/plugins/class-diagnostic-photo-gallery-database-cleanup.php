<?php
/**
 * Photo Gallery Database Cleanup Diagnostic
 *
 * Photo gallery database entries accumulating.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.502.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Photo Gallery Database Cleanup Diagnostic Class
 *
 * @since 1.502.0000
 */
class Diagnostic_PhotoGalleryDatabaseCleanup extends Diagnostic_Base {

	protected static $slug = 'photo-gallery-database-cleanup';
	protected static $title = 'Photo Gallery Database Cleanup';
	protected static $description = 'Photo gallery database entries accumulating';
	protected static $family = 'performance';

	public static function check() {
		// Check if Photo Gallery plugin is installed
		if ( ! function_exists( 'photo_gallery' ) && ! defined( 'WD_BWG_VERSION' ) ) {
			return null;
		}

		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check gallery tables
		$gallery_table = $wpdb->prefix . 'bwg_gallery';
		$image_table = $wpdb->prefix . 'bwg_image';

		// Check if tables exist
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$gallery_table}'" ) !== $gallery_table ) {
			return null;
		}

		// Check for orphaned images
		$orphaned_images = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$image_table} i
			 LEFT JOIN {$gallery_table} g ON i.gallery_id = g.id
			 WHERE g.id IS NULL"
		);
		if ( $orphaned_images > 10 ) {
			$issues[] = 'orphaned_images';
			$threat_level += 15;
		}

		// Check database table size
		$table_size = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS size_mb
				 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = %s AND TABLE_NAME = %s",
				DB_NAME,
				$image_table
			)
		);
		if ( $table_size > 500 ) {
			$issues[] = 'database_table_too_large';
			$threat_level += 20;
		}

		// Check for deleted gallery references
		$deleted_galleries = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$gallery_table} WHERE published = -1"
		);
		if ( $deleted_galleries > 5 ) {
			$issues[] = 'undeleted_gallery_data';
			$threat_level += 10;
		}

		// Check for old thumbnails
		$upload_dir = wp_upload_dir();
		$thumb_dir = $upload_dir['basedir'] . '/photo-gallery/thumb';
		if ( is_dir( $thumb_dir ) ) {
			$thumb_files = glob( $thumb_dir . '/*' );
			if ( $thumb_files && count( $thumb_files ) > 1000 ) {
				$issues[] = 'excessive_thumbnail_files';
				$threat_level += 15;
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of database cleanup issues */
				__( 'Photo Gallery database needs cleanup: %s. This wastes database space (%.2f MB) and slows queries.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) ),
				$table_size
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/photo-gallery-database-cleanup',
			);
		}
		
		return null;
	}
}
