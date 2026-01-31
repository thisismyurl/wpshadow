<?php
/**
 * Wp Offload Media S3 Bucket Policy Diagnostic
 *
 * Wp Offload Media S3 Bucket Policy detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.781.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Offload Media S3 Bucket Policy Diagnostic Class
 *
 * @since 1.781.0000
 */
class Diagnostic_WpOffloadMediaS3BucketPolicy extends Diagnostic_Base {

	protected static $slug = 'wp-offload-media-s3-bucket-policy';
	protected static $title = 'Wp Offload Media S3 Bucket Policy';
	protected static $description = 'Wp Offload Media S3 Bucket Policy detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! ( defined( 'AS3CF_VERSION' ) || function_exists( 'as3cf' ) ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Bucket policy verified
		$policy_verified = get_option( 'as3cf_bucket_policy_verified', 0 );
		if ( ! $policy_verified ) {
			$issues[] = 'S3 bucket policy not verified';
		}

		// Check 2: Public access block
		$public_block = get_option( 'as3cf_public_access_block_enabled', 0 );
		if ( ! $public_block ) {
			$issues[] = 'Public access block not enabled';
		}

		// Check 3: ACL configuration
		$acl = get_option( 'as3cf_object_acl_configured', 0 );
		if ( ! $acl ) {
			$issues[] = 'Object ACL not properly configured';
		}

		// Check 4: CloudFront OAI
		$oai = get_option( 'as3cf_cloudfront_oai_enabled', 0 );
		if ( ! $oai ) {
			$issues[] = 'CloudFront Origin Access Identity not configured';
		}

		// Check 5: Server-side encryption
		$encryption = get_option( 'as3cf_sse_enabled', 0 );
		if ( ! $encryption ) {
			$issues[] = 'Server-side encryption not enabled';
		}

		// Check 6: Versioning enabled
		$versioning = get_option( 'as3cf_bucket_versioning_enabled', 0 );
		if ( ! $versioning ) {
			$issues[] = 'Bucket versioning not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 50;
			$threat_multiplier = 6;
			$max_threat = 80;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d S3 bucket policy issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-offload-media-s3-bucket-policy',
			);
		}

		return null;
	}
}
