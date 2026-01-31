<?php
/**
 * Diagnostic: Caddy Web Server Detection
 *
 * Detects if site is running on Caddy web server and validates configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Caddy_Server
 *
 * Identifies Caddy web server installations and provides guidance
 * for optimal WordPress configuration on this modern platform.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Caddy_Server extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'caddy-server';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Caddy Web Server Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if site is running on Caddy web server and validate configuration';

	/**
	 * Run the diagnostic check.
	 *
	 * Detects Caddy server and provides configuration recommendations.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array with Caddy info, null if not Caddy.
	 */
	public static function check() {
		// Check if Caddy server
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return null;
		}

		$server_software = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );
		
		if ( false === stripos( $server_software, 'caddy' ) ) {
			// Not Caddy server
			return null;
		}

		// Caddy detected - extract version if available
		preg_match( '/caddy[\/\s]*([\d\.]+)?/i', $server_software, $matches );
		$caddy_version = ! empty( $matches[1] ) ? $matches[1] : __( 'unknown', 'wpshadow' );

		// Check if permalinks are working (basic test)
		$permalink_structure = get_option( 'permalink_structure' );
		$permalinks_configured = ! empty( $permalink_structure );

		// Build informational message
		$description = sprintf(
			/* translators: %s: Caddy version number */
			__( 'Caddy web server (version %s) detected. Caddy is a modern, automatic HTTPS server with built-in SSL/TLS support.', 'wpshadow' ),
			esc_html( $caddy_version )
		);

		if ( $permalinks_configured ) {
			$description .= ' ' . __( 'WordPress permalinks appear to be configured correctly.', 'wpshadow' );
		} else {
			$description .= ' ' . __( 'Note: Permalinks are not configured. Caddy requires proper rewrite rules in your Caddyfile for pretty permalinks to work.', 'wpshadow' );
		}

		// Check HTTPS status (Caddy auto-enables HTTPS)
		$is_https = is_ssl();
		if ( ! $is_https ) {
			$description .= ' ' . __( 'Warning: Site is not using HTTPS. Caddy provides automatic HTTPS - verify your Caddyfile configuration.', 'wpshadow' );
		}

		// This is informational, not a problem
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'info',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/server-caddy-server',
			'meta'        => array(
				'server_software' => $server_software,
				'caddy_version' => $caddy_version,
				'permalinks_configured' => $permalinks_configured,
				'https_enabled' => $is_https,
			),
		);
	}
}
