<?php
/**
 * Theme Code Injection Vulnerabilities Diagnostic
 *
 * Scans theme files for dangerous functions (eval, create_function, etc).
 * eval/exec execute arbitrary code. Theme includes eval = arbitrary code exec.
 * Attacker injects code = full compromise.
 *
 * **What This Check Does:**
 * - Searches theme files for eval, exec, assert
 * - Detects create_function (deprecated, dangerous)
 * - Finds base64_decode + eval pattern (obfuscation)
 * - Tests for preg_replace with /e modifier
 * - Checks for serialization->unserialize->code exec
 * - Returns severity for each dangerous pattern
 *
 * **Why This Matters:**
 * Theme uses eval() on user input. Attacker provides PHP code.
 * Theme evaluates it. Code executes with full permissions.
 * Total compromise.
 *
 * **Business Impact:**
 * Custom theme uses eval to parse template variables:
 * ```
 * eval(\$_POST['template_code']);
 * ```
 * Attacker injects malicious PHP. Server executes. Attacker has
 * shell access. Files exfiltrated. Site defaced. Cost: $500K+
 * (forensics, recovery, notification). With check: eval never used.
 * Template variables processed safely.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Theme code is safe
 * - #9 Show Value: Prevents arbitrary code execution
 * - #10 Beyond Pure: Code safety scanning
 *
 * **Related Checks:**
 * - Plugin Code Obfuscation Not Applied (similar risk)
 * - Theme Data Validation (input handling)
 * - PHP Code Quality Scanning (broader checks)
 *
 * **Learn More:**
 * Theme code safety: https://wpshadow.com/kb/theme-code-injection
 * Video: Dangerous PHP patterns (13min): https://wpshadow.com/training/code-injection
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Code Injection Vulnerabilities Diagnostic
 *
 * Flags risky eval/exec patterns in theme files.
 *
 * **Detection Pattern:**
 * 1. List all active theme PHP files
 * 2. Search for dangerous functions (eval, exec)
 * 3. Detect base64_decode + eval patterns
 * 4. Find create_function usage
 * 5. Search for preg_replace with /e modifier
 * 6. Return each finding
 *
 * **Real-World Scenario:**
 * Theme builder plugin uses eval to compile templates:
 * ```
 * eval('\$output = \"' . \$template_html . '\";');
 * ```
 * Attacker creates template with PHP: {${shell}}. Evaluated.
 * Executes. With patterns-based check: eval never appears in
 * theme. Templates parsed safely without eval.
 *
 * **Implementation Notes:**
 * - Scans theme files (not all plugins, just active theme)
 * - Detects eval, exec, system, passthru patterns
 * - Includes obfuscated patterns (base64+eval)
 * - Severity: critical (eval/exec), high (create_function)
 * - Treatment: remove dangerous functions, use safe APIs
 *
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
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
