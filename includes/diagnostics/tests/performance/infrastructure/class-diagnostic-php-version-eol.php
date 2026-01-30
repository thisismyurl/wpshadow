<?php
/**
 * Diagnostic: PHP Version End-of-Life Status
 *
 * Checks if the current PHP version is at or past end-of-life.
 * EOL versions no longer receive security updates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Infrastructure
 * @since      1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_PHP_Version_EOL Class
 *
 * Checks the current PHP version against the official PHP version lifecycle
 * dates. PHP versions reach end-of-life when they stop receiving security
 * updates.
 *
 * Current PHP version timeline (as of 2025):
 * - PHP 8.3: Supported until Nov 2026
 * - PHP 8.2: Supported until Dec 2024 (EOL)
 * - PHP 8.1: Supported until Nov 2023 (EOL)
 * - PHP 8.0: Supported until Nov 2023 (EOL)
 * - PHP 7.4: Supported until Nov 2022 (EOL)
 * - Older: All EOL
 *
 * @since 1.2601.2200
 */
class Diagnostic_PHP_Version_EOL extends Diagnostic_Base {

	/**
	 * Diagnostic slug/identifier
	 *
	 * @var string
	 */
	protected static $slug = 'php-version-eol';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version End-of-Life Status';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies PHP version is supported and receiving security updates';

	/**
	 * Family grouping
	 *
	 * @var string
	 */
	protected static $family = 'infrastructure';

	/**
	 * Family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Infrastructure';

	/**
	 * PHP version lifecycle dates (YYYY-MM-DD)
	 * Maps minor versions to their EOL dates
	 *
	 * @var array
	 */
	private static $php_eol_dates = array(
		// Version => array( 'name' => 'Human name', 'eol' => 'YYYY-MM-DD', 'active_support' => 'YYYY-MM-DD' )
		'8.3' => array(
			'name' => 'PHP 8.3',
			'released' => '2023-11-23',
			'active_support' => '2025-11-23',
			'security_support' => '2026-11-23',
			'eol' => '2026-11-23',
		),
		'8.2' => array(
			'name' => 'PHP 8.2',
			'released' => '2022-12-08',
			'active_support' => '2023-12-08',
			'security_support' => '2024-12-08',
			'eol' => '2024-12-08',
		),
		'8.1' => array(
			'name' => 'PHP 8.1',
			'released' => '2021-11-25',
			'active_support' => '2022-11-25',
			'security_support' => '2023-11-25',
			'eol' => '2023-11-25',
		),
		'8.0' => array(
			'name' => 'PHP 8.0',
			'released' => '2020-11-26',
			'active_support' => '2021-11-26',
			'security_support' => '2023-11-26',
			'eol' => '2023-11-26',
		),
		'7.4' => array(
			'name' => 'PHP 7.4',
			'released' => '2019-11-28',
			'active_support' => '2020-11-28',
			'security_support' => '2022-11-28',
			'eol' => '2022-11-28',
		),
	);

	/**
	 * Run the diagnostic check.
	 *
	 * Checks the current PHP version against EOL dates and recommends
	 * upgrading if necessary.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if EOL detected, null if current version.
	 */
	public static function check() {
		$php_version = phpversion();

		if ( ! $php_version ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Unable to determine PHP version.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/infrastructure-php-eol',
				'family'      => self::$family,
			);
		}

		// Get major.minor version (e.g., "8.2" from "8.2.1")
		$version_parts = explode( '.', $php_version );
		$minor_version = isset( $version_parts[0], $version_parts[1] ) 
			? $version_parts[0] . '.' . $version_parts[1]
			: null;

		if ( ! $minor_version ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: PHP version */
					__( 'Could not parse PHP version: %s', 'wpshadow' ),
					esc_html( $php_version )
				),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/infrastructure-php-eol',
				'family'      => self::$family,
			);
		}

		// Get EOL info for this version
		$eol_info = self::get_eol_info( $minor_version );

		if ( ! $eol_info ) {
			// Version not in our list - likely very old or very new
			if ( version_compare( $php_version, '8.0', '<' ) ) {
				// Older than 8.0 - definitely EOL
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => sprintf(
						/* translators: %s: PHP version */
						__( 'PHP version %s is end-of-life and no longer receives security updates. Upgrade to PHP 8.2 or later immediately.', 'wpshadow' ),
						esc_html( $php_version )
					),
					'severity'    => 'critical',
					'threat_level' => 80,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/infrastructure-php-eol',
					'family'      => self::$family,
					'meta'        => array(
						'php_version' => $php_version,
						'support_status' => 'end-of-life',
					),
				);
			}

			// Unknown version but newer than our data - probably OK
			return null;
		}

		// Check if version is EOL
		$eol_date = strtotime( $eol_info['eol'] );
		$current_date = time();

		if ( $current_date > $eol_date ) {
			// Version is EOL
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: PHP version, 2: EOL date */
					__( 'PHP version %1$s reached end-of-life on %2$s and no longer receives security updates. Upgrade immediately to PHP 8.3 (supported until Nov 2026) or later.', 'wpshadow' ),
					esc_html( $php_version ),
					esc_html( wp_date( get_option( 'date_format' ), $eol_date ) )
				),
				'severity'    => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/infrastructure-php-eol',
				'family'      => self::$family,
				'meta'        => array(
					'php_version' => $php_version,
					'eol_date' => $eol_info['eol'],
					'support_status' => 'end-of-life',
					'days_since_eol' => floor( ( $current_date - $eol_date ) / DAY_IN_SECONDS ),
				),
			);
		}

		// Get active support cutoff date
		$active_support_date = strtotime( $eol_info['active_support'] );

		if ( $current_date > $active_support_date ) {
			// In security-fixes-only phase
			$days_until_eol = floor( ( $eol_date - $current_date ) / DAY_IN_SECONDS );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: PHP version, 2: days until EOL, 3: EOL date */
					__( 'PHP %1$s is in security-fixes-only mode and will reach end-of-life in %2$d days (%3$s). Plan your upgrade to PHP 8.3 (supported until Nov 2026) now.', 'wpshadow' ),
					esc_html( $php_version ),
					$days_until_eol,
					esc_html( wp_date( get_option( 'date_format' ), $eol_date ) )
				),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/infrastructure-php-eol',
				'family'      => self::$family,
				'meta'        => array(
					'php_version' => $php_version,
					'eol_date' => $eol_info['eol'],
					'support_status' => 'security-fixes-only',
					'days_until_eol' => $days_until_eol,
				),
			);
		}

		// Version is in active support - no issue
		return null;
	}

	/**
	 * Get EOL information for a PHP version.
	 *
	 * @since  1.2601.2200
	 * @param  string $minor_version PHP minor version (e.g., "8.2").
	 * @return array|null EOL info array or null if not found.
	 */
	private static function get_eol_info( $minor_version ) {
		return self::$php_eol_dates[ $minor_version ] ?? null;
	}
}
