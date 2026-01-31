<?php
/**
 * Diagnostic: PHP open_basedir Restrictions
 *
 * Checks if open_basedir is properly configured for security.
 * When set, PHP can only access files within specified directories.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Open_Basedir
 *
 * Tests PHP open_basedir configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Open_Basedir extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-open-basedir';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP open_basedir Restrictions';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP open_basedir is properly configured';

	/**
	 * Check PHP open_basedir setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get open_basedir setting.
		$open_basedir = ini_get( 'open_basedir' );

		// If open_basedir is not set, check if it should be (shared hosting, high-security environment).
		if ( empty( $open_basedir ) ) {
			// Check if this is likely a shared hosting environment.
			$is_shared_hosting = ( defined( 'WP_CONTENT_DIR' ) && strpos( WP_CONTENT_DIR, '/home/' ) !== false );

			if ( $is_shared_hosting ) {
				return array(
					'id'          => self::$slug,
					'title'       => self::$title,
					'description' => __( 'PHP open_basedir is not set. On shared hosting environments, consider enabling it to restrict PHP file access to your WordPress directory only. This improves security if another user\'s site is compromised.', 'wpshadow' ),
					'severity'    => 'low',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'     => 'https://wpshadow.com/kb/php_open_basedir',
					'meta'        => array(
						'open_basedir'         => '',
						'is_shared_hosting'    => true,
					),
				);
			}

			// Not shared hosting, open_basedir not required.
			return null;
		}

		// Parse open_basedir paths (platform-specific separator).
		$separator = PATH_SEPARATOR;
		$paths     = explode( $separator, $open_basedir );

		// Check if ABSPATH is in open_basedir.
		$abspath_found = false;

		foreach ( $paths as $path ) {
			if ( empty( $path ) ) {
				continue;
			}

			// Normalize paths for comparison.
			$normalized_path = realpath( $path );
			$normalized_abspath = realpath( ABSPATH );

			if ( false !== $normalized_path && false !== $normalized_abspath ) {
				if ( strpos( $normalized_abspath, $normalized_path ) === 0 ) {
					$abspath_found = true;
					break;
				}
			}

			// Fallback string comparison.
			if ( strpos( ABSPATH, $path ) === 0 ) {
				$abspath_found = true;
				break;
			}
		}

		if ( ! $abspath_found ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: WordPress directory path */
					__( 'PHP open_basedir is set but does not include the WordPress directory (%s). WordPress may not function properly. Ensure open_basedir includes the WordPress directory.', 'wpshadow' ),
					ABSPATH
				),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_open_basedir',
				'meta'        => array(
					'open_basedir'      => $open_basedir,
					'wordpress_dir'     => ABSPATH,
					'contains_wordpress' => false,
				),
			);
		}

		// Check if open_basedir is too permissive (contains /).
		if ( in_array( '/', $paths, true ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP open_basedir is set to "/" (root directory), which defeats the purpose of open_basedir restrictions. Consider restricting it to just the WordPress directory.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_open_basedir',
				'meta'        => array(
					'open_basedir'   => $open_basedir,
					'is_permissive'  => true,
				),
			);
		}

		// open_basedir is properly configured.
		return null;
	}
}
