<?php
/**
 * Diagnostic: FS_METHOD Detection
 *
 * Checks filesystem access method (direct, ssh2, ftpext, ftpsockets).
 * Incorrect FS_METHOD can cause plugin/theme update failures.
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
 * Class Diagnostic_Fs_Method_Detection
 *
 * Validates FS_METHOD configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Fs_Method_Detection extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'fs-method-detection';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'FS_METHOD Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks filesystem access method configuration';

	/**
	 * Check FS_METHOD configuration.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get filesystem method.
		$fs_method = get_filesystem_method();

		// Check if FS_METHOD constant is defined.
		$fs_method_defined = defined( 'FS_METHOD' );
		$fs_method_constant = $fs_method_defined ? FS_METHOD : null;

		// Warn if using FTP methods (less secure than direct).
		if ( in_array( $fs_method, array( 'ftpext', 'ftpsockets' ), true ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Filesystem method */
					__( 'Site is using FTP filesystem method (%s). Consider switching to direct method for better security and performance.', 'wpshadow' ),
					$fs_method
				),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/fs_method_detection',
				'meta'        => array(
					'fs_method'         => $fs_method,
					'fs_method_defined' => $fs_method_defined,
					'fs_method_constant' => $fs_method_constant,
				),
			);
		}

		// Warn if FS_METHOD is defined but different from detected method.
		if ( $fs_method_defined && $fs_method_constant !== $fs_method ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: Defined constant, 2: Detected method */
					__( 'FS_METHOD constant (%1$s) does not match detected method (%2$s). This may cause update issues.', 'wpshadow' ),
					$fs_method_constant,
					$fs_method
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/fs_method_detection',
				'meta'        => array(
					'fs_method'         => $fs_method,
					'fs_method_constant' => $fs_method_constant,
				),
			);
		}

		// Recommend setting FS_METHOD to 'direct' if not defined and using direct.
		if ( ! $fs_method_defined && 'direct' === $fs_method ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'FS_METHOD constant is not defined. Consider adding define( \'FS_METHOD\', \'direct\' ) to wp-config.php to improve performance.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/fs_method_detection',
				'meta'        => array(
					'fs_method'         => $fs_method,
					'fs_method_defined' => false,
				),
			);
		}

		// Filesystem method is optimal.
		return null;
	}
}
