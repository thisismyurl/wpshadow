<?php
/**
 * Diagnostic: LiteSpeed Version & Support
 *
 * Detects LiteSpeed server version and verifies it meets minimum requirements.
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
 * Class Diagnostic_Litespeed_Version
 *
 * Checks LiteSpeed web server version to ensure it's current and supported.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Litespeed_Version extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'litespeed-version';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'LiteSpeed Version & Support';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect LiteSpeed server version and verify it meets minimum requirements';

	/**
	 * Minimum recommended LiteSpeed version.
	 *
	 * @var string
	 */
	private const MINIMUM_VERSION = '6.0';

	/**
	 * Run the diagnostic check.
	 *
	 * Parses LiteSpeed version from SERVER_SOFTWARE and compares to minimum.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if version too old, null otherwise.
	 */
	public static function check() {
		// Check if LiteSpeed server
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return null;
		}

		$server_software = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );
		
		if ( false === stripos( $server_software, 'litespeed' ) ) {
			// Not LiteSpeed server
			return null;
		}

		// Extract version number (format: LiteSpeed/6.1.2 or LiteSpeed)
		preg_match( '/litespeed[\/\s]*([\d\.]+)?/i', $server_software, $matches );
		
		if ( empty( $matches[1] ) ) {
			// Version not detectable
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'LiteSpeed web server detected, but version number could not be determined. Contact your hosting provider to verify you\'re running a current version.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/server-litespeed-version',
				'meta'        => array(
					'server_software' => $server_software,
					'version_detected' => false,
				),
			);
		}

		$current_version = $matches[1];

		// Compare versions
		if ( version_compare( $current_version, self::MINIMUM_VERSION, '<' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: current version, 2: minimum recommended version */
					__( 'LiteSpeed version %1$s detected. Version %2$s or higher is recommended for best compatibility, security, and performance. Contact your hosting provider about upgrading.', 'wpshadow' ),
					esc_html( $current_version ),
					esc_html( self::MINIMUM_VERSION )
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/server-litespeed-version',
				'meta'        => array(
					'server_software' => $server_software,
					'current_version' => $current_version,
					'minimum_version' => self::MINIMUM_VERSION,
				),
			);
		}

		// Version is current
		return null;
	}
}
