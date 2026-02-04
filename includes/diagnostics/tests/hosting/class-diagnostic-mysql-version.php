<?php
/**
 * MySQL Version Diagnostic
 *
 * Checks if MySQL/MariaDB version meets WordPress requirements.
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
 * MySQL Version Diagnostic Class
 *
 * Verifies MySQL/MariaDB version is current and secure. The database engine
 * is like your site's filing cabinet—an old one may lose data or run slowly.
 *
 * @since 1.6035.1530
 */
class Diagnostic_Mysql_Version extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mysql-version';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'MySQL/MariaDB Version';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if MySQL/MariaDB version meets WordPress requirements';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'hosting';

	/**
	 * Run the MySQL version diagnostic check.
	 *
	 * @since  1.6035.1530
	 * @return array|null Finding array if MySQL version issues detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$db_version = $wpdb->db_version();
		$is_mariadb = false;

		// Check if MariaDB.
		if ( false !== strpos( strtolower( $db_version ), 'mariadb' ) ) {
			$is_mariadb = true;
			// Extract version number from "5.5.5-10.3.32-MariaDB" format.
			preg_match( '/(\d+\.\d+\.\d+)/', $db_version, $matches );
			$version = $matches[1] ?? $db_version;
		} else {
			$version = $db_version;
		}

		// WordPress requirements.
		$mysql_min = '5.7';
		$mariadb_min = '10.2';
		$mysql_recommended = '8.0';
		$mariadb_recommended = '10.6';

		// Check for end-of-life versions.
		$mysql_eol = array(
			'5.5' => '2018-12-31',
			'5.6' => '2021-02-28',
			'5.7' => '2023-10-31',
		);

		$mariadb_eol = array(
			'10.1' => '2020-10-17',
			'10.2' => '2022-05-22',
			'10.3' => '2023-05-25',
			'10.4' => '2024-06-18',
			'10.5' => '2025-06-24',
		);

		$is_eol = false;
		$eol_date = '';

		if ( $is_mariadb ) {
			foreach ( $mariadb_eol as $eol_version => $date ) {
				if ( version_compare( $version, $eol_version, '>=' ) && version_compare( $version, $eol_version . '.99', '<=' ) ) {
					$is_eol = true;
					$eol_date = $date;
					break;
				}
			}
		} else {
			foreach ( $mysql_eol as $eol_version => $date ) {
				if ( version_compare( $version, $eol_version, '>=' ) && version_compare( $version, $eol_version . '.99', '<=' ) ) {
					$is_eol = true;
					$eol_date = $date;
					break;
				}
			}
		}

		$db_type = $is_mariadb ? 'MariaDB' : 'MySQL';

		if ( $is_eol ) {
			return array(
				'id'           => self::$slug . '-eol',
				'title'        => sprintf(
					/* translators: %s: database type (MySQL or MariaDB) */
					__( '%s Version End-of-Life', 'wpshadow' ),
					$db_type
				),
				'description'  => sprintf(
					/* translators: 1: database type, 2: current version, 3: EOL date */
					__( 'Your %1$s version (%2$s) stopped receiving security updates on %3$s (like using an old filing cabinet that no longer gets security fixes). This leaves your data vulnerable to known security issues. Contact your hosting provider to upgrade.', 'wpshadow' ),
					$db_type,
					$version,
					date_i18n( get_option( 'date_format' ), strtotime( $eol_date ) )
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mysql-version',
				'context'      => array(
					'current_version' => $version,
					'db_type'         => $db_type,
					'eol_date'        => $eol_date,
				),
			);
		}

		// Check against WordPress minimum.
		$min_version = $is_mariadb ? $mariadb_min : $mysql_min;
		if ( version_compare( $version, $min_version, '<' ) ) {
			return array(
				'id'           => self::$slug . '-below-minimum',
				'title'        => sprintf(
					/* translators: %s: database type (MySQL or MariaDB) */
					__( '%s Version Below WordPress Minimum', 'wpshadow' ),
					$db_type
				),
				'description'  => sprintf(
					/* translators: 1: database type, 2: current version, 3: minimum required */
					__( 'Your %1$s version (%2$s) is below WordPress requirements. WordPress needs at least %1$s %3$s. Your site may experience errors or data corruption. Contact your hosting provider to upgrade.', 'wpshadow' ),
					$db_type,
					$version,
					$min_version
				),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mysql-version',
				'context'      => array(
					'current_version' => $version,
					'min_version'     => $min_version,
					'db_type'         => $db_type,
				),
			);
		}

		// Check against recommended version.
		$recommended = $is_mariadb ? $mariadb_recommended : $mysql_recommended;
		if ( version_compare( $version, $recommended, '<' ) ) {
			return array(
				'id'           => self::$slug . '-below-recommended',
				'title'        => sprintf(
					/* translators: %s: database type (MySQL or MariaDB) */
					__( '%s Version Below Recommended', 'wpshadow' ),
					$db_type
				),
				'description'  => sprintf(
					/* translators: 1: database type, 2: current version, 3: recommended version */
					__( 'Your %1$s version (%2$s) works, but upgrading to %1$s %3$s or newer would improve performance and security (like upgrading to a newer, faster filing system). Contact your hosting provider about upgrading.', 'wpshadow' ),
					$db_type,
					$version,
					$recommended
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mysql-version',
				'context'      => array(
					'current_version' => $version,
					'recommended'     => $recommended,
					'db_type'         => $db_type,
				),
			);
		}

		return null; // Database version is current.
	}
}
