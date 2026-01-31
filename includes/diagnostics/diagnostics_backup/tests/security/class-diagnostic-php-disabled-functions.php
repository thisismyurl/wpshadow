<?php
/**
 * Diagnostic: PHP disabled_functions
 *
 * Checks if critical PHP functions are disabled via disable_functions directive.
 * Some hosts disable functions for security, but this can break WordPress features.
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
 * Class Diagnostic_Php_Disabled_Functions
 *
 * Tests for disabled PHP functions that WordPress needs.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Php_Disabled_Functions extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'php-disabled-functions';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PHP disabled_functions';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if critical PHP functions are disabled';

	/**
	 * Check PHP disabled_functions.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Get disabled_functions list.
		$disabled_functions_str = ini_get( 'disable_functions' );

		if ( empty( $disabled_functions_str ) ) {
			return null; // No functions disabled.
		}

		// Parse disabled functions list.
		$disabled_functions = array_map( 'trim', explode( ',', $disabled_functions_str ) );

		// WordPress-critical functions.
		$critical_functions = array(
			'file_get_contents',
			'file_put_contents',
			'fopen',
			'fread',
			'fwrite',
			'fclose',
			'curl_init',
			'curl_exec',
			'fsockopen',
			'pfsockopen',
			'stream_socket_client',
			'socket_create',
			'exec',
			'shell_exec',
			'system',
			'passthru',
			'proc_open',
			'popen',
		);

		// Check which critical functions are disabled.
		$disabled_critical = array_intersect( $critical_functions, $disabled_functions );

		if ( ! empty( $disabled_critical ) ) {
			$severity     = count( $disabled_critical ) > 5 ? 'medium' : 'low';
			$threat_level = count( $disabled_critical ) > 5 ? 40 : 35;

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: Number of disabled functions, 2: List of disabled functions */
					_n(
						'%1$d critical PHP function is disabled: %2$s. This may break WordPress features like file uploads, HTTP requests, or plugin installations.',
						'%1$d critical PHP functions are disabled: %2$s. This may break WordPress features like file uploads, HTTP requests, or plugin installations.',
						count( $disabled_critical ),
						'wpshadow'
					),
					count( $disabled_critical ),
					implode( ', ', $disabled_critical )
				),
				'severity'    => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_disabled_functions',
				'meta'        => array(
					'disabled_functions'  => $disabled_functions,
					'disabled_critical'   => $disabled_critical,
					'critical_count'      => count( $disabled_critical ),
				),
			);
		}

		// Check for security-oriented disabled functions (good practice).
		$security_functions = array(
			'exec',
			'shell_exec',
			'system',
			'passthru',
			'proc_open',
			'popen',
			'pcntl_exec',
			'eval',
		);

		$disabled_security = array_intersect( $security_functions, $disabled_functions );

		if ( ! empty( $disabled_security ) && count( $disabled_critical ) === 0 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: List of disabled security functions */
					__( 'Security-sensitive PHP functions are disabled: %s. This is good security practice, but may prevent some advanced plugins from working.', 'wpshadow' ),
					implode( ', ', $disabled_security )
				),
				'severity'    => 'info',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/php_disabled_functions',
				'meta'        => array(
					'disabled_functions'  => $disabled_functions,
					'disabled_security'   => $disabled_security,
				),
			);
		}

		// Disabled functions don't affect WordPress core functionality.
		return null;
	}
}
