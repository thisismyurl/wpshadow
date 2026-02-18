<?php
/**
 * PHP Extensions Diagnostic
 *
 * Checks if required and recommended PHP extensions are installed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1530
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PHP Extensions Diagnostic Class
 *
 * Verifies required and recommended PHP extensions are installed. Extensions
 * are like apps on your phone—without the right ones, some features won't work.
 *
 * @since 1.6035.1530
 */
class Diagnostic_Php_Extensions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-extensions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Extensions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if required and recommended PHP extensions are installed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the PHP extensions diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if extension issues detected, null otherwise.
	 */
	public static function check() {
		// Required extensions for WordPress.
		$required = array(
			'json'   => 'JSON - Required for REST API and modern WordPress features',
			'mysqli' => 'MySQLi - Required for database connections',
		);

		// Recommended extensions for optimal functionality.
		$recommended = array(
			'curl'       => 'cURL - Used for external HTTP requests (payment gateways, updates)',
			'dom'        => 'DOM - Required for XML processing and some plugins',
			'exif'       => 'EXIF - Reads image metadata for better photo management',
			'fileinfo'   => 'Fileinfo - Detects file types for security',
			'gd'         => 'GD - Image processing (thumbnails, resizing)',
			'imagick'    => 'ImageMagick - Advanced image processing',
			'mbstring'   => 'Mbstring - Handles international characters properly',
			'openssl'    => 'OpenSSL - Required for secure HTTPS connections',
			'xml'        => 'XML - Required for XML-RPC and RSS feeds',
			'zip'        => 'Zip - Required for plugin/theme installation',
		);

		$missing_required    = array();
		$missing_recommended = array();

		// Check required extensions.
		foreach ( $required as $ext => $description ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing_required[ $ext ] = $description;
			}
		}

		// Check recommended extensions.
		foreach ( $recommended as $ext => $description ) {
			if ( ! extension_loaded( $ext ) ) {
				$missing_recommended[ $ext ] = $description;
			}
		}

		// Critical: missing required extensions.
		if ( ! empty( $missing_required ) ) {
			return array(
				'id'           => self::$slug . '-required',
				'title'        => __( 'Required PHP Extensions Missing', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of missing extensions */
					__( 'Your server is missing PHP extensions that WordPress needs to function properly (like a phone missing essential apps). The following are required: %s. Contact your hosting provider to install these extensions.', 'wpshadow' ),
					implode( ', ', array_keys( $missing_required ) )
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-extensions',
				'context'      => array(
					'missing_required' => $missing_required,
				),
			);
		}

		// Warning: missing recommended extensions.
		if ( ! empty( $missing_recommended ) ) {
			// Prioritize by impact.
			$high_impact = array( 'curl', 'gd', 'zip', 'mbstring', 'openssl' );
			$has_high_impact = false;
			foreach ( $high_impact as $ext ) {
				if ( isset( $missing_recommended[ $ext ] ) ) {
					$has_high_impact = true;
					break;
				}
			}

			$severity = $has_high_impact ? 'high' : 'medium';
			$threat_level = $has_high_impact ? 65 : 40;

			return array(
				'id'           => self::$slug . '-recommended',
				'title'        => __( 'Recommended PHP Extensions Missing', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: list of missing extensions */
					__( 'Your server could benefit from additional PHP extensions (like optional apps that add useful features). Missing: %s. These aren\'t required, but they improve functionality—like image processing, payment gateways, and security. Ask your hosting provider to install them.', 'wpshadow' ),
					implode( ', ', array_keys( $missing_recommended ) )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-extensions',
				'context'      => array(
					'missing_recommended' => $missing_recommended,
					'total_missing'       => count( $missing_recommended ),
				),
			);
		}

		return null; // All extensions present.
	}
}
