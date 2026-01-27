<?php
/**
 * Missing WP Includes Detection Diagnostic
 *
 * Confirms integrity of /wp-includes/ directory.
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
 * Diagnostic_Missing_Wp_Includes_Detection
 *
 * Checks for critical missing files in /wp-includes/ directory.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Missing_Wp_Includes_Detection extends Diagnostic_Base {

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

		$includes_dir = ABSPATH . 'wp-includes/';
		if ( ! is_dir( $includes_dir ) ) {
			return array(
				'id'           => 'missing-wp-includes-detection',
				'title'        => __( 'wp-includes Directory Missing', 'wpshadow' ),
				'description'  => __( 'The wp-includes directory is missing or not readable. This is a critical WordPress core directory containing essential functions. Your site cannot operate without it.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/missing_wp_includes_detection',
				'meta'         => array(
					'missing_dir' => 'wp-includes',
				),
			);
		}

		// Check for critical files.
		$critical_files = array(
			'functions.php',
			'plugin.php',
			'wp-db.php',
			'class-wp-hook.php',
			'load.php',
		);

		$missing = array();
		foreach ( $critical_files as $file ) {
			if ( ! is_file( $includes_dir . $file ) ) {
				$missing[] = $file;
			}
		}

		if ( ! empty( $missing ) ) {
			return array(
				'id'           => 'missing-wp-includes-detection',
				'title'        => __( 'Critical wp-includes Files Missing', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: missing files */
					__( 'These critical wp-includes files are missing: %s. Your site will not function correctly. Try reinstalling WordPress core files.', 'wpshadow' ),
					implode( ', ', $missing )
				),
				'severity'     => 'high',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/missing_wp_includes_detection',
				'meta'         => array(
					'missing_files' => $missing,
					'count'         => count( $missing ),
				),
			);
		}

		return null;
	}
}
