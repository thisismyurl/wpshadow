<?php
/**
 * Wp Offload Media S3 Sync Diagnostic
 *
 * Wp Offload Media S3 Sync detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.778.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Offload Media S3 Sync Diagnostic Class
 *
 * @since 1.778.0000
 */
class Diagnostic_WpOffloadMediaS3Sync extends Diagnostic_Base {

	protected static $slug = 'wp-offload-media-s3-sync';
	protected static $title = 'Wp Offload Media S3 Sync';
	protected static $description = 'Wp Offload Media S3 Sync detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for WP Offload Media plugin
		if ( ! class_exists( 'Amazon_S3_And_CloudFront' ) && ! function_exists( 'as3cf_init' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: S3 bucket configured
		$bucket = get_option( 'tantan_wordpress_s3_bucket', '' );
		if ( empty( $bucket ) ) {
			$issues[] = __( 'S3 bucket not configured', 'wpshadow' );
			return null;
		}
		
		// Check 2: Access credentials
		$access_key = defined( 'AS3CF_AWS_ACCESS_KEY_ID' ) ? AS3CF_AWS_ACCESS_KEY_ID : get_option( 'tantan_wordpress_s3_access_key', '' );
		if ( empty( $access_key ) ) {
			$issues[] = __( 'S3 access credentials not configured', 'wpshadow' );
		}
		
		// Check 3: Failed uploads
		$failed_uploads = get_option( 'as3cf_attachment_error_log', array() );
		if ( is_array( $failed_uploads ) && count( $failed_uploads ) > 10 ) {
			$issues[] = sprintf( __( '%d failed S3 uploads logged', 'wpshadow' ), count( $failed_uploads ) );
		}
		
		// Check 4: URL rewriting enabled
		$rewrite_urls = get_option( 'tantan_wordpress_s3_cloudfront', '' );
		$serve_from_s3 = get_option( 'tantan_wordpress_s3_virtual_host', false );
		
		if ( ! $serve_from_s3 && empty( $rewrite_urls ) ) {
			$issues[] = __( 'Media URLs not rewritten to S3/CDN', 'wpshadow' );
		}
		
		// Check 5: Local media cleanup
		$remove_local = get_option( 'tantan_wordpress_s3_remove_local_file', false );
		$upload_dir = wp_upload_dir();
		$upload_size = 0;
		
		if ( $remove_local ) {
			// Check for orphaned local files
			$local_files = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
					'amazonS3_info',
					''
				)
			);
			
			if ( $local_files > 50 ) {
				$issues[] = sprintf( __( '%d media items not synced to S3', 'wpshadow' ), $local_files );
			}
		}
		
		// Check 6: CDN configuration
		if ( ! empty( $rewrite_urls ) && strpos( $rewrite_urls, '.cloudfront.net' ) !== false ) {
			$custom_domain = get_option( 'tantan_wordpress_s3_cloudfront_cname', '' );
			if ( empty( $custom_domain ) ) {
				$issues[] = __( 'CloudFront without custom domain (branding opportunity)', 'wpshadow' );
			}
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 65;
		} elseif ( count( $issues ) >= 2 ) {
			$threat_level = 58;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of S3 sync issues */
				__( 'WP Offload Media S3 sync has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-offload-media-s3-sync',
		);
	}
}
