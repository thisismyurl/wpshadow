<?php
/**
 * Upload Timeout Errors Diagnostic
 *
 * Checks for upload timeout and time limit issues.
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
 * Diagnostic_Upload_Timeout_Errors Class
 *
 * Detects upload timeout configuration issues.
 *
 * @since 1.26033.0901
 */
class Diagnostic_Upload_Timeout_Errors extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'upload-timeout-errors';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Upload Timeout Errors';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for upload timeout configuration issues';

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
		$max_execution_time = (int) ini_get( 'max_execution_time' );
		$default_socket_timeout = (int) ini_get( 'default_socket_timeout' );

		// Check if max execution time is very short
		if ( $max_execution_time > 0 && $max_execution_time < 30 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: timeout value in seconds */
					__( 'Your max_execution_time is only %d seconds. Large file uploads may timeout. Consider increasing it to at least 300 seconds.', 'wpshadow' ),
					$max_execution_time
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upload-timeout-errors',
			);
		}

		if ( $default_socket_timeout > 0 && $default_socket_timeout < 30 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: timeout value in seconds */
					__( 'Your default_socket_timeout is only %d seconds. Remote uploads or API calls may timeout. Consider increasing it.', 'wpshadow' ),
					$default_socket_timeout
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upload-timeout-errors',
			);
		}

		return null; // Upload timeout configuration is adequate
	}
}
