<?php
/**
 * Query String Parameter Not Sanitized Diagnostic
 *
 * Checks if query parameters are properly sanitized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Query String Parameter Not Sanitized Diagnostic Class
 *
 * Detects unsan itized query parameters.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Query_String_Parameter_Not_Sanitized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-string-parameter-not-sanitized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Query String Parameter Not Sanitized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if query parameters are sanitized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for security plugins that validate input
		$security_plugins = array(
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
		);

		$security_active = false;
		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$security_active = true;
				break;
			}
		}

		if ( ! $security_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'No plugin is validating query parameters. Ensure custom plugins sanitize all input using sanitize_*() functions.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/query-string-parameter-not-sanitized',
			);
		}

		return null;
	}
}
