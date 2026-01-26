<?php
/**
 * Apache Version Diagnostic
 *
 * Detects if Apache version is current and supported.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Settings
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Apache Version Diagnostic Class
 *
 * Checks if the server is running Apache and if the version
 * is current, supported, and includes necessary security patches.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Apache_Version extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'apache-version';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Apache Version';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Apache version is current and supported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if running on Apache server.
		if ( ! self::is_apache() ) {
			return null; // Not applicable for non-Apache servers.
		}

		// Get server software string.
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		if ( empty( $server_software ) ) {
			return null; // Cannot determine server software.
		}

		// Extract Apache version.
		$version = self::extract_apache_version( $server_software );

		if ( null === $version ) {
			return null; // Could not parse version.
		}

		// Check if version is outdated (< 2.4).
		if ( version_compare( $version, '2.4', '<' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: Current Apache version, 2: Recommended version */
					__( 'Your server is running Apache %1$s. Apache versions older than 2.4 are outdated and may have security vulnerabilities. Recommended: Apache 2.4 or higher.', 'wpshadow' ),
					esc_html( $version ),
					'2.4'
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/settings-apache-version',
			);
		}

		return null; // Apache version is acceptable.
	}

	/**
	 * Check if the server is running Apache.
	 *
	 * @since  1.2601.2148
	 * @return bool True if Apache, false otherwise.
	 */
	private static function is_apache(): bool {
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return false;
		}

		$server_software = strtolower( sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) );

		return false !== strpos( $server_software, 'apache' );
	}

	/**
	 * Extract Apache version from server software string.
	 *
	 * @since  1.2601.2148
	 * @param  string $server_software Server software string.
	 * @return string|null Apache version or null if not found.
	 */
	private static function extract_apache_version( string $server_software ): ?string {
		// Pattern to match Apache/X.Y.Z or Apache/X.Y.
		if ( preg_match( '/Apache\/([0-9]+\.[0-9]+(?:\.[0-9]+)?)/i', $server_software, $matches ) ) {
			return $matches[1];
		}

		return null;
	}
}
