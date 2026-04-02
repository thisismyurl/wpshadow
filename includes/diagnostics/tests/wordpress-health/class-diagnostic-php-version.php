<?php
/**
 * PHP Version Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the wordpress-health gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Php_Version_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Php_Version extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-version';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the server is running a supported, actively maintained PHP version with current security patches.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'wordpress-health';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check PHP_VERSION or Site Health data for supported, performant PHP versions.
	 *
	 * TODO Fix Plan:
	 * - Upgrade PHP to a supported version with security and speed benefits.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$version = Server_Env::get_php_version();

		// PHP 8.2+ is actively maintained with security patches.
		if ( Server_Env::is_php_at_least( '8.2.0' ) ) {
			return null;
		}

		// PHP 8.1 reached end-of-life December 2025 — no further security patches.
		if ( Server_Env::is_php_at_least( '8.1.0' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: PHP version string */
					__( 'Your server is running PHP %s, which reached end-of-life in December 2025 and no longer receives security updates. Upgrade to PHP 8.2 or higher to keep your server patched and benefit from significant performance improvements.', 'wpshadow' ),
					esc_html( $version )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-version',
				'details'      => array(
					'current'     => $version,
					'minimum_ok'  => '8.2',
					'recommended' => '8.3',
					'eol'         => 'December 2025',
				),
			);
		}

		// PHP 8.0 reached end-of-life November 2023.
		if ( Server_Env::is_php_at_least( '8.0.0' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: PHP version string */
					__( 'Your server is running PHP %s, which reached end-of-life in November 2023. This version has known, unpatched vulnerabilities. Upgrade to PHP 8.2 or higher immediately.', 'wpshadow' ),
					esc_html( $version )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-version',
				'details'      => array(
					'current'     => $version,
					'minimum_ok'  => '8.2',
					'recommended' => '8.3',
					'eol'         => 'November 2023',
				),
			);
		}

		// PHP 7.x or older — critically EOL.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: PHP version string */
				__( 'Your server is running PHP %s, which is critically out of date and has not received security patches for years. This poses a severe risk to your site and server. Upgrade to PHP 8.2 or higher as a matter of urgency.', 'wpshadow' ),
				esc_html( $version )
			),
			'severity'     => 'high',
			'threat_level' => 95,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/php-version',
			'details'      => array(
				'current'     => $version,
				'minimum_ok'  => '8.2',
				'recommended' => '8.3',
			),
		);
	}
}
