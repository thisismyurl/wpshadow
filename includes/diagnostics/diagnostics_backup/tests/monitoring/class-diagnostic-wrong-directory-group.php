<?php
/**
 * Wrong Directory Group Diagnostic
 *
 * Checks directory group ownership/permissions.
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
 * Diagnostic_Wrong_Directory_Group
 *
 * Verifies directory group ownership is correct and matches server expectations.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Wrong_Directory_Group extends Diagnostic_Base {

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

		// Get expected group (usually www-data, nobody, or apache).
		$expected_group = self::get_expected_group();
		if ( ! $expected_group ) {
			return null; // Unable to determine expected group.
		}

		// Check WordPress root directory group.
		$current_group = self::get_directory_group( ABSPATH );
		if ( false === $current_group ) {
			return null;
		}

		if ( $current_group !== $expected_group ) {
			return array(
				'id'           => 'wrong-directory-group',
				'title'        => __( 'WordPress Directory Has Wrong Group Ownership', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %1$s: current group, %2$s: expected group */
					__( 'WordPress directory is owned by group "%1$s" but expected "%2$s". This may cause permission issues when files are uploaded or modified.', 'wpshadow' ),
					$current_group,
					$expected_group
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wrong_directory_group',
				'meta'         => array(
					'current_group'  => $current_group,
					'expected_group' => $expected_group,
					'path'           => ABSPATH,
				),
			);
		}

		return null;
	}

	/**
	 * Get current directory group.
	 *
	 * @since  1.2601.2112
	 * @param  string $path Directory path.
	 * @return string|false Group name or false if unable to determine.
	 */
	private static function get_directory_group( $path ) {
		if ( ! is_dir( $path ) ) {
			return false;
		}

		if ( ! function_exists( 'posix_getgrgid' ) ) {
			return false; // POSIX functions not available (likely Windows).
		}

		$gid = filegroup( $path );
		if ( false === $gid ) {
			return false;
		}

		$group_info = posix_getgrgid( $gid );
		if ( false === $group_info ) {
			return false;
		}

		return $group_info['name'] ?? $gid;
	}

	/**
	 * Get expected directory group for web server.
	 *
	 * @since  1.2601.2112
	 * @return string|null Expected group name or null if unable to determine.
	 */
	private static function get_expected_group() {
		// Try to detect web server user group.
		$common_groups = array( 'www-data', 'apache', 'nobody', 'nginx', 'www', '_www' );

		foreach ( $common_groups as $group ) {
			if ( function_exists( 'posix_getgrnam' ) && false !== @posix_getgrnam( $group ) ) {
				return $group;
			}
		}

		// If running PHP-FPM, check pool config.
		if ( function_exists( 'php_uname' ) ) {
			$uname = php_uname();
			if ( strpos( $uname, 'Linux' ) !== false ) {
				return 'www-data'; // Safe default for Linux.
			}
		}

		return null;
	}
}
