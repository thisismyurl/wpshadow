<?php
/**
 * Google Cloud Storage Integration Diagnostic
 *
 * Google Cloud Storage Integration needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1012.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Cloud Storage Integration Diagnostic Class
 *
 * @since 1.1012.0000
 */
class Diagnostic_GoogleCloudStorageIntegration extends Diagnostic_Base {

	protected static $slug = 'google-cloud-storage-integration';
	protected static $title = 'Google Cloud Storage Integration';
	protected static $description = 'Google Cloud Storage Integration needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check for GCS plugins
		$gcs_configured = get_option( 'gcs_bucket_name', '' ) ||
		                  defined( 'GCS_BUCKET' ) ||
		                  get_option( 'wpgcs_bucket', '' );
		
		if ( ! $gcs_configured ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Bucket name configured
		$bucket_name = get_option( 'gcs_bucket_name', defined( 'GCS_BUCKET' ) ? GCS_BUCKET : '' );
		if ( empty( $bucket_name ) ) {
			$issues[] = __( 'GCS bucket name not configured', 'wpshadow' );
		}
		
		// Check 2: Authentication method
		$auth_method = get_option( 'gcs_auth_method', 'key_file' );
		if ( 'key_file' === $auth_method ) {
			$key_file = get_option( 'gcs_key_file_path', '' );
			if ( empty( $key_file ) || ! file_exists( $key_file ) ) {
				$issues[] = __( 'GCS key file not found or not configured', 'wpshadow' );
			}
		}
		
		// Check 3: CDN configuration
		$cdn_url = get_option( 'gcs_cdn_url', '' );
		if ( empty( $cdn_url ) ) {
			$issues[] = __( 'CDN URL not configured (direct bucket access slower)', 'wpshadow' );
		}
		
		// Check 4: Media library sync
		$auto_sync = get_option( 'gcs_auto_sync_media', false );
		if ( ! $auto_sync ) {
			$issues[] = __( 'Automatic media sync disabled (manual uploads required)', 'wpshadow' );
		}
		
		// Check 5: Local file deletion
		$delete_local = get_option( 'gcs_delete_local_files', false );
		if ( $delete_local ) {
			$issues[] = __( 'Local files deleted after upload (no backup)', 'wpshadow' );
		}
		
		// Check 6: Bucket permissions
		$public_bucket = get_option( 'gcs_bucket_public', false );
		if ( $public_bucket ) {
			$issues[] = __( 'Bucket set to public access (security risk)', 'wpshadow' );
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
				/* translators: %s: list of GCS integration issues */
				__( 'Google Cloud Storage integration has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/google-cloud-storage-integration',
		);
	}
}
