<?php
/**
 * Diagnostic: PHP include_path Configuration
 *
 * Checks if PHP include_path is properly configured.
 * Include path should contain essential directories for PHP to find required files.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Php_Include_Path
 *
 * Tests PHP include_path configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Include_Path extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-include-path';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP include_path Configuration';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP include_path is properly configured';

	/**
	 * Check PHP include_path configuration.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get include_path setting.
		$include_path = ini_get( 'include_path' );

		if ( empty( $include_path ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP include_path is empty. This may cause issues with include() and require() statements. Ensure the include path contains at least "." (current directory).', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_include_path',
				'meta'        => array(
					'include_path' => '',
				),
			);
		}

		// Parse include path (platform-specific separator).
		$separator = PATH_SEPARATOR;
		$paths     = explode( $separator, $include_path );

		// Check if current directory is in include path.
		$has_current_dir = in_array( '.', $paths, true );

		if ( ! $has_current_dir ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP include_path does not contain "." (current directory). This may cause relative include() and require() statements to fail.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_include_path',
				'meta'        => array(
					'include_path'    => $include_path,
					'has_current_dir' => false,
				),
			);
		}

		// Check for suspicious or world-writable directories in include path.
		$suspicious_paths = array();

		foreach ( $paths as $path ) {
			if ( empty( $path ) || '.' === $path ) {
				continue;
			}

			// Check if path is writable by anyone (security risk).
			if ( file_exists( $path ) && is_dir( $path ) && is_writable( $path ) ) {
				$perms = fileperms( $path );
				// Check if world-writable (0002 bit set).
				if ( ( $perms & 0x0002 ) !== 0 ) {
					$suspicious_paths[] = $path;
				}
			}
		}

		if ( ! empty( $suspicious_paths ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: List of suspicious paths */
					__( 'PHP include_path contains world-writable directories: %s. This is a security risk as attackers could inject malicious files into these locations.', 'wpshadow' ),
					implode( ', ', $suspicious_paths )
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_include_path',
				'meta'        => array(
					'include_path'      => $include_path,
					'suspicious_paths'  => $suspicious_paths,
				),
			);
		}

		// PHP include_path is properly configured.
		return null;
	}
}
