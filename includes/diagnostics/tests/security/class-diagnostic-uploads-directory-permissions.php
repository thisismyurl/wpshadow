<?php
/**
 * Uploads Directory Permissions Diagnostic
 *
 * Checks uploads directory is writable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1522
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Uploads Directory Permissions Diagnostic Class
 *
 * Verifies uploads directory has correct permissions.
 *
 * @since 1.6035.1522
 */
class Diagnostic_Uploads_Directory_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uploads-directory-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Uploads Directory Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies uploads directory is writable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the permissions diagnostic check.
	 *
	 * @since  1.6035.1522
	 * @return array|null Finding array if permission issue detected, null otherwise.
	 */
	public static function check() {
		$upload_dir = wp_upload_dir();
		$upload_path = $upload_dir['basedir'];

		if ( ! is_dir( $upload_path ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Uploads directory does not exist.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/uploads-directory-missing',
			);
		}

		if ( ! is_writable( $upload_path ) ) {
			$perms = substr( sprintf( '%o', fileperms( $upload_path ) ), -4 );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: permissions */
					__( 'Uploads directory is not writable. Current permissions: %s', 'wpshadow' ),
					$perms
				),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/fix-uploads-directory-permissions',
				'meta'        => array(
					'current_permissions' => $perms,
					'expected_permissions' => '0755',
				),
			);
		}

		// Test actual write capability.
		$test_file = $upload_path . '/.wpshadow-write-test-' . time() . '.tmp';

		if ( ! @fopen( $test_file, 'w' ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Uploads directory is not writeable (cannot create test file).', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/uploads-directory-write-test-failed',
			);
		}

		@unlink( $test_file );

		return null;
	}
}
