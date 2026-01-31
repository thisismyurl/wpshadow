<?php
/**
 * Missing WP Admin Files Diagnostic
 *
 * Confirms integrity of /wp-admin/ directory.
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
 * Diagnostic_Missing_Wp_Admin_Files
 *
 * Checks for critical missing files in /wp-admin/ directory.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Missing_Wp_Admin_Files extends Diagnostic_Base {

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

		$admin_dir = ABSPATH . 'wp-admin/';
		if ( ! is_dir( $admin_dir ) ) {
			return array(
				'id'           => 'missing-wp-admin-files',
				'title'        => __( 'wp-admin Directory Missing', 'wpshadow' ),
				'description'  => __( 'The wp-admin directory is missing or not readable. This is a critical WordPress core directory. Your site cannot function without it.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/missing_wp_admin_files',
				'meta'         => array(
					'missing_dir' => 'wp-admin',
				),
			);
		}

		// Check for critical files.
		$critical_files = array(
			'index.php',
			'admin.php',
			'user-edit.php',
			'post.php',
			'edit.php',
			'options-general.php',
		);

		$missing = array();
		foreach ( $critical_files as $file ) {
			if ( ! is_file( $admin_dir . $file ) ) {
				$missing[] = $file;
			}
		}

		if ( ! empty( $missing ) ) {
			return array(
				'id'           => 'missing-wp-admin-files',
				'title'        => __( 'Critical wp-admin Files Missing', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: missing files */
					__( 'These critical wp-admin files are missing: %s. Your site may not function correctly. Try reinstalling WordPress core.', 'wpshadow' ),
					implode( ', ', $missing )
				),
				'severity'     => 'high',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/missing_wp_admin_files',
				'meta'         => array(
					'missing_files' => $missing,
					'count'         => count( $missing ),
				),
			);
		}

		return null;
	}
}
