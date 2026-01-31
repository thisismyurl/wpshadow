<?php
/**
 * Wp Offload Media S3 Security Diagnostic
 *
 * Wp Offload Media S3 Security detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.780.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Offload Media S3 Security Diagnostic Class
 *
 * @since 1.780.0000
 */
class Diagnostic_WpOffloadMediaS3Security extends Diagnostic_Base {

	protected static $slug = 'wp-offload-media-s3-security';
	protected static $title = 'Wp Offload Media S3 Security';
	protected static $description = 'Wp Offload Media S3 Security detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! is_plugin_active( 'amazon-s3-and-cloudfront/wordpress-s3.php' ) && ! class_exists( 'AS3CF' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify S3 credentials
		$s3_credentials = get_option( 'as3cf_access_key', '' );
		if ( empty( $s3_credentials ) ) {
			$issues[] = __( 'S3 credentials not configured', 'wpshadow' );
		}

		// Check 2: Check SSL for media uploads
		if ( ! is_ssl() ) {
			$issues[] = __( 'SSL not enabled for secure media uploads', 'wpshadow' );
		}

		// Check 3: Verify bucket security
		$bucket_security = get_option( 'as3cf_bucket_security', false );
		if ( ! $bucket_security ) {
			$issues[] = __( 'S3 bucket security not configured', 'wpshadow' );
		}

		// Check 4: Check ACL permissions
		$acl_configured = get_option( 'as3cf_acl_configured', false );
		if ( ! $acl_configured ) {
			$issues[] = __( 'S3 ACL permissions not configured', 'wpshadow' );
		}

		// Check 5: Verify signed URLs
		$signed_urls = get_option( 'as3cf_signed_urls', false );
		if ( ! $signed_urls ) {
			$issues[] = __( 'Signed URLs for media not enabled', 'wpshadow' );
		}

		// Check 6: Check encryption
		$encryption = get_option( 'as3cf_encryption', false );
		if ( ! $encryption ) {
			$issues[] = __( 'Server-side encryption for S3 not enabled', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 100, 75 + ( count( $issues ) * 5 ) );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: Comma-separated list of issues */
					__( 'WP Offload Media S3 security issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'     => 'high',
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/wp-offload-media-s3-security',
			);
		}

		return null;
	}
}
