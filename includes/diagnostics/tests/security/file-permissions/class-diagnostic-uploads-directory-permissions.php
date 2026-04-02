<?php
/**
 * Uploads Directory Permissions Diagnostic
 *
 * Checks if uploads directory has correct permissions.
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
 * Uploads Directory Permissions Diagnostic Class
 *
 * Verifies uploads folder has secure yet functional permissions.
 * Like checking that your photo album storage has the right access settings.
 *
 * @since 1.6093.1200
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
	protected static $description = 'Checks if uploads directory has correct permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the uploads directory permissions diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if permission issues detected, null otherwise.
	 */
	public static function check() {
		$upload_dir = wp_upload_dir();
		$basedir = $upload_dir['basedir'];

		if ( ! is_dir( $basedir ) ) {
			return array(
				'id'           => self::$slug . '-missing',
				'title'        => __( 'Uploads Directory Missing', 'wpshadow' ),
				'description'  => __( 'Your uploads directory doesn\'t exist (like having no place to store photos). WordPress needs this to store media files. Try uploading an image through the WordPress media library to create it, or create it manually via FTP.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path' => $basedir,
				),
			);
		}

		$perms = fileperms( $basedir );
		$perms_octal = substr( sprintf( '%o', $perms ), -4 );

		$recommended = '0755';
		$acceptable = array( '0755', '0775' );
		$too_permissive = array( '0777', '0776' );

		// Check if permissions are too open.
		if ( in_array( $perms_octal, $too_permissive, true ) ) {
			return array(
				'id'           => self::$slug . '-too-permissive',
				'title'        => __( 'Uploads Directory Too Permissive', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current permissions, 2: recommended permissions */
					__( 'Your uploads directory permissions (%1$s) are too open (like leaving your photo album accessible to anyone). Attackers could upload malicious files disguised as images. Change permissions to %2$s using your hosting control panel or FTP client.', 'wpshadow' ),
					$perms_octal,
					$recommended
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path'         => $basedir,
					'permissions'  => $perms_octal,
					'recommended'  => $recommended,
				),
			);
		}

		// Check if directory is not writable.
		if ( ! is_writable( $basedir ) ) {
			return array(
				'id'           => self::$slug . '-not-writable',
				'title'        => __( 'Uploads Directory Not Writable', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: current permissions */
					__( 'Your uploads directory isn\'t writable (permissions: %s). This is like having a photo album you can\'t add pictures to. You won\'t be able to upload images, videos, or documents through WordPress. Change permissions to 0755 using your hosting control panel or FTP client.', 'wpshadow' ),
					$perms_octal
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path'         => $basedir,
					'permissions'  => $perms_octal,
					'recommended'  => $recommended,
					'writable'     => false,
				),
			);
		}

		// Check subdirectories (year/month structure).
		$current_year = gmdate( 'Y' );
		$current_month = gmdate( 'm' );
		$current_subdir = "$basedir/$current_year/$current_month";

		if ( is_dir( $current_subdir ) ) {
			$subdir_perms = fileperms( $current_subdir );
			$subdir_perms_octal = substr( sprintf( '%o', $subdir_perms ), -4 );

			if ( in_array( $subdir_perms_octal, $too_permissive, true ) ) {
				return array(
					'id'           => self::$slug . '-subdir-too-permissive',
					'title'        => __( 'Uploads Subdirectory Too Permissive', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: 1: subdirectory path, 2: current permissions, 3: recommended permissions */
						__( 'An uploads subdirectory (%1$s) has overly permissive permissions (%2$s). Change to %3$s for better security. You may need to fix permissions on all subdirectories.', 'wpshadow' ),
						basename( $current_subdir ),
						$subdir_perms_octal,
						$recommended
					),
					'severity'     => 'medium',
					'threat_level' => 60,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
					'context'      => array(
						'path'         => $current_subdir,
						'permissions'  => $subdir_perms_octal,
						'recommended'  => $recommended,
					),
				);
			}
		}

		return null; // Permissions are good.
	}
}
