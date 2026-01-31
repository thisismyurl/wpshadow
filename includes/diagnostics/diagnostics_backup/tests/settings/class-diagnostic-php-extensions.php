<?php
/**
 * Diagnostic: Required PHP Extensions
 *
 * Checks if required PHP extensions are installed and enabled.
 *
 * @package WPShadow\Diagnostics
 * @since   1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_PHP_Extensions Class
 *
 * Detects if required PHP extensions are missing or disabled. WordPress and
 * modern plugins rely on specific extensions for functionality:
 *
 * **Critical Extensions:**
 * - gd: Image manipulation (for thumbnails, optimization)
 * - curl: HTTP requests (for updates, external APIs)
 * - json: JSON processing (WordPress REST API)
 *
 * **Important Extensions:**
 * - dom: XML/HTML parsing
 * - mbstring: Multibyte string handling (international characters)
 * - fileinfo: File type detection
 * - openssl: SSL/TLS encryption
 * - zip: ZIP file operations
 *
 * Returns different threat levels based on which extensions are missing.
 *
 * @since 1.2601.2200
 */
class Diagnostic_PHP_Extensions extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'php-extensions';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'Required PHP Extensions';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Verifies required PHP extensions are installed for WordPress functionality';

	/**
	 * Family grouping for batch operations
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Family label (human-readable)
	 *
	 * @var string
	 */
	protected static $family_label = 'Settings';

	/**
	 * Critical extensions (WordPress won't work without these)
	 *
	 * @var array
	 */
	private static $critical_extensions = array(
		'gd'   => 'Image processing (thumbnails, optimization)',
		'curl' => 'HTTP requests (updates, REST API)',
		'json' => 'JSON processing (WordPress REST API)',
	);

	/**
	 * Important extensions (needed for many plugins)
	 *
	 * @var array
	 */
	private static $important_extensions = array(
		'dom'        => 'XML/HTML parsing',
		'mbstring'   => 'Multibyte string handling',
		'fileinfo'   => 'File type detection',
		'openssl'    => 'SSL/TLS encryption',
		'zip'        => 'ZIP file operations',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for presence of critical and important extensions.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if extensions missing, null if all present.
	 */
	public static function check() {
		$missing_critical   = array();
		$missing_important = array();

		// Check critical extensions
		foreach ( self::$critical_extensions as $ext => $description ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing_critical[ $ext ] = $description;
			}
		}

		// Check important extensions
		foreach ( self::$important_extensions as $ext => $description ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing_important[ $ext ] = $description;
			}
		}

		// Critical: Missing critical extensions
		if ( ! empty( $missing_critical ) ) {
			$missing_list = implode(
				', ',
				array_map(
					function( $ext, $desc ) {
						return $ext . ' (' . $desc . ')';
					},
					array_keys( $missing_critical ),
					array_values( $missing_critical )
				)
			);

			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: %s: list of missing extensions */
					esc_html__( 'Your server is missing critical PHP extensions: %s. These are required for WordPress to function properly. Contact your hosting provider to enable them.', 'wpshadow' ),
					$missing_list
				),
				'severity'           => 'critical',
				'threat_level'       => 85,
				'site_health_status' => 'critical',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/settings-php-extensions',
				'family'             => self::$family,
				'details'            => array(
					'missing_critical'   => $missing_critical,
					'missing_important'  => $missing_important,
					'recommendation'     => 'Contact hosting provider to enable ' . implode( ', ', array_keys( $missing_critical ) ),
			),
			);
		}

		// Medium: Missing important extensions
		if ( ! empty( $missing_important ) ) {
			$missing_list = implode(
				', ',
				array_map(
					function( $ext, $desc ) {
						return $ext . ' (' . $desc . ')';
					},
					array_keys( $missing_important ),
					array_values( $missing_important )
				)
			);

			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: %s: list of missing extensions */
					esc_html__( 'Your server is missing some important PHP extensions: %s. These may be required by certain plugins or features. Consider contacting your hosting provider to enable them.', 'wpshadow' ),
					$missing_list
				),
				'severity'           => 'medium',
				'threat_level'       => 50,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/settings-php-extensions',
				'family'             => self::$family,
				'details'            => array(
					'missing_critical'   => $missing_critical,
					'missing_important'  => $missing_important,
					'recommendation'     => 'Request hosting provider to enable ' . implode( ', ', array_keys( $missing_important ) ),
				),
			);
		}

		// All good - all required extensions are present
		return null;
	}
}
