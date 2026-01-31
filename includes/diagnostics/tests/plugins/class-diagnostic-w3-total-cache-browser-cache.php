<?php
/**
 * W3 Total Cache Browser Cache Diagnostic
 *
 * W3 Total Cache Browser Cache not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.888.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * W3 Total Cache Browser Cache Diagnostic Class
 *
 * @since 1.888.0000
 */
class Diagnostic_W3TotalCacheBrowserCache extends Diagnostic_Base {

	protected static $slug = 'w3-total-cache-browser-cache';
	protected static $title = 'W3 Total Cache Browser Cache';
	protected static $description = 'W3 Total Cache Browser Cache not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'W3TC' ) && ! get_option( 'w3tc_config', array() ) ) {
			return null;
		}

		$issues = array();
		$config = get_option( 'w3tc_config', array() );

		// Check 1: Browser cache enabled
		$browser_cache = isset( $config['browsercache.enabled'] ) ? (bool) $config['browsercache.enabled'] : false;
		if ( ! $browser_cache ) {
			$issues[] = 'Browser cache not enabled';
		}

		// Check 2: Expires headers
		$expires = isset( $config['browsercache.expires'] ) ? (bool) $config['browsercache.expires'] : false;
		if ( ! $expires ) {
			$issues[] = 'Expires headers not enabled';
		}

		// Check 3: Cache control headers
		$cache_control = isset( $config['browsercache.cache.control'] ) ? (bool) $config['browsercache.cache.control'] : false;
		if ( ! $cache_control ) {
			$issues[] = 'Cache-Control headers not enabled';
		}

		// Check 4: ETag support
		$etag = isset( $config['browsercache.etag'] ) ? (bool) $config['browsercache.etag'] : false;
		if ( ! $etag ) {
			$issues[] = 'ETag headers not enabled';
		}

		// Check 5: Compression for static files
		$gzip = isset( $config['browsercache.compression'] ) ? (bool) $config['browsercache.compression'] : false;
		if ( ! $gzip ) {
			$issues[] = 'Gzip compression not enabled for browser cache';
		}

		// Check 6: Cache policy for HTML
		$html_cache = isset( $config['browsercache.html.cache.control'] ) ? (bool) $config['browsercache.html.cache.control'] : false;
		if ( ! $html_cache ) {
			$issues[] = 'HTML cache policy not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 45;
			$threat_multiplier = 6;
			$max_threat = 75;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d W3TC browser cache issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/w3-total-cache-browser-cache',
			);
		}

		return null;
	}
}
