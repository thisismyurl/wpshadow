<?php
/**
 * PHP Version Diagnostic
 *
 * Checks if PHP version meets WordPress requirements and security standards.
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
 * PHP Version Diagnostic Class
 *
 * Verifies that PHP version is current and secure. Running outdated PHP is like
 * using an old operating system that no longer receives security updates.
 *
 * @since 1.6035.1530
 */
class Diagnostic_Php_Version extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'php-version';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'PHP Version';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP version meets WordPress requirements and security standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the PHP version diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if PHP version issues detected, null otherwise.
	 */
	public static function check() {
		$current_version = phpversion();
		$min_version     = '7.4';
		$recommended     = '8.1';
		$latest_stable   = '8.3';

		// Check for end-of-life versions.
		$eol_versions = array(
			'5.6' => '2019-01-01',
			'7.0' => '2019-01-01',
			'7.1' => '2019-12-01',
			'7.2' => '2020-11-30',
			'7.3' => '2021-12-06',
			'7.4' => '2022-11-28',
			'8.0' => '2023-11-26',
		);

		$is_eol = false;
		$eol_date = '';

		foreach ( $eol_versions as $version => $date ) {
			if ( version_compare( $current_version, $version, '>=' ) && version_compare( $current_version, $version . '.99', '<=' ) ) {
				$is_eol = true;
				$eol_date = $date;
				break;
			}
		}

		if ( $is_eol ) {
			return array(
				'id'           => self::$slug . '-eol',
				'title'        => __( 'PHP Version End-of-Life', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current PHP version, 2: EOL date, 3: recommended version */
					__( 'Your site is running PHP %1$s, which stopped receiving security updates on %2$s (like using an old phone that no longer gets security patches). This leaves your site vulnerable to known security issues. Contact your hosting provider to upgrade to PHP %3$s or newer.', 'wpshadow' ),
					$current_version,
					date_i18n( get_option( 'date_format' ), strtotime( $eol_date ) ),
					$recommended
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-version-upgrade',
				'context'      => array(
					'current_version' => $current_version,
					'eol_date'        => $eol_date,
					'recommended'     => $recommended,
				),
			);
		}

		// Check if below WordPress minimum.
		if ( version_compare( $current_version, $min_version, '<' ) ) {
			return array(
				'id'           => self::$slug . '-below-minimum',
				'title'        => __( 'PHP Version Below WordPress Minimum', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current PHP version, 2: minimum required version */
					__( 'Your site is running PHP %1$s, but WordPress requires at least PHP %2$s. Your site may experience errors or security vulnerabilities. Contact your hosting provider to upgrade PHP.', 'wpshadow' ),
					$current_version,
					$min_version
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-version-upgrade',
				'context'      => array(
					'current_version' => $current_version,
					'min_version'     => $min_version,
				),
			);
		}

		// Check if below recommended version.
		if ( version_compare( $current_version, $recommended, '<' ) ) {
			return array(
				'id'           => self::$slug . '-below-recommended',
				'title'        => __( 'PHP Version Below Recommended', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current PHP version, 2: recommended version */
					__( 'Your site is running PHP %1$s. While this works, upgrading to PHP %2$s or newer would improve performance (up to 3x faster) and security. Contact your hosting provider—most can upgrade this in minutes.', 'wpshadow' ),
					$current_version,
					$recommended
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/php-version-upgrade',
				'context'      => array(
					'current_version' => $current_version,
					'recommended'     => $recommended,
				),
			);
		}

		return null; // PHP version is current.
	}
}
