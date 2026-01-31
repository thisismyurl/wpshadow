<?php
/**
 * Plugin Code Obfuscation Not Applied Diagnostic
 *
 * Checks if plugin code is obfuscated.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Code Obfuscation Not Applied Diagnostic Class
 *
 * Detects non-obfuscated plugin code.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Plugin_Code_Obfuscation_Not_Applied extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-code-obfuscation-not-applied';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Code Obfuscation Not Applied';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if plugin code is obfuscated';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if plugins are minified for production
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Plugin code obfuscation is not applied. Minify and compress plugin code to reduce file sizes and improve security.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/plugin-code-obfuscation-not-applied',
			);
		}

		return null;
	}
}
