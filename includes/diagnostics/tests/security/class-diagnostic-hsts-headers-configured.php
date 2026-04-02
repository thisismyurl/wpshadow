<?php
/**
 * HSTS Headers Configured Diagnostic
 *
 * Checks for Strict-Transport-Security headers and recommended settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Request_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HSTS Headers Configured Diagnostic Class
 *
 * Validates HSTS header presence, max-age, and includeSubDomains.
 *
 * @since 1.6093.1200
 */
class Diagnostic_HSTS_Headers_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hsts-headers-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HSTS Headers Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for Strict-Transport-Security headers and recommended settings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_ssl() ) {
			return null;
		}

		$response = Diagnostic_Request_Helper::head_result( home_url( '/' ) );
		if ( ! $response['success'] || empty( $response['response'] ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to retrieve response headers to verify HSTS configuration.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hsts-headers-configured',
			);
		}

		$header = self::get_header_value( $response['response'], 'strict-transport-security' );

		if ( empty( $header ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Strict-Transport-Security header is missing. Add HSTS to enforce HTTPS and prevent downgrade attacks.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hsts-headers-configured',
			);
		}

		$max_age = self::get_max_age( $header );
		$has_subdomains = ( false !== stripos( $header, 'includesubdomains' ) );
		$has_preload = ( false !== stripos( $header, 'preload' ) );

		if ( $max_age < 31536000 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'HSTS max-age is below 1 year. Increase max-age to at least 31536000 seconds for strong protection.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hsts-headers-configured',
				'meta'         => array(
					'max_age' => $max_age,
				),
			);
		}

		if ( ! $has_subdomains ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'HSTS header is missing includeSubDomains. Add it to protect subdomains from downgrade attacks.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hsts-headers-configured',
				'meta'         => array(
					'max_age'    => $max_age,
					'preload'    => $has_preload,
					'subdomains' => $has_subdomains,
				),
			);
		}

		return null;
	}

	/**
	 * Retrieve header value from response.
	 *
	 * @since 1.6093.1200
	 * @param  array $response Response array from wp_remote_head.
	 * @param  string $header Header name (lowercase).
	 * @return string|null Header value.
	 */
	private static function get_header_value( array $response, string $header ): ?string {
		$headers = wp_remote_retrieve_headers( $response );

		if ( is_object( $headers ) && method_exists( $headers, 'get' ) ) {
			$value = $headers->get( $header );
			return is_string( $value ) ? $value : null;
		}

		if ( is_array( $headers ) ) {
			foreach ( $headers as $key => $value ) {
				if ( strtolower( $key ) === strtolower( $header ) ) {
					return is_string( $value ) ? $value : null;
				}
			}
		}

		return null;
	}

	/**
	 * Extract max-age from HSTS header.
	 *
	 * @since 1.6093.1200
	 * @param  string $header HSTS header value.
	 * @return int Max-age value.
	 */
	private static function get_max_age( string $header ): int {
		if ( preg_match( '/max-age=(\d+)/i', $header, $matches ) ) {
			return (int) $matches[1];
		}

		return 0;
	}
}
