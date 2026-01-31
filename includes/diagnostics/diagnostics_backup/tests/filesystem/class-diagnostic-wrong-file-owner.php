<?php
/**
 * Wrong File Owner Diagnostic
 *
 * Detects mismatched file user/group ownership.
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
 * Diagnostic_Wrong_File_Owner
 *
 * Checks WordPress core files and wp-content directory for mismatched user/group ownership.
 *
 * @since 1.2601.2112
 */
class Diagnostic_Wrong_File_Owner extends Diagnostic_Base {

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

		// Can't check ownership on Windows or if POSIX not available.
		if ( ! function_exists( 'posix_geteuid' ) ) {
			return null;
		}

		$root_owner  = self::get_directory_owner( ABSPATH );
		$content_dir = WP_CONTENT_DIR;
		$content_owner = self::get_directory_owner( $content_dir );

		// Check if owners mismatch.
		if ( false !== $root_owner && false !== $content_owner && $root_owner !== $content_owner ) {
			return array(
				'id'           => 'wrong-file-owner',
				'title'        => __( 'WordPress Directories Have Mismatched Ownership', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %1$s: root owner, %2$s: content owner */
					__( 'WordPress root is owned by "%1$s" but wp-content is owned by "%2$s". This can cause permission issues when installing plugins, themes, or uploading files. Contact your hosting provider to fix ownership.', 'wpshadow' ),
					$root_owner,
					$content_owner
				),
				'severity'     => 'medium',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wrong_file_owner',
				'meta'         => array(
					'root_owner'    => $root_owner,
					'content_owner' => $content_owner,
				),
			);
		}

		return null;
	}

	/**
	 * Get directory owner username.
	 *
	 * @since  1.2601.2112
	 * @param  string $path Directory path.
	 * @return string|false Owner name or false if unable to determine.
	 */
	private static function get_directory_owner( $path ) {
		if ( ! is_dir( $path ) ) {
			return false;
		}

		if ( ! function_exists( 'posix_getpwuid' ) ) {
			return false;
		}

		$uid = fileowner( $path );
		if ( false === $uid ) {
			return false;
		}

		$user_info = posix_getpwuid( $uid );
		if ( false === $user_info ) {
			return false;
		}

		return $user_info['name'] ?? $uid;
	}
}
