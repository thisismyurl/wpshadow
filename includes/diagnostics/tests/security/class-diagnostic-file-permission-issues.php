<?php
/**
 * File Permission Issues Diagnostic
 *
 * Checks for risky file and directory permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_File_Permission_Issues Class
 *
 * Flags world-writable permissions on key directories.
 *
 * @since 1.6093.1200
 */
class Diagnostic_File_Permission_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'file-permission-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'File Permission Issues';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for insecure file and directory permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$paths = array(
			WP_CONTENT_DIR,
			WP_CONTENT_DIR . '/plugins',
			WP_CONTENT_DIR . '/themes',
			WP_CONTENT_DIR . '/uploads',
		);

		$world_writable = array();
		foreach ( $paths as $path ) {
			if ( is_dir( $path ) ) {
				$perms = fileperms( $path );
				if ( false !== $perms && ( $perms & 0x0002 ) ) {
					$world_writable[] = $path;
				}
			}
		}

		if ( ! empty( $world_writable ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'One or more directories are world-writable (777). Tighten permissions to reduce risk.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permission-issues',
				'meta'         => array(
					'world_writable_paths' => $world_writable,
				),
			);
		}

		return null;
	}
}