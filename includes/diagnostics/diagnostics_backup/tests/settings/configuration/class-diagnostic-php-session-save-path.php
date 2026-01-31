<?php
/**
 * Diagnostic: PHP session.save_path
 *
 * Checks if PHP session.save_path is configured and usable.
 * An invalid path can break authentication flows that rely on PHP sessions.
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
 * Class Diagnostic_Php_Session_Save_Path
 *
 * Tests PHP session.save_path configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Session_Save_Path extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-session-save-path';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP session.save_path';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if PHP session.save_path is configured and usable';

	/**
	 * Check PHP session.save_path.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$session_save_path = ini_get( 'session.save_path' );

		if ( empty( $session_save_path ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'PHP session.save_path is not set. This can break login flows that rely on PHP sessions. Set session.save_path to a writable directory.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_session_save_path',
				'meta'        => array(
					'session_save_path' => '',
				),
			);
		}

		// session.save_path may contain optional mode prefix like "N;".
		$paths = explode( ';', $session_save_path );
		$path  = end( $paths );

		if ( ! is_dir( $path ) || ! is_writable( $path ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: session.save_path value */
					__( 'PHP session.save_path (%s) is not a writable directory. Sessions may fail to start, affecting logins and integrations. Ensure the directory exists and is writable.', 'wpshadow' ),
					$session_save_path
				),
				'severity'    => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_session_save_path',
				'meta'        => array(
					'session_save_path' => $session_save_path,
					'is_dir'            => is_dir( $path ),
					'is_writable'       => is_writable( $path ),
				),
			);
		}

		return null;
	}
}
