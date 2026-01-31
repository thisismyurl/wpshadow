<?php
/**
 * Geolocation Blocking Not Configured Diagnostic
 *
 * Checks if geolocation blocking is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Geolocation Blocking Not Configured Diagnostic Class
 *
 * Detects missing geolocation blocking.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Geolocation_Blocking_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'geolocation-blocking-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Geolocation Blocking Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if geolocation blocking is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for geolocation blocking rules
		if ( ! get_option( 'geolocation_blocking_enabled' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Geolocation blocking is not configured. Block access from high-risk countries or allow access only from specific regions based on your business requirements and compliance needs.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/geolocation-blocking-not-configured',
			);
		}

		return null;
	}
}
