<?php
/**
 * Aws S3 Media Offload Diagnostic
 *
 * Aws S3 Media Offload needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1010.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Aws S3 Media Offload Diagnostic Class
 *
 * @since 1.1010.0000
 */
class Diagnostic_AwsS3MediaOffload extends Diagnostic_Base {

	protected static $slug = 'aws-s3-media-offload';
	protected static $title = 'Aws S3 Media Offload';
	protected static $description = 'Aws S3 Media Offload needs attention';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'as3cf_get_service' ) && ! class_exists( 'Amazon_S3_And_CloudFront' ) && ! defined( 'AS3CF_PLUGIN_PATH' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: AWS credentials.
		$s3_key = get_option( 'as3cf_aws_key', '' );
		if ( empty( $s3_key ) ) {
			$issues[] = 'AWS credentials not configured';
		}
		
		// Check 2: S3 bucket.
		$s3_bucket = get_option( 'as3cf_bucket', '' );
		if ( empty( $s3_bucket ) ) {
			$issues[] = 'S3 bucket not configured';
		}
		
		// Check 3: Offload enabled.
		$offload_enabled = get_option( 'as3cf_offload_media', '0' );
		if ( '0' === $offload_enabled ) {
			$issues[] = 'media offloading disabled';
		}
		
		// Check 4: SSL for uploads.
		if ( ! is_ssl() ) {
			$issues[] = 'uploads without HTTPS';
		}
		
		// Check 5: Backup strategy.
		$backup_synced = get_option( 'as3cf_backup_synced', '0' );
		if ( '0' === $backup_synced ) {
			$issues[] = 'no S3 backup sync';
		}
		
		// Check 6: Region specified.
		$s3_region = get_option( 'as3cf_aws_region', '' );
		if ( empty( $s3_region ) ) {
			$issues[] = 'S3 region not specified';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 50 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'S3 media offload issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/aws-s3-media-offload',
			);
		}
		
		return null;
	}
}
