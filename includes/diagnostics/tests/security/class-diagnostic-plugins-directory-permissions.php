<?php
/**
 * Plugins Directory Permissions Diagnostic
 *
 * Checks plugins directory permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1524
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugins Directory Permissions Diagnostic Class
 *
 * Verifies plugins directory permissions are secure.
 *
 * @since 1.6035.1524
 */
class Diagnostic_Plugins_Directory_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugins-directory-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugins Directory Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies plugins directory permissions are secure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the permissions diagnostic check.
	 *
	 * @since  1.6035.1524
	 * @return array|null Finding array if permission issue detected, null otherwise.
	 */
	public static function check() {
		$plugins_dir = WP_PLUGIN_DIR;

		if ( ! is_dir( $plugins_dir ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Plugins directory does not exist.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/plugins-directory-missing',
			);
		}

		$perms = substr( sprintf( '%o', fileperms( $plugins_dir ) ), -4 );

		// Check if world-writable (overly permissive).
		$perms_int = (int) octdec( $perms );
		if ( ( $perms_int & 0o002 ) === 0o002 ) { // World-writable.
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: permissions */
					__( 'Plugins directory is world-writable (overly permissive). Current permissions: %s', 'wpshadow' ),
					$perms
				),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/fix-plugins-directory-permissions',
				'meta'        => array(
					'current_permissions' => $perms,
					'expected_permissions' => '0755',
				),
			);
		}

		// Warn if not group-executable (may prevent plugin installation).
		if ( ( $perms_int & 0o010 ) !== 0o010 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: permissions */
					__( 'Plugins directory may not allow plugin installation. Current permissions: %s', 'wpshadow' ),
					$perms
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/plugins-directory-permission-install-issues',
				'meta'        => array(
					'current_permissions' => $perms,
					'expected_permissions' => '0755',
				),
			);
		}

		return null;
	}
}
