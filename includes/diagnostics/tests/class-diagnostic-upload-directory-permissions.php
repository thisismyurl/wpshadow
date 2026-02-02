<?php
/**
 * Upload Directory Permissions Diagnostic
 *
 * Checks if upload directory has proper permissions.
 *
 * @since   1.26033.0901
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Upload_Directory_Permissions Class
 *
 * Validates upload directory permissions.
 *
 * @since 1.26033.0901
 */
class Diagnostic_Upload_Directory_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-directory-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload Directory Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if upload directory has proper permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'uploads';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0901
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$upload_dir = wp_upload_dir();
		$basedir = $upload_dir['basedir'];

		// Check if upload directory is writable
		if ( ! is_writable( $basedir ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: upload directory path */
					__( 'Upload directory %s is not writable. WordPress will not be able to save images and other media files.', 'wpshadow' ),
					esc_attr( $basedir )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upload-directory-permissions',
			);
		}

		// Check for overly open permissions (777)
		$perms = substr( sprintf( '%o', fileperms( $basedir ) ), -3 );
		if ( $perms === '777' ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Upload directory has overly open permissions (777). This is a security risk. Consider changing to 755.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upload-directory-permissions',
			);
		}

		return null; // Upload directory permissions are secure
	}
}
