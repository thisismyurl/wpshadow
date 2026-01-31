<?php
/**
 * Wp Migrate Db Pro Media Files Diagnostic
 *
 * Wp Migrate Db Pro Media Files issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1064.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Migrate Db Pro Media Files Diagnostic Class
 *
 * @since 1.1064.0000
 */
class Diagnostic_WpMigrateDbProMediaFiles extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-pro-media-files';
	protected static $title = 'Wp Migrate Db Pro Media Files';
	protected static $description = 'Wp Migrate Db Pro Media Files issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for WP Migrate DB Pro
		if ( ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Media Files addon
		$media_addon = class_exists( 'WPMDB_Media_Files' ) || defined( 'WPMDB_MEDIA_FILES_VERSION' );
		if ( ! $media_addon ) {
			return null;
		}
		
		// Check 2: File size limit
		$file_limit = get_option( 'wpmdb_media_file_limit', 10 ); // MB
		if ( $file_limit < 5 ) {
			$issues[] = sprintf( __( 'Media file limit: %dMB (may skip large files)', 'wpshadow' ), $file_limit );
		}
		
		// Check 3: Chunking enabled
		$chunk_size = get_option( 'wpmdb_media_chunk_size', 1 ); // MB
		if ( $chunk_size > 5 ) {
			$issues[] = sprintf( __( 'Large chunk size: %dMB (timeout risk)', 'wpshadow' ), $chunk_size );
		}
		
		// Check 4: Attachment metadata sync
		$sync_metadata = get_option( 'wpmdb_sync_attachment_meta', true );
		if ( ! $sync_metadata ) {
			$issues[] = __( 'Attachment metadata not synced (broken images)', 'wpshadow' );
		}
		
		// Check 5: File verification
		$verify_files = get_option( 'wpmdb_verify_media_files', false );
		if ( ! $verify_files ) {
			$issues[] = __( 'File verification disabled (corruption undetected)', 'wpshadow' );
		}
		
		// Check 6: Bandwidth throttling
		$throttle = get_option( 'wpmdb_media_throttle', 0 );
		if ( $throttle === 0 ) {
			$issues[] = __( 'No bandwidth throttling (server overload risk)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of media migration issues */
				__( 'WP Migrate DB Pro media files has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-pro-media-files',
		);
	}
}
