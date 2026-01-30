<?php
/**
 * Wordfence Scan Configuration Diagnostic
 *
 * Validates scan settings and coverage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1800
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordfence Scan Configuration Class
 *
 * Checks scan settings for optimal security coverage.
 *
 * @since 1.5029.1800
 */
class Diagnostic_Wordfence_Scan extends Diagnostic_Base {

	protected static $slug        = 'wordfence-scan';
	protected static $title       = 'Wordfence Scan Configuration';
	protected static $description = 'Validates scan settings';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! class_exists( 'wordfence' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_wordfence_scan';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check if scans are scheduled.
		$scan_schedule = wfConfig::get( 'scheduledScanEnabled', 0 );
		if ( ! $scan_schedule ) {
			$issues[] = 'Scheduled scans are disabled';
		}

		// Check scan types enabled.
		$scan_options = array(
			'scansEnabled_core' => 'Core file integrity scans',
			'scansEnabled_plugins' => 'Plugin file scans',
			'scansEnabled_themes' => 'Theme file scans',
			'scansEnabled_malware' => 'Malware signature scans',
			'spamvertizeCheck' => 'Spam check',
		);

		foreach ( $scan_options as $option => $label ) {
			$enabled = wfConfig::get( $option, 0 );
			if ( ! $enabled ) {
				$issues[] = $label . ' disabled';
			}
		}

		// Check if high sensitivity is enabled.
		$high_sensitivity = wfConfig::get( 'scansEnabled_highSensitivity', 0 );
		if ( $high_sensitivity ) {
			$issues[] = 'High sensitivity may cause false positives - review scan results carefully';
		}

		// Check last scan time.
		$last_scan = wfConfig::get( 'lastScanCompleted', 0 );
		if ( $last_scan ) {
			$days_since_scan = ( time() - $last_scan ) / DAY_IN_SECONDS;
			if ( $days_since_scan > 7 ) {
				$issues[] = sprintf( 'Last scan was %.1f days ago - scans may not be running', $days_since_scan );
			}
		} else {
			$issues[] = 'No scans have ever completed';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d scan configuration issues found. Security coverage may be incomplete.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-wordfence-scan',
				'data'         => array(
					'scan_issues' => $issues,
					'total_issues' => count( $issues ),
					'last_scan' => $last_scan ? date_i18n( get_option( 'date_format' ), $last_scan ) : 'Never',
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
