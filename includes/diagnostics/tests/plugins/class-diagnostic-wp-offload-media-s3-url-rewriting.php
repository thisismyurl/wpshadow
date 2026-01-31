<?php
/**
 * Wp Offload Media S3 Url Rewriting Diagnostic
 *
 * Wp Offload Media S3 Url Rewriting detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.782.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Offload Media S3 Url Rewriting Diagnostic Class
 *
 * @since 1.782.0000
 */
class Diagnostic_WpOffloadMediaS3UrlRewriting extends Diagnostic_Base {

	protected static $slug = 'wp-offload-media-s3-url-rewriting';
	protected static $title = 'Wp Offload Media S3 Url Rewriting';
	protected static $description = 'Wp Offload Media S3 Url Rewriting detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'Amazon_S3_And_CloudFront' ) && ! defined( 'AS3CF_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check if S3 bucket is configured
		$bucket = get_option( 'tantan_wordpress_s3_bucket', '' );
		if ( empty( $bucket ) && ! defined( 'AS3CF_BUCKET' ) ) {
			$issues[] = 'S3 bucket not configured';
		}

		// Check for URL rewriting enabled
		$rewrite_urls = get_option( 'tantan_wordpress_s3_rewrite_file_urls', '0' );
		if ( '0' === $rewrite_urls ) {
			$issues[] = 'URL rewriting disabled (files not served from S3)';
		}

		// Check for custom domain configuration
		$custom_domain = get_option( 'tantan_wordpress_s3_cloudfront', '' );
		if ( empty( $custom_domain ) && '1' === $rewrite_urls ) {
			$issues[] = 'using S3 URLs directly (CloudFront CDN not configured)';
		}

		// Check for offloaded media count
		global $wpdb;
		$offloaded_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s",
				'amazonS3_info'
			)
		);

		$total_media = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
				'attachment'
			)
		);

		if ( $total_media > 0 && $offloaded_count < ( $total_media * 0.5 ) ) {
			$percent = round( ( $offloaded_count / $total_media ) * 100 );
			$issues[] = "only {$percent}% of media offloaded to S3";
		}

		// Check for local file removal
		$remove_local = get_option( 'tantan_wordpress_s3_remove_local_file', '0' );
		if ( '0' === $remove_local && $offloaded_count > 100 ) {
			$issues[] = 'local files not removed after S3 upload (wasting disk space)';
		}

		// Check for ACL settings
		$object_acl = get_option( 'tantan_wordpress_s3_object_acl', 'public-read' );
		if ( 'private' === $object_acl && '1' === $rewrite_urls ) {
			$issues[] = 'files set to private ACL (may not be accessible publicly)';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 75, 45 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WP Offload Media S3 URL rewriting issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-offload-media-s3-url-rewriting',
			);
		}

		return null;
	}
}
