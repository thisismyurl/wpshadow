<?php
/**
 * WP-Content Directory Permissions Diagnostic
 *
 * Checks wp-content directory is writable.
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
 * WP-Content Directory Permissions Diagnostic Class
 *
 * Verifies wp-content directory has correct permissions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Wp_Content_Directory_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-content-directory-permissions';

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
	protected static $description = 'Verifies wp-content directory is writable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'file-permissions';

	/**
	 * Run the permissions diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if permission issue detected, null otherwise.
	 */
	public static function check() {
		$wp_content_dir = WP_CONTENT_DIR;

		if ( ! is_dir( $wp_content_dir ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'wp-content directory does not exist.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-content-directory-missing',
			);
		}

		if ( ! is_writable( $wp_content_dir ) ) {
			$perms = substr( sprintf( '%o', fileperms( $wp_content_dir ) ), -4 );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: path, 2: permissions */
					__( 'wp-content directory is not writable. Current permissions: %s', 'wpshadow' ),
					$perms
				),
				'severity'    => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/fix-wp-content-permissions',
				'meta'        => array(
					'current_permissions' => $perms,
					'expected_permissions' => '0755',
				),
			);
		}

		return null;
	}
}
