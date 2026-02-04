<?php
/**
 * PHP Version Check Diagnostic
 *
 * Ensures PHP version meets minimum requirements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1500
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PHP Version Check Diagnostic Class
 *
 * Verifies PHP version meets minimum requirements and is not EOL.
 *
 * @since 1.6035.1500
 */
class Diagnostic_PHP_Version_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-version-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures PHP version meets minimum requirements';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting-environment';

	/**
	 * Minimum PHP version required
	 *
	 * @var string
	 */
	private const MIN_PHP_VERSION = '8.1.0';

	/**
	 * PHP versions that are EOL
	 *
	 * @var array
	 */
	private const EOL_VERSIONS = array( '7.0', '7.1', '7.2', '7.3', '7.4', '8.0' );

	/**
	 * Run the PHP version diagnostic check.
	 *
	 * @since  1.6035.1500
	 * @return array|null Finding array if version issue detected, null otherwise.
	 */
	public static function check() {
		$current_version = PHP_VERSION;
		$is_below_minimum = version_compare( $current_version, self::MIN_PHP_VERSION, '<' );
		$is_eol = self::is_eol_version( $current_version );

		if ( $is_below_minimum ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: current PHP version, 2: minimum required */
					__( 'PHP version %1$s is below minimum required version %2$s. Update immediately for security.', 'wpshadow' ),
					$current_version,
					self::MIN_PHP_VERSION
				),
				'severity'    => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/upgrade-php-version',
				'meta'        => array(
					'current_version' => $current_version,
					'minimum_version' => self::MIN_PHP_VERSION,
				),
			);
		}

		if ( $is_eol ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: PHP version */
					__( 'PHP %s is end-of-life and no longer receiving security updates. Upgrade to PHP 8.2+ recommended.', 'wpshadow' ),
					PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION
				),
				'severity'    => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php-end-of-life',
				'meta'        => array(
					'current_version' => $current_version,
				),
			);
		}

		return null;
	}

	/**
	 * Check if PHP version is EOL.
	 *
	 * @since  1.6035.1500
	 * @param  string $version PHP version string.
	 * @return bool True if EOL, false otherwise.
	 */
	private static function is_eol_version( string $version ): bool {
		$major_minor = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
		return in_array( $major_minor, self::EOL_VERSIONS, true );
	}
}
