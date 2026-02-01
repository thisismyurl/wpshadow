<?php
/**
 * Theme Code Injection Vulnerabilities Diagnostic
 *
 * Scans theme files for potential code injection patterns.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Code Injection Vulnerabilities Diagnostic
 *
 * Flags risky eval/exec patterns in theme files.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Theme_Code_Injection_Vulnerabilities extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-code-injection-vulnerabilities';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Code Injection Vulnerabilities';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Scans theme files for potential code injection patterns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = wp_get_theme()->get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( ! file_exists( $functions_file ) ) {
			return null;
		}

		$content = file_get_contents( $functions_file, false, null, 0, 60000 );
		if ( false === $content ) {
			return null;
		}

		$patterns = array(
			'eval(',
			'system(',
			'exec(',
			'passthru(',
			'shell_exec(',
		);

		$matches = array();
		foreach ( $patterns as $pattern ) {
			if ( false !== strpos( $content, $pattern ) ) {
				$matches[] = $pattern;
			}
		}

		if ( empty( $matches ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Risky code execution patterns detected in theme', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/theme-code-injection-vulnerabilities',
			'details'      => array(
				'matches' => $matches,
			),
		);
	}
}
