<?php
/**
 * No HTTP/2 or Modern Web Standards Diagnostic
 *
 * Detects when modern web standards are not enabled,
 * causing slower page loads and poor performance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No HTTP/2 or Modern Web Standards
 *
 * Checks whether HTTP/2, HTTPS, and modern
 * web standards are enabled.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_HTTP2_Or_Modern_Web_Standards extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-http2-modern-standards';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP/2 & Modern Web Standards';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether HTTP/2 and modern standards are enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for HTTPS
		$is_https = is_ssl();

		// Check server HTTP version
		$homepage = wp_remote_head( home_url() );
		$http_version = 'HTTP/1.1'; // Default fallback

		if ( ! is_wp_error( $homepage ) ) {
			$headers = wp_remote_retrieve_headers( $homepage );
			if ( isset( $headers['server'] ) ) {
				if ( stripos( $headers['server'], 'HTTP/2' ) !== false ) {
					$http_version = 'HTTP/2';
				}
			}
		}

		if ( ! $is_https || $http_version === 'HTTP/1.1' ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your site is using older web standards (HTTP/1.1, not HTTPS, or missing modern optimizations). HTTP/2 can load pages 20-50% faster because it: loads multiple files in parallel (instead of one-by-one), compresses headers better, enables server push. HTTPS (secure connection) is now expected by users and required by browsers. Modern standards like HTTP/2, HTTPS, and compression can compound for 20-50% faster performance.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Page Load Performance',
					'potential_gain' => '+20-50% faster page loads',
					'roi_explanation' => 'HTTP/2 and modern standards improve page speed, directly improving SEO rankings and conversion rates.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/http2-modern-web-standards?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
