<?php
/**
 * Shortpixel Cdn Integration Diagnostic
 *
 * Shortpixel Cdn Integration detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.746.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortpixel Cdn Integration Diagnostic Class
 *
 * @since 1.746.0000
 */
class Diagnostic_ShortpixelCdnIntegration extends Diagnostic_Base {

	protected static $slug = 'shortpixel-cdn-integration';
	protected static $title = 'Shortpixel Cdn Integration';
	protected static $description = 'Shortpixel Cdn Integration detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'SHORTPIXEL_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get ShortPixel settings
		$shortpixel_settings = get_option( 'wp-short-pixel-options', array() );

		// Check API key
		$api_key = isset( $shortpixel_settings['apiKey'] ) ? $shortpixel_settings['apiKey'] : '';
		if ( empty( $api_key ) ) {
			$issues[] = 'api_key_not_configured';
			$threat_level += 30;
		}

		// Check CDN settings
		$cdn_enabled = isset( $shortpixel_settings['cdn_enabled'] ) ? $shortpixel_settings['cdn_enabled'] : false;
		if ( ! $cdn_enabled ) {
			$issues[] = 'cdn_not_enabled';
			$threat_level += 25;
		}

		// Check CDN domain
		if ( $cdn_enabled ) {
			$cdn_domain = isset( $shortpixel_settings['cdn_domain'] ) ? $shortpixel_settings['cdn_domain'] : '';
			if ( empty( $cdn_domain ) ) {
				$issues[] = 'cdn_domain_not_configured';
				$threat_level += 20;
			} else {
				// Test CDN availability
				$test_url = 'https://' . $cdn_domain;
				$response = wp_remote_get( $test_url, array( 'timeout' => 5 ) );
				if ( is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) !== 200 ) {
					$issues[] = 'cdn_domain_unreachable';
					$threat_level += 15;
				}
			}
		}

		// Check API quota
		if ( ! empty( $api_key ) ) {
			$quota_exceeded = isset( $shortpixel_settings['quotaExceeded'] ) ? $shortpixel_settings['quotaExceeded'] : false;
			if ( $quota_exceeded ) {
				$issues[] = 'api_quota_exceeded';
				$threat_level += 15;
			}
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of CDN integration issues */
				__( 'ShortPixel CDN integration has problems: %s. This prevents optimal image delivery and slows page loads.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/shortpixel-cdn-integration',
			);
		}
		
		return null;
	}
}
