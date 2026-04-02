<?php
/**
 * WP-Content Permissions Diagnostic
 *
 * Checks if wp-content directory has correct permissions.
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
 * WP-Content Permissions Diagnostic Class
 *
 * Verifies wp-content folder has secure yet functional permissions.
 * Like checking that your filing cabinet has the right lock settings.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Wp_Content_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-content-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WP-Content Directory Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if wp-content directory has correct permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the wp-content permissions diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if permission issues detected, null otherwise.
	 */
	public static function check() {
		$wp_content_dir = WP_CONTENT_DIR;

		if ( ! is_dir( $wp_content_dir ) ) {
			return array(
				'id'           => self::$slug . '-missing',
				'title'        => __( 'WP-Content Directory Missing', 'wpshadow' ),
				'description'  => __( 'Your wp-content directory is missing (like your main filing cabinet disappeared). This is a critical issue that prevents WordPress from working. Contact your hosting provider immediately.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path' => $wp_content_dir,
				),
			);
		}

		$perms = fileperms( $wp_content_dir );
		$perms_octal = substr( sprintf( '%o', $perms ), -4 );

		$recommended = '0755';
		$acceptable = array( '0755', '0775' );
		$too_permissive = array( '0777', '0776' );

		// Check if permissions are too open.
		if ( in_array( $perms_octal, $too_permissive, true ) ) {
			return array(
				'id'           => self::$slug . '-too-permissive',
				'title'        => __( 'WP-Content Directory Too Permissive', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current permissions, 2: recommended permissions */
					__( 'Your wp-content directory permissions (%1$s) are too open (like leaving your filing cabinet unlocked for anyone to access). This allows unauthorized users to potentially upload malicious files. Change permissions to %2$s using your hosting control panel or FTP client.', 'wpshadow' ),
					$perms_octal,
					$recommended
				),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path'         => $wp_content_dir,
					'permissions'  => $perms_octal,
					'recommended'  => $recommended,
				),
			);
		}

		// Check if directory is not writable.
		if ( ! is_writable( $wp_content_dir ) ) {
			return array(
				'id'           => self::$slug . '-not-writable',
				'title'        => __( 'WP-Content Directory Not Writable', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: %s: current permissions */
					__( 'Your wp-content directory isn\'t writable (permissions: %s). This is like having a filing cabinet you can\'t add files to. WordPress can\'t install plugins, upload media, or update themes. Change permissions to 0755 using your hosting control panel or FTP client.', 'wpshadow' ),
					$perms_octal
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path'         => $wp_content_dir,
					'permissions'  => $perms_octal,
					'recommended'  => $recommended,
					'writable'     => false,
				),
			);
		}

		// Check if permissions are non-standard but functional.
		if ( ! in_array( $perms_octal, $acceptable, true ) && '0' !== substr( $perms_octal, 0, 1 ) ) {
			return array(
				'id'           => self::$slug . '-non-standard',
				'title'        => __( 'WP-Content Directory Has Unusual Permissions', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current permissions, 2: recommended permissions */
					__( 'Your wp-content directory has unusual permissions (%1$s). While it may work, the standard is %2$s (like having a custom lock when a standard one is better). Consider changing to standard permissions for better compatibility and security.', 'wpshadow' ),
					$perms_octal,
					$recommended
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/file-permissions',
				'context'      => array(
					'path'         => $wp_content_dir,
					'permissions'  => $perms_octal,
					'recommended'  => $recommended,
				),
			);
		}

		return null; // Permissions are good.
	}
}
