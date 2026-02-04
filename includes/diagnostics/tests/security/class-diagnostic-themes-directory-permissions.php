<?php
/**
 * Themes Directory Permissions Diagnostic
 *
 * Checks themes directory permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1526
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Themes Directory Permissions Diagnostic Class
 *
 * Verifies themes directory permissions are secure.
 *
 * @since 1.6035.1526
 */
class Diagnostic_Themes_Directory_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'themes-directory-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Themes Directory Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies themes directory permissions are secure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the permissions diagnostic check.
	 *
	 * @since  1.6035.1526
	 * @return array|null Finding array if permission issue detected, null otherwise.
	 */
	public static function check() {
		$themes_dir = get_theme_root();

		if ( ! is_dir( $themes_dir ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Themes directory does not exist.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/themes-directory-missing',
			);
		}

		$perms = substr( sprintf( '%o', fileperms( $themes_dir ) ), -4 );

		// Check if world-writable (overly permissive).
		$perms_int = (int) octdec( $perms );
		if ( ( $perms_int & 0o002 ) === 0o002 ) { // World-writable.
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: permissions */
					__( 'Themes directory is world-writable (overly permissive). Current permissions: %s', 'wpshadow' ),
					$perms
				),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/fix-themes-directory-permissions',
				'meta'        => array(
					'current_permissions' => $perms,
					'expected_permissions' => '0755',
				),
			);
		}

		return null;
	}
}
