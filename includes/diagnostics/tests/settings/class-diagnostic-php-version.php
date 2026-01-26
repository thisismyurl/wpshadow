<?php
/**
 * Diagnostic: PHP Version Check
 *
 * Checks if the server's PHP version meets WordPress and WPShadow recommendations.
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
 * Diagnostic_PHP_Version Class
 *
 * Detects outdated PHP versions that may cause security, performance,
 * or compatibility issues. Checks against three thresholds:
 *
 * - Critical: Below PHP 7.4 (WordPress minimum)
 * - High: Below PHP 8.1 (WPShadow minimum + modern features)
 * - Recommended: Below PHP 8.2 (current recommended)
 *
 * Old PHP versions lack security patches, modern features, and performance
 * improvements. Sites running outdated PHP are vulnerable to exploits and
 * may experience plugin compatibility issues.
 *
 * This diagnostic cannot be auto-fixed as PHP upgrades require server-level
 * changes by the hosting provider or system administrator.
 *
 * @since 1.2601.2200
 */
class Diagnostic_PHP_Version extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'php-version';

	/**
	 * Diagnostic title (user-facing)
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version';

	/**
	 * Diagnostic description (plain language)
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP version meets security and compatibility standards';

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
	 * Run the diagnostic check.
	 *
	 * Checks the current PHP version against recommended thresholds:
	 * - PHP 7.4: WordPress absolute minimum (critical if below)
	 * - PHP 8.1: WPShadow minimum + modern features (high priority)
	 * - PHP 8.2: Current recommended version (medium priority)
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if PHP version is outdated, null if current.
	 */
	public static function check() {
		$current_version = phpversion();
		$parsed_version  = self::parse_php_version( $current_version );

		// Critical: Below WordPress minimum (7.4)
		if ( version_compare( $current_version, '7.4.0', '<' ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current PHP version, 2: minimum recommended version */
					__(
						'Your server is running PHP %1$s, which is severely outdated and unsupported. WordPress requires at least PHP 7.4, and PHP %2$s has critical security vulnerabilities. This puts your site at risk of being hacked. Contact your hosting provider immediately to upgrade to PHP 8.2 or newer.',
						'wpshadow'
					),
					$current_version,
					$parsed_version
				),
				'severity'           => 'critical',
				'threat_level'       => 95,
				'site_health_status' => 'critical',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/settings-php-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version'      => $current_version,
					'minimum_version'      => '7.4.0',
					'recommended_version'  => '8.2.0',
					'eol_status'           => self::get_eol_status( $parsed_version ),
					'security_support'     => false,
				),
			);
		}

		// High: Below WPShadow minimum (8.1)
		if ( version_compare( $current_version, '8.1.0', '<' ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current PHP version, 2: recommended version */
					__(
						'Your server is running PHP %1$s. While this meets WordPress\'s minimum requirement, it lacks modern security patches and performance improvements. We strongly recommend upgrading to PHP %2$s or newer for better security, speed, and compatibility with modern plugins.',
						'wpshadow'
					),
					$current_version,
					'8.2'
				),
				'severity'           => 'high',
				'threat_level'       => 65,
				'site_health_status' => 'critical',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/settings-php-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version'      => $current_version,
					'minimum_version'      => '8.1.0',
					'recommended_version'  => '8.2.0',
					'eol_status'           => self::get_eol_status( $parsed_version ),
					'security_support'     => version_compare( $current_version, '7.4.0', '>=' ),
				),
			);
		}

		// Recommended: Below PHP 8.2
		if ( version_compare( $current_version, '8.2.0', '<' ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => sprintf(
					/* translators: 1: current PHP version, 2: recommended version */
					__(
						'Your server is running PHP %1$s, which is good. However, PHP %2$s offers additional performance improvements and newer features. Consider upgrading when convenient for optimal site performance.',
						'wpshadow'
					),
					$current_version,
					'8.2'
				),
				'severity'           => 'low',
				'threat_level'       => 25,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/settings-php-version',
				'family'             => self::$family,
				'details'            => array(
					'current_version'      => $current_version,
					'minimum_version'      => '8.1.0',
					'recommended_version'  => '8.2.0',
					'eol_status'           => 'active',
					'security_support'     => true,
				),
			);
		}

		// PHP 8.2+ - all good!
		return null;
	}

	/**
	 * Parse PHP version to major.minor format.
	 *
	 * @since  1.2601.2200
	 * @param  string $version Full PHP version string (e.g., '8.1.15').
	 * @return string Major.minor version (e.g., '8.1').
	 */
	private static function parse_php_version( string $version ): string {
		$parts = explode( '.', $version );
		if ( count( $parts ) >= 2 ) {
			return $parts[0] . '.' . $parts[1];
		}
		return $version;
	}

	/**
	 * Get EOL (End of Life) status for PHP version.
	 *
	 * Returns human-readable status indicating whether version receives
	 * security updates.
	 *
	 * @since  1.2601.2200
	 * @param  string $version PHP version (major.minor format).
	 * @return string EOL status (e.g., 'end-of-life', 'security-only', 'active').
	 */
	private static function get_eol_status( string $version ): string {
		// PHP EOL dates (as of January 2026)
		$eol_dates = array(
			'7.4' => '2022-11-28', // End of life
			'8.0' => '2023-11-26', // End of life
			'8.1' => '2025-12-31', // Active (security fixes until Nov 2024, then one more year)
			'8.2' => '2026-12-31', // Active support
			'8.3' => '2027-12-31', // Active support
		);

		if ( isset( $eol_dates[ $version ] ) ) {
			$eol_date = strtotime( $eol_dates[ $version ] );
			$now      = time();

			if ( $now > $eol_date ) {
				return 'end-of-life';
			} elseif ( $now > ( $eol_date - YEAR_IN_SECONDS ) ) {
				return 'security-only';
			}
			return 'active';
		}

		// Unknown version - assume old if < 8.1
		return version_compare( $version, '8.1', '<' ) ? 'end-of-life' : 'active';
	}

	/**
	 * This diagnostic cannot be applied automatically.
	 *
	 * PHP version upgrades require server-level changes that must be
	 * performed by hosting provider or system administrator.
	 *
	 * @since  1.2601.2200
	 * @return array Empty array (no treatments available).
	 */
	public static function get_available_treatments(): array {
		return array(); // No auto-fix possible
	}
}
