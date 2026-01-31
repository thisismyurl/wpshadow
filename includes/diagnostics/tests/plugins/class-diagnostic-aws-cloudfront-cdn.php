<?php
/**
 * Aws Cloudfront Cdn Diagnostic
 *
 * Aws Cloudfront Cdn needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1011.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Aws Cloudfront Cdn Diagnostic Class
 *
 * @since 1.1011.0000
 */
class Diagnostic_AwsCloudfrontCdn extends Diagnostic_Base {

	protected static $slug = 'aws-cloudfront-cdn';
	protected static $title = 'Aws Cloudfront Cdn';
	protected static $description = 'Aws Cloudfront Cdn needs attention';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'get_cloudfront_domain' ) && ! class_exists( 'CloudFront_Client' ) && ! defined( 'CLOUDFRONT_DOMAIN' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: CloudFront domain configured.
		$cf_domain = get_option( 'cloudfront_domain', '' );
		if ( empty( $cf_domain ) ) {
			$issues[] = 'CloudFront domain not configured';
		}
		
		// Check 2: SSL enabled.
		if ( ! is_ssl() && ! empty( $cf_domain ) ) {
			$issues[] = 'CDN without HTTPS (mixed content risk)';
		}
		
		// Check 3: Cache TTL.
		$cf_cache_ttl = get_option( 'cloudfront_cache_ttl', 0 );
		if ( 0 === $cf_cache_ttl ) {
			$issues[] = 'caching disabled';
		}
		
		// Check 4: Auto invalidation.
		$cf_auto_invalidate = get_option( 'cloudfront_auto_invalidate', '1' );
		if ( '0' === $cf_auto_invalidate ) {
			$issues[] = 'automatic cache invalidation disabled';
		}
		
		// Check 5: Distribution status.
		$cf_enabled = get_option( 'cloudfront_enabled', '0' );
		if ( '0' === $cf_enabled ) {
			$issues[] = 'CloudFront integration disabled';
		}
		
		// Check 6: Geographic restrictions.
		$cf_restrictions = get_option( 'cloudfront_geo_restrictions', array() );
		if ( is_array( $cf_restrictions ) && count( $cf_restrictions ) > 20 ) {
			$issues[] = 'too many geo restrictions';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 50 + ( count( $issues ) * 4 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'CloudFront issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/aws-cloudfront-cdn',
			);
		}
		
		return null;
	}
}
