<?php
/**
 * Wp Offload Media S3 Performance Diagnostic
 *
 * Wp Offload Media S3 Performance detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.779.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Offload Media S3 Performance Diagnostic Class
 *
 * @since 1.779.0000
 */
class Diagnostic_WpOffloadMediaS3Performance extends Diagnostic_Base {

	protected static $slug = 'wp-offload-media-s3-performance';
	protected static $title = 'Wp Offload Media S3 Performance';
	protected static $description = 'Wp Offload Media S3 Performance detected';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();
		
		// Check 1: CDN enabled for S3
		$cdn_enabled = get_option( 'amazon_s3_and_cloudfront_cloudfront', false );
		if ( ! $cdn_enabled ) {
			$issues[] = 'CDN not enabled for S3';
		}
		
		// Check 2: Remove local files after upload
		$remove_local = get_option( 'amazon_s3_and_cloudfront_remove_local_file', false );
		if ( ! $remove_local ) {
			$issues[] = 'Local files not removed after upload';
		}
		
		// Check 3: Force HTTPS for media
		$force_https = get_option( 'amazon_s3_and_cloudfront_force_https', false );
		if ( ! $force_https ) {
			$issues[] = 'HTTPS not forced for media';
		}
		
		// Check 4: Image optimization enabled
		$image_opt = get_option( 'amazon_s3_and_cloudfront_image_optimization', false );
		if ( ! $image_opt ) {
			$issues[] = 'Image optimization disabled';
		}
		
		// Check 5: Object prefix configured
		$object_prefix = get_option( 'amazon_s3_and_cloudfront_object_prefix', '' );
		if ( empty( $object_prefix ) ) {
			$issues[] = 'Object prefix not configured';
		}
		
		// Check 6: Bucket region optimized
		$bucket_region = get_option( 'amazon_s3_and_cloudfront_region', '' );
		if ( empty( $bucket_region ) ) {
			$issues[] = 'Bucket region not specified';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WP Offload Media S3 performance issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-offload-media-s3-performance',
			);
		}
		
		return null;
	}
}
