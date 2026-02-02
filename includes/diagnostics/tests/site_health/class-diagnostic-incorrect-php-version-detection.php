<?php
/**
 * Incorrect PHP Version Detection
 *
 * Detects when Site Health reports wrong PHP version or outdated version when current.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SiteHealth
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Incorrect_PHP_Version_Detection Class
 *
 * Validates PHP version detection accuracy and checks for misreporting.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Incorrect_PHP_Version_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'incorrect-php-version-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version Detection Accuracy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates that PHP version is correctly detected and reported';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'site_health';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests PHP version detection accuracy.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check PHP version consistency
		$version_issue = self::check_php_version_consistency();
		if ( $version_issue ) {
			$issues[] = $version_issue;
		}

		// 2. Check for EOL status misreporting
		$eol_issue = self::check_eol_status_accuracy();
		if ( $eol_issue ) {
			$issues[] = $eol_issue;
		}

		// 3. Check for SAPI mismatch
		$sapi_issue = self::check_sapi_detection();
		if ( $sapi_issue ) {
			$issues[] = $sapi_issue;
		}

		// 4. Check for module detection issues
		$modules_issue = self::check_php_modules_detection();
		if ( $modules_issue ) {
			$issues[] = $modules_issue;
		}

		// 5. Check hosting environment PHP version
		$hosting_issue = self::check_hosting_php_version();
		if ( $hosting_issue ) {
			$issues[] = $hosting_issue;
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of detection issues */
					__( '%d PHP version detection issues found', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 60,
				'auto_fixable' => true,
				'details'      => array_merge(
					$issues,
					array(
						sprintf( __( 'Current PHP version: %s', 'wpshadow' ), phpversion() ),
						sprintf( __( 'SAPI: %s', 'wpshadow' ), php_sapi_name() ),
					)
				),
				'kb_link'      => 'https://wpshadow.com/kb/php-version-detection',
				'recommendations' => array(
					__( 'Verify PHP version from hosting provider', 'wpshadow' ),
					__( 'Check Site Health against actual server configuration', 'wpshadow' ),
					__( 'Use WP-CLI to verify PHP information', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check PHP version consistency.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_php_version_consistency() {
		$php_version = phpversion();

		// Check if version is being reported correctly
		if ( empty( $php_version ) ) {
			return __( 'PHP version could not be detected', 'wpshadow' );
		}

		// Check version format
		if ( ! preg_match( '/^\d+\.\d+\.\d+/', $php_version ) ) {
			return sprintf(
				/* translators: %s: PHP version */
				__( 'PHP version format incorrect: %s', 'wpshadow' ),
				esc_html( $php_version )
			);
		}

		return null;
	}

	/**
	 * Check EOL status accuracy.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_eol_status_accuracy() {
		$php_version = phpversion();
		list( $major, $minor ) = explode( '.', $php_version );
		$version_number = (int) $major . '.' . (int) $minor;

		// Define EOL dates (as of 2024)
		$eol_versions = array(
			'5.6' => '2018-12-31',
			'7.0' => '2019-01-10',
			'7.1' => '2019-12-01',
			'7.2' => '2020-11-30',
			'7.3' => '2021-12-06',
			'7.4' => '2022-11-28',
			'8.0' => '2023-11-26',
		);

		foreach ( $eol_versions as $version => $eol_date ) {
			if ( version_compare( $php_version, $version, '<' ) ) {
				return sprintf(
					/* translators: %s: PHP version, %s: EOL date */
					__( 'PHP %s reached EOL on %s', 'wpshadow' ),
					$version,
					$eol_date
				);
			}
		}

		return null;
	}

	/**
	 * Check SAPI detection.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_sapi_detection() {
		$sapi = php_sapi_name();

		// Common SAPI types
		$expected_sapis = array( 'fpm-fcgi', 'cgi-fcgi', 'cgi', 'cli', 'apache2handler', 'litespeed' );

		if ( ! in_array( $sapi, $expected_sapis, true ) ) {
			return sprintf(
				/* translators: %s: SAPI name */
				__( 'Unusual PHP SAPI detected: %s (may affect performance)', 'wpshadow' ),
				esc_html( $sapi )
			);
		}

		return null;
	}

	/**
	 * Check PHP modules detection.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_php_modules_detection() {
		// Check for common missing modules
		$critical_modules = array( 'curl', 'json', 'gd', 'mbstring', 'openssl' );
		$missing_modules  = array();

		foreach ( $critical_modules as $module ) {
			if ( ! extension_loaded( $module ) ) {
				$missing_modules[] = $module;
			}
		}

		if ( ! empty( $missing_modules ) ) {
			return sprintf(
				/* translators: %s: list of modules */
				__( 'Missing PHP modules: %s (may impact functionality)', 'wpshadow' ),
				implode( ', ', $missing_modules )
			);
		}

		return null;
	}

	/**
	 * Check hosting PHP version.
	 *
	 * @since  1.2601.2148
	 * @return string|null Issue description or null if no issue.
	 */
	private static function check_hosting_php_version() {
		// Check if running under different PHP versions
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		if ( empty( $server_software ) ) {
			return null;
		}

		// Check for version mismatch in server software string
		if ( preg_match( '/PHP\/(\d+\.\d+\.\d+)/', $server_software, $matches ) ) {
			$reported_version = phpversion();
			$server_version   = $matches[1];

			if ( $reported_version !== $server_version ) {
				return sprintf(
					/* translators: %s: reported version, %s: server version */
					__( 'PHP version mismatch: reported %s but server shows %s', 'wpshadow' ),
					esc_html( $reported_version ),
					esc_html( $server_version )
				);
			}
		}

		return null;
	}
}
