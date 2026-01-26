<?php
/**
 * Maintenance Mode Detection Diagnostic
 *
 * Confirms presence and status of .maintenance file.
 *
 * @since   1.2601.2112
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Maintenance_Mode_Detection
 *
 * Checks if a stale .maintenance file is blocking site access.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Maintenance_Mode_Detection extends Diagnostic_Base {

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2112
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		$maintenance_file = ABSPATH . '.maintenance';

		if ( ! is_file( $maintenance_file ) ) {
			return null; // Not in maintenance mode, all good.
		}

		// Site has .maintenance file. Check if it's stale.
		$maintenance_time = filemtime( $maintenance_file );
		$current_time     = time();
		$age_minutes      = ( $current_time - $maintenance_time ) / 60;

		// If .maintenance is older than 2 hours, it's likely stale/forgotten.
		if ( $age_minutes > 120 ) {
			return array(
				'id'           => 'maintenance-mode-detection',
				'title'        => __( 'Stale .maintenance File Detected', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %d: age in minutes */
					__( 'Your site has a .maintenance file that is %d minutes old. This file may be left over from an incomplete update. Delete it to restore normal site access: rm %s/.maintenance', 'wpshadow' ),
					round( $age_minutes ),
					ABSPATH
				),
				'severity'     => 'high',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/maintenance_mode_detection',
				'meta'         => array(
					'file_age_minutes' => round( $age_minutes, 2 ),
					'file_path'        => '.maintenance',
					'stale'            => true,
				),
			);
		}

		return null;
	}
}
