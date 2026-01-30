<?php
/**
 * Cloudinary Api Security Diagnostic
 *
 * Cloudinary Api Security detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.783.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cloudinary Api Security Diagnostic Class
 *
 * @since 1.783.0000
 */
class Diagnostic_CloudinaryApiSecurity extends Diagnostic_Base {

	protected static $slug = 'cloudinary-api-security';
	protected static $title = 'Cloudinary Api Security';
	protected static $description = 'Cloudinary Api Security detected';
	protected static $family = 'security';

	public static function check() {
		// Check for Cloudinary plugin
		if ( ! class_exists( 'Cloudinary\Plugin' ) && ! defined( 'CLOUDINARY_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: API credentials in wp-config.php vs database
		$cloud_name = get_option( 'cloudinary_cloud_name', '' );
		$api_key = get_option( 'cloudinary_api_key', '' );
		$api_secret = get_option( 'cloudinary_api_secret', '' );
		
		if ( ! empty( $cloud_name ) || ! empty( $api_key ) || ! empty( $api_secret ) ) {
			$issues[] = __( 'Cloudinary API credentials stored in database (should use wp-config.php)', 'wpshadow' );
		}
		
		// Check 2: Verify constants are defined
		if ( ! defined( 'CLOUDINARY_URL' ) && ( empty( $cloud_name ) && empty( $api_key ) ) ) {
			$issues[] = __( 'Cloudinary credentials not configured', 'wpshadow' );
		}
		
		// Check 3: API secret exposure in frontend
		global $wpdb;
		$frontend_exposure = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE %s AND meta_value LIKE %s",
				'%cloudinary%',
				'%api_secret%'
			)
		);
		
		if ( $frontend_exposure > 0 ) {
			$issues[] = sprintf( __( 'API secret found in %d post metadata entries', 'wpshadow' ), $frontend_exposure );
		}
		
		// Check 4: Signed URLs enabled for secure content
		$use_signed_urls = get_option( 'cloudinary_use_signed_urls', false );
		if ( ! $use_signed_urls ) {
			$issues[] = __( 'Signed URLs not enabled (recommended for security)', 'wpshadow' );
		}
		
		// Check 5: API rate limiting
		$rate_limit = get_option( 'cloudinary_rate_limit_enabled', false );
		if ( ! $rate_limit ) {
			$issues[] = __( 'API rate limiting not configured', 'wpshadow' );
		}
		
		// Check 6: Webhook signature verification
		$webhook_secret = get_option( 'cloudinary_webhook_secret', '' );
		$webhook_enabled = get_option( 'cloudinary_webhooks_enabled', false );
		if ( $webhook_enabled && empty( $webhook_secret ) ) {
			$issues[] = __( 'Webhooks enabled without signature verification', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 70;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 85;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 78;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of security issues */
				__( 'Cloudinary API has %d security issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cloudinary-api-security',
		);
	}
}
