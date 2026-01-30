<?php
/**
 * CDN Integration Diagnostic
 *
 * Validates CDN configuration with cache plugins.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1810
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CDN Integration Class
 *
 * Checks CDN setup.
 *
 * @since 1.5029.1810
 */
class Diagnostic_CDN_Integration extends Diagnostic_Base {

	protected static $slug        = 'cdn-integration';
	protected static $title       = 'CDN Integration';
	protected static $description = 'Validates CDN configuration';
	protected static $family      = 'plugins';

	public static function check() {
		$cache_key = 'wpshadow_cdn_integration';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();
		$cdn_configured = false;

		// Check WP Rocket CDN.
		if ( function_exists( 'get_rocket_option' ) ) {
			$cdn_cnames = get_rocket_option( 'cdn_cnames', array() );
			if ( ! empty( $cdn_cnames ) ) {
				$cdn_configured = true;
				
				// Validate CDN URLs.
				foreach ( $cdn_cnames as $cname ) {
					if ( ! filter_var( 'https://' . $cname, FILTER_VALIDATE_URL ) ) {
						$issues[] = sprintf( 'Invalid CDN URL: %s', $cname );
					}
				}
			}
		}

		// Check WP Fastest Cache CDN.
		if ( class_exists( 'WpFastestCache' ) ) {
			$cdn_options = get_option( 'WpFcCDN', array() );
			if ( ! empty( $cdn_options ) && isset( $cdn_options['cdn_url'] ) ) {
				$cdn_configured = true;
			}
		}

		// Check Cloudflare integration.
		if ( defined( 'CLOUDFLARE_VERSION' ) || is_plugin_active( 'cloudflare/cloudflare.php' ) ) {
			$cdn_configured = true;
		}

		if ( ! $cdn_configured ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No CDN configured. Consider using a CDN for faster global content delivery.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cdn-setup',
				'data'         => array(
					'cdn_active' => false,
					'recommendation' => 'Configure Cloudflare or another CDN',
				),
			);

			set_transient( $cache_key, $result, 7 * DAY_IN_SECONDS );
			return $result;
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d CDN configuration issues detected.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cdn-troubleshooting',
				'data'         => array(
					'cdn_active' => true,
					'cdn_issues' => $issues,
					'total_issues' => count( $issues ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
